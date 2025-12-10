<?php
header('Content-Type: application/json');

session_save_path(__DIR__ . '/sessions');
session_start();

$host = "localhost";
$db = "empresa";
$user = "root";
$pass = "";
$respuesta = [];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        $respuesta = ['success' => false, 'error' => 'Método de solicitud no permitido.'];
        echo json_encode($respuesta);
        exit;
    }

    $usuario_id = $_SESSION['user_id'] ?? null;
    if (!$usuario_id) {
        $respuesta = ['success' => false, 'error' => 'Debe iniciar sesión para publicar un producto.'];
        echo json_encode($respuesta);
        exit;
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = $_POST['precio'] ?? '';
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $estado = trim($_POST['estado'] ?? '');

    if ($nombre === '' || $descripcion === '' || $precio === '' || $ubicacion === '' || $estado === '' || empty($_FILES['imagenes'])) {
        $respuesta = ['success' => false, 'error' => 'Faltan datos obligatorios en el formulario.'];
        echo json_encode($respuesta);
        exit;
    }

    if (!is_numeric($precio)) {
        $respuesta = ['success' => false, 'error' => 'Precio inválido.'];
        echo json_encode($respuesta);
        exit;
    }

    $files = $_FILES['imagenes'];
    if (!is_array($files['name'])) {
        $respuesta = ['success' => false, 'error' => 'Error al procesar las imágenes.'];
        echo json_encode($respuesta);
        exit;
    }

    $totalArchivos = count($files['name']);
    $indicesValidos = [];
    for ($i = 0; $i < $totalArchivos; $i++) {
        if ($files['name'][$i] === '') {
            continue;
        }
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $respuesta = ['success' => false, 'error' => 'Error al subir una de las imágenes. Código: ' . $files['error'][$i]];
            echo json_encode($respuesta);
            exit;
        }
        $indicesValidos[] = $i;
    }

    if (count($indicesValidos) === 0) {
        $respuesta = ['success' => false, 'error' => 'Debe subir al menos una imagen.'];
        echo json_encode($respuesta);
        exit;
    }

    if (count($indicesValidos) > 10) {
        $respuesta = ['success' => false, 'error' => 'Solo se permiten hasta 10 imágenes por producto.'];
        echo json_encode($respuesta);
        exit;
    }

    $allowed = ['jpg','jpeg','png','webp','gif'];
    $directorioUploads = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    if (!is_dir($directorioUploads)) {
        mkdir($directorioUploads, 0755, true);
    }

    $rutasWeb = [];

    foreach ($indicesValidos as $idx) {
        $originalName = basename($files['name'][$idx]);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            $respuesta = ['success' => false, 'error' => 'Una de las imágenes tiene un tipo de archivo no permitido.'];
            echo json_encode($respuesta);
            exit;
        }

        $nombreUnico = uniqid('prod_', true) . '.' . $ext;
        $rutaDestinoFS = $directorioUploads . $nombreUnico;
        $rutaDestinoWeb = 'uploads/' . $nombreUnico;

        if (!is_uploaded_file($files['tmp_name'][$idx]) || !move_uploaded_file($files['tmp_name'][$idx], $rutaDestinoFS)) {
            $respuesta = ['success' => false, 'error' => 'No se pudo guardar una de las imágenes en el servidor.'];
            echo json_encode($respuesta);
            exit;
        }

        $rutasWeb[] = $rutaDestinoWeb;
    }

    if (count($rutasWeb) === 0) {
        $respuesta = ['success' => false, 'error' => 'No se pudo procesar ninguna imagen.'];
        echo json_encode($respuesta);
        exit;
    }

    $precio_val = floatval($precio);
    $imagenPrincipal = $rutasWeb[0];

    $sql = "INSERT INTO productos (usuario_id, nombre, descripcion, precio, imagen, ubicacion, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdsss", $usuario_id, $nombre, $descripcion, $precio_val, $imagenPrincipal, $ubicacion, $estado);
    $stmt->execute();
    $inserted_id = $conn->insert_id;
    $stmt->close();

    $sqlImg = "INSERT INTO producto_imagenes (producto_id, ruta) VALUES (?, ?)";
    $stmtImg = $conn->prepare($sqlImg);
    foreach ($rutasWeb as $ruta) {
        $stmtImg->bind_param("is", $inserted_id, $ruta);
        $stmtImg->execute();
    }
    $stmtImg->close();

    $respuesta = ['success' => true, 'message' => 'Producto guardado correctamente.', 'id' => $inserted_id];

} catch (mysqli_sql_exception $e) {
    $respuesta = ['success' => false, 'error' => 'Error de Base de Datos: ' . $e->getMessage()];
} catch (Exception $e) {
    $respuesta = ['success' => false, 'error' => 'Error General: ' . $e->getMessage()];
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    echo json_encode($respuesta);
}

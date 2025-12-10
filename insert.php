<?php
$servidor = 'localhost';
$usuario = 'root';
$contraseña = '';
$base_de_datos = 'empresa';

session_save_path(__DIR__ . '/sessions');
session_start();

$conexion = new mysqli($servidor, $usuario, $contraseña, $base_de_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if (isset($_POST['nombre'], $_POST['username'], $_POST['correo'], $_POST['contrasena'])) {
    $nombre = $_POST['nombre'];
    $username = $_POST['username'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $telefono = $_POST['telefono'] ?? null;

    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

    $query = "INSERT INTO usuarios (nombre, username, correo_electronico, contrasena, telefono) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('sssss', $nombre, $username, $correo, $hashed_password, $telefono);

    if ($stmt->execute()) {
        header("Location: intro.html"); 
        exit();
    } else {
        echo "Error al registrar el usuario: " . $stmt->error;
    }
    $stmt->close();
} else {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
        exit;
    }

    $current_user_id = $_SESSION['user_id'];
    $action = $_REQUEST['action'] ?? '';
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    switch ($action) {
        case 'load_profile':
            $stmt = $conexion->prepare("SELECT id, nombre, username, correo_electronico, biografia, telefono, idioma FROM usuarios WHERE id = ?");
            $stmt->bind_param('i', $current_user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            $stmt->close();

            if ($user_data) {
                $response = [
                    'success' => true,
                    'user' => $user_data,
                    'activity' => [
                        'compras' => [],
                        'favoritos' => []
                    ]
                ];
                echo json_encode($response);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
            }
            break;

        case 'update_info':
            $nombre = $data['nombre'] ?? '';
            $username = $data['username'] ?? '';
            $biografia = $data['biografia'] ?? '';
            $telefono = $data['telefono'] ?? '';

            $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, username = ?, biografia = ?, telefono = ? WHERE id = ?");
            $stmt->bind_param('ssssi', $nombre, $username, $biografia, $telefono, $current_user_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Información actualizada correctamente.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al actualizar información.']);
            }
            $stmt->close();
            break;

        case 'change_password':
            $current_password = $data['current_password'] ?? '';
            $new_password = $data['new_password'] ?? '';
            $confirm_password = $data['confirm_password'] ?? '';

            if ($new_password !== $confirm_password) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Las nuevas contraseñas no coinciden.']);
                exit;
            }

            $stmt = $conexion->prepare("SELECT contrasena FROM usuarios WHERE id = ?");
            $stmt->bind_param('i', $current_user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($current_password, $user['contrasena'])) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
                $stmt->bind_param('si', $hashed_new_password, $current_user_id);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Contraseña cambiada con éxito.']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error al guardar la nueva contraseña.']);
                }
                $stmt->close();
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Contraseña actual incorrecta.']);
            }
            break;

        case 'update_preferences':
            $idioma = $data['idioma'] ?? 'es';

            $stmt = $conexion->prepare("UPDATE usuarios SET idioma = ? WHERE id = ?");
            $stmt->bind_param('si', $idioma, $current_user_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Preferencias guardadas.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al guardar preferencias.']);
            }
            $stmt->close();
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
            break;
    }
}

$conexion->close();
?>
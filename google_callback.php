<?php
session_save_path(__DIR__ . '/sessions');
session_start();

$servidor = 'localhost';
$usuario = 'root';
$contraseña = '';
$base_de_datos = 'empresa';

$conexion = new mysqli($servidor, $usuario, $contraseña, $base_de_datos);

if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit();
}

header('Content-Type: application/json');

if (isset($_POST['credential'])) {
    $id_token = $_POST['credential'];
    
    $payload_parts = explode('.', $id_token);
    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload_parts[1])));

    if (isset($payload->email)) {
        $email = $payload->email;
        $nombre = $payload->name ?? 'Usuario Google';
        $google_id = $payload->sub;

        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo_electronico = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
        } else {
            $stmt_insert = $conexion->prepare("INSERT INTO usuarios (nombre, correo_electronico, google_id) VALUES (?, ?, ?)");
            $stmt_insert->bind_param('sss', $nombre, $email, $google_id);
            $stmt_insert->execute();
            $user_id = $stmt_insert->insert_id;
            $stmt_insert->close();
        }
        $stmt->close();

        $_SESSION['user_id'] = $user_id;
        
        session_write_close();
        $conexion->close();
        
        echo json_encode(['success' => true]);
        exit();

    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Token de Google inválido.']);
        exit();
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No se recibió la credencial.']);
    exit();
}
?>
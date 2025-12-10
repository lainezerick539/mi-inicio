<?php
$conexion = new mysqli('localhost', 'root', '', 'empresa');

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.'
];

if ($conexion->connect_error) {
    $response['message'] = "Error de conexión: " . $conexion->connect_error;
} else if (isset($_POST['token'], $_POST['contrasena'])) {
    $token = $_POST['token'];
    $nueva = $_POST['contrasena'];

    $stmt = $conexion->prepare("SELECT id, token_expira FROM usuarios WHERE token_recuperacion=?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (strtotime($row['token_expira']) > time()) {
            
            $hashed_password = password_hash($nueva, PASSWORD_DEFAULT);

            $update = $conexion->prepare("UPDATE usuarios SET contrasena=?, token_recuperacion=NULL, token_expira=NULL WHERE id=?");
            
            $update->bind_param('si', $hashed_password, $row['id']);
            
            if ($update->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Contraseña actualizada correctamente.';
            } else {
                $response['message'] = 'Error al actualizar la contraseña en la base de datos.';
            }
            $update->close();
        } else {
            $response['message'] = 'El enlace ha expirado.';
        }
    } else {
        $response['message'] = 'Token inválido.';
    }
    $stmt->close();
} else {
    $response['message'] = 'Datos incompletos. Se requiere token y contraseña.';
}

$conexion->close();

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
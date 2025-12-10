<?php
session_save_path(__DIR__ . '/sessions');
session_start();

$servidor = 'localhost';
$usuario = 'root';
$contraseña = '';
$base_de_datos = 'empresa';

$conexion = new mysqli($servidor, $usuario, $contraseña, $base_de_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if (isset($_POST['correo'], $_POST['contrasena'])) {
    $login_identifier = $_POST['correo'];
    $password = $_POST['contrasena'];

    $query = "SELECT id, nombre, contrasena FROM usuarios WHERE correo_electronico = ? OR username = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('ss', $login_identifier, $login_identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['contrasena'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['nombre'];
            
            $conexion->close(); 
            header("Location: login_success.html"); 
            exit();
        }
    }
    
    $_SESSION['error_login'] = "Correo o contraseña incorrectos.";
    $conexion->close(); 
    header("Location: login.php");
    exit();

} else {
    $_SESSION['error_login'] = "Por favor, completa todos los campos.";
    $conexion->close(); 
    header("Location: login.php");
    exit();
}
?>
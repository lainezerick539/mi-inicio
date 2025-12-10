<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$conexion = new mysqli('localhost', 'root', '', 'empresa');
if ($conexion->connect_error) {
    echo "Error de conexión con la base de datos.";
    exit;
}

if (isset($_POST['correo'])) {
    $correo = $_POST['correo'];

    $check = $conexion->prepare("SELECT id FROM usuarios WHERE correo_electronico = ?");
    $check->bind_param('s', $correo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $token = bin2hex(random_bytes(16));
        $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conexion->prepare("UPDATE usuarios SET token_recuperacion=?, token_expira=? WHERE correo_electronico=?");
        $update->bind_param('sss', $token, $expira, $correo);
        $update->execute();

        $enlace = "http://localhost/ShopGo/restablecer.html?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'shopgosv@gmail.com';
            $mail->Password = 'stnlrgabwnjjrbqg';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('shopgosv@gmail.com', 'ShopGo - Recuperación');
            $mail->addAddress($correo);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = '🔐 Recuperación de contraseña | ShopGo';
            $mail->Body = "
                <div style='font-family:Arial,Helvetica,sans-serif;color:#333;background:#f9f9f9;padding:25px;border-radius:8px;max-width:600px;margin:auto;'>
                    <h2 style='color:#6a5acd;text-align:center;'>Restablece tu contraseña</h2>
                    <p>Hola,</p>
                    <p>Hemos recibido una solicitud para restablecer tu contraseña en <strong>ShopGo</strong>.</p>
                    <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                    <div style='text-align:center;margin:30px 0;'>
                        <a href='$enlace' style='background:#6a5acd;color:#fff;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:bold;'>Restablecer contraseña</a>
                    </div>
                    <p>Si no realizaste esta solicitud, puedes ignorar este mensaje.</p>
                    <p style='font-size:12px;color:#777;'>Este enlace expirará en 1 hora por motivos de seguridad.</p>
                </div>
            ";

            $mail->send();
            echo "Correo enviado correctamente. Revisa tu bandeja de entrada.";
        } catch (Exception $e) {
            echo "Error al enviar el correo. Por favor, intenta nuevamente.";
        }
    } else {
        echo "El correo no está registrado en el sistema.";
    }

    $check->close();
}

$conexion->close();
?>

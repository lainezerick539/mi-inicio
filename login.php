<?php 
session_save_path(__DIR__ . '/sessions'); 
session_start(); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="login.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <div class="glow"></div>
    <div class="login-box">
        <h2>LOGIN</h2>
        
        <?php
        
        if (isset($_SESSION['error_login'])) {
            
            echo "<div style='color: #ff5555; background: rgba(255,255,255,0.1); padding:10px; border-radius:8px; margin-bottom:15px; text-align:center; font-weight: bold;'>
                      " . $_SESSION['error_login'] . "
                  </div>";
            unset($_SESSION['error_login']); 
        }
        
        ?>

        <form action="verificar_login.php" method="post"> 
            <input type="email" id="correo" name="correo" placeholder="Username" required />
            <input type="password" id="contrasena" name="contrasena" placeholder="Password" required />
            <div class="button-group">
                <input type="submit" value="Log In" />
                <a href="registro.html" class="register-btn">Registro</a>
            </div>
        </form>

        <div class="google-btn">
            <div id="g_id_onload"
                data-client_id="644819228817-vi6akujhhqs6fd2l72a59snp8gcjojum.apps.googleusercontent.com"
                data-context="signin"
                data-ux_mode="redirect"
                data-login_uri="http://localhost/ShopGo/menu.html"
                data-auto_prompt="false">
            </div>
            <div class="g_id_signin"
                data-type="standard"
                data-shape="pill"
                data-theme="filled_black"
                data-text="signin_with"
                data-size="large"
                data-logo_alignment="left">
            </div>
        </div>

        <div class="extra-links">
            <a href="recuperar.html">¿Has olvidado tu contraseña?</a>
        </div>
    </div>

    <script src="login.js"></script>
</body>
</html>
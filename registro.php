<?php
session_start();
if (isset($_SESSION['registro_msg'])) {
    echo "<div style='color: #90ee90; background: rgba(255,255,255,0.1); padding:10px; border-radius:8px; margin-bottom:15px; text-align:center;'>
            " . $_SESSION['registro_msg'] . "
          </div>";
    unset($_SESSION['registro_msg']);
}
?>

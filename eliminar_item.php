<?php
session_save_path(__DIR__ . '/sessions');
session_start();

$id = intval($_POST['id']);

if (isset($_SESSION['carrito'])) {
    $index = array_search($id, $_SESSION['carrito']);
    if ($index !== false) {
        unset($_SESSION['carrito'][$index]);
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }
}

echo "ok";

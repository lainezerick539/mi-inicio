<?php
session_save_path(__DIR__ . '/sessions');
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$id = intval($_POST['id'] ?? 0);

if ($id > 0) {
    $_SESSION['carrito'][] = $id;
    echo "ok";
} else {
    echo "error";
}

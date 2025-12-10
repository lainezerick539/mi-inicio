<?php
session_save_path(__DIR__ . '/sessions');
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: mis_productos.php");
    exit;
}

$id = intval($_GET['id']);

$conn = new mysqli("localhost","root","","empresa");
$conn->set_charset("utf8mb4");

$stmt = $conn->prepare("DELETE FROM producto_imagenes WHERE producto_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$stmt2 = $conn->prepare("DELETE FROM productos WHERE id=? AND usuario_id=?");
$stmt2->bind_param("ii", $id, $_SESSION['user_id']);
$stmt2->execute();
$stmt2->close();

header("Location: mis_productos.php");
exit;

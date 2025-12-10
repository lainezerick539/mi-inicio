<?php
header('Content-Type: application/json; charset=utf-8');
$host = 'localhost';
$db = 'empresa';
$user = 'root';
$pass = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { echo json_encode(['success'=>false]); exit; }
$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8mb4');
$stmt = $conn->prepare("SELECT id, nombre, descripcion, precio, ubicacion, imagen, created_at FROM productos WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    echo json_encode(['success'=>true, 'product'=>$row]);
} else {
    echo json_encode(['success'=>false]);
}
$stmt->close();
$conn->close();
?>

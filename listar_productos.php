<?php
header('Content-Type: application/json');
$conexion = new mysqli("localhost","root","","empresa");
$conexion->set_charset("utf8mb4");

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

if ($search !== "") {
    $sql = "SELECT id, nombre, descripcion, precio, imagen, ubicacion 
            FROM productos 
            WHERE nombre LIKE ? OR descripcion LIKE ?
            ORDER BY id DESC";
    $stmt = $conexion->prepare($sql);
    $like = "%".$search."%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conexion->query("SELECT id, nombre, descripcion, precio, imagen, ubicacion 
                             FROM productos 
                             ORDER BY id DESC");
}

$productos = [];
while ($row = $res->fetch_assoc()) {
    $productos[] = $row;
}

echo json_encode(["success"=>true, "products"=>$productos]);
?>

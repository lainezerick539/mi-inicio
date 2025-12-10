<?php
session_save_path(__DIR__.'/sessions');
session_start();

if (!isset($_SESSION['user_id'])) { 
    echo "nologin"; 
    exit; 
}

if (!isset($_GET['id'])) { 
    echo "error"; 
    exit; 
}

$u = $_SESSION['user_id'];
$p = intval($_GET['id']);

$conn = new mysqli("localhost","root","","empresa");
$conn->set_charset("utf8mb4");

$q = $conn->prepare("SELECT id FROM favoritos WHERE usuario_id=? AND producto_id=?");
$q->bind_param("ii", $u, $p);
$q->execute();
$r = $q->get_result();

if ($r->num_rows > 0) {
    $del = $conn->prepare("DELETE FROM favoritos WHERE usuario_id=? AND producto_id=?");
    $del->bind_param("ii", $u, $p);
    $del->execute();
    echo "removed";
} else {
    $ins = $conn->prepare("INSERT INTO favoritos(usuario_id,producto_id) VALUES (?,?)");
    $ins->bind_param("ii", $u, $p);
    $ins->execute();
    echo "added";
}

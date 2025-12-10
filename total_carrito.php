<?php
session_save_path(__DIR__.'/sessions');
session_start();
$total = 0;
if (!empty($_SESSION['carrito'])) {
    $conn = new mysqli("localhost","root","","empresa");
    $ids = implode(",", array_map("intval", $_SESSION['carrito']));
    $res = $conn->query("SELECT precio FROM productos WHERE id IN ($ids)");
    while ($row = $res->fetch_assoc()) $total += $row['precio'];
}
echo number_format($total,2);

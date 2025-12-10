<?php
session_save_path(__DIR__ . '/sessions');
session_start();
if (!isset($_SESSION['user_id'])) { exit; }

$conn = new mysqli("localhost", "root", "", "empresa");

$conv = intval($_POST['conversacion_id']);
$mensaje = $_POST['mensaje'];
$rem = $_SESSION['user_id'];

$q = $conn->query("SELECT user1, user2 FROM conversaciones WHERE id = $conv");
$row = $q->fetch_assoc();

$receptor = ($row['user1'] == $rem) ? $row['user2'] : $row['user1'];

$stmt = $conn->prepare("INSERT INTO mensajes (conversacion_id, remitente_id, receptor_id, mensaje) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $conv, $rem, $receptor, $mensaje);
$stmt->execute();

header("Location: chat.php?id=$conv");

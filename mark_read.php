<?php
session_save_path(__DIR__ . '/sessions');
session_start();
if(!isset($_SESSION['user_id'])) exit;
$me = (int)$_SESSION['user_id'];
$conv = (int)($_POST['c'] ?? 0);
$mysqli = new mysqli("localhost","root","","empresa");
$stmt = $mysqli->prepare("UPDATE mensajes SET leido=1 WHERE conversacion_id=? AND receptor_id=?");
$stmt->bind_param("ii",$conv,$me);
$stmt->execute();

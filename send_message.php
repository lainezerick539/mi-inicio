<?php
session_save_path(__DIR__ . '/sessions');
session_start();
if(!isset($_SESSION['user_id'])) exit("noauth");
$me = (int)$_SESSION['user_id'];
$conv = (int)($_POST['c'] ?? 0);
$msg = trim($_POST['message'] ?? '');
if($msg==='') exit("empty");
$mysqli = new mysqli("localhost","root","","empresa");
$stmt = $mysqli->prepare("SELECT user1,user2 FROM conversaciones WHERE id=?");
$stmt->bind_param("i",$conv);
$stmt->execute();
$c = $stmt->get_result()->fetch_assoc();
$receptor = ($c['user1']==$me)?$c['user2']:$c['user1'];
$stmt = $mysqli->prepare("INSERT INTO mensajes (conversacion_id, remitente_id, receptor_id, mensaje, enviado, leido) VALUES (?,?,?,?,NOW(),0)");
$stmt->bind_param("iiis",$conv,$me,$receptor,$msg);
$stmt->execute();
echo "ok";

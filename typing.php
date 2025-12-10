<?php
session_save_path(__DIR__ . '/sessions');
session_start();
if(!isset($_SESSION['user_id'])) exit;
$me = (int)$_SESSION['user_id'];
$conv = (int)($_REQUEST['c'] ?? 0);
$file = __DIR__."/typing_".$conv.".json";
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['stop'])){ @unlink($file); exit; }
    file_put_contents($file,json_encode(["user"=>$me,"time"=>time()]));
    exit;
}
if(file_exists($file)){
    $d = json_decode(file_get_contents($file),true);
    if($d && (time()-$d['time']<4) && $d['user']!=$me){
        $mysqli = new mysqli("localhost","root","","empresa");
        $stmt = $mysqli->prepare("SELECT nombre FROM usuarios WHERE id=?");
        $stmt->bind_param("i",$d['user']);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        echo htmlspecialchars($r['nombre'])." está escribiendo...";
        exit;
    }
}
echo "";

<?php
session_save_path(__DIR__ . '/sessions');
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$me = (int)$_SESSION['user_id'];
$mysqli = new mysqli("localhost", "root", "", "empresa");

function svg_default_avatar($initial = '') {
    $bg = "#2b2f36";
    $fg = "#ffffff";
    $initial = $initial ? strtoupper(htmlspecialchars($initial[0])) : '';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><rect width="100%" height="100%" fill="'.$bg.'"/><text x="50%" y="52%" font-size="52" fill="'.$fg.'" dominant-baseline="middle" text-anchor="middle" font-family="Arial, Helvetica, sans-serif">'.$initial.'</text></svg>';
    return 'data:image/svg+xml;base64,'.base64_encode($svg);
}

if (isset($_GET['user'])) {
    $other = (int)$_GET['user'];
    if ($other !== $me) {
        $s = $mysqli->prepare("SELECT id FROM conversaciones WHERE (user1=? AND user2=?) OR (user1=? AND user2=?)");
        $s->bind_param("iiii", $me, $other, $other, $me);
        $s->execute();
        $r = $s->get_result();
        if ($r->num_rows === 1) {
            $id = $r->fetch_assoc()['id'];
            header("Location: chat.php?c=".$id);
            exit;
        }
        $n = $mysqli->prepare("INSERT INTO conversaciones (user1,user2) VALUES (?,?)");
        $n->bind_param("ii", $me, $other);
        $n->execute();
        header("Location: chat.php?c=".$n->insert_id);
        exit;
    }
}

$c = (int)($_GET['c'] ?? 0);

$chk = $mysqli->prepare("SELECT * FROM conversaciones WHERE id=? AND (user1=? OR user2=?)");
$chk->bind_param("iii", $c, $me, $me);
$chk->execute();
$res = $chk->get_result();
if ($res->num_rows !== 1) { echo "No puedes ver esta conversación."; exit; }

$data = $res->fetch_assoc();
$other = ($data['user1']==$me)?$data['user2']:$data['user1'];

$q = $mysqli->prepare("SELECT nombre, perfil FROM usuarios WHERE id=?");
$q->bind_param("i", $other);
$q->execute();
$u = $q->get_result()->fetch_assoc();

$name = $u['nombre'] ?? '';
if (!empty($u['perfil'])) {
    $pf = "chat_images/".$u['perfil'];
} else {
    $pf = svg_default_avatar($name);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="chat.css">
</head>
<body>

<div class="chat-app">

<aside class="sidebar">
<iframe src="conversaciones.php" class="convs-iframe"></iframe>
</aside>

<section class="chat-panel">

<header class="chat-header">
    <img src="<?=$pf?>" class="avatar-lg" alt="avatar">
    <div class="head-info">
        <div class="head-name">
            <a href="vendedor.php?id=<?=$other?>" style="color:white;text-decoration:none;">
                <?=htmlspecialchars($name)?>
            </a>
        </div>
        <div id="typing" class="typing"></div>
    </div>
</header>

<div id="messages" class="messages" data-conv="<?=$c?>"></div>

<div class="composer">
    <button id="emojiBtn">😊</button>
    <textarea id="textInput" placeholder="Escribe un mensaje..."></textarea>

    <input id="imageInput" type="file" accept="image/*" hidden>
    <button id="imgBtn">📷</button>

    <input id="audioInput" type="file" accept="audio/*" capture="microphone" hidden>
    <button id="recBtn">🎙️</button>

    <button id="sendBtn">Enviar</button>
</div>

</section>

</div>

<script>
window.ME = <?=$me?>;
</script>
<script src="chat.js"></script>
</body>
</html>

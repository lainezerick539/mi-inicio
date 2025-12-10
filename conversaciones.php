<?php
session_save_path(__DIR__ . '/sessions');
session_start();
if (!isset($_SESSION['user_id'])) { exit; }

$me = (int)$_SESSION['user_id'];
$mysqli = new mysqli("localhost", "root", "", "empresa");

function svg_default_avatar($initial = '') {
    $bg = "#2b2f36";
    $fg = "#ffffff";
    $initial = $initial ? strtoupper(htmlspecialchars($initial[0])) : '';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80">
    <rect width="100%" height="100%" fill="'.$bg.'"/>
    <text x="50%" y="52%" font-size="34" fill="'.$fg.'" dominant-baseline="middle"
    text-anchor="middle" font-family="Arial">'.$initial.'</text></svg>';
    return 'data:image/svg+xml;base64,'.base64_encode($svg);
}

$sql = "SELECT 
c.id,
CASE WHEN c.user1=? THEN c.user2 ELSE c.user1 END AS other_id,
u.nombre,
u.perfil,
(SELECT mensaje FROM mensajes WHERE conversacion_id=c.id ORDER BY id DESC LIMIT 1) AS ultimo,
(SELECT enviado FROM mensajes WHERE conversacion_id=c.id ORDER BY id DESC LIMIT 1) AS tiempo,
(SELECT COUNT(*) FROM mensajes WHERE conversacion_id=c.id AND receptor_id=? AND leido=0) AS noleido
FROM conversaciones c
JOIN usuarios u
ON u.id = CASE WHEN c.user1=? THEN c.user2 ELSE c.user1 END
WHERE c.user1=? OR c.user2=?
ORDER BY tiempo DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("iiiii", $me, $me, $me, $me, $me);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="chat.css">
</head>
<body>

<div class="conv-wrapper">
    <h2 class="conv-title">Chats</h2>

    <input id="search" class="conv-search" placeholder="Buscar en Messenger">

    <div class="conv-tabs">
        <button class="tab active" data-filter="all">Todos</button>
        <button class="tab" data-filter="unread">No leídos</button>
    </div>

    <div id="conversations">
        <?php while($r=$res->fetch_assoc()):
            $name = $r['nombre'];
            $pf = !empty($r['perfil']) ? "chat_images/".$r['perfil'] : svg_default_avatar($name);
        ?>
        <a class="conv-item" href="chat.php?c=<?= $r['id'] ?>">
            <img src="<?= $pf ?>" class="conv-avatar">
            <div class="conv-meta">
                <div class="conv-name"><?= htmlspecialchars($name) ?></div>
                <div class="conv-last"><?= htmlspecialchars($r['ultimo']) ?></div>
            </div>
            <div class="conv-right">
                <div class="conv-time" data-time="<?= $r['tiempo'] ?>"></div>
                <?php if($r['noleido']>0): ?>
                <div class="conv-badge"><?= $r['noleido'] ?></div>
                <?php endif; ?>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
</div>

<script src="chat.js"></script>
</body>
</html>

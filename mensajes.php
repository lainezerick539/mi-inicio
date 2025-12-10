<?php
session_save_path(__DIR__ . '/sessions');
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$me = (int)$_SESSION['user_id'];
$mysqli = new mysqli("localhost", "root", "", "empresa");

$sql = "
SELECT 
    c.id AS conversacion_id,
    IF(c.user1 = $me, c.user2, c.user1) AS other_id,
    u.nombre AS other_name,
    m.mensaje,
    m.leido,
    m.enviado
FROM conversaciones c
INNER JOIN usuarios u 
    ON u.id = IF(c.user1 = $me, c.user2, c.user1)
INNER JOIN mensajes m 
    ON m.id = (
        SELECT id FROM mensajes 
        WHERE conversacion_id = c.id 
        ORDER BY enviado DESC 
        LIMIT 1
    )
WHERE c.user1 = $me OR c.user2 = $me
ORDER BY m.enviado DESC
";

$res = $mysqli->query($sql);
$convs = [];
while ($row = $res->fetch_assoc()) $convs[] = $row;
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Mensajes</title>
<style>
body { background:#141820; color:white; font-family:Arial; }
.container { width:90%; max-width:750px; margin:40px auto; background:#1f2430; padding:20px; border-radius:12px; }
.item {
    padding:14px;
    background:#2a2f3a;
    border-radius:10px;
    margin-bottom:12px;
    display:flex;
    justify-content:space-between;
    color:white;
    text-decoration:none;
}
.unread { font-weight:bold; color:#ffd200; }
</style>
</head>

<body>

<div class="container">
    <h2>Mis conversaciones</h2>

    <?php if (empty($convs)): ?>
        <p>No tienes conversaciones aún.</p>
    <?php else: ?>
        <?php foreach ($convs as $c): ?>
            <a class="item" href="chat.php?c=<?=$c['conversacion_id']?>">
                <div>
                    <div><?=$c['other_name']?></div>
                    <div class="<?=$c['leido']==0 ? 'unread' : ''?>">
                        <?=$c['mensaje']?>
                    </div>
                </div>
                <div><?=date("H:i", strtotime($c['enviado']))?></div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>

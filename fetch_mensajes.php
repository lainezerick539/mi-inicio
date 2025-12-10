<?php
session_save_path(__DIR__ . '/sessions');
session_start();

if (!isset($_SESSION['user_id'])) exit;

$me = $_SESSION['user_id'];

$mysqli = new mysqli("localhost", "root", "", "empresa");

$c = (int)($_GET['c'] ?? 0);

$q = $mysqli->prepare("
    SELECT m.*, u.nombre 
    FROM mensajes m 
    JOIN usuarios u ON u.id = m.remitente_id
    WHERE m.conversacion_id = ?
    ORDER BY m.id ASC
");
$q->bind_param("i", $c);
$q->execute();
$r = $q->get_result();

while($m = $r->fetch_assoc()):
    $isMe = ($m['remitente_id'] == $me);
    $class = $isMe ? "me" : "other";
?>
<div class="message <?= $class ?>">
    
    <span class="msg-info">
        <?= htmlspecialchars($m['nombre']) ?> · <?= date("H:i", strtotime($m['enviado'])) ?>
    </span>

    <?php if ($m['mensaje']): ?>
        <div class="msg-text"><?= nl2br(htmlspecialchars($m['mensaje'])) ?></div>
    <?php endif; ?>

    <?php if ($m['imagen']): ?>
        <img src="uploads/<?= $m['imagen'] ?>" class="msg-img">
    <?php endif; ?>

    <?php if ($m['audio']): ?>
        <audio controls>
            <source src="uploads/<?= $m['audio'] ?>" type="audio/webm">
        </audio>
    <?php endif; ?>

</div>
<?php endwhile; ?>

<?php
session_save_path(__DIR__ . '/sessions');
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost","root","","empresa");
$conn->set_charset("utf8mb4");

$sql = "
SELECT p.id, p.nombre, p.precio, p.imagen 
FROM favoritos f
JOIN productos p ON f.producto_id = p.id
WHERE f.usuario_id = ?
ORDER BY f.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();

function esc($v){ return htmlspecialchars($v??'', ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Favoritos</title>
<style>
body{margin:0;background:#111827;color:white;font-family:Inter,system-ui}
.container{max-width:1000px;margin:30px auto;padding:20px}
.card{background:#1f2937;padding:16px;border-radius:12px;margin-bottom:16px;display:flex;align-items:center;gap:20px}
.card img{width:110px;height:90px;border-radius:8px;object-fit:cover}
.btn{padding:10px 14px;border-radius:8px;text-decoration:none;color:white;cursor:pointer;font-weight:600}
.btn-view{background:#2563eb}
.btn-remove{background:#dc2626}
</style>
</head>
<body>

<div class="container">
<h2>Favoritos</h2>

<?php while($p = $res->fetch_assoc()): ?>
<div class="card">
<img src="<?php echo esc($p['imagen']); ?>" onerror="this.src='https://placehold.co/200x150'">

<div>
<div style="font-size:1.2rem;font-weight:bold"><?php echo esc($p['nombre']); ?></div>
<div style="margin:6px 0">$<?php echo number_format($p['precio'],2); ?></div>

<a href="producto_detalle.php?id=<?php echo $p['id']; ?>" class="btn btn-view">Ver</a>
<button class="btn btn-remove" onclick="removeFav(<?php echo $p['id']; ?>, this)">Quitar</button>
</div>
</div>
<?php endwhile; ?>

</div>

<script>
async function removeFav(id, btn){
    const res = await fetch("toggle_favorito.php?id="+id);
    const r = await res.text();

    if(r === "ok" || r === "removed"){
        btn.closest(".card").remove();
    }
}
</script>

</body>
</html>

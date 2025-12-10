<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$vendedor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($vendedor_id === 0) {
    header("Location: menu.php");
    exit;
}
$conn = new mysqli("localhost", "root", "", "empresa");
if ($conn->connect_error) {
    http_response_code(500);
    echo "DB connection error";
    exit;
}
$conn->set_charset("utf8mb4");
$sql_vendedor = "SELECT id, nombre, perfil, fecha_registro FROM usuarios WHERE id = ? LIMIT 1"; 
$stmt_vendedor = $conn->prepare($sql_vendedor);
$stmt_vendedor->bind_param("i", $vendedor_id);
$stmt_vendedor->execute();
$res_vendedor = $stmt_vendedor->get_result();
$vendedor = $res_vendedor->fetch_assoc();
$stmt_vendedor->close();
if (!$vendedor) {
    http_response_code(404);
    echo "Vendedor no encontrado.";
    exit;
}
$sql_productos = "SELECT id, nombre, precio, imagen, ubicacion FROM productos WHERE usuario_id = ? ORDER BY id DESC";
$stmt_productos = $conn->prepare($sql_productos);
$stmt_productos->bind_param("i", $vendedor_id);
$stmt_productos->execute();
$res_productos = $stmt_productos->get_result();
$productos = [];
while ($row = $res_productos->fetch_assoc()) {
    $productos[] = $row;
}
$stmt_productos->close();
$conn->close();
function esc($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }
$avatar_letra = strtoupper(substr($vendedor['nombre'] ?? 'V', 0, 1));
$avatar_color = hash('crc32', $vendedor['id'] ?? 0);
$avatar_url = "https://placehold.co/128x128/{$avatar_color}/ffffff?text={$avatar_letra}";
$miembro_desde_anio = $vendedor['fecha_registro'] ? date('Y', strtotime($vendedor['fecha_registro'])) : 'Desconocido'; 
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Perfil de <?php echo esc($vendedor['nombre']); ?></title>
    <style>
        :root{--bg:#111827;--card:#1f2937;--muted:#9ca3af;--accent:#2563eb;--price:#fcd34d;}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui;background:var(--bg);color:white}
        .container{max-width:1200px;margin:24px auto;padding:0 18px;display:grid;grid-template-columns:300px 1fr;gap:24px;align-items:start}
        .sidebar{background:var(--card);border-radius:12px;padding:20px;box-shadow:0 8px 30px rgba(0,0,0,0.1)}
        .vendedor-header{text-align:center;padding-bottom:15px;border-bottom:1px solid #374151;margin-bottom:20px}
        .vendedor-avatar{width:100px;height:100px;border-radius:50%;margin:0 auto 10px;background:#374151;display:flex;align-items:center;justify-content:center;overflow:hidden}
        .vendedor-avatar img{width:100%;height:100%;object-fit:cover}
        .vendedor-nombre{font-size:1.5rem;font-weight:700;margin:0}
        .vendedor-meta{font-size:0.95rem;color:var(--muted);margin-top:5px}
        .vendedor-perfil-desc{margin-top:15px;line-height:1.5;font-size:0.95rem;color:var(--muted)}
        .label{font-weight:700;color:white;display:block;margin-bottom:6px;font-size:0.95rem}
        .contact-btn{display:block;width:100%;padding:10px;border-radius:8px;background:var(--accent);color:white;text-align:center;text-decoration:none;margin-top:20px}
        .contact-btn:hover{opacity:0.9}
        .productos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px}
        .producto-card{background:var(--card);border-radius:12px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.1);transition:transform 0.2s}
        .producto-card:hover{transform:translateY(-3px)}
        .producto-card img{width:100%;height:200px;object-fit:cover;display:block}
        .producto-info{padding:15px}
        .producto-title{font-size:1.1rem;font-weight:600;margin:0 0 5px}
        .producto-price{color:var(--price);font-weight:800;font-size:1rem;margin-bottom:5px}
        .producto-meta{font-size:0.85rem;color:var(--muted)}
        .producto-link{text-decoration:none;color:inherit}
        .back-link{display:inline-block;margin-bottom:12px;color:var(--accent);text-decoration:none}
        .no-productos{padding:50px;text-align:center;color:var(--muted);background:var(--card);border-radius:12px}
        h2{font-size:1.5rem;margin-top:0;margin-bottom:20px}
        @media(max-width:980px){.container{grid-template-columns:1fr;padding:12px}.sidebar{order:2;margin-top:24px}}
    </style>
</head>
<body>

<a href="menu.php" style="position:fixed;left:16px;top:16px;z-index:2000">
    <img src="logo.png" style="width:40px;height:40px;border-radius:8px">
</a>

<div class="container">
    <aside class="sidebar">
        <a class="back-link" href="javascript:history.back()">← Volver</a>
        
        <div class="vendedor-header">
            <div class="vendedor-avatar">
                <img src="<?php echo $avatar_url; ?>" alt="<?php echo esc($vendedor['nombre']); ?>">
            </div>
            <h1 class="vendedor-nombre"><?php echo esc($vendedor['nombre']); ?></h1>
            <div class="vendedor-meta">Miembro desde <?php echo $miembro_desde_anio; ?></div>
        </div>
        
        <div class="vendedor-body">
            <div class="label">Descripción del vendedor</div>
            <p class="vendedor-perfil-desc">
                <?php echo nl2br(esc($vendedor['perfil'] ?? 'El vendedor aún no ha añadido una descripción.')); ?>
            </p>

            <a href="#" class="contact-btn" id="msg-vendedor">Enviar mensaje</a>
        </div>
    </aside>

    <main class="main-content">
        <h2>Otras Publicaciones de <?php echo esc($vendedor['nombre']); ?></h2>
        
        <?php if (!empty($productos)): ?>
            <div class="productos-grid">
                <?php foreach ($productos as $producto): ?>
                    <a href="producto_detalle.php?id=<?php echo (int)$producto['id']; ?>" class="producto-link">
                        <div class="producto-card">
                            <img src="<?php echo esc($producto['imagen']); ?>" alt="<?php echo esc($producto['nombre']); ?>" onerror="this.onerror=null;this.src='https://placehold.co/400x300/e0e0e0/555555?text=Sin+imagen'">
                            <div class="producto-info">
                                <div class="producto-title"><?php echo esc($producto['nombre']); ?></div>
                                <div class="producto-price">$<?php echo number_format(floatval($producto['precio']), 2); ?></div>
                                <div class="producto-meta"><?php echo esc($producto['ubicacion']); ?></div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-productos">
                Este vendedor aún no ha publicado otros productos.
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const vendedorNombre = <?php echo json_encode($vendedor['nombre']); ?>;
  const msgBtn = document.getElementById('msg-vendedor');
  
  if (msgBtn) {
    msgBtn.addEventListener('click', function(e){
      e.preventDefault();
      const text = encodeURIComponent('Hola ' + vendedorNombre + ', ¿podrías darme más información sobre tus productos publicados?');
      const whatsapp = 'https://wa.me/?text=' + text;
      window.open(whatsapp, '_blank');
    });
  }
});
</script>
</body>
</html>
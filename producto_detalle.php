<?php
session_save_path(__DIR__ . '/sessions');
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$conn = new mysqli("localhost","root","","empresa");
$conn->set_charset("utf8mb4");

$sql="
SELECT 
p.id,p.nombre,p.descripcion,p.precio,p.imagen,p.ubicacion,p.usuario_id,
u.nombre AS vendedor_nombre,u.perfil AS vendedor_perfil,u.fecha_registro AS vendedor_fecha_registro
FROM productos p
JOIN usuarios u ON p.usuario_id=u.id
WHERE p.id=? LIMIT 1
";

$stmt=$conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();
$res=$stmt->get_result();
$product=$res->fetch_assoc();
$stmt->close();

$imgs_stmt=$conn->prepare("SELECT ruta FROM producto_imagenes WHERE producto_id=?");
$imgs_stmt->bind_param("i",$id);
$imgs_stmt->execute();
$imgs_res=$imgs_stmt->get_result();
$imagenes=[];
while($row=$imgs_res->fetch_assoc()){ $imagenes[]=$row['ruta']; }
$imgs_stmt->close();

if(empty($imagenes)){ if(!empty($product['imagen'])) $imagenes[]=$product['imagen']; }

$fav=false;
if(isset($_SESSION['user_id'])){
    $q=$conn->prepare("SELECT id FROM favoritos WHERE usuario_id=? AND producto_id=?");
    $q->bind_param("ii",$_SESSION['user_id'],$id);
    $q->execute();
    $r=$q->get_result();
    $fav=$r->num_rows>0;
}

$conn->close();

if(!$product){ header("Location: menu.php"); exit; }

function esc($v){return htmlspecialchars($v??'',ENT_QUOTES);}
$miembro_desde_anio=date('Y',strtotime($product['vendedor_fecha_registro']));
$avatar_letra=strtoupper(substr($product['vendedor_nombre'],0,1));
$avatar_color=hash('crc32',$product['usuario_id']);
$avatar_url="https://placehold.co/128x128/{$avatar_color}/ffffff?text={$avatar_letra}";
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo esc($product['nombre']); ?></title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
*{box-sizing:border-box}
body{margin:0;background:#111827;color:white;font-family:Inter,system-ui}
.container{max-width:1200px;margin:24px auto;padding:0 18px;display:grid;grid-template-columns:1fr 380px;gap:24px}
.gallery{padding:18px;border-radius:12px;background:#1f2937;position:relative;overflow:hidden}
.save-btn{position:absolute;top:18px;right:18px;width:52px;height:40px;border-radius:12px;background:#2b2d31;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:20}
.save-btn svg{width:22px;height:22px;fill:white}
.saved{background:#111}
.main-img{height:540px;display:flex;justify-content:center;align-items:center;background:#000;border-radius:12px;position:relative;overflow:hidden}
.main-img img{max-width:100%;max-height:100%;transition:opacity .3s}
.arrow{position:absolute;top:50%;transform:translateY(-50%);width:48px;height:48px;border-radius:50%;background:#ffffff55;color:#000;display:flex;align-items:center;justify-content:center;font-size:28px;cursor:pointer;z-index:15}
.arrow:hover{background:white}
.arrow-left{left:10px}
.arrow-right{right:10px}
.thumb-row{display:flex;gap:10px;margin-top:12px;overflow-x:auto;padding-bottom:6px}
.thumb-row img{width:80px;height:65px;border-radius:6px;object-fit:cover;cursor:pointer;border:2px solid transparent}
.thumb-row img.active{border-color:#2563eb}
.card{background:#1f2937;padding:18px;border-radius:12px;margin-bottom:16px}
.label{font-weight:700;margin-bottom:6px}
.seller-header{display:flex;gap:12px;align-items:center;margin-top:16px}
.seller-avatar{width:56px;height:56px;border-radius:50%;overflow:hidden}
.seller-avatar img{width:100%;height:100%;object-fit:cover}
.map-wrap{height:220px;border-radius:12px;overflow:hidden;margin-top:12px}
.btn{padding:12px 18px;border-radius:10px;font-weight:600;text-decoration:none;cursor:pointer;display:inline-flex;align-items:center}
.btn-primary{background:#2563eb;color:white}
.btn-outline{border:2px solid #ccc;color:white;background:transparent}
.btn-red{background:#dc2626;color:white}
.btn-yellow{background:#ca8a04;color:white}
.fixed-actions{position:fixed;bottom:0;left:0;width:100%;background:#1f2937;padding:12px;display:flex;justify-content:center;gap:12px;z-index:3000;box-shadow:0 -3px 10px rgba(0,0,0,0.5)}
#toastFav{position:fixed;bottom:85px;left:50%;transform:translateX(-50%);background:#2563eb;color:white;padding:12px 20px;border-radius:10px;font-weight:600;opacity:0;transition:.3s;z-index:5000}
</style>
</head>
<body>

<div id="toastFav"></div>

<a href="menu.php" style="position:fixed;left:16px;top:16px;z-index:2000">
<img src="logo.png" style="width:40px;height:40px;border-radius:8px">
</a>

<div class="container">
<div class="gallery">

<?php if($_SESSION['user_id'] != $product['usuario_id']): ?>
<div class="save-btn <?php echo $fav?'saved':''; ?>" id="saveBtn" data-id="<?php echo $product['id']; ?>">
<?php if($fav): ?>
<svg viewBox="0 0 24 24"><path d="M6 2h12a2 2 0 0 1 2 2v18l-8-5-8 5V4a2 2 0 0 1 2-2z"/></svg>
<?php else: ?>
<svg viewBox="0 0 24 24"><path d="M6 2h12a2 2 0 0 1 2 2v18l-8-5-8 5V4a2 2 0 0 1 2-2zm0 2v15.764L12 15l6 4.764V4H6z"/></svg>
<?php endif; ?>
</div>
<?php endif; ?>

<a href="menu.php" class="btn-outline" style="padding:6px 10px;border-radius:6px;display:inline-block;margin-bottom:12px">← Volver</a>

<div class="main-img">
<div class="arrow arrow-left" onclick="prevImg()">❮</div>
<div class="arrow arrow-right" onclick="nextImg()">❯</div>
<img id="mainImage" src="<?php echo esc($imagenes[0]); ?>">
</div>

<div class="thumb-row">
<?php foreach($imagenes as $i=>$img): ?>
<img src="<?php echo esc($img); ?>" class="thumb <?php echo $i===0?'active':''; ?>" onclick="setImg(<?php echo $i; ?>)">
<?php endforeach; ?>
</div>

</div>

<aside>
<div class="card">
<div class="label" style="font-size:1.4rem"><?php echo esc($product['nombre']); ?></div>
<div style="font-weight:800;font-size:1.3rem">$<?php echo number_format($product['precio'],2); ?></div>

<div class="seller-header">
<div class="seller-avatar"><img src="<?php echo $avatar_url; ?>"></div>
<div>
<a href="vendedor.php?id=<?php echo $product['usuario_id']; ?>" class="label" style="color:white;text-decoration:none">
<?php echo esc($product['vendedor_nombre']); ?>
</a>
<div>Miembro desde <?php echo $miembro_desde_anio; ?></div>
</div>
</div>

<div style="margin-top:18px">
<div class="label">Detalles</div>
<div><strong>Categoría:</strong> General</div>
<div><strong>Estado:</strong> Usado - Buen estado</div>
</div>

<div style="margin-top:18px">
<div class="label">Descripción</div>
<div><?php echo nl2br(esc($product['descripcion'])); ?></div>
</div>

<div style="margin-top:18px">
<div class="label">Ubicación aproximada</div>
<div><?php echo esc($product['ubicacion']); ?></div>
<a href="#" id="open-maps" class="btn-outline" style="margin-top:8px">Ver en mapa</a>
<div class="map-wrap" id="map"></div>
</div>

</div>
</aside>

</div>

<?php if($_SESSION['user_id'] != $product['usuario_id']): ?>
<div class="fixed-actions">
<a href="/ShopGo/chat.php?user=4<?php echo $product['usuario_id']; ?>" class="btn btn-primary">Enviar mensaje</a>
<button class="btn btn-outline" id="addCart" data-id="<?php echo $product['id']; ?>">Agregar al carrito</button>
</div>
<?php else: ?>
<div class="fixed-actions">
<a href="editar_producto.php?id=<?php echo $product['id']; ?>" class="btn btn-yellow">Editar</a>
<a href="eliminar_producto.php?id=<?php echo $product['id']; ?>" class="btn btn-red" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
</div>
<?php endif; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let imgs=<?php echo json_encode($imagenes); ?>;
let index=0;

function setImg(i){
    index=i;
    const img=document.getElementById("mainImage");
    img.style.opacity=0;
    setTimeout(()=>{img.src=imgs[index];img.style.opacity=1;},150);
    document.querySelectorAll(".thumb").forEach(t=>t.classList.remove("active"));
    document.querySelectorAll(".thumb")[index].classList.add("active");
}

function nextImg(){ index=(index+1)%imgs.length; setImg(index); }
function prevImg(){ index=(index-1+imgs.length)%imgs.length; setImg(index); }

const ubicacion=<?php echo json_encode($product['ubicacion']); ?>;

async function loadMap(){
    const res=await fetch("https://nominatim.openstreetmap.org/search?format=json&q="+encodeURIComponent(ubicacion));
    const data=await res.json();
    if(!data.length)return;
    const map=L.map("map").setView([data[0].lat,data[0].lon],13);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);
    L.marker([data[0].lat,data[0].lon]).addTo(map).bindPopup(ubicacion).openPopup();
}
loadMap();

document.getElementById("open-maps").onclick=e=>{
    e.preventDefault();
    window.open("https://www.openstreetmap.org/search?query="+encodeURIComponent(ubicacion),"_blank");
};

function showFavToast(msg){
    const t=document.getElementById("toastFav");
    t.innerText=msg;
    t.style.opacity="1";
    setTimeout(()=>{t.style.opacity="0";},1800);
}

const saveBtn=document.getElementById("saveBtn");

if(saveBtn){
    saveBtn.onclick=async()=>{
        const id=saveBtn.getAttribute("data-id");
        const r=await fetch("toggle_favorito.php?id="+id);
        const res=await r.text();

        if(res==="nologin"){ window.location="login.php"; return; }

        saveBtn.classList.toggle("saved");

        if(saveBtn.classList.contains("saved")){
            saveBtn.innerHTML='<svg viewBox="0 0 24 24"><path d="M6 2h12a2 2 0 0 1 2 2v18l-8-5-8 5V4a2 2 0 0 1 2-2z"/></svg>';
            showFavToast("Guardado en favoritos");
        } else {
            saveBtn.innerHTML='<svg viewBox="0 0 24 24"><path d="M6 2h12a2 2 0 0 1 2 2v18l-8-5-8 5V4a2 2 0 0 1 2-2zm0 2v15.764L12 15l6 4.764V4H6z"/></svg>';
            showFavToast("Eliminado de favoritos");
        }
    };
}
</script>

</body>
</html>

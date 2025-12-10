<?php
session_save_path(__DIR__ . '/sessions');
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$conn = new mysqli("localhost","root","","empresa");
$conn->set_charset("utf8mb4");

$q = $conn->prepare("SELECT * FROM productos WHERE id=? LIMIT 1");
$q->bind_param("i", $id);
$q->execute();
$product = $q->get_result()->fetch_assoc();

if(!$product || $product['usuario_id'] != $_SESSION['user_id']){
    header("Location: menu.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $descripcion = $_POST['descripcion'];
    $ubicacion = $_POST['ubicacion'];
    $imagen = $product['imagen'];

    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0){
        $new = time()."_".basename($_FILES['imagen']['name']);
        $dest = "uploads/".$new;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $dest);
        $imagen = $dest;
    }

    if(isset($_FILES['imagenes'])){
        foreach($_FILES['imagenes']['tmp_name'] as $i => $tmp){
            if($_FILES['imagenes']['error'][$i] === 0){
                $name = time() . "_" . basename($_FILES['imagenes']['name'][$i]);
                $dest = "uploads/" . $name;
                move_uploaded_file($tmp, $dest);

                $stmt = $conn->prepare("INSERT INTO producto_imagenes (producto_id, ruta) VALUES (?,?)");
                $stmt->bind_param("is", $id, $dest);
                $stmt->execute();
            }
        }
    }

    $stmt = $conn->prepare("UPDATE productos SET nombre=?, precio=?, descripcion=?, ubicacion=?, imagen=? WHERE id=?");
    $stmt->bind_param("sdsssi", $nombre, $precio, $descripcion, $ubicacion, $imagen, $id);
    $stmt->execute();

    header("Location: producto_detalle.php?id=".$id);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Editar producto</title>
<style>
body{background:#111827;color:white;font-family:Inter;padding:40px}
.box{background:#1f2937;padding:20px;border-radius:12px;max-width:600px;margin:auto}
input,textarea{width:100%;padding:10px;border-radius:6px;margin-top:10px;background:#0f172a;color:white;border:1px solid #444}
.btn{background:#2563eb;color:white;padding:12px 16px;border-radius:8px;text-decoration:none;display:inline-block;margin-top:14px}
</style>
</head>
<body>

<div class="box">
<h2>Editar producto</h2>

<form method="post" enctype="multipart/form-data">

<label>Nombre</label>
<input type="text" name="nombre" value="<?php echo $product['nombre']; ?>" required>

<label>Precio</label>
<input type="number" step="0.01" name="precio" value="<?php echo $product['precio']; ?>" required>

<label>Descripción</label>
<textarea name="descripcion" rows="4" required><?php echo $product['descripcion']; ?></textarea>

<label>Ubicación</label>
<input type="text" name="ubicacion" value="<?php echo $product['ubicacion']; ?>" required>

<label>Imagen principal (opcional)</label>
<input type="file" name="imagen">

<label>Agregar más imágenes</label>
<input type="file" name="imagenes[]" multiple>

<button class="btn">Guardar cambios</button>

</form>

</div>

</body>
</html>

<?php
session_save_path(__DIR__ . '/sessions');
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$logged = true;

$totalCarrito = 0;
if (!empty($_SESSION['carrito'])) {
    $conn = new mysqli("localhost", "root", "", "empresa");
    $ids = implode(",", array_map("intval", $_SESSION['carrito']));
    $res = $conn->query("SELECT precio FROM productos WHERE id IN ($ids)");
    while ($row = $res->fetch_assoc()) $totalCarrito += $row['precio'];
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>ShopGo - Innovación para tu negocio</title>
<link rel="stylesheet" href="styles.css">
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<style>
.add-cart-btn{
  position:absolute;
  bottom:12px;
  right:12px;
  width:42px;
  height:42px;
  border-radius:10px;
  border:none;
  background:#ffd200;
  color:#000;
  font-size:20px;
  cursor:pointer;
  display:flex;
  align-items:center;
  justify-content:center;
  transition:.22s ease;
}
.add-cart-btn:hover{background:#ffb300;transform:scale(1.1);}
.add-cart-btn.cart-added{animation:cartBounce .35s ease;}
@keyframes cartBounce{
  0%{transform:scale(1)}
  35%{transform:scale(1.25)}
  70%{transform:scale(.9)}
  100%{transform:scale(1)}
}
#cart-top.cart-anim{
  animation:cartBounce .35s ease;
}
.sidebar-expanded{
  position:fixed;
  top:0;
  left:0;
  width:230px;
  height:100vh;
  background:#1f2430;
  padding:20px;
  display:flex;
  flex-direction:column;
  gap:12px;
  box-shadow:3px 0 10px rgba(0,0,0,0.4);
  z-index:9999;
}
.sidebar-expanded a{
  color:white;
  text-decoration:none;
  padding:10px;
  border-radius:8px;
  background:rgba(255,255,255,0.1);
}
.sidebar-expanded a:hover{
  background:rgba(255,255,255,0.2);
}
</style>

</head>
<body>

<header class="topbar">
  <a class="brand" href="menu.php">
    <img src="logo.png">
    <span>ShopGo</span>
  </a>

  <div class="search-wrap">
    <div class="search">
      <input id="top-search" placeholder="¿Qué estás buscando?">
      <button id="top-search-btn"><ion-icon name="search-outline"></ion-icon></button>
    </div>
  </div>

  <div class="actions">
    <a href="favoritos.php" class="btn">Favoritos</a>
    <a href="perfil.html" class="btn">Mi Cuenta</a>
    <a href="mis_productos.php" class="btn">Mis Productos</a>
    <a href="mensajes.php" class="btn">Mensajes</a>
    <a href="logout.php" class="btn">Cerrar sesión</a>

    <a id="cart-top" href="carrito.php" class="btn primary">
        <ion-icon name="cart-outline"></ion-icon>
        $<?php echo number_format($totalCarrito,2); ?>
    </a>
  </div>
</header>

<div class="sidebar-compact">
  <a class="icon-btn" href="producto.html"><ion-icon name="add-circle-outline"></ion-icon></a>
  <a class="icon-btn" id="open-menu"><ion-icon name="menu-outline"></ion-icon></a>
</div>

<main class="content-wrap">
  <section class="hero" style="text-align:center;padding:48px 18px 10px">
    <h1 style="margin:0;font-size:clamp(2.5rem,6vw,5rem);color:#ffffff">Bienvenido a ShopGo</h1>
  </section>

  <section id="productos" class="product-showcase">
    <h2 style="color:#ffffff;text-align:center;margin:0 0 8px">Descubre nuestros productos</h2>
    <p style="text-align:center;color:#ffffff;margin-top:6px">Inspírate con lo que otros están creando.</p>
    <div class="product-carousel" id="products-grid"></div>
  </section>

  <footer style="padding:20px 18px;text-align:center;color:#6b7280">© 
    <span id="year"></span> ShopGo.
  </footer>
</main>

<script>
document.getElementById("year").textContent = new Date().getFullYear();

document.getElementById("open-menu").addEventListener("click", function() {
    const sidebar = document.querySelector(".sidebar-expanded");
    if (!sidebar) {
        const div = document.createElement("div");
        div.className = "sidebar-expanded";
        div.innerHTML = `
            <a href="menu.php">Inicio</a>
            <a href="favoritos.php">Favoritos</a>
            <a href="perfil.html">Mi Cuenta</a>
            <a href="mis_productos.php">Mis Productos</a>
            <a href="mensajes.php">Mensajes</a>
            <a href="logout.php">Cerrar sesión</a>
        `;
        document.body.appendChild(div);
    } else {
        sidebar.remove();
    }
});

function loadProducts() {
  const grid = document.getElementById("products-grid");
  const params = new URLSearchParams(window.location.search);
  const search = params.get("search") || "";
  grid.innerHTML = "Cargando...";

  fetch("listar_productos.php?search=" + encodeURIComponent(search))
  .then(r => r.json())
  .then(data => {
    grid.innerHTML = "";
    (data.products || []).forEach(p => {
      const card = document.createElement("div");
      card.className = "product-card";
      card.style.position = "relative";

      card.innerHTML = `
        <a href="producto_detalle.php?id=${p.id}" class="card-link">
          <img src="${p.imagen}">
          <h4>${p.nombre}</h4>
          <p class="price">$${p.precio}</p>
        </a>

        <button class="add-cart-btn" data-id="${p.id}">
            <ion-icon name="cart-outline"></ion-icon>
        </button>
      `;

      grid.appendChild(card);
    });

    document.querySelectorAll(".add-cart-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            let id = btn.dataset.id;

            fetch("agregar_carrito.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "id=" + id
            })
            .then(r => r.text())
            .then(t => {
                if (t === "ok") {
                    actualizarCarritoTop();
                    btn.classList.add("cart-added");
                    setTimeout(() => btn.classList.remove("cart-added"), 400);
                }
            });
        });
    });

  });
}
loadProducts();

function actualizarCarritoTop() {
    fetch("total_carrito.php")
    .then(r => r.text())
    .then(total => {
        const btn = document.getElementById("cart-top");
        btn.innerHTML = `<ion-icon name="cart-outline"></ion-icon> $${total}`;
        btn.classList.add("cart-anim");
        setTimeout(() => btn.classList.remove("cart-anim"), 400);
    });
}

document.getElementById("top-search-btn").onclick = () => {
  const v = document.getElementById("top-search").value;
  if (v) location.href = "menu.php?search=" + encodeURIComponent(v);
};

document.getElementById("top-search").addEventListener("keydown", e => {
  if (e.key === "Enter") {
    const v = document.getElementById("top-search").value;
    if (v) location.href = "menu.php?search=" + encodeURIComponent(v);
  }
});
</script>

</body>
</html>

<?php
session_save_path(__DIR__ . '/sessions');
session_start();

$conn = new mysqli("localhost", "root", "", "empresa");
$conn->set_charset("utf8mb4");

$items = $_SESSION['carrito'] ?? [];
$productos = [];

if (!empty($items)) {
    $ids = implode(",", array_map("intval", $items));
    $res = $conn->query("SELECT id, nombre, precio FROM productos WHERE id IN ($ids)");
    while ($row = $res->fetch_assoc()) {
        $productos[] = $row;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Carrito</title>
<link rel="stylesheet" href="carrito.css">
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

<div class="top-nav">
  <a href="menu.php" class="back-btn">
      <ion-icon name="arrow-back-outline"></ion-icon>
      Volver al menú
  </a>
</div>

<div class="cart-wrapper">
  <div class="cart-card">
      <h1 class="title"><ion-icon name="bag-handle-outline"></ion-icon> Tu carrito</h1>

      <ul class="cart-list">
          <?php if (empty($productos)): ?>
              <li class="empty">No hay productos en el carrito.</li>
          <?php else: ?>
              <?php $total = 0; ?>
              <?php foreach ($productos as $p): ?>
                  <?php $total += $p['precio']; ?>

                  <li class="cart-item" data-id="<?php echo $p['id']; ?>">
                      <a href="producto_detalle.php?id=<?php echo $p['id']; ?>" class="item-link">
                          <span><?php echo $p['nombre']; ?></span>
                      </a>
                      <span class="price">$<?php echo number_format($p['precio'], 2); ?></span>
                      <button class="delete-btn" data-id="<?php echo $p['id']; ?>">
                          <ion-icon name="trash-outline"></ion-icon>
                      </button>
                  </li>

              <?php endforeach; ?>
          <?php endif; ?>
      </ul>

      <?php if (!empty($productos)): ?>
          <div class="total" id="total-value">Total: $<?php echo number_format($total, 2); ?></div>
      <?php endif; ?>
  </div>
</div>

<script>
document.querySelectorAll(".delete-btn").forEach(btn => {
    btn.addEventListener("click", async () => {
        const id = btn.dataset.id;
        const item = btn.closest(".cart-item");

        const res = await fetch("eliminar_item.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + id
        });

        const txt = await res.text();

        if (txt === "ok") {
            item.style.opacity = "0";
            item.style.transform = "translateX(-15px)";
            setTimeout(() => {
                item.remove();
                recalcularTotal();
            }, 250);
        }
    });
});

function recalcularTotal() {
    let total = 0;
    document.querySelectorAll(".price").forEach(p => {
        total += parseFloat(p.textContent.replace("$", "").replace(",", ""));
    });
    const t = document.getElementById("total-value");
    if (t) t.textContent = "Total: $" + total.toFixed(2);
}
</script>

</body>
</html>

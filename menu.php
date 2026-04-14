<?php require_once 'backend/auth.php'; ?>

<?php
$restaurantId = $_GET['restaurant'] ?? null;

// Get restaurant
$restaurant = null;
if ($restaurantId) {
  $stmt = db()->prepare("SELECT * FROM restaurants WHERE id=?");
  $stmt->execute([$restaurantId]);
  $restaurant = $stmt->fetch();
}

// Get menu
if ($restaurantId) {
  $stmt = db()->prepare("SELECT * FROM menu_items WHERE restaurant_id=?");
  $stmt->execute([$restaurantId]);
} else {
  $stmt = db()->query("SELECT * FROM menu_items");
}

$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>Menu</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'partials/nav.php'; ?>

<div class="wrapper">

<div class="page-header">
  <h1 class="page-title">
    <?= $restaurant ? $restaurant['name'] : 'All Menu' ?>
  </h1>

  <a href="cart.php" class="btn secondary">Go to Cart</a>
</div>

<div class="menu-grid">

<?php foreach($items as $item): ?>
  <div class="menu-card">

    <img src="./<?= $item['image'] ?>" class="menu-img">

    <div class="menu-body">

      <div class="menu-top">
        <h3><?= $item['name'] ?></h3>
        <span class="price">$<?= number_format((float)$item['price'], 2) ?></span>
      </div>

      <p class="desc"><?= $item['description'] ?></p>

      <div class="menu-actions">

        <!-- 🔥 FIXED BUTTON -->
        <button type="button" onclick="addToCart(<?= $item['id'] ?>)" class="btn-mini">
          Add to Cart
        </button>

      </div>

    </div>
  </div>
<?php endforeach; ?>

</div>

</div>

<!-- AJAX SCRIPT -->
<script>
function addToCart(id) {
  fetch("cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "action=add&menu_item_id=" + id + "&quantity=1"
  })
  .then(res => res.text())
  .then(() => {
    showToast("Added to cart 🛒");
    updateCartCount();
  });
}

function showToast(msg) {
  const t = document.createElement("div");
  t.className = "toast";
  t.innerText = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2000);
}

function updateCartCount() {
  fetch("cart.php?action=count")
    .then(res => res.text())
    .then(c => {
      const el = document.querySelector(".cart-count");
      if (el) el.innerText = c;
    });
}
</script>

</body>
</html>
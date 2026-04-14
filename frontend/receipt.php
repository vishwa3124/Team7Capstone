<?php
declare(strict_types=1);

require_once __DIR__ . '/backend/auth.php';

require_auth();

$userId = current_user_id();
$orderId = (int)($_GET['id'] ?? 0);

if (!$orderId) die("Invalid order");

/* =========================
   FETCH ORDER
========================= */
$stmt = db()->prepare("
SELECT * FROM orders 
WHERE id=? AND user_id=?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) die("Order not found");

/* =========================
   FETCH ITEMS (FIXED JOIN)
========================= */
$stmt = db()->prepare("
SELECT oi.quantity, oi.price, m.name, m.image
FROM order_items oi
JOIN menu_items m ON oi.menu_item_id = m.id
WHERE oi.order_id=?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>Receipt</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<?php include 'partials/nav.php'; ?>

<div class="wrapper">

<div class="receipt-card">

  <h1>Order Receipt</h1>

  <p class="receipt-meta">
    Order #<?= $order['id'] ?><br>
    <?= date("F j, Y, g:i A", strtotime($order['created_at'])) ?>
  </p>

  <hr>

  <!-- DELIVERY -->
  <h3>Delivery Info</h3>

  <p>
    <strong><?= htmlspecialchars($order['delivery_name']) ?></strong><br>
    <?= htmlspecialchars($order['delivery_email']) ?><br>
    <?= htmlspecialchars($order['delivery_phone']) ?><br>
    <?= htmlspecialchars($order['delivery_address']) ?>
  </p>

  <hr>

  <!-- ITEMS -->
  <h3>Items</h3>

  <?php 
  $total = 0;
  foreach ($items as $item):

    $price = (float)$item['price'];
    $qty   = (int)$item['quantity'];
    $sub   = $price * $qty;
    $total += $sub;
  ?>

  <div class="receipt-item">
    <div class="receipt-left">
      <img src="./<?= htmlspecialchars($item['image']) ?>" class="receipt-img">
      <div>
        <p><?= htmlspecialchars($item['name']) ?></p>
        <small><?= $qty ?> × $<?= number_format($price, 2) ?></small>
      </div>
    </div>
    <strong>$<?= number_format($sub, 2) ?></strong>
  </div>

  <?php endforeach; ?>

  <hr>

  <!-- TOTAL -->
  <div class="receipt-total">
    <span>Total</span>
    <strong>$<?= number_format((float)$order['total_amount'], 2) ?></strong>
  </div>

  <button onclick="window.print()" class="btn" style="margin-top:20px;">
    Print Receipt 🧾
  </button>

</div>

</div>

</body>
</html>
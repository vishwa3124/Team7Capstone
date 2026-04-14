<?php
declare(strict_types=1);

require_once __DIR__ . '/backend/auth.php';

require_auth();

$userId = current_user_id();

/* =========================
   HANDLE CHECKOUT
========================= */
if (is_post()) {

    // 🔥 GET FORM DATA
    $name    = input('name');
    $phone   = input('phone');
    $email   = input('email');
    $address = input('address');
    $notes   = input('notes');

    if (!$name || !$phone || !$email || !$address) {
        die("Please fill all required fields");
    }

    // 🔥 GET CART
    $stmt = db()->prepare("
        SELECT c.menu_item_id, c.quantity, m.price
        FROM cart c
        JOIN menu_items m ON c.menu_item_id = m.id
        WHERE c.user_id=?
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();

    if (!$items) {
        die("Cart is empty");
    }

    // 🔥 TOTAL
    $total = 0;
    foreach ($items as $i) {
        $total += (float)$i['price'] * (int)$i['quantity'];
    }

    // 🔥 INSERT ORDER (FULL FIX)
    db()->prepare("
        INSERT INTO orders 
        (user_id, total_amount, status, delivery_name, delivery_phone, delivery_email, delivery_address, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        $userId,
        $total,
        'Preparing',
        $name,
        $phone,
        $email,
        $address,
        $notes
    ]);

    $orderId = db()->lastInsertId();

    // 🔥 INSERT ITEMS
    foreach ($items as $i) {
        db()->prepare("
            INSERT INTO order_items (order_id, menu_item_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ")->execute([
            $orderId,
            $i['menu_item_id'],
            (int)$i['quantity'],
            (float)$i['price']
        ]);
    }

    // 🔥 CLEAR CART
    db()->prepare("DELETE FROM cart WHERE user_id=?")->execute([$userId]);

    // 🔥 REDIRECT
    redirect("receipt.php?id=" . $orderId);
}

/* =========================
   FETCH CART
========================= */
$stmt = db()->prepare("
SELECT c.menu_item_id, c.quantity, m.name, m.price, m.image
FROM cart c
JOIN menu_items m ON c.menu_item_id = m.id
WHERE c.user_id=?
");
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Checkout</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<?php include 'partials/nav.php'; ?>

<div class="wrapper">

<div class="page-header">
  <h1 class="page-title">Checkout</h1>
  <p class="page-subtitle">Review your order and place it</p>
</div>

<?php if (!$items): ?>
  <div class="surface" style="padding:20px;">
    <p>Your cart is empty.</p>
  </div>
<?php else: ?>

<form method="POST">

<div class="checkout-grid">

  <!-- LEFT: DELIVERY -->
  <div class="checkout-card">

    <h2>Delivery Details</h2>

    <div class="form-grid">

      <div class="input-group">
        <label>Full Name</label>
        <input type="text" name="name" required>
      </div>

      <div class="input-group">
        <label>Phone</label>
        <input type="text" name="phone" required>
      </div>

      <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>

      <div class="input-group" style="grid-column: span 2;">
        <label>Address</label>
        <input type="text" name="address" required>
      </div>

      <div class="input-group" style="grid-column: span 2;">
        <label>Notes</label>
        <textarea name="notes"></textarea>
      </div>

    </div>

  </div>

  <!-- RIGHT: SUMMARY -->
  <div class="checkout-card">

    <h2>Your Order</h2>

    <?php foreach ($items as $item):

      $price = (float)$item['price'];
      $qty   = (int)$item['quantity'];
      $sub   = $price * $qty;
      $total += $sub;
    ?>

    <div class="summary-item">
      <div class="summary-left">
        <img src="./<?= htmlspecialchars($item['image']) ?>" class="summary-img">
        <div>
          <p><?= htmlspecialchars($item['name']) ?></p>
          <small><?= $qty ?> × $<?= number_format($price, 2) ?></small>
        </div>
      </div>
      <strong>$<?= number_format($sub, 2) ?></strong>
    </div>

    <?php endforeach; ?>

    <hr>

    <div class="summary-total">
      <span>Total</span>
      <span>$<?= number_format($total, 2) ?></span>
    </div>

    <button class="btn block" style="margin-top:15px;">
      Place Order →
    </button>

  </div>

</div>

</form>

<?php endif; ?>

</div>

</body>
</html>
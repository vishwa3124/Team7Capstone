<?php
require_once __DIR__ . '/backend/auth.php';
require_auth();

$userId = current_user_id();

/* =========================
   CANCEL ORDER (SAFE)
========================= */
if (isset($_GET['cancel'])) {
    $orderId = (int)$_GET['cancel'];

    // check order belongs to user AND is pending
    $stmt = db()->prepare("
        SELECT id FROM orders 
        WHERE id=? AND user_id=? AND status='pending'
    ");
    $stmt->execute([$orderId, $userId]);

    if ($stmt->fetch()) {
        db()->prepare("
            UPDATE orders 
            SET status='cancelled' 
            WHERE id=?
        ")->execute([$orderId]);
    }

    header("Location: orders.php");
    exit;
}

/* =========================
   REORDER
========================= */
if (isset($_GET['reorder'])) {
    $orderId = (int)$_GET['reorder'];

    $stmt = db()->prepare("
        SELECT menu_item_id, quantity 
        FROM order_items 
        WHERE order_id=?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();

    foreach ($items as $item) {

        $stmt = db()->prepare("
            SELECT id FROM cart 
            WHERE user_id=? AND menu_item_id=?
        ");
        $stmt->execute([$userId, $item['menu_item_id']]);

        if ($stmt->fetch()) {
            db()->prepare("
                UPDATE cart 
                SET quantity = quantity + ? 
                WHERE user_id=? AND menu_item_id=?
            ")->execute([
                (int)$item['quantity'],
                $userId,
                $item['menu_item_id']
            ]);
        } else {
            db()->prepare("
                INSERT INTO cart (user_id, menu_item_id, quantity)
                VALUES (?, ?, ?)
            ")->execute([
                $userId,
                $item['menu_item_id'],
                (int)$item['quantity']
            ]);
        }
    }

    header("Location: cart.php");
    exit;
}

/* =========================
   FETCH ORDERS
========================= */
$stmt = db()->prepare("
SELECT * FROM orders 
WHERE user_id=? 
ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>My Orders</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<?php include 'partials/nav.php'; ?>

<div class="wrapper">

<div class="page-header">
  <h1 class="page-title">My Orders</h1>
  <p class="page-subtitle">Track your past orders</p>
</div>

<?php if (!$orders): ?>
  <div class="surface" style="padding:20px;">
    <p>No orders yet.</p>
  </div>
<?php else: ?>

<div class="orders-grid">

<?php foreach ($orders as $order): 

$status = strtolower($order['status'] ?? 'pending');
$total  = (float)($order['total_amount'] ?? 0);

?>

<div class="order-card">

  <!-- TOP -->
  <div class="order-top">
    <div>
      <h3>Order #<?= $order['id'] ?></h3>
      <p class="muted">
        <?= date('M j, Y • g:i A', strtotime($order['created_at'])) ?>
      </p>
    </div>

    <span class="status <?= $status ?>">
      <?= ucfirst($status) ?>
    </span>
  </div>

  <!-- BODY -->
  <div class="order-body">
    <p><strong>Deliver to:</strong> <?= htmlspecialchars($order['delivery_name']) ?></p>
    <p class="muted"><?= htmlspecialchars($order['delivery_address']) ?></p>
  </div>

  <!-- FOOTER -->
  <div class="order-footer">

    <div class="order-total">
      $<?= number_format($total, 2) ?>
    </div>

    <div class="order-actions">

      <a href="receipt.php?id=<?= $order['id'] ?>" class="btn-mini secondary">
        Receipt
      </a>

      <a href="orders.php?reorder=<?= $order['id'] ?>" class="btn-mini">
        Reorder
      </a>

      <?php if ($status === 'pending'): ?>
        <a href="orders.php?cancel=<?= $order['id'] ?>"
           class="btn-mini danger"
           onclick="return confirm('Cancel this order?')">
          Cancel
        </a>
      <?php endif; ?>

    </div>

  </div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>

</body>
</html>
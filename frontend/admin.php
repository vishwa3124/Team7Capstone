<?php
require_once __DIR__ . '/backend/auth.php';
require_auth();
require_admin();

/* =========================
   UPDATE STATUS
========================= */
if (isset($_GET['status'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];

    $allowed = ['pending','preparing','delivered','cancelled'];

    if (in_array($status, $allowed)) {
        db()->prepare("UPDATE orders SET status=? WHERE id=?")
           ->execute([$status, $id]);
    }

    header("Location: admin.php");
    exit;
}

/* =========================
   FILTER
========================= */
$filter = $_GET['filter'] ?? 'all';

$query = "
SELECT o.*, u.fullname 
FROM orders o
JOIN users u ON o.user_id = u.id
";

if ($filter !== 'all') {
    $query .= " WHERE o.status = ?";
}

$query .= " ORDER BY o.created_at DESC";

$stmt = db()->prepare($query);

if ($filter !== 'all') {
    $stmt->execute([$filter]);
} else {
    $stmt->execute();
}

$orders = $stmt->fetchAll();

/* =========================
   STATS
========================= */
$totalOrders = db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = db()->query("SELECT SUM(total_amount) FROM orders")->fetchColumn();
$pendingOrders = db()->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<?php include 'partials/nav.php'; ?>

<div class="wrapper">

<!-- HEADER -->
<div class="page-header">
  <h1 class="page-title">Admin Dashboard</h1>
  <p class="page-subtitle">Manage orders & track performance</p>
</div>

<!-- STATS -->
<div class="admin-stats">

  <div class="stat-card">
    <h3>Total Orders</h3>
    <p><?= $totalOrders ?></p>
  </div>

  <div class="stat-card">
    <h3>Revenue</h3>
    <p>$<?= number_format((float)$totalRevenue,2) ?></p>
  </div>

  <div class="stat-card">
    <h3>Pending</h3>
    <p><?= $pendingOrders ?></p>
  </div>

</div>

<!-- FILTER -->
<div class="admin-filters">
  <a href="admin.php?filter=all">All</a>
  <a href="admin.php?filter=pending">Pending</a>
  <a href="admin.php?filter=preparing">Preparing</a>
  <a href="admin.php?filter=delivered">Delivered</a>
</div>

<!-- ORDERS -->
<div class="orders-grid">

<?php foreach ($orders as $order): 
$status = strtolower($order['status']);
$total  = (float)$order['total_amount'];
?>

<div class="order-card">

  <div class="order-top">
    <div>
      <h3>Order #<?= $order['id'] ?></h3>
      <p class="muted"><?= htmlspecialchars($order['fullname']) ?></p>
    </div>

    <span class="status <?= $status ?>">
      <?= ucfirst($status) ?>
    </span>
  </div>

  <div class="order-body">
    <p><?= htmlspecialchars($order['delivery_address']) ?></p>
  </div>

  <div class="order-footer">

    <div class="order-total">
      $<?= number_format($total, 2) ?>
    </div>

    <div class="order-actions">
      <a href="?status=pending&id=<?= $order['id'] ?>" class="btn-mini secondary">Pending</a>
      <a href="?status=preparing&id=<?= $order['id'] ?>" class="btn-mini">Preparing</a>
      <a href="?status=delivered&id=<?= $order['id'] ?>" class="btn-mini">Delivered</a>
      <a href="?status=cancelled&id=<?= $order['id'] ?>" class="btn-mini danger">Cancel</a>
    </div>

  </div>

</div>

<?php endforeach; ?>

</div>

</div>

</body>
</html>
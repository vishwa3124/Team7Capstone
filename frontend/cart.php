<?php
require_once __DIR__ . '/backend/auth.php';
require_auth();

$userId = current_user_id();

/* =========================
   HANDLE ACTIONS
========================= */

// ADD / UPDATE / REMOVE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // ADD TO CART
    if ($action === 'add') {

        $itemId = (int)($_POST['menu_item_id'] ?? 0);
        $qty    = max(1, (int)($_POST['quantity'] ?? 1));

        if ($itemId) {

            // check if exists
            $stmt = db()->prepare("
                SELECT id FROM cart 
                WHERE user_id=? AND menu_item_id=?
            ");
            $stmt->execute([$userId, $itemId]);

            if ($stmt->fetch()) {
                db()->prepare("
                    UPDATE cart 
                    SET quantity = quantity + ?
                    WHERE user_id=? AND menu_item_id=?
                ")->execute([$qty, $userId, $itemId]);
            } else {
                db()->prepare("
                    INSERT INTO cart (user_id, menu_item_id, quantity)
                    VALUES (?, ?, ?)
                ")->execute([$userId, $itemId, $qty]);
            }
        }

        header("Location: cart.php");
        exit;
    }

    // UPDATE QTY
    if ($action === 'update') {
        db()->prepare("
            UPDATE cart SET quantity=? 
            WHERE user_id=? AND menu_item_id=?
        ")->execute([
            (int)$_POST['quantity'],
            $userId,
            (int)$_POST['id']
        ]);
        exit;
    }

    // REMOVE
    if ($action === 'remove') {
        db()->prepare("
            DELETE FROM cart 
            WHERE user_id=? AND menu_item_id=?
        ")->execute([
            $userId,
            (int)$_POST['id']
        ]);
        exit;
    }
}

/* =========================
   CART COUNT (NAV)
========================= */
if (isset($_GET['action']) && $_GET['action'] === 'count') {
    $stmt = db()->prepare("SELECT SUM(quantity) FROM cart WHERE user_id=?");
    $stmt->execute([$userId]);
    echo $stmt->fetchColumn() ?? 0;
    exit;
}

/* =========================
   FETCH CART ITEMS
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
<title>Your Cart</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<?php include 'partials/nav.php'; ?>

<div class="wrapper">

<div class="page-header">
  <h1 class="page-title">Your Cart</h1>
</div>

<?php if (!$items): ?>
  <div class="surface" style="padding:20px;">
    <p>Your cart is empty.</p>
  </div>
<?php else: ?>

<div class="cart-layout">

<!-- ITEMS -->
<div class="cart-items">

<?php foreach ($items as $item):

  $price = (float)$item['price'];
  $qty   = (int)$item['quantity'];
  $sub   = $price * $qty;
  $total += $sub;
?>

<div class="cart-card">

  <img src="./<?= htmlspecialchars($item['image']) ?>" class="cart-img">

  <div class="cart-info">
    <h3><?= htmlspecialchars($item['name']) ?></h3>
    <p class="price">$<?= number_format($price,2) ?></p>

    <div class="qty-box">
      <button onclick="updateQty(<?= $item['menu_item_id'] ?>, <?= $qty-1 ?>)">−</button>
      <span><?= $qty ?></span>
      <button onclick="updateQty(<?= $item['menu_item_id'] ?>, <?= $qty+1 ?>)">+</button>
    </div>
  </div>

  <div class="cart-right">
    <p class="subtotal">$<?= number_format($sub,2) ?></p>
    <button class="remove-btn" onclick="removeItem(<?= $item['menu_item_id'] ?>)">
      Remove
    </button>
  </div>

</div>

<?php endforeach; ?>

</div>

<!-- SUMMARY -->
<div class="cart-summary-box">

<h3>Order Summary</h3>

<div class="summary-row">
  <span>Subtotal</span>
  <span>$<?= number_format($total,2) ?></span>
</div>

<div class="summary-total">
  <span>Total</span>
  <span>$<?= number_format($total,2) ?></span>
</div>

<a href="checkout.php" class="btn block">
  Proceed to Checkout →
</a>

</div>

</div>

<?php endif; ?>

</div>

<script>
function updateQty(id, qty){
  if(qty < 1) return removeItem(id);

  fetch("cart.php", {
    method:"POST",
    headers:{"Content-Type":"application/x-www-form-urlencoded"},
    body:"action=update&id="+id+"&quantity="+qty
  }).then(()=>location.reload());
}

function removeItem(id){
  fetch("cart.php", {
    method:"POST",
    headers:{"Content-Type":"application/x-www-form-urlencoded"},
    body:"action=remove&id="+id
  }).then(()=>location.reload());
}
</script>

</body>
</html>
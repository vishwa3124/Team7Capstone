<?php require_once 'backend/auth.php'; ?>
<?php $restaurants = db()->query("SELECT * FROM restaurants")->fetchAll(); ?>

<!DOCTYPE html>
<html>
<head>
<title>Restaurants</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'partials/nav.php'; ?>

<div class="wrapper">

<div class="page-header">
  <h1 class="page-title">Restaurants</h1>
  <p class="page-subtitle">Choose a restaurant</p>
</div>

<div class="restaurant-grid">

<?php foreach($restaurants as $r): ?>
  <a class="restaurant-card" href="menu.php?restaurant=<?= $r['id'] ?>">
    <img src="<?= $r['image'] ?>" class="restaurant-img">
    <div class="restaurant-body">
      <h3><?= $r['name'] ?></h3>
      <p><?= $r['description'] ?></p>
    </div>
  </a>
<?php endforeach; ?>

</div>

</div>
</body>
</html>
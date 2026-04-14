<?php require_once 'backend/auth.php'; ?>
<?php
$restaurants = db()->query("SELECT * FROM restaurants LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Home | Online Food Ordering</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
</head>

<body>

<?php include 'partials/nav.php'; ?>

<div class="wrapper">

<!-- HERO -->
<section class="hero2">
  <img src="images/hero/hero-food.jpg" class="hero-img">
  <div class="hero2-overlay"></div>

  <div class="hero2-content">
    <span class="hero2-pill">Fast • Fresh • Easy</span>

    <h1>Order Food Fast & Easy</h1>

    <p>
      Discover restaurants near you, browse menus, add to cart,
      and checkout in minutes.
    </p>

    <div class="cta">
      <a href="restaurants.php" class="btn">View Restaurants</a>
      <a href="menu.php" class="btn secondary">Browse Menu</a>
    </div>
  </div>
</section>

<!-- 🔥 FEATURED RESTAURANTS (DYNAMIC NOW) -->
<section class="home-section">
  <div class="section-head">
    <div>
      <h2 class="section-title">Featured Restaurants</h2>
      <p class="section-subtitle">Explore top picks</p>
    </div>
  </div>

  <div class="slider" id="restSlider">

    <?php foreach($restaurants as $r): ?>
      <a class="slide-card" href="menu.php?restaurant=<?= $r['id'] ?>">
        <img src="<?= $r['image'] ?>" class="slide-img">
        <div class="slide-body">
          <div class="slide-top">
            <h3><?= $r['name'] ?></h3>
            <span class="badge">Top</span>
          </div>
          <p class="slide-meta"><?= $r['description'] ?></p>
        </div>
      </a>
    <?php endforeach; ?>

  </div>

  <div class="section-foot">
    <a href="restaurants.php" class="btn secondary">See All Restaurants</a>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="home-section">
  <h2 class="section-title">How it works</h2>

  <div class="grid">
    <div class="card">
      <h3>Browse Restaurants</h3>
      <p>Pick a restaurant and explore menus.</p>
    </div>

    <div class="card">
      <h3>Add to Cart</h3>
      <p>Select dishes and add them instantly.</p>
    </div>

    <div class="card">
      <h3>Checkout</h3>
      <p>Enter details and place your order.</p>
    </div>
  </div>
</section>

</div>

<footer class="footer">
  <div class="wrapper">
    © 2026 Online Food Ordering
  </div>
</footer>

<!-- SLIDER -->
<script>
const slider = document.getElementById("restSlider");

function scrollSlider(dir) {
  const card = slider.querySelector(".slide-card");
  const amount = card ? card.offsetWidth + 16 : 300;
  slider.scrollBy({ left: dir * amount, behavior: "smooth" });
}
</script>

</body>
</html>
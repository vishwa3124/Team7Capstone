<?php
require_once __DIR__ . '/backend/auth.php';

if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = db()->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {

        auth_login($user['id']); // 🔥 use your function

        header("Location: index.php");
        exit;

    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">

<div class="auth-card">

  <!-- LEFT SIDE -->
  <div class="auth-media">
    <div class="auth-brand">
      <img src="./images/logo.png" class="auth-logo">
      <h1>Welcome Back</h1>
      <p>Login to manage your orders, cart, and checkout faster.</p>
    </div>

    <img src="./images/login-illustration.png" class="auth-illustration">
  </div>

  <!-- RIGHT SIDE -->
  <div class="auth-form">

    <h2>Login</h2>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

      <div class="input-group">
        <input type="email" name="email" placeholder="Email" required>
      </div>

      <div class="input-group">
        <input type="password" name="password" placeholder="Password" required>
      </div>

      <button class="btn-auth">Login</button>

      <p>
        Don’t have an account?
        <a href="register.php">Register</a>
      </p>

    </form>

  </div>

</div>

</body>
</html>

<?php ob_end_flush(); ?>
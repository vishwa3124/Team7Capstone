<?php
require_once __DIR__ . '/backend/auth.php';

if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = $_POST['fullname'];
    $email = strtolower($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirmPassword'];

    if ($password !== $confirm) {
        $error = "Passwords do not match";
    } else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            db()->prepare("
                INSERT INTO users (fullname, email, password_hash)
                VALUES (?, ?, ?)
            ")->execute([$fullname, $email, $hash]);

            header("Location: login.php");
            exit;

        } catch (PDOException $e) {
            $error = "Email already exists";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up</title>
<link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">

<div class="auth-card">

  <!-- LEFT SIDE -->
  <div class="auth-media">
    <div class="auth-brand">
      <img src="./images/logo.png" class="auth-logo">
      <h1>Create your account</h1>
      <p>Order faster, save favorites, track your cart, and checkout in minutes.</p>
    </div>

    <img src="./images/register-illustration.png" class="auth-illustration">
  </div>

  <!-- RIGHT SIDE -->
  <div class="auth-form">

    <h2>Sign Up</h2>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

      <div class="input-group">
        <input name="fullname" placeholder="Full Name" required>
      </div>

      <div class="input-group">
        <input type="email" name="email" placeholder="Email" required>
      </div>

      <div class="input-group">
        <input type="password" name="password" placeholder="Password" required>
      </div>

      <div class="input-group">
        <input type="password" name="confirmPassword" placeholder="Confirm Password" required>
      </div>

      <button class="btn-auth">Register</button>

      <p>
        Already have an account?
        <a href="login.php">Login</a>
      </p>

    </form>

  </div>

</div>

</body>
</html>
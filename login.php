<?php
// login.php - processes POST from login.html (action="login.php")
declare(strict_types=1);

require_once __DIR__ . '/backend/auth.php';

if (!is_post()) {
    // If user visits login.php directly, send them to the login page.
    redirect('login.html');
}

$email = strtolower(input('email'));
$password = input('password');

if ($email === '' || $password === '') {
    flash_set('error', 'Email and password are required.');
    redirect('login.html');
}

$stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$row = $stmt->fetch();

if (!$row || !password_verify($password, $row['password_hash'])) {
    flash_set('error', 'Invalid email or password.');
    redirect('login.html');
}

auth_login((int)$row['id']);
flash_set('success', 'Logged in successfully!');

// Redirect somewhere after login
redirect('index.html');

<?php
// register.php - processes POST from register.html (action="register.php")
declare(strict_types=1);

require_once __DIR__ . '/backend/auth.php';

if (!is_post()) {
    redirect('register.html');
}

$fullname = input('fullname');
$email = strtolower(input('email'));
$password = input('password');
$confirm = input('confirmPassword');

if ($fullname === '' || $email === '' || $password === '' || $confirm === '') {
    flash_set('error', 'All fields are required.');
    redirect('register.html');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('error', 'Please enter a valid email address.');
    redirect('register.html');
}

if ($password !== $confirm) {
    flash_set('error', 'Passwords do not match.');
    redirect('register.html');
}

// Basic password rules (adjust if you want)
if (strlen($password) < 8) {
    flash_set('error', 'Password must be at least 8 characters.');
    redirect('register.html');
}

$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user (handle duplicate email)
try {
    $stmt = db()->prepare('INSERT INTO users (fullname, email, password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$fullname, $email, $hash]);

    auth_login((int)db()->lastInsertId());
    flash_set('success', 'Account created! You are now logged in.');
    redirect('index.html');
} catch (PDOException $e) {
    // Duplicate email (MySQL error code 1062)
    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
        flash_set('error', 'That email is already registered. Please login.');
        redirect('login.html');
    }
    // For debugging locally, you can temporarily display $e->getMessage()
    flash_set('error', 'Registration failed. Please try again.');
    redirect('register.html');
}

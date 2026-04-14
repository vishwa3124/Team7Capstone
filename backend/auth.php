<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   AUTH
========================= */

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function require_auth(): void {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/* =========================
   LOGIN / LOGOUT
========================= */

function auth_login(int $userId): void {
    session_regenerate_id(true);

    $_SESSION['user_id'] = $userId;

    $stmt = db()->prepare("SELECT fullname FROM users WHERE id=?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $_SESSION['fullname'] = $user['fullname'] ?? 'User';
}

function logout(): void {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit;
}

/* =========================
   HELPERS (🔥 FIX)
========================= */

function is_post(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function input(string $key): string {
    return trim($_POST[$key] ?? '');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function is_admin() {
    if (!isset($_SESSION['user_id'])) return false;

    $stmt = db()->prepare("SELECT is_admin FROM users WHERE id=?");
    $stmt->execute([$_SESSION['user_id']]);
    return (bool)$stmt->fetchColumn();
}

function require_admin() {
    if (!is_admin()) {
        die("Access denied");
    }
}
<?php
session_start();

$conn = new mysqli("localhost", "root", "", "kawaii_pos");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Generate CSRF token field
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

// Verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

// Hash password using bcrypt
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Sanitize output
function escape($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
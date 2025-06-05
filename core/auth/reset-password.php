<?php
namespace AffiliateBasic\Core\Auth;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;

use PDO;
use PDOException;

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('forgot-password', 'danger', 'Invalid request method.');
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('forgot-password', 'danger', 'CSRF token validation failed.');
}

// Rate limiting: Prevent abuse
$ip_address = $_SERVER['REMOTE_ADDR'];
$last_reset_key = 'last_password_reset_' . md5($ip_address);
$reset_interval = 300; // 5 minutes in seconds

if (isset($_SESSION[$last_reset_key])) {
    $last_reset_time = $_SESSION[$last_reset_key];
    if (time() - $last_reset_time < $reset_interval) {
        redirectWithMessage('forgot-password', 'danger', 'Please wait a few minutes before attempting another password reset.');
    }
}

// Get token and passwords
$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate token
if (empty($token)) {
    redirectWithMessage('forgot-password', 'danger', 'Invalid password reset link.');
}

// Validate passwords
if (empty($password) || strlen($password) < 8) {
    redirectWithMessage("reset-password?token=" . urlencode($token), 'danger', 'Password must be at least 8 characters long.');
} elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    redirectWithMessage("reset-password?token=" . urlencode($token), 'danger', 'Password must contain at least one letter and one number.');
}

if ($password !== $confirm_password) {
    redirectWithMessage("reset-password?token=" . urlencode($token), 'danger', 'Passwords do not match.');
}

// Verify token and get user
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        redirectWithMessage('forgot-password', 'danger', 'Invalid or expired password reset link.');
    }

    // Hash new password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Update password and clear reset token
    $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
    $stmt->execute([$password_hash, $user['id']]);

    // Log the password reset
    $log_message = date('Y-m-d H:i:s') . " - Password reset for user ID: {$user['id']} (IP: {$ip_address})\n";
    file_put_contents(LOGS_PATH . 'password_resets.log', $log_message, FILE_APPEND);

    // Regenerate session ID if user is logged in (e.g., resetting password while logged in)
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        session_regenerate_id(true);
    }

    // Update last reset time
    $_SESSION[$last_reset_key] = time();

    redirectWithMessage('login', 'success', 'Password reset successfully. You can now log in with your new password.');
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    redirectWithMessage('forgot-password', 'danger', 'Database error: ' . $e->getMessage());
}
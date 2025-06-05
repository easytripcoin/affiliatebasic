<?php
namespace AffiliateBasic\Core\Auth;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;

use PDO;
use PDOException;

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('change-password', 'danger', 'Invalid request method.');
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('change-password', 'danger', 'CSRF token validation failed.');
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    redirectWithMessage('login', 'danger', 'Please log in to change your password.');
}

// Get input data
$current_password = trim($_POST['current_password'] ?? '');
$new_password = trim($_POST['new_password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

// Validate inputs
if (empty($current_password)) {
    redirectWithMessage('change-password', 'danger', 'Please provide your current password.');
}

if (empty($new_password) || strlen($new_password) < 8) {
    redirectWithMessage('change-password', 'danger', 'New password must be at least 8 characters long.');
} elseif (!preg_match('/[A-Za-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
    redirectWithMessage('change-password', 'danger', 'New password must contain at least one letter and one number.');
}

if ($new_password !== $confirm_password) {
    redirectWithMessage('change-password', 'danger', 'New passwords do not match.');
}

try {
    global $pdo;
    // Get current user's password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        redirectWithMessage('login', 'danger', 'User not found. Please log in again.');
    }

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        redirectWithMessage('change-password', 'danger', 'Current password is incorrect.');
    }

    // Hash new password
    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

    // Update password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$new_password_hash, $_SESSION['user_id']]);

    // Log the password change event
    $log_message = date('Y-m-d H:i:s') . " - User ID {$_SESSION['user_id']} changed their password.\n";
    file_put_contents(LOGS_PATH . 'password_changes.log', $log_message, FILE_APPEND);

    // Regenerate session ID for security after password change
    session_regenerate_id(true);

    redirectWithMessage('profile', 'success', 'Password changed successfully!');
} catch (PDOException $e) {
    redirectWithMessage('change-password', 'danger', 'Database error: ' . $e->getMessage());
}
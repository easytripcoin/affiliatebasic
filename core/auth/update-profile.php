<?php
namespace AffiliateBasic\Core\Auth;

use function AffiliateBasic\Config\sanitizeInput;
use function AffiliateBasic\Config\verifyCSRFToken;
use function AffiliateBasic\Config\redirectWithMessage;
use PDOException;

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('profile', 'danger', 'Invalid request method.');
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('profile', 'danger', 'CSRF token validation failed.');
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirectWithMessage('login', 'danger', 'Please log in to update your profile.');
}

// Sanitize and validate input
$username = sanitizeInput($_POST['username'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

$errors = [];
if (empty($username) || strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required.';
}

// Check for existing username or email (excluding the current user)
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $_SESSION['user_id']]);
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Username or email already exists.';
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    redirectWithMessage('profile', 'danger', 'Database error: ' . $e->getMessage());
}

if (!empty($errors)) {
    redirectWithMessage('profile', 'danger', implode('<br>', $errors));
}

// Update user in the database
try {
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->execute([$username, $email, $_SESSION['user_id']]);

    // Update session variables
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    // Log the update
    $log_message = date('Y-m-d H:i:s') . " - Profile updated for user ID: {$_SESSION['user_id']} (IP: {$_SERVER['REMOTE_ADDR']})\n";
    file_put_contents(LOGS_PATH . 'profile_updates.log', $log_message, FILE_APPEND);

    redirectWithMessage('profile', 'success', 'Profile updated successfully.');
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    redirectWithMessage('profile', 'danger', 'Database error: ' . $e->getMessage());
}
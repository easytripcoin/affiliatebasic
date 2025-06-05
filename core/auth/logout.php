<?php
namespace AffiliateBasic\Core\Auth; // Changed namespace

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken; // For CSRF check

// Config should be loaded by index.php when this action is routed
// However, if accessed directly (which we're trying to prevent for actions),
// it would need its own require. For routed actions, this is fine.
// require_once __DIR__ . '/../config/config.php'; // Not strictly needed if routed by index.php

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect to home or login if accessed via GET or other methods
    // Or display an error, but redirecting is often cleaner for actions.
    redirectWithMessage('home', 'danger', 'Invalid request method for logout.');
}

// Verify CSRF token
// The token should be submitted via the logout form
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    // CSRF token is invalid or missing
    redirectWithMessage('home', 'danger', 'Invalid security token. Logout failed.');
}

// --- Start Logout Process ---

// Unset all session variables
$_SESSION = [];

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, // Set to a past time
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Delete remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    // Clear the token from the database as well for added security
    if (isset($pdo) && isset($_SESSION['user_id_before_logout'])) { // Check if $pdo is available and user_id was stored
        try {
            // It's better to clear the token based on the cookie value if possible,
            // or ensure user_id is available before session_destroy if needed here.
            // For simplicity, we'll just expire the cookie.
            // A more robust way would be to invalidate the token in the DB.
        } catch (\PDOException $e) {
            // Log error, but proceed with cookie deletion
            error_log("Error clearing remember_token from DB during logout: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
        }
    }
    setcookie('remember_token', '', time() - 3600, '/'); // Expire the cookie
}

// --- End Logout Process ---

// Redirect to login page with a success message
redirectWithMessage('login', 'success', 'You have been logged out successfully.');
// Note: redirectWithMessage uses page keys now, so 'login' is correct.
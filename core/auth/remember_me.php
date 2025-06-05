<?php
namespace AffiliateBasic\Core\Auth;

use PDO; // If not already in global scope or handled by autoloader
use PDOException; // If not already in global scope

// This script assumes config.php has been loaded by the calling script (index.php)
// and $pdo, LOGS_PATH, $subdirectory are available (e.g., as globals or passed in).

// If $pdo and $subdirectory are not automatically global from where config.php is included in index.php,
// you might need to declare them global here or refactor this into a function that accepts $pdo.
global $pdo; // Assuming $pdo & $subdirectory are set as global in config.php or index.php after config include

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if (isset($_COOKIE['remember_token'])) {
        // Ensure $pdo is available. If it's not global, this won't work.
        if (!isset($pdo)) {
            error_log("PDO object not available in handle_remember_me.php", 3, LOGS_PATH . 'critical_errors.log');
            // Potentially die or handle error, as DB access is crucial here
        } else {
            $token = $_COOKIE['remember_token'];
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? AND remember_token_expires > NOW()");
                $stmt->execute([$token]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && $user['is_verified']) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['logged_in'] = true;

                    $log_message = date('Y-m-d H:i:s') . " - Auto-login via remember token for user: {$user['username']} (ID: {$user['id']}, IP: {{$_SERVER['REMOTE_ADDR']}})\n";
                    file_put_contents(LOGS_PATH . 'login_success.log', $log_message, FILE_APPEND);
                } else {
                    setcookie('remember_token', '', time() - 3600, '/');
                }
            } catch (PDOException $e) {
                error_log("Database error during auto-login in handle_remember_me.php: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
            }
        }
    }
}
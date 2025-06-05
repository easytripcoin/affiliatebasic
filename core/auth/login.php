<?php
namespace AffiliateBasic\Core\Auth;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;
// Add use function statement for clarity and easier calling
use function AffiliateBasic\Core\Ecommerce\mergeGuestCartToUserDbCart;

use PDO;
use PDOException;

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('login', 'danger', 'Invalid request method.');
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('login', 'danger', 'CSRF token validation failed.');
    exit;
}

// Rate limiting: Prevent brute-force attacks
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
$failed_attempts_key = 'login_attempts_' . md5($ip_address);
$lockout_duration = 900; // 15 minutes in seconds
$max_attempts = 5; // Maximum failed attempts before lockout
$attempt_window = 900; // 15 minutes in seconds

// Check for lockout
if (isset($_SESSION[$failed_attempts_key])) {
    $attempt_data = $_SESSION[$failed_attempts_key];
    if ($attempt_data['count'] >= $max_attempts && (time() - $attempt_data['first_attempt_time']) < $lockout_duration) {
        redirectWithMessage('login', 'danger', 'Too many failed login attempts. Please try again in 15 minutes.');
        exit;
    }
}

// Sanitize input
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']) ? true : false;

// Validate inputs
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithMessage('login', 'danger', 'Please provide a valid email address.');
    exit;
}

if (empty($password)) {
    redirectWithMessage('login', 'danger', 'Please provide your password.');
    exit;
}

// Check user credentials
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Initialize or update failed login attempts
    if (!$user || !password_verify($password, $user['password'])) {
        if (!isset($_SESSION[$failed_attempts_key])) {
            $_SESSION[$failed_attempts_key] = [
                'count' => 1,
                'first_attempt_time' => time()
            ];
        } else {
            $attempt_data = $_SESSION[$failed_attempts_key];
            if ((time() - $attempt_data['first_attempt_time']) > $attempt_window) {
                $_SESSION[$failed_attempts_key] = [
                    'count' => 1,
                    'first_attempt_time' => time()
                ];
            } else {
                $_SESSION[$failed_attempts_key]['count'] = $attempt_data['count'] + 1;
            }
        }

        $log_message = date('Y-m-d H:i:s') . " - Failed login attempt for email: {$email} (IP: {$ip_address})\n";
        if (defined('LOGS_PATH')) {
            file_put_contents(LOGS_PATH . 'login_attempts.log', $log_message, FILE_APPEND);
        }
        redirectWithMessage('login', 'danger', 'Invalid email or password.');
        exit;
    }

    unset($_SESSION[$failed_attempts_key]);

    if (!$user['is_verified']) {
        redirectWithMessage('login', 'warning', 'Please verify your email address before logging in.');
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['logged_in'] = true;
    $_SESSION['is_admin'] = (bool) $user['is_admin'];
    $_SESSION['is_affiliate'] = (bool) $user['is_affiliate']; // Added this line

    // --- MERGE GUEST CART ---
    if (isset($_SESSION['pending_cart_to_merge_after_login']['items']) && !empty($_SESSION['pending_cart_to_merge_after_login']['items'])) {
        if (file_exists(__DIR__ . '/../ecommerce/cart_functions.php')) {
            require_once __DIR__ . '/../ecommerce/cart_functions.php';
        }

        $userIdToMerge = (int) $_SESSION['user_id'];
        $guestCartItemsToMerge = $_SESSION['pending_cart_to_merge_after_login']['items'];

        if (mergeGuestCartToUserDbCart($pdo, $userIdToMerge, $guestCartItemsToMerge)) {
            unset($_SESSION['pending_cart_to_merge_after_login']);
        } else {
            if (defined('LOGS_PATH')) {
                error_log("Failed to fully merge guest cart for user ID {$userIdToMerge} after login.", 3, LOGS_PATH . 'cart_errors.log');
            }
        }
    }
    // --- END MERGE GUEST CART ---

    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (60 * 60 * 24 * 30); // 30 days

        $stmt_remember = $pdo->prepare("UPDATE users SET remember_token = ?, remember_token_expires = ? WHERE id = ?");
        $stmt_remember->execute([$token, date('Y-m-d H:i:s', $expiry), $user['id']]);

        setcookie('remember_token', $token, [
            'expires' => $expiry,
            'path' => defined('BASE_PATH') ? BASE_PATH . '/' : '/', // Use configured base path
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    $log_message = date('Y-m-d H:i:s') . " - Successful login for user: {$user['username']} (ID: {$user['id']}, IP: {$ip_address})\n";
    if (defined('LOGS_PATH')) {
        file_put_contents(LOGS_PATH . 'login_success.log', $log_message, FILE_APPEND);
    }

    if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
        $redirectUrl = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        $redirectPageKey = ltrim(str_replace(SITE_URL, '', $redirectUrl), '/');
        if (empty($redirectPageKey))
            $redirectPageKey = 'home';
        redirectWithMessage($redirectPageKey, 'success', 'Login successful! You can now complete your order.');
    } else {
        redirectWithMessage('dashboard', 'success', 'Login successful!');
    }
    exit;

} catch (PDOException $e) {
    if (defined('LOGS_PATH')) {
        error_log("Database error during login: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    }
    redirectWithMessage('login', 'danger', 'A database error occurred. Please try again.');
    exit;
}

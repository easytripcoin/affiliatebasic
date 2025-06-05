<?php
namespace AffiliateBasic\Config;

// Function to sanitize input data
function sanitizeInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to redirect with message
// $url parameter should now be a path relative to SITE_URL, e.g., '/login' or '/dashboard'
// OR it can be a page key that index.php router understands e.g. 'login', 'dashboard'
function redirectWithMessage($urlPath, $type, $message)
{
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;

    // Ensure SITE_URL is defined (it should be from config.php)
    if (!defined('SITE_URL')) {
        // Fallback or error handling if SITE_URL is not defined
        // This should not happen if config.php is always included.
        header("Location: " . ltrim($urlPath, '/')); // Basic fallback
        exit();
    }

    // Construct the full URL. Ensure $urlPath starts with a / or is handled correctly.
    // If $urlPath is like 'login', it becomes SITE_URL . '/login'
    // If $urlPath is like '/login', it becomes SITE_URL . '/login'
    $finalUrl = SITE_URL . '/' . ltrim($urlPath, '/');

    header("Location: " . $finalUrl);
    exit();
}

// Function to display messages
function displayMessage()
{
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
    }
    return '';
}

// Function to generate CSRF token
function generateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to send email (remains the same, but links within emails will change)
function sendEmail($to, $subject, $body)
{
    // In a production environment, use PHPMailer or similar library
    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($to, $subject, $body, $headers);
}
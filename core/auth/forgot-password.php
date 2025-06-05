<?php
namespace AffiliateBasic\Core\Auth;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;

use PDO;
use PDOException;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('forgot-password', 'danger', 'Invalid request method.'); // Use page key
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('forgot-password', 'danger', 'CSRF token validation failed.'); // Use page key
}

// Rate limiting: Prevent abuse
$ip_address = $_SERVER['REMOTE_ADDR'];
$last_request_key = 'last_forgot_password_request_' . md5($ip_address);
$request_interval = 300; // 5 minutes in seconds

if (isset($_SESSION[$last_request_key])) {
    $last_request_time = $_SESSION[$last_request_key];
    if (time() - $last_request_time < $request_interval) {
        redirectWithMessage('forgot-password', 'danger', 'Please wait a few minutes before requesting another password reset.');
    }
}

// Sanitize input
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithMessage('forgot-password', 'danger', 'Please provide a valid email address.');
}

// Check if email exists
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Always show the same message to prevent email enumeration
    $generic_message = 'If an account exists with this email, a reset link has been sent.';

    if (!$user) {
        // Still update last request time to prevent spamming non-existent emails
        $_SESSION[$last_request_key] = time();
        redirectWithMessage('forgot-password', 'info', $generic_message);
    }

    // Generate reset token and expiry (1 hour from now)
    $reset_token = bin2hex(random_bytes(32));
    $reset_token_expires = date('Y-m-d H:i:s', time() + 3600);

    // Update user record with reset token
    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
    $stmt->execute([$reset_token, $reset_token_expires, $user['id']]);

    // Log the password reset request
    $log_message = date('Y-m-d H:i:s') . " - Password reset requested for email: {$email} (IP: {$ip_address})\n";
    file_put_contents(LOGS_PATH . 'password_reset_requests.log', $log_message, FILE_APPEND);

    // Send reset email using PHPMailer
    // Use the new URI structure for the reset link
    $reset_link = SITE_URL . "/reset-password?token=" . urlencode($reset_token);
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email, $user['username']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Password Reset Request";
        $mail->Body = "
            <html>
            <head>
                <title>Password Reset Request</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; }
                    .button:hover { background-color: #0056b3; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Password Reset Request</h2>
                    <p>Hello " . htmlspecialchars($user['username']) . ",</p>
                    <p>We received a request to reset your password. Click the button below to reset your password:</p>
                    <p><a href='$reset_link' class='button' style='color: #ffffff; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p><a href='$reset_link'>$reset_link</a></p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you didn't request this, please ignore this email or contact our support team.</p>
                    <p>Best regards,<br>Auth System Team</p>
                </div>
            </body>
            </html>
        ";

        $mail->send();

        // Update last request time
        $_SESSION[$last_request_key] = time();

        redirectWithMessage('login', 'success', $generic_message);
    } catch (Exception $e) {
        error_log("Failed to send password reset email: " . $mail->ErrorInfo, 3, LOGS_PATH . 'email_errors.log');
        // Don't reveal that the email sending failed if the user exists, stick to generic message
        redirectWithMessage('forgot-password', 'info', $generic_message . ' (Email sending may have encountered an issue, please try again or contact support if problems persist.)');
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    redirectWithMessage('forgot-password', 'danger', 'Database error. Please try again.');
}
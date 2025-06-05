<?php
namespace AffiliateBasic\Core\Auth;

use function AffiliateBasic\Config\sanitizeInput;
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;

use PDOException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException; // Alias PHPMailer's Exception

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('register', 'danger', 'Invalid request method.');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('register', 'danger', 'CSRF token validation failed.');
}

// Rate limiting
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
$last_registration_key = 'last_registration_' . md5($ip_address);
$registration_interval = 30; // Seconds

if (isset($_SESSION[$last_registration_key]) && (time() - $_SESSION[$last_registration_key] < $registration_interval)) {
    redirectWithMessage('register', 'warning', 'Please wait a few minutes before registering another account.');
}

// Sanitize and validate input
$username = sanitizeInput($_POST['username'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];
$form_data = ['username' => $username, 'email' => $email]; // Store for repopulating form

if (empty($username) || strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required.';
}
if (empty($password) || strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
} elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors[] = 'Password must contain at least one letter and one number.';
}
if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match.';
}

// Check for existing username or email
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Username or email already exists.';
    }
} catch (PDOException $e) {
    error_log("Database error during registration check: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    $errors[] = 'A database error occurred. Please try again later.';
}

if (!empty($errors)) {
    $_SESSION['form_data'] = $form_data;
    redirectWithMessage('register', 'danger', implode('<br>', $errors));
}
unset($_SESSION['form_data']); // Clear on successful validation path

// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Generate verification token
$verification_token = bin2hex(random_bytes(32));
$verification_token_expires = date('Y-m-d H:i:s', time() + 24 * 3600); // 24 hours

// Insert user into database
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, verification_token, verification_token_expires, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$username, $email, $password_hash, $verification_token, $verification_token_expires]);
    $new_user_id = $pdo->lastInsertId(); // Get the ID of the newly registered user

    // Log the registration
    $log_message = date('Y-m-d H:i:s') . " - New user registered: {$username} (ID: {$new_user_id}, Email: {$email}, IP: {$ip_address})\n";
    file_put_contents(LOGS_PATH . 'registrations.log', $log_message, FILE_APPEND);

    // Send verification email
    $verification_link = SITE_URL . "/verify-email?token=" . urlencode($verification_token);
    $mail = new PHPMailer(true);

    try {
        // Server settings from config.php (SMTP_HOST, SMTP_USERNAME, etc.)
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = defined('PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS') && SMTP_ENCRYPTION === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : (defined('PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS') && SMTP_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : false);
        $mail->Port = SMTP_PORT;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email, $username);

        $mail->isHTML(true);
        $mail->Subject = "Verify Your Email Address - AffiliateBasic";
        $mail->Body = "
            <html><head><title>Email Verification</title></head><body>
                <h2>Welcome to AffiliateBasic, " . htmlspecialchars($username) . "!</h2>
                <p>Please verify your email address by clicking the link below:</p>
                <p><a href='$verification_link'>$verification_link</a></p>
                <p>This link will expire in 24 hours.</p>
                <p>If you didn't register, please ignore this email.</p>
                <p>Best regards,<br>The AffiliateBasic Team</p>
            </body></html>";
        $mail->AltBody = "Hello " . htmlspecialchars($username) . ",\n\nPlease verify your email address by visiting this link:\n$verification_link\n\nThis link will expire in 24 hours.\n\nBest regards,\nThe AffiliateBasic Team";

        $mail->send();

        $_SESSION[$last_registration_key] = time(); // Update rate limit timestamp

        // --- NEW: MERGE GUEST CART IF USER WAS REDIRECTED FROM CHECKOUT ---
        // Note: Typically, after registration, user verifies email THEN logs in.
        // If your flow auto-logs in *before* verification (not recommended for security),
        // or if you want to merge guest cart immediately and prompt for verification upon next login to checkout,
        // then the merge logic would go here.
        // For now, we assume verification is required before login, so cart merge happens on first *login*.
        // If redirect_after_login was set (e.g. by checkout), it will be handled by login.php

        redirectWithMessage('login', 'success', 'Registration successful! Please check your email to verify your account.');

    } catch (PHPMailerException $e) {
        error_log("Failed to send verification email to {$email}: " . $mail->ErrorInfo, 3, LOGS_PATH . 'email_errors.log');
        // User is registered, but email failed. Inform them, maybe suggest contacting support.
        redirectWithMessage('register', 'warning', 'Registration successful, but we couldn\'t send the verification email. Please contact support or try registering again later.');
    }

} catch (PDOException $e) {
    error_log("Database error during registration: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    $_SESSION['form_data'] = $form_data; // Preserve form data on DB error
    redirectWithMessage('register', 'danger', 'A database error occurred. Please try again. ' . $e->getMessage());
}
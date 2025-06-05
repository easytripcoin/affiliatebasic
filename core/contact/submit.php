<?php
namespace AffiliateBasic\Core\Contact;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception; // Ensure this is used for PHPMailer exceptions
use PDOException; // For database exceptions

// Functions from config/functions.php are globally available as index.php includes config.php
// which in turn includes functions.php.
// So, explicit 'use function AuthBasic\Config\...' is not strictly needed here if they are global.
// However, for clarity, especially if moving to stricter namespacing/autoloading later, it can be good.
use function AffiliateBasic\Config\sanitizeInput;
use function AffiliateBasic\Config\verifyCSRFToken;
use function AffiliateBasic\Config\redirectWithMessage;

// Config.php is included by index.php, so $pdo, SITE_URL, LOGS_PATH, SMTP constants are available.
// global $pdo; // Only if not already globally available or if you prefer explicit declaration within functions.

// --- Main Script Execution Logic ---

// 1. Check Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('contact', 'danger', 'Invalid request method.');
    exit; // Ensure script termination after redirect
}

// 2. Verify CSRF Token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('contact', 'danger', 'CSRF token validation failed. Please try submitting the form again.');
    exit;
}

// 3. Rate Limiting (copied from original process_contact.php logic)
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$last_submission_key = 'last_contact_submission_' . md5($ip_address);
$submission_interval = 300; // 5 minutes in seconds

if (isset($_SESSION[$last_submission_key])) {
    $last_submission_time = $_SESSION[$last_submission_key];
    if (time() - $last_submission_time < $submission_interval) {
        redirectWithMessage('contact', 'warning', 'Please wait a few minutes before submitting another message.');
        exit;
    }
}

// 4. Sanitize and Validate Input
$name = sanitizeInput($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$subject = sanitizeInput($_POST['subject'] ?? '');
$message_content = sanitizeInput($_POST['message'] ?? ''); // Renamed to avoid conflict with $message in redirectWithMessage

$errors = [];

if (empty($name)) {
    $errors['name'] = 'Please provide your name.';
} elseif (strlen($name) > 100) {
    $errors['name'] = 'Name cannot exceed 100 characters.';
}

if (empty($email)) {
    $errors['email'] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please provide a valid email.';
} elseif (strlen($email) > 100) {
    $errors['email'] = 'Email cannot exceed 100 characters.';
}

if (empty($subject)) {
    $errors['subject'] = 'Subject is required.';
} elseif (strlen($subject) > 200) {
    $errors['subject'] = 'Subject cannot exceed 200 characters.';
}

if (empty($message_content)) {
    $errors['message'] = 'Message is required.';
} elseif (strlen($message_content) < 10) {
    $errors['message'] = 'Message should be at least 10 characters.';
} elseif (strlen($message_content) > 2000) {
    $errors['message'] = 'Message cannot exceed 2000 characters.';
}

if (!empty($errors)) {
    // Store errors and form data in session to repopulate the form
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST; // Store original POST data
    redirectWithMessage('contact', 'danger', 'Please correct the errors in the form.');
    exit;
}
// Clear any previous form errors if validation passes
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);


// 5. Process the Contact Form Submission
try {
    processContactSubmission($name, $email, $subject, $message_content);

    // Update last submission time
    $_SESSION[$last_submission_key] = time();

    redirectWithMessage('contact', 'success', 'Thank you for your message! We will get back to you soon.');
    exit;

} catch (\Exception $e) { // Catch generic Exception, which PHPMailer's Exception extends
    error_log("Contact form processing error: " . $e->getMessage(), 3, LOGS_PATH . 'contact_errors.log');
    redirectWithMessage('contact', 'danger', 'Message sending failed. Please try again later or contact support.');
    exit;
}


// --- Helper Functions (from your previous php_contact_submit_smtp_fix) ---

/**
 * Process contact form submission
 */
function processContactSubmission($name, $email, $subject, $message_content)
{
    global $pdo; // Ensure $pdo is accessible
    // Save to database
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, subject, message, submitted_at, ip_address) 
                              VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmt->execute([
            $name,
            $email,
            $subject,
            $message_content,
            $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
    } catch (PDOException $e) {
        error_log("Failed to save contact submission: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
        // Optionally re-throw or handle, but for contact form, email is often primary.
        // If DB save fails, we might still want to send the email.
        // For now, just log it. If this needs to stop the process, throw new \Exception($e->getMessage());
    }

    // Log the submission
    $log_message = date('Y-m-d H:i:s') . " - Contact form submission from {$email} (IP: {$_SERVER['REMOTE_ADDR']})\n";
    file_put_contents(LOGS_PATH . 'contact_submissions.log', $log_message, FILE_APPEND);

    // Send email notification to admin (this will throw an Exception on failure)
    sendContactEmail($name, $email, $subject, $message_content);

    // Send confirmation email to user (this logs errors but doesn't throw by default)
    sendContactConfirmation($email, $name, $subject, $message_content);
}

/**
 * Send contact email to admin using PHPMailer
 */
function sendContactEmail($name, $email, $subject, $message_content)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        if (SMTP_ENCRYPTION === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (SMTP_ENCRYPTION === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }
        $mail->Port = SMTP_PORT;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress(SMTP_FROM); // Admin email
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Contact Form Submission: " . htmlspecialchars($subject);
        $mail->Body = "
            <html><head><title>Contact Form Submission</title></head><body>
                <h2>New Contact Form Submission</h2>
                <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message_content)) . "</p>
                <hr><p>This message was submitted from IP: {$_SERVER['REMOTE_ADDR']}</p>
            </body></html>";
        $mail->AltBody = "New Contact Form Submission:\n\n"
            . "Name: " . htmlspecialchars($name) . "\n"
            . "Email: " . htmlspecialchars($email) . "\n"
            . "Subject: " . htmlspecialchars($subject) . "\n"
            . "Message:\n" . htmlspecialchars($message_content) . "\n\n"
            . "IP: {$_SERVER['REMOTE_ADDR']}";
        $mail->send();
    } catch (Exception $e) {
        error_log("Failed to send admin contact email: " . $mail->ErrorInfo, 3, LOGS_PATH . 'contact_errors.log');
        throw new Exception("Failed to send admin email: " . $mail->ErrorInfo); // Re-throw to be caught by main try-catch
    }
}

/**
 * Send confirmation email to user using PHPMailer
 */
function sendContactConfirmation($email, $name, $subject_submitted, $message_submitted) // Added params for submitted data
{
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
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Thank you for contacting us, " . htmlspecialchars($name) . "!";
        $mail->Body = "
            <html><head><title>Contact Confirmation</title></head><body>
                <h2>Hello " . htmlspecialchars($name) . ",</h2>
                <p>Thank you for contacting us. We have received your message and will get back to you as soon as possible.</p>
                <p>Here's a copy of the information you submitted:</p>
                <blockquote>
                    <p><strong>Subject:</strong> " . htmlspecialchars($subject_submitted) . "</p>
                    <p><strong>Message:</strong></p>
                    <p>" . nl2br(htmlspecialchars($message_submitted)) . "</p>
                </blockquote>
                <p>If you didn't submit this request, please contact our support team immediately.</p>
                <p>Best regards,<br>Auth System Team</p>
            </body></html>";
        $mail->AltBody = "Hello " . htmlspecialchars($name) . ",\n\n"
            . "Thank you for contacting us. We have received your message and will get back to you as soon as possible.\n\n"
            . "Subject: " . htmlspecialchars($subject_submitted) . "\n"
            . "Message:\n" . htmlspecialchars($message_submitted) . "\n\n"
            . "Best regards,\nAuth System Team";
        $mail->send();
    } catch (Exception $e) {
        error_log("Failed to send contact confirmation email to {$email}: " . $mail->ErrorInfo, 3, LOGS_PATH . 'contact_errors.log');
        // Do not re-throw here, as admin email might have succeeded.
    }
}
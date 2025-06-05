<?php
namespace AffiliateBasic\Config;

use PDO;
use PDOException;
use Dotenv\Dotenv; // Import Dotenv

define('DEBUG_MODE', true);
define('ALLOW_ADMIN_URL_CRON_TRIGGER', filter_var(env('ALLOW_ADMIN_URL_CRON_TRIGGER', false), FILTER_VALIDATE_BOOLEAN)); // Default to false
define('AFFILIATE_REFUND_PERIOD_DAYS', (int) env('AFFILIATE_REFUND_PERIOD_DAYS', 15)); // Default to 15 days

// Define PROJECT_ROOT_PATH early as it's needed by Dotenv
define('PROJECT_ROOT_PATH', dirname(__DIR__)); // authbasic project root

// Load Composer's autoloader
require_once PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Load environment variables from .env file
// It will look for .env in PROJECT_ROOT_PATH
try {
    $dotenv = Dotenv::createImmutable(PROJECT_ROOT_PATH);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // This can happen if .env file is missing. Provide a clear error or fallback.
    // For a production environment, you might want to die() if .env is crucial and missing.
    // For development, you might have default fallbacks or just log the error.
    error_log("Error loading .env file: " . $e->getMessage() . " - Please ensure a .env file exists in the project root based on .env.example.", 3, PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'critical_errors.log');
    // Optionally, you could die here if the .env file is absolutely critical for operation:
    // die("Critical configuration error: .env file not found or unreadable. Please create one from .env.example.");
}

// --- Retrieve Environment Variables with Fallbacks (optional, or die if not set) ---
// Helper function to get env variables with a default
function env(string $key, $default = null)
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

// Set the default timezone for the application
$appTimezone = env('APP_TIMEZONE', 'UTC'); // Default to UTC if not set
if (!date_default_timezone_set($appTimezone)) {
    error_log("Failed to set default timezone to " . $appTimezone);
}

// Base path configuration (less critical to be in .env, but can be)
define('BASE_PATH', env('APP_BASE_PATH', '')); // Relative base path for URLs, if needed.

// Path definitions using DIRECTORY_SEPARATOR
define('PAGES_PATH', PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . 'pages');
define('ASSETS_PATH', PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . 'assets');
define('LOGS_PATH', PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR);

// Ensure logs directory exists (same logic as before)
if (!is_dir(rtrim(LOGS_PATH, DIRECTORY_SEPARATOR))) {
    if (!mkdir(rtrim(LOGS_PATH, DIRECTORY_SEPARATOR), 0755, true) && !is_dir(rtrim(LOGS_PATH, DIRECTORY_SEPARATOR))) {
        die('Failed to create logs directory: ' . LOGS_PATH);
    }
}
// ... (is_writable check for LOGS_PATH)

// Database configuration from .env
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USERNAME', env('DB_USERNAME', 'root'));
define('DB_PASSWORD', env('DB_PASSWORD', '')); // Default to empty if not set
define('DB_NAME', env('DB_DATABASE', 'shopbasic_db'));
define('DB_PORT', env('DB_PORT', '3306')); // If you use a non-standard port

// Site URL from .env or dynamically determined
$appUrl = env('APP_URL');
$appSubdirectory = env('APP_SUBDIRECTORY', ''); // Default to no subdirectory

if ($appUrl) {
    define('SITE_URL', rtrim($appUrl, '/'));
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('SITE_URL', rtrim($protocol . $host . $appSubdirectory, '/'));
}
// This global variable is used by index.php for routing if SITE_URL includes a subdirectory
$subdirectory = $appSubdirectory;


// SMTP configuration for email from .env
define('SMTP_HOST', env('MAIL_HOST', 'smtp.gmail.com'));
define('SMTP_USERNAME', env('MAIL_USERNAME'));
define('SMTP_PASSWORD', env('MAIL_PASSWORD'));
define('SMTP_PORT', (int) env('MAIL_PORT', 587)); // Cast to int
define('SMTP_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls')); // 'tls' or 'ssl'
define('SMTP_FROM', env('MAIL_FROM_ADDRESS', 'noreply@example.com'));
define('SMTP_FROM_NAME', env('MAIL_FROM_NAME', 'AuthBasic System'));


// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Create database connection
// Check if essential DB variables are set
if (!DB_HOST || !DB_NAME || !DB_USERNAME) {
    $log_file = rtrim(LOGS_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'critical_errors.log';
    $error_message = date('[Y-m-d H:i:s]') . " Critical database configuration missing from environment variables (.env)." . PHP_EOL;
    @file_put_contents($log_file, $error_message, FILE_APPEND);
    die("Critical database configuration is missing. Please check your .env file or server environment variables.");
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);
} catch (PDOException $e) {
    $log_file = rtrim(LOGS_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'database_errors.log';
    $error_message = date('[Y-m-d H:i:s]') . " Database connection failed: " . $e->getMessage() . PHP_EOL;
    @file_put_contents($log_file, $error_message, FILE_APPEND);
    die("Unable to connect to the database. Please check configuration or contact support.");
}

// Include functions
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
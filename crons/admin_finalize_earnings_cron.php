<?php
// affiliatebasic/crons/admin_finalize_earnings_cron.php
// This script finalizes affiliate earnings past their refund/clearance period.
// It can be run as a server cron job or (for testing) triggered via URL by an admin.

// Define PROJECT_ROOT_PATH if this script is run outside the context of index.php
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(__DIR__));
}

// Include necessary configuration and functions
// This assumes your config.php sets up $pdo and defines constants like LOGS_PATH
// and starts the session (which is needed for admin check if run via URL)
require_once PROJECT_ROOT_PATH . '/config/config.php';
require_once PROJECT_ROOT_PATH . '/config/functions.php';
require_once PROJECT_ROOT_PATH . '/core/affiliate/affiliate_functions.php';

global $pdo;

// --- Access Control for URL Trigger ---
// Check if the script is being accessed via a web browser (HTTP/HTTPS)
$is_web_request = (isset($_SERVER['REQUEST_METHOD']));

if ($is_web_request) {
    // If it's a web request, check if URL triggering is allowed and if admin is logged in
    if (!defined('ALLOW_ADMIN_URL_CRON_TRIGGER') || ALLOW_ADMIN_URL_CRON_TRIGGER !== true) {
        http_response_code(403);
        die("URL triggering for this cron job is disabled.");
    }
    // Session should have been started by config.php (which includes functions.php which starts session)
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        // Provide a slightly more helpful message if accessed via URL without auth
        die("Access Denied. You must be an admin to trigger this job via URL. Please <a href='" . SITE_URL . "/login'>login</a>.");
    }
    // If admin is logged in and URL trigger is allowed, proceed.
    // Output will go to the browser.
    header('Content-Type: text/plain'); // Set content type for browser output
    echo "<pre>"; // Format browser output
    echo "Admin trigger acknowledged. Running finalize earnings job..." . PHP_EOL . PHP_EOL;
} else {
    // If it's a CLI request (presumably the Hostinger cron), no admin login is required.
    // Output will go to CLI or be redirected as per cron setup.
}
// --- End Access Control ---


// Set a higher execution time limit for cron jobs if needed (especially for CLI)
set_time_limit(300); // 5 minutes, adjust as necessary

// Define your refund period (e.g., 15 days). Ensure this matches your business logic.
// This should be defined in config.php
if (!defined('AFFILIATE_REFUND_PERIOD_DAYS')) {
    define('AFFILIATE_REFUND_PERIOD_DAYS', 15); // Default if not set in config
}

$cronJobName = "FinalizeAffiliateEarnings";
$logPrefix = "[" . date('Y-m-d H:i:s') . "] [$cronJobName] ";
echo $logPrefix . "Starting job." . PHP_EOL;


try {
    $totalProcessed = 0;
    $totalFailed = 0;

    // Get all earnings IDs that are 'awaiting_clearance' and past the refund period.
    $stmtEligible = $pdo->prepare(
        "SELECT id FROM affiliate_earnings 
         WHERE status = 'awaiting_clearance' 
         AND order_payment_confirmed_at IS NOT NULL 
         AND order_payment_confirmed_at <= DATE_SUB(NOW(), INTERVAL :refund_days DAY)"
    );
    $stmtEligible->bindValue(':refund_days', AFFILIATE_REFUND_PERIOD_DAYS, PDO::PARAM_INT);
    $stmtEligible->execute();
    $eligibleEarningIds = $stmtEligible->fetchAll(PDO::FETCH_COLUMN);

    if (empty($eligibleEarningIds)) {
        echo $logPrefix . "No earnings currently eligible for finalization (past " . AFFILIATE_REFUND_PERIOD_DAYS . " day refund period)." . PHP_EOL;
    } else {
        echo $logPrefix . "Found " . count($eligibleEarningIds) . " earnings eligible for finalization." . PHP_EOL;

        foreach ($eligibleEarningIds as $earningId) {
            if (AffiliateBasic\Core\Affiliate\finalizeAffiliateEarning($pdo, (int) $earningId)) {
                echo $logPrefix . "Successfully finalized earning ID: " . $earningId . PHP_EOL;
                $totalProcessed++;
            } else {
                // finalizeAffiliateEarning logs its own specific errors
                echo $logPrefix . "Failed to finalize earning ID: " . $earningId . ". See affiliate_errors.log or affiliate_info.log for details." . PHP_EOL;
                $totalFailed++;
            }
        }
    }

    echo $logPrefix . "Job finished. Successfully Processed: " . $totalProcessed . ", Failed to Process: " . $totalFailed . PHP_EOL;

} catch (\Exception $e) {
    $errorMessage = $logPrefix . "Job encountered an unhandled error: " . $e->getMessage() . PHP_EOL;
    echo $errorMessage;
    if (defined('LOGS_PATH')) {
        error_log($errorMessage, 3, LOGS_PATH . 'cron_errors.log');
    }
}

if ($is_web_request)
    echo "</pre>";

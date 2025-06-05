<?php
namespace AffiliateBasic\Core\Affiliate;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;
use function AffiliateBasic\Config\sanitizeInput;

use PDO;
use PDOException;

require_once dirname(__DIR__, 2) . '/config/config.php'; // Adjusted path
require_once dirname(__DIR__, 2) . '/config/functions.php'; // Adjusted path

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('affiliate-dashboard', 'danger', 'Invalid request method.');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_affiliate']) || $_SESSION['is_affiliate'] !== true) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}

if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('affiliate-dashboard', 'danger', 'CSRF token validation failed.');
}

$userId = (int) $_SESSION['user_id'];
$requestedAmount = filter_input(INPUT_POST, 'withdrawal_amount', FILTER_VALIDATE_FLOAT);
$paymentDetails = sanitizeInput($_POST['payment_details'] ?? '');

// Fetch current balance
$stmtUser = $pdo->prepare("SELECT affiliate_balance FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);
$currentBalance = $user ? (float) $user['affiliate_balance'] : 0.00;

if ($requestedAmount === false || $requestedAmount <= 0) {
    redirectWithMessage('affiliate-dashboard', 'danger', 'Invalid withdrawal amount specified.');
}

if ($requestedAmount > $currentBalance) {
    redirectWithMessage('affiliate-dashboard', 'danger', 'Withdrawal amount exceeds your available balance.');
}

if (empty($paymentDetails)) {
    redirectWithMessage('affiliate-dashboard', 'danger', 'Payment details are required.');
}

// Insert withdrawal request
try {
    $stmt = $pdo->prepare(
        "INSERT INTO withdrawal_requests (user_id, requested_amount, payment_details, status, requested_at) 
         VALUES (?, ?, ?, 'pending', NOW())"
    );
    if ($stmt->execute([$userId, $requestedAmount, $paymentDetails])) {
        redirectWithMessage('affiliate-dashboard', 'success', 'Withdrawal request submitted successfully. It will be processed by an admin.');
    } else {
        redirectWithMessage('affiliate-dashboard', 'danger', 'Could not submit withdrawal request. Please try again.');
    }
} catch (PDOException $e) {
    error_log("Withdrawal request error: " . $e->getMessage(), 3, LOGS_PATH . 'affiliate_errors.log');
    redirectWithMessage('affiliate-dashboard', 'danger', 'Database error during withdrawal request.');
}
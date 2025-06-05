<?php
namespace AffiliateBasic\Core\Affiliate;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;
use function AffiliateBasic\Config\sanitizeInput;
use PDO;
use PDOException;

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('admin-withdrawal-requests', 'danger', 'Invalid request method.');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !$_SESSION['is_admin']) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}

if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('admin-withdrawal-requests', 'danger', 'CSRF token validation failed.');
}

$requestId = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
$action = sanitizeInput($_POST['action'] ?? ''); // 'approve' or 'reject'
$adminNotes = sanitizeInput($_POST['admin_notes'] ?? '');


if (!$requestId || !in_array($action, ['approve', 'reject'])) {
    redirectWithMessage('admin-withdrawal-requests', 'danger', 'Invalid request ID or action.');
}

// Fetch the request to ensure it's pending and get details
$stmtReq = $pdo->prepare("SELECT * FROM withdrawal_requests WHERE id = ? AND status = 'pending'");
$stmtReq->execute([$requestId]);
$request = $stmtReq->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    redirectWithMessage('admin-withdrawal-requests', 'warning', 'Withdrawal request not found or already processed.');
}

$pdo->beginTransaction();
try {
    if ($action === 'approve') {
        // 1. Deduct from user's affiliate_balance
        $stmtUpdateBalance = $pdo->prepare("UPDATE users SET affiliate_balance = affiliate_balance - ? WHERE id = ? AND affiliate_balance >= ?");
        $stmtUpdateBalance->execute([$request['requested_amount'], $request['user_id'], $request['requested_amount']]);

        if ($stmtUpdateBalance->rowCount() === 0) {
            $pdo->rollBack();
            redirectWithMessage('admin-withdrawal-requests', 'danger', 'Failed to update user balance. Insufficient funds or user not found.');
        }

        // 2. Update withdrawal_requests status
        $stmtUpdateReq = $pdo->prepare("UPDATE withdrawal_requests SET status = 'approved', processed_at = NOW(), admin_notes = ? WHERE id = ?");
        $stmtUpdateReq->execute([$adminNotes, $requestId]);

        // 3. Update affiliate_earnings to 'paid' for the amounts that make up this withdrawal.
        // This is more complex: you need to find 'cleared' earnings for this user up to the withdrawal_amount
        // and mark them as 'paid'. For simplicity here, we'll assume this step is handled by admin review or a separate process.
        // A simple way for now: Mark all 'cleared' earnings as 'paid' if balance was sufficient.
        // More accurately, you'd track which earnings contribute to which withdrawal.
        // For this example, we're not directly linking earnings to withdrawals in DB when paying out.
        // The balance deduction is the main financial transaction record here.

    } elseif ($action === 'reject') {
        $stmtUpdateReq = $pdo->prepare("UPDATE withdrawal_requests SET status = 'rejected', processed_at = NOW(), admin_notes = ? WHERE id = ?");
        $stmtUpdateReq->execute([$adminNotes, $requestId]);
        // No balance change for rejection.
    }

    $pdo->commit();
    redirectWithMessage('admin-withdrawal-requests', 'success', 'Withdrawal request ' . $action . 'd successfully.');

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Admin process withdrawal error: " . $e->getMessage(), 3, LOGS_PATH . 'affiliate_errors.log');
    redirectWithMessage('admin-withdrawal-requests', 'danger', 'Database error while processing withdrawal.');
}
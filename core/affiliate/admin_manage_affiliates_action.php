<?php
namespace AffiliateBasic\Core\Affiliate;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;
use function AffiliateBasic\Config\sanitizeInput;
use PDOException;

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';
require_once __DIR__ . '/affiliate_functions.php'; // For generateUniqueUserAffiliateCode

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('admin-manage-affiliates', 'danger', 'Invalid request method.');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !$_SESSION['is_admin']) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}

if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('admin-manage-affiliates', 'danger', 'CSRF token validation failed.');
}

$userIdToManage = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$action = sanitizeInput($_POST['action'] ?? ''); // 'activate' or 'deactivate'

if (!$userIdToManage || !in_array($action, ['activate', 'deactivate'])) {
    redirectWithMessage('admin-manage-affiliates', 'danger', 'Invalid user ID or action.');
}

try {
    if ($action === 'activate') {
        // Check if user already has a code
        $stmtCheck = $pdo->prepare("SELECT user_affiliate_code FROM users WHERE id = ?");
        $stmtCheck->execute([$userIdToManage]);
        $existingCode = $stmtCheck->fetchColumn();

        $affiliateCode = $existingCode ?: generateUniqueUserAffiliateCode($pdo);

        $stmt = $pdo->prepare("UPDATE users SET is_affiliate = 1, user_affiliate_code = ? WHERE id = ?");
        $stmt->execute([$affiliateCode, $userIdToManage]);
        $message = 'User activated as affiliate.';
    } elseif ($action === 'deactivate') {
        // Consider what to do with user_affiliate_code upon deactivation.
        // Option 1: Nullify it (user can get a new one if reactivated)
        // Option 2: Keep it (if reactivated, they get the same code)
        // For simplicity, let's nullify it.
        $stmt = $pdo->prepare("UPDATE users SET is_affiliate = 0, user_affiliate_code = NULL WHERE id = ?");
        $stmt->execute([$userIdToManage]);
        $message = 'User deactivated as affiliate.';
    }
    redirectWithMessage('admin-manage-affiliates', 'success', $message);

} catch (PDOException $e) {
    error_log("Admin manage affiliate error: " . $e->getMessage(), 3, LOGS_PATH . 'affiliate_errors.log');
    redirectWithMessage('admin-manage-affiliates', 'danger', 'Database error during affiliate management.');
}
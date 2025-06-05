<?php
namespace AffiliateBasic\Core\Affiliate;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;
// finalizeAffiliateEarning will be in affiliate_functions.php

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/functions.php';
require_once __DIR__ . '/affiliate_functions.php'; // For finalizeAffiliateEarning

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('admin-finalize-earnings', 'danger', 'Invalid request method.');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}

if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('admin-finalize-earnings', 'danger', 'CSRF token validation failed.');
}

$action = $_POST['action'] ?? '';
$earning_ids_raw = $_POST['earning_ids'] ?? []; // For "finalize_selected"
$earning_ids = [];

if (strpos($action, 'finalize_single_') === 0) {
    $earning_ids[] = (int) substr($action, strlen('finalize_single_'));
} elseif ($action === 'finalize_selected') {
    foreach ($earning_ids_raw as $id) {
        if (filter_var($id, FILTER_VALIDATE_INT)) {
            $earning_ids[] = (int) $id;
        }
    }
}

if (empty($earning_ids)) {
    redirectWithMessage('admin-finalize-earnings', 'warning', 'No earnings selected or specified for finalization.');
}

$successCount = 0;
$failCount = 0;

foreach ($earning_ids as $earningId) {
    if (finalizeAffiliateEarning($pdo, $earningId)) {
        $successCount++;
    } else {
        $failCount++;
    }
}

$message = '';
if ($successCount > 0) {
    $message .= "$successCount earning(s) finalized successfully. ";
}
if ($failCount > 0) {
    $message .= "$failCount earning(s) could not be finalized (possibly already processed or invalid state).";
}

$messageType = ($successCount > 0 && $failCount == 0) ? 'success' : (($successCount > 0 && $failCount > 0) ? 'warning' : 'danger');
redirectWithMessage('admin-finalize-earnings', $messageType, trim($message));


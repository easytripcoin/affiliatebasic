<?php
namespace AffiliateBasic\Core\Ecommerce;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;

require_once __DIR__ . '/order_functions.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('admin-orders', 'danger', 'Invalid request method.');
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}
if (!verifyCSRFToken($_POST['csrf_token'])) {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    redirectWithMessage('admin-order-detail?id=' . $orderId, 'danger', 'CSRF token validation failed.');
}

$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$newOrderStatus = trim($_POST['order_status'] ?? '');
$newPaymentStatus = trim($_POST['payment_status'] ?? ''); // New payment status

// Define possible statuses for validation
$possible_order_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'pending_cod_confirmation'];
$possible_payment_statuses = ['pending_payment', 'paid', 'failed', 'refunded', 'pending_cod_confirmation', 'paid_placeholder'];

if (!$orderId || empty($newOrderStatus) || !in_array($newOrderStatus, $possible_order_statuses) || empty($newPaymentStatus) || !in_array($newPaymentStatus, $possible_payment_statuses)) {
    redirectWithMessage('admin-order-detail?id=' . $orderId, 'danger', 'Invalid data for status update.');
}

// Call the updated function (we will modify updateOrderStatus next)
if (updateOrderStatuses($pdo, $orderId, $newOrderStatus, $newPaymentStatus)) {
    redirectWithMessage('admin-order-detail?id=' . $orderId, 'success', 'Order and Payment statuses updated successfully.');
} else {
    redirectWithMessage('admin-order-detail?id=' . $orderId, 'danger', 'Failed to update statuses.');
}

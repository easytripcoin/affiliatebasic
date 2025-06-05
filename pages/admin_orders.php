<?php

// Ensure core functions and config are loaded.
// This is typically handled by index.php, but if accessing directly, you might need:
// require_once __DIR__ . '/../config/config.php'; // Defines PROJECT_ROOT_PATH, SITE_URL, $pdo
// require_once PROJECT_ROOT_PATH . '/config/functions.php'; // For redirectWithMessage, displayMessage
// require_once PROJECT_ROOT_PATH . '/core/ecommerce/order_functions.php'; // For getAllOrders

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Core\Ecommerce\getAllOrders; // Make sure this path and namespace are correct
use function AffiliateBasic\Core\Ecommerce\formatStatusText;
use function AffiliateBasic\Core\Ecommerce\getStatusBadgeClass;

// IMPORTANT: Include the functions file before calling its functions
require_once PROJECT_ROOT_PATH . '/core/ecommerce/order_functions.php';

global $pdo; // $pdo should be globally available from config.php

// Admin access check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirectWithMessage('login', 'danger', 'Access denied. You must be an admin to view this page.');
    exit;
}

$orders = getAllOrders($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Orders | Admin | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php
    // Ensure PROJECT_ROOT_PATH is defined in your config.php
    // Example: define('PROJECT_ROOT_PATH', dirname(__DIR__)); // if config.php is in config/
    if (!defined('PROJECT_ROOT_PATH')) {
        // Fallback or error, but this should be defined if config.php is loaded
        define('PROJECT_ROOT_PATH', dirname(__DIR__));
    }
    include PROJECT_ROOT_PATH . '/templates/navbar.php';
    ?>
    <main class="container py-5">
        <h1 class="mb-4">Manage Orders</h1>
        <?php echo displayMessage(); // This should use AffiliateBasic\Config\displayMessage if namespaced ?>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">No orders found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Order Status</th>
                            <th class="text-center">Payment Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(date('M j, Y, g:i a', strtotime($order['created_at']))); ?></td>
                                <td class="text-end">$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo getStatusBadgeClass($order['order_status']); ?>">
                                        <?php echo formatStatusText($order['order_status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo getStatusBadgeClass($order['payment_status']); ?>">
                                        <?php echo formatStatusText($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo SITE_URL; ?>/admin-order-detail?id=<?php echo $order['id']; ?>"
                                        class="btn btn-sm btn-info" title="View Details">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
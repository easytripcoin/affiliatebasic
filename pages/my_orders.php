<?php
// affiliatebasic/pages/my_orders.php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Core\Ecommerce\getOrdersByUserId;
use function AffiliateBasic\Core\Ecommerce\getTotalOrdersByUserId;
use function AffiliateBasic\Core\Ecommerce\formatStatusText;
use function AffiliateBasic\Core\Ecommerce\getStatusBadgeClass;

// Ensure user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirectWithMessage('login', 'danger', 'Please log in to view your orders.');
}

global $pdo;
$userId = (int) $_SESSION['user_id'];

// Include order functions if not already loaded by index.php (though it should be for page requests)
require_once PROJECT_ROOT_PATH . '/core/ecommerce/order_functions.php';

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10; // Orders per page
$offset = ($page - 1) * $limit;

$orders = getOrdersByUserId($pdo, $userId, $limit, $offset);
$totalOrders = getTotalOrdersByUserId($pdo, $userId);
$totalPages = ceil($totalOrders / $limit);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>

    <main class="container py-5">
        <h1 class="mb-4">My Orders</h1>
        <?php echo displayMessage(); ?>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info" role="alert">
                You have not placed any orders yet. <a href="<?php echo SITE_URL; ?>/products" class="alert-link">Start
                    shopping!</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Date Placed</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-center">Order Status</th>
                            <th class="text-center">Payment Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order['id']); ?></td>
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
                                    <a href="<?php echo SITE_URL; ?>/order-detail?id=<?php echo $order['id']; ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Order history pagination">
                    <ul class="pagination justify-content-center mt-4">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="<?php echo SITE_URL; ?>/my-orders?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php endif; ?>
    </main>

    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>

</html>
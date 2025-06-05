<?php
// affiliatebasic/pages/order_detail_user.php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Core\Ecommerce\getOrderDetails; // Re-use this function
use function AffiliateBasic\Core\Ecommerce\formatStatusText;
use function AffiliateBasic\Core\Ecommerce\getStatusBadgeClass;

// Ensure user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirectWithMessage('login', 'danger', 'Please log in to view your order details.');
}

global $pdo;
$userId = (int) $_SESSION['user_id'];
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId) {
    redirectWithMessage('my-orders', 'danger', 'Invalid order ID specified.');
}

// Include order functions
require_once PROJECT_ROOT_PATH . '/core/ecommerce/order_functions.php';

$order = getOrderDetails($pdo, $orderId);

// Verify the order belongs to the current user or if the user is an admin
if (!$order || ($order['user_id'] != $userId && (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true))) {
    redirectWithMessage('my-orders', 'danger', 'Order not found or you do not have permission to view it.');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo htmlspecialchars($order['id']); ?> Details | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Order Details #<?php echo htmlspecialchars($order['id']); ?></h1>
            <a href="<?php echo SITE_URL; ?>/my-orders" class="btn btn-outline-secondary"><i
                    class="bi bi-arrow-left"></i> Back to My Orders</a>
        </div>
        <?php echo displayMessage(); ?>

        <div class="card shadow-sm">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Order Date:</strong>
                        <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($order['created_at']))); ?>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <strong>Total Amount:</strong>
                        $<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h5>Shipping Address</h5>
                        <address>
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </address>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h5>Order & Payment Status</h5>
                        <p class="mb-1"><strong>Order Status:</strong>
                            <span class="badge bg-<?php echo getStatusBadgeClass($order['order_status']); ?>">
                                <?php echo formatStatusText($order['order_status']); ?>
                            </span>
                        </p>
                        <p class="mb-1"><strong>Payment Method:</strong>
                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_method']))); ?>
                        </p>
                        <p class="mb-0"><strong>Payment Status:</strong>
                            <span class="badge bg-<?php echo getStatusBadgeClass($order['payment_status']); ?>">
                                <?php echo formatStatusText($order['payment_status']); ?>
                            </span>
                        </p>
                    </div>
                </div>

                <h5 class="mt-4">Items in this Order</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width:15%;">Image</th>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Price/Unit</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $imageUrl = SITE_URL . '/assets/images/placeholder.png'; // Default
                                        if (!empty($item['image_url'])) {
                                            $itemImageUrl = htmlspecialchars(trim($item['image_url'], '/'));
                                            if (filter_var($itemImageUrl, FILTER_VALIDATE_URL)) {
                                                $imageUrl = $itemImageUrl;
                                            } elseif (file_exists(PROJECT_ROOT_PATH . '/' . $itemImageUrl)) {
                                                $imageUrl = SITE_URL . '/' . $itemImageUrl;
                                            }
                                        }
                                        ?>
                                        <img src="<?php echo $imageUrl; ?>"
                                            alt="<?php echo htmlspecialchars($item['product_name'] ?? 'Product Image'); ?>"
                                            class="img-fluid" style="max-height: 75px; object-fit: contain;">
                                    </td>
                                    <td>
                                        <a href="<?php echo SITE_URL . '/product?id=' . $item['product_id']; ?>"
                                            class="text-decoration-none">
                                            <?php echo htmlspecialchars($item['product_name'] ?? 'Product Deleted'); ?>
                                        </a>
                                    </td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td class="text-end">
                                        $<?php echo htmlspecialchars(number_format($item['price_per_unit'], 2)); ?></td>
                                    <td class="text-end">
                                        $<?php echo htmlspecialchars(number_format($item['price_per_unit'] * $item['quantity'], 2)); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>

</html>
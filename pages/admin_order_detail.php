<?php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Core\Ecommerce\getOrderDetails;
use function AffiliateBasic\Core\Ecommerce\getStatusBadgeClass; // Re-used for payment status
use function AffiliateBasic\Core\Ecommerce\formatStatusText; // Re-used for payment status

require_once PROJECT_ROOT_PATH . '/core/ecommerce/order_functions.php';

global $pdo;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirectWithMessage('login', 'danger', 'Access denied.');
    exit;
}

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    redirectWithMessage('admin-orders', 'danger', 'Invalid order ID.');
}

$order = getOrderDetails($pdo, $orderId);
if (!$order) {
    redirectWithMessage('admin-orders', 'danger', 'Order not found.');
}

// Define possible statuses for both order and payment
$possible_order_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'pending_cod_confirmation'];
$possible_payment_statuses = ['pending_payment', 'paid', 'failed', 'refunded', 'pending_cod_confirmation', 'paid_placeholder']; // Added 'paid_placeholder'

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order #<?php echo htmlspecialchars($order['id']); ?> Details | Admin | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Order Details #<?php echo htmlspecialchars($order['id']); ?></h1>
            <a href="<?php echo SITE_URL; ?>/admin-orders" class="btn btn-outline-secondary">Back to Orders</a>
        </div>
        <hr>
        <?php echo displayMessage(); ?>

        <div class="row">
            <div class="col-md-8">
                <h4>Items</h4>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price/Unit</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name'] ?? 'Product Deleted'); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($item['price_per_unit'], 2)); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($item['price_per_unit'] * $item['quantity'], 2)); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="text-end fw-bold fs-5">Total:
                    $<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>

                <h4>Shipping Address</h4>
                <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Information</h5>
                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?>
                            (<?php echo htmlspecialchars($order['customer_email']); ?>)</p>
                        <p><strong>Order Date:</strong>
                            <?php echo htmlspecialchars(date('M j, Y, g:i a', strtotime($order['created_at']))); ?></p>
                        <p><strong>Payment Method:</strong>
                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_method']))); ?>
                        </p>
                        <p><strong>Payment Status:</strong> <span
                                class="badge bg-<?php echo getStatusBadgeClass($order['payment_status']); ?>"><?php echo formatStatusText($order['payment_status']); ?></span>
                        </p>
                        <p><strong>Order Status:</strong> <span
                                class="badge bg-<?php echo getStatusBadgeClass($order['order_status']); ?>"><?php echo formatStatusText($order['order_status']); ?></span>
                        </p>

                        <hr>
                        <form action="<?php echo SITE_URL; ?>/admin-order-update-status-action" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">

                            <div class="mb-3">
                                <label for="order_status" class="form-label">Update Order Status:</label>
                                <select name="order_status" id="order_status" class="form-select">
                                    <?php foreach ($possible_order_statuses as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo ($order['order_status'] == $status) ? 'selected' : ''; ?>>
                                            <?php echo formatStatusText($status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="payment_status" class="form-label">Update Payment Status:</label>
                                <select name="payment_status" id="payment_status" class="form-select">
                                    <?php foreach ($possible_payment_statuses as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo ($order['payment_status'] == $status) ? 'selected' : ''; ?>>
                                            <?php echo formatStatusText($status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Update Statuses</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
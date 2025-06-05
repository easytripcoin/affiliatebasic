<?php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\redirectWithMessage;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirectWithMessage('login', 'warning', 'Please log in.');
}

$lastOrderId = $_SESSION['last_order_id'] ?? null;
// unset($_SESSION['last_order_id']); // Optional: clear after display
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Confirmation | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5 text-center">
        <?php echo displayMessage(); ?>
        <?php if ($lastOrderId): ?>
            <h1>Thank You for Your Order!</h1>
            <p class="lead">Your order #<?php echo htmlspecialchars($lastOrderId); ?> has been placed successfully.</p>
            <p>We will process it shortly. You can view your order details in your order history.</p>
        <?php else: ?>
            <h1>Order Confirmation</h1>
            <p class="lead">There seems to be an issue retrieving your order details, or no recent order was found.</p>
        <?php endif; ?>
        <a href="<?php echo SITE_URL; ?>/products" class="btn btn-primary mt-3">Continue Shopping</a>
        <a href="<?php echo SITE_URL; ?>/my-orders" class="btn btn-outline-secondary mt-3">View My Orders</a>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
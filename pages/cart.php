<?php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
// Ecommerce functions are included based on login state within the logic below

global $pdo; // $pdo from config.php

$cartItems = [];
$is_guest_cart = false;
$cartTotal = 0.00;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // --- GUEST USER ---
    $is_guest_cart = true;
    if (isset($_SESSION['guest_cart']['items']) && is_array($_SESSION['guest_cart']['items']) && !empty($_SESSION['guest_cart']['items'])) {
        $cartItems = array_values($_SESSION['guest_cart']['items']); // Ensure numeric keys for looping
    }
} else {
    // --- LOGGED-IN USER ---
    if (file_exists(PROJECT_ROOT_PATH . '/core/ecommerce/cart_functions.php')) {
        require_once PROJECT_ROOT_PATH . '/core/ecommerce/cart_functions.php';
    }
    $userId = (int) $_SESSION['user_id'];
    $cartItems = AffiliateBasic\Core\Ecommerce\getCartItems($pdo, $userId);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php
    if (!defined('PROJECT_ROOT_PATH')) {
        define('PROJECT_ROOT_PATH', dirname(__DIR__));
    }
    include PROJECT_ROOT_PATH . '/templates/navbar.php';
    ?>
    <main class="container py-5">
        <h1 class="mb-4">Your Shopping Cart</h1>
        <?php echo displayMessage(); ?>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info text-center">
                <p class="lead mb-3">Your cart is empty.</p>
                <a href="<?php echo SITE_URL; ?>/products" class="btn btn-primary btn-lg"><i class="bi bi-bag-plus"></i>
                    Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%;">Product</th>
                            <th style="width: 30%;"></th>
                            <th style="width: 15%;">Price</th>
                            <th style="width: 20%;">Quantity</th>
                            <th style="width: 15%;" class="text-end">Total</th>
                            <th style="width: 10%;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                            // Common properties
                            $productId = (int) $item['product_id'];
                            $productName = htmlspecialchars($item['name']);
                            $itemQuantity = (int) $item['quantity'];
                            $priceAtAddition = (float) $item['price_at_addition'];
                            $itemTotal = $priceAtAddition * $itemQuantity;
                            $cartTotal += $itemTotal;
                            $productStock = (int) ($is_guest_cart ? ($item['stock_quantity'] ?? 999) : ($item['product_stock'] ?? 999)); // product_stock is from getCartItems for DB
                    
                            $imageUrl = SITE_URL . '/assets/images/placeholder.png';
                            if (!empty($item['image_url'])) {
                                $itemImageUrl = htmlspecialchars(trim($item['image_url'], '/'));
                                if (filter_var($itemImageUrl, FILTER_VALIDATE_URL)) {
                                    $imageUrl = $itemImageUrl;
                                } elseif (file_exists(PROJECT_ROOT_PATH . '/' . $itemImageUrl)) {
                                    $imageUrl = SITE_URL . '/' . $itemImageUrl;
                                }
                            }

                            // ID for forms: cart_item_id for DB, product_id for session
                            $formIdentifierField = $is_guest_cart ? 'product_id' : 'cart_item_id';
                            $formIdentifierValue = $is_guest_cart ? $productId : $item['cart_item_id'];
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo SITE_URL; ?>/product?id=<?php echo $productId; ?>">
                                        <img src="<?php echo $imageUrl; ?>" alt="<?php echo $productName; ?>"
                                            style="width: 80px; height: 80px; object-fit: cover; border-radius: .25rem;">
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo SITE_URL; ?>/product?id=<?php echo $productId; ?>"
                                        class="text-decoration-none fw-bold">
                                        <?php echo $productName; ?>
                                    </a>
                                </td>
                                <td>$<?php echo htmlspecialchars(number_format($priceAtAddition, 2)); ?></td>
                                <td>
                                    <form action="<?php echo SITE_URL; ?>/cart-update-action" method="post"
                                        class="d-inline-flex align-items-center input-group input-group-sm"
                                        style="max-width: 150px;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="<?php echo $formIdentifierField; ?>"
                                            value="<?php echo $formIdentifierValue; ?>">
                                        <?php if (!$is_guest_cart): ?>
                                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                        <?php endif; ?>
                                        <input type="number" name="quantity" value="<?php echo $itemQuantity; ?>" min="0"
                                            max="<?php echo $productStock; ?>" class="form-control form-control-sm text-center"
                                            onchange="this.form.submit()"
                                            title="Set to 0 to remove. Max: <?php echo $productStock; ?>">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm"
                                            title="Update Quantity"><i class="bi bi-arrow-repeat"></i></button>
                                    </form>
                                </td>
                                <td class="text-end fw-medium">$<?php echo htmlspecialchars(number_format($itemTotal, 2)); ?>
                                </td>
                                <td class="text-center">
                                    <form action="<?php echo SITE_URL; ?>/cart-remove-action" method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="<?php echo $formIdentifierField; ?>"
                                            value="<?php echo $formIdentifierValue; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove item"
                                            onclick="return confirm('Are you sure you want to remove this item from your cart?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end border-top pt-3"><strong class="fs-5">Total:</strong></td>
                            <td class="text-end border-top pt-3"><strong
                                    class="fs-5">$<?php echo htmlspecialchars(number_format($cartTotal, 2)); ?></strong>
                            </td>
                            <td class="border-top"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <a href="<?php echo SITE_URL; ?>/products" class="btn btn-outline-secondary"><i
                        class="bi bi-arrow-left"></i> Continue Shopping</a>
                <?php if (!empty($cartItems)): ?>
                    <a href="<?php echo SITE_URL; ?>/checkout" class="btn btn-primary btn-lg">Proceed to Checkout <i
                            class="bi bi-arrow-right"></i></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
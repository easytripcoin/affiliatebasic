<?php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Config\redirectWithMessage;

global $pdo; // $pdo from config.php

// Ensure necessary function files are included, especially if accessed not through index.php
if (file_exists(PROJECT_ROOT_PATH . '/core/ecommerce/cart_functions.php')) {
    require_once PROJECT_ROOT_PATH . '/core/ecommerce/cart_functions.php';
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // --- GUEST USER ---
    // Check if guest has items in their session cart
    if (isset($_SESSION['guest_cart']['items']) && is_array($_SESSION['guest_cart']['items']) && !empty($_SESSION['guest_cart']['items'])) {
        // Store guest cart for merging after login/registration
        $_SESSION['pending_cart_to_merge_after_login'] = $_SESSION['guest_cart'];
        // Store the intended redirect URL (back to checkout)
        $_SESSION['redirect_after_login'] = SITE_URL . '/checkout';

        redirectWithMessage('login', 'info', 'Please log in or register to complete your purchase.');
        exit;
    } else {
        // Guest has no items, redirect to products page
        redirectWithMessage('products', 'info', 'Your cart is empty. Please add some products before checking out.');
        exit;
    }
}

// --- LOGGED-IN USER ---
$userId = (int) $_SESSION['user_id'];
$cartItems = AffiliateBasic\Core\Ecommerce\getCartItems($pdo, $userId); // Fetch from DB for logged-in user

if (empty($cartItems)) {
    redirectWithMessage('products', 'info', 'Your cart is empty. Please add products before checkout.');
    exit;
}

$cartTotal = 0.00;
foreach ($cartItems as $item) {
    $cartTotal += (float) $item['price_at_addition'] * (int) $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout | AffiliateBasic System</title>
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
        <div class="row g-5">
            <div class="col-md-5 col-lg-4 order-md-last">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-primary">Your cart</span>
                    <span class="badge bg-primary rounded-pill"><?php echo count($cartItems); ?></span>
                </h4>
                <ul class="list-group mb-3">
                    <?php foreach ($cartItems as $item): ?>
                        <li class="list-group-item d-flex justify-content-between lh-sm">
                            <div>
                                <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?>
                                    (x<?php echo (int) $item['quantity']; ?>)</h6>
                                <small class="text-muted">Price/unit:
                                    $<?php echo htmlspecialchars(number_format((float) $item['price_at_addition'], 2)); ?></small>
                            </div>
                            <span
                                class="text-muted">$<?php echo htmlspecialchars(number_format((float) $item['price_at_addition'] * (int) $item['quantity'], 2)); ?></span>
                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item d-flex justify-content-between bg-light">
                        <span class="fw-bold">Total (USD)</span>
                        <strong class="fw-bold">$<?php echo htmlspecialchars(number_format($cartTotal, 2)); ?></strong>
                    </li>
                </ul>
                <div class="card p-2">
                    <p class="mb-1"><strong>Logged in as:</strong></p>
                    <p class="mb-0"><i class="bi bi-person-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p class="mb-0 small"><i class="bi bi-envelope me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                </div>
            </div>

            <div class="col-md-7 col-lg-8">
                <h1 class="mb-3">Checkout</h1>
                <?php echo displayMessage(); ?>
                <h4 class="mb-3">Shipping & Payment</h4>
                <form action="<?php echo SITE_URL; ?>/order-place-action" method="post" class="needs-validation"
                    novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Shipping Address <span
                                class="text-danger">*</span></label>
                        <textarea name="shipping_address" id="shipping_address" class="form-control" rows="4" required
                            placeholder="1234 Main St&#10;Apartment, studio, or floor&#10;City, State, ZIP"></textarea>
                        <div class="invalid-feedback">Please enter your shipping address.</div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method <span
                                class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="">Select Payment Method...</option>
                            <option value="cod">Cash on Delivery (COD)</option>
                            <option value="placeholder_card">Credit/Debit Card (Placeholder)</option>
                        </select>
                        <div class="invalid-feedback">Please select a payment method.</div>
                    </div>

                    <div id="card_details_placeholder" style="display:none;"
                        class="mb-3 border p-3 rounded bg-light shadow-sm">
                        <p class="text-muted small mb-2">This is a placeholder for card payment fields. No actual
                            payment will be processed. Enter any details for simulation.</p>
                        <div class="mb-2">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" id="card_number" class="form-control" placeholder="1234-5678-9012-3456">
                        </div>
                        <div class="row">
                            <div class="col-md-7 mb-2">
                                <label for="card_expiry" class="form-label">Expiry (MM/YY)</label>
                                <input type="text" id="card_expiry" class="form-control" placeholder="MM/YY">
                            </div>
                            <div class="col-md-5 mb-2">
                                <label for="card_cvv" class="form-label">CVV</label>
                                <input type="text" id="card_cvv" class="form-control" placeholder="123">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-shield-check"></i> Place
                        Order</button>
                </form>
            </div>
        </div>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })();

        const paymentMethodSelect = document.getElementById('payment_method');
        const cardDetailsPlaceholder = document.getElementById('card_details_placeholder');
        if (paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', function () {
                if (this.value === 'placeholder_card') {
                    cardDetailsPlaceholder.style.display = 'block';
                } else {
                    cardDetailsPlaceholder.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>
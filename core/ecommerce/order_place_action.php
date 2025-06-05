<?php
namespace AffiliateBasic\Core\Ecommerce;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;
use function AffiliateBasic\Core\Ecommerce\getProductById;
use function AffiliateBasic\Core\Ecommerce\getCartItems;
use function AffiliateBasic\Core\Ecommerce\createOrder;
use function AffiliateBasic\Core\Ecommerce\getOrderItemsForOrder;
use function AffiliateBasic\Core\Ecommerce\clearUserCart;
use function AffiliateBasic\Core\Affiliate\createAffiliateEarning;

// Make sure to include product_functions to get product details like bonus percentage
require_once __DIR__ . '/product_functions.php';
require_once __DIR__ . '/cart_functions.php';
require_once __DIR__ . '/order_functions.php'; // For createOrder
require_once __DIR__ . '/../affiliate/affiliate_functions.php'; // For createAffiliateEarning

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('checkout', 'danger', 'Invalid request method.');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    redirectWithMessage('login', 'warning', 'Please log in to place an order.');
}

if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('checkout', 'danger', 'CSRF token validation failed.');
}

$userId = (int) $_SESSION['user_id'];
$shippingAddress = trim($_POST['shipping_address'] ?? '');
$paymentMethod = trim($_POST['payment_method'] ?? '');

// Affiliate details from session
$referrerUserId = $_SESSION['referrer_user_id'] ?? null;
$affiliateCodeUsed = $_SESSION['affiliate_code_used'] ?? null;

if (empty($shippingAddress) || empty($paymentMethod)) {
    redirectWithMessage('checkout', 'danger', 'Shipping address and payment method are required.');
}

$cartItems = getCartItems($pdo, $userId);

if (empty($cartItems)) {
    redirectWithMessage('products', 'info', 'Your cart is empty. Cannot place an order.');
}

$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['price_at_addition'] * $item['quantity'];
}

// Simulate payment status (in a real app, this would come after payment gateway interaction)
$paymentStatus = ($paymentMethod === 'cod') ? 'pending_cod_confirmation' : 'pending_payment';
if ($paymentMethod === 'placeholder_card') {
    // Simulate a successful placeholder payment
    $paymentStatus = 'paid_placeholder';
}

try {
    // Pass affiliate details to createOrder
    $orderId = createOrder($pdo, $userId, $totalAmount, $shippingAddress, $paymentMethod, $paymentStatus, $cartItems, $referrerUserId, $affiliateCodeUsed);

    if ($orderId) {
        // If order is successful and there was a referrer, process earnings
        if ($referrerUserId) {
            $orderItemsForEarnings = getOrderItemsForOrder($pdo, $orderId); // You'll need to create this function
            foreach ($orderItemsForEarnings as $orderItem) {
                $productDetails = getProductById($pdo, $orderItem['product_id']);
                if ($productDetails && $productDetails['affiliate_bonus_percentage'] > 0) {
                    $earnedAmount = ($orderItem['price_per_unit'] * $orderItem['quantity']) * ($productDetails['affiliate_bonus_percentage'] / 100);
                    // Create an affiliate earning record
                    createAffiliateEarning(
                        $pdo,
                        $referrerUserId,
                        $orderId,
                        $orderItem['id'], // order_item_id
                        $orderItem['product_id'],
                        $earnedAmount,
                        $productDetails['affiliate_bonus_percentage']
                    );
                }
            }
        }

        clearUserCart($pdo, $userId);
        $_SESSION['last_order_id'] = $orderId;
        // Clear referral session data after successful order
        unset($_SESSION['referrer_user_id']);
        unset($_SESSION['affiliate_code_used']);

        redirectWithMessage('order-confirmation', 'success', 'Your order has been placed successfully! Order ID: ' . $orderId);
    } else {
        redirectWithMessage('checkout', 'danger', 'There was an issue placing your order. Please try again.');
    }
} catch (\Exception $e) {
    redirectWithMessage('checkout', 'danger', 'Could not place order: ' . $e->getMessage());
}
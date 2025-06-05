<?php
namespace AffiliateBasic\Core\Ecommerce;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;

// Ensure cart_functions.php is loaded
if (file_exists(__DIR__ . '/cart_functions.php')) {
    require_once __DIR__ . '/cart_functions.php';
}

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('cart', 'danger', 'Invalid request method.');
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('cart', 'danger', 'CSRF token validation failed.');
    exit;
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id'])) {
    // --- LOGGED-IN USER ---
    $cartItemId = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);
    if (!$cartItemId) {
        redirectWithMessage('cart', 'danger', 'Invalid item ID.');
        exit;
    }
    if (removeCartItem($pdo, $cartItemId, (int) $_SESSION['user_id'])) {
        redirectWithMessage('cart', 'success', 'Item removed from cart.');
    } else {
        redirectWithMessage('cart', 'danger', 'Could not remove item from cart.');
    }
} else {
    // --- GUEST USER ---
    $productIdToRemove = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    if (!$productIdToRemove) {
        redirectWithMessage('cart', 'danger', 'Invalid product ID for guest cart removal.');
        exit;
    }

    $itemIdKey = (string) $productIdToRemove;

    if (isset($_SESSION['guest_cart']['items'][$itemIdKey])) {
        unset($_SESSION['guest_cart']['items'][$itemIdKey]);
        if (empty($_SESSION['guest_cart']['items'])) { // Clear entire guest cart if now empty
            unset($_SESSION['guest_cart']);
        } else {
            $_SESSION['guest_cart']['updated_at'] = time();
        }
        redirectWithMessage('cart', 'success', 'Item removed from guest cart.');
    } else {
        redirectWithMessage('cart', 'danger', 'Item not found in guest cart.');
    }
}
exit;
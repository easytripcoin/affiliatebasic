<?php
namespace AffiliateBasic\Core\Ecommerce;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;
use PDO;

// Ensure cart_functions and product_functions are loaded
if (file_exists(__DIR__ . '/cart_functions.php')) {
    require_once __DIR__ . '/cart_functions.php';
}
if (file_exists(__DIR__ . '/product_functions.php')) {
    require_once __DIR__ . '/product_functions.php';
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

$newQuantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if ($newQuantity === false || $newQuantity < 0) { // Allow 0 to trigger removal
    redirectWithMessage('cart', 'danger', 'Invalid quantity provided.');
    exit;
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id'])) {
    // --- LOGGED-IN USER ---
    $cartItemId = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);
    $userId = (int) $_SESSION['user_id'];

    if (!$cartItemId) {
        redirectWithMessage('cart', 'danger', 'Invalid cart item ID for update.');
        exit;
    }

    // Fetch product_id and stock for the cart item to check against new quantity
    $stmtCheck = $pdo->prepare(
        "SELECT ci.product_id, p.stock_quantity, p.name as product_name 
         FROM cart_items ci 
         JOIN products p ON ci.product_id = p.id 
         JOIN cart c ON ci.cart_id = c.id
         WHERE ci.id = ? AND c.user_id = ?"
    );
    $stmtCheck->execute([$cartItemId, $userId]);
    $itemDetails = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$itemDetails) {
        redirectWithMessage('cart', 'danger', 'Cart item not found or does not belong to you.');
        exit;
    }

    if ($newQuantity > $itemDetails['stock_quantity']) {
        redirectWithMessage('cart', 'warning', 'Requested quantity (' . $newQuantity . ') exceeds available stock (' . $itemDetails['stock_quantity'] . ') for ' . htmlspecialchars($itemDetails['product_name']) . '. Cart not updated.');
        exit;
    }

    if ($newQuantity == 0) {
        if (removeCartItem($pdo, $cartItemId, $userId)) {
            redirectWithMessage('cart', 'success', 'Item removed from cart.');
        } else {
            redirectWithMessage('cart', 'danger', 'Could not remove item from cart.');
        }
    } elseif (updateCartItemQuantity($pdo, $cartItemId, $newQuantity, $userId)) {
        redirectWithMessage('cart', 'success', 'Cart updated successfully.');
    } else {
        redirectWithMessage('cart', 'danger', 'Could not update cart item.');
    }

} else {
    // --- GUEST USER ---
    $productIdToUpdate = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if (!$productIdToUpdate) {
        redirectWithMessage('cart', 'danger', 'Invalid product ID for guest cart update.');
        exit;
    }

    $itemIdKey = (string) $productIdToUpdate;

    if (isset($_SESSION['guest_cart']['items'][$itemIdKey])) {
        if ($newQuantity == 0) { // Remove item if quantity is 0
            unset($_SESSION['guest_cart']['items'][$itemIdKey]);
            if (empty($_SESSION['guest_cart']['items'])) { // Clear entire guest cart if empty
                unset($_SESSION['guest_cart']);
            } else {
                $_SESSION['guest_cart']['updated_at'] = time();
            }
            redirectWithMessage('cart', 'success', 'Item removed from guest cart.');
        } else {
            $product = getProductById($pdo, $productIdToUpdate);
            if (!$product) {
                unset($_SESSION['guest_cart']['items'][$itemIdKey]); // Remove if product DNE
                redirectWithMessage('cart', 'danger', 'Product for guest cart update not found. Item removed.');
                exit;
            }
            if ($newQuantity > $product['stock_quantity']) {
                redirectWithMessage('cart', 'warning', 'Requested quantity (' . $newQuantity . ') exceeds available stock (' . $product['stock_quantity'] . ') for ' . htmlspecialchars($product['name']) . '. Guest cart not updated.');
            } else {
                $_SESSION['guest_cart']['items'][$itemIdKey]['quantity'] = $newQuantity;
                $_SESSION['guest_cart']['updated_at'] = time();
                redirectWithMessage('cart', 'success', 'Guest cart updated.');
            }
        }
    } else {
        redirectWithMessage('cart', 'danger', 'Item not found in guest cart.');
    }
}
exit;
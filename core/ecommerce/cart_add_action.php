<?php
namespace AffiliateBasic\Core\Ecommerce;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;

// Ensure product_functions and cart_functions are loaded
// These should ideally be handled by your autoloader or a central include in index.php
// For robustness in a direct-access scenario (though not recommended for action scripts):
if (file_exists(__DIR__ . '/product_functions.php')) {
    require_once __DIR__ . '/product_functions.php';
}
if (file_exists(__DIR__ . '/cart_functions.php')) {
    require_once __DIR__ . '/cart_functions.php';
}


global $pdo; // Assuming $pdo is globally available from config.php (via index.php)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('products', 'danger', 'Invalid request method.');
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('products', 'danger', 'CSRF token validation failed.');
    exit;
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$productId || !$quantity || $quantity < 1) {
    redirectWithMessage('products', 'danger', 'Invalid product data.');
    exit;
}

$product = getProductById($pdo, $productId);

if (!$product) {
    redirectWithMessage('products', 'danger', 'Product not found.');
    exit;
}
// Combined stock check for both guest and logged-in scenarios will happen below


if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id'])) {
    // --- LOGGED-IN USER ---
    $userId = (int) $_SESSION['user_id'];
    $cartId = getOrCreateUserCart($pdo, $userId);

    if (!$cartId) {
        redirectWithMessage('products', 'danger', 'Could not access your cart. Please try again.');
        exit;
    }

    // Check current quantity in DB cart if item exists to prevent over-adding
    $stmtCheck = $pdo->prepare("SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmtCheck->execute([$cartId, $productId]);
    $existingCartItem = $stmtCheck->fetch();
    $currentCartQuantity = $existingCartItem ? (int) $existingCartItem['quantity'] : 0;

    if (($currentCartQuantity + $quantity) > $product['stock_quantity']) {
        redirectWithMessage('product?id=' . $productId, 'danger', 'Adding this quantity would exceed available stock for ' . htmlspecialchars($product['name']) . '. You have ' . $currentCartQuantity . ' in cart. Available: ' . $product['stock_quantity']);
        exit;
    }

    if (addItemToCart($pdo, $cartId, $productId, $quantity, (float) $product['price'])) {
        redirectWithMessage('cart', 'success', htmlspecialchars($product['name']) . ' added to your cart!');
    } else {
        redirectWithMessage('product?id=' . $productId, 'danger', 'Could not add item to cart.');
    }
} else {
    // --- GUEST USER ---
    if (!isset($_SESSION['guest_cart']) || !is_array($_SESSION['guest_cart'])) {
        $_SESSION['guest_cart'] = ['items' => []];
    }

    $itemIdKey = (string) $productId; // Use product ID as key in session cart

    $currentGuestCartQuantity = isset($_SESSION['guest_cart']['items'][$itemIdKey]['quantity']) ? (int) $_SESSION['guest_cart']['items'][$itemIdKey]['quantity'] : 0;

    if (($currentGuestCartQuantity + $quantity) > $product['stock_quantity']) {
        redirectWithMessage('product?id=' . $productId, 'danger', 'Adding this quantity would exceed available stock for ' . htmlspecialchars($product['name']) . '. You have ' . $currentGuestCartQuantity . ' in your guest cart. Available: ' . $product['stock_quantity']);
        exit;
    }

    if (isset($_SESSION['guest_cart']['items'][$itemIdKey])) {
        $_SESSION['guest_cart']['items'][$itemIdKey]['quantity'] += $quantity;
        // Price at addition is set once, not typically updated on quantity change for guest simplicity
    } else {
        $_SESSION['guest_cart']['items'][$itemIdKey] = [
            'product_id' => $productId,
            'name' => $product['name'],
            'quantity' => $quantity,
            'price_at_addition' => (float) $product['price'],
            'image_url' => $product['image_url'], // For display in cart
            'stock_quantity' => $product['stock_quantity'] // Store stock for reference in cart page
        ];
    }
    $_SESSION['guest_cart']['updated_at'] = time(); // Optional: for session cleanup logic
    redirectWithMessage('cart', 'success', htmlspecialchars($product['name']) . ' added to your guest cart!');
}
exit;
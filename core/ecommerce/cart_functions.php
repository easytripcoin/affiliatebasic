<?php
namespace AffiliateBasic\Core\Ecommerce;

use PDO;

// Get or create a cart for the logged-in user
function getOrCreateUserCart(PDO $pdo, int $userId): ?int
{
    // Check if user has an active cart
    $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        return (int) $cart['id'];
    } else {
        // Create a new cart
        $stmt = $pdo->prepare("INSERT INTO cart (user_id) VALUES (?)");
        if ($stmt->execute([$userId])) {
            return (int) $pdo->lastInsertId();
        }
    }
    return null;
}

// Add item to cart or update quantity if it already exists
function addItemToCart(PDO $pdo, int $cartId, int $productId, int $quantity, float $priceAtAddition): bool
{
    // Check if item already in cart
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cartId, $productId]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        // Update quantity
        $newQuantity = $cartItem['quantity'] + $quantity;
        $stmtUpdate = $pdo->prepare("UPDATE cart_items SET quantity = ?, price_at_addition = ?, updated_at = NOW() WHERE id = ?");
        return $stmtUpdate->execute([$newQuantity, $priceAtAddition, $cartItem['id']]);
    } else {
        // Add new item
        $stmtInsert = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price_at_addition) VALUES (?, ?, ?, ?)");
        return $stmtInsert->execute([$cartId, $productId, $quantity, $priceAtAddition]);
    }
}

// Get cart items for a user
function getCartItems(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare("
        SELECT ci.id as cart_item_id, p.id as product_id, p.name, p.image_url, p.stock_quantity as product_stock, ci.quantity, ci.price_at_addition
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        JOIN cart c ON ci.cart_id = c.id
        WHERE c.user_id = ?
        ORDER BY ci.created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Update cart item quantity (for logged-in user)
function updateCartItemQuantity(PDO $pdo, int $cartItemId, int $newQuantity, int $userId): bool
{
    $stmt = $pdo->prepare("
        UPDATE cart_items ci
        JOIN cart c ON ci.cart_id = c.id
        SET ci.quantity = ?, ci.updated_at = NOW()
        WHERE ci.id = ? AND c.user_id = ?
    ");
    return $stmt->execute([$newQuantity, $cartItemId, $userId]);
}

// Remove item from cart (for logged-in user)
function removeCartItem(PDO $pdo, int $cartItemId, int $userId): bool
{
    $stmt = $pdo->prepare("
        DELETE ci FROM cart_items ci
        JOIN cart c ON ci.cart_id = c.id
        WHERE ci.id = ? AND c.user_id = ?
    ");
    return $stmt->execute([$cartItemId, $userId]);
}

// Clear user's cart (e.g., after order placement)
function clearUserCart(PDO $pdo, int $userId): bool
{
    $cartId = getOrCreateUserCart($pdo, $userId); // This will get the cart ID
    if ($cartId) {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        return $stmt->execute([$cartId]);
    }
    return false;
}

/**
 * Merges a guest's session cart into their database cart after login/registration.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param int $userId The ID of the user whose cart is being merged into.
 * @param array $guestCartItems The items from the guest's session cart.
 * Expected format: [product_id => ['product_id' => ..., 'quantity' => ..., 'price_at_addition' => ...]]
 * @return bool True if merge operation was attempted (individual item additions might still fail silently if stock changes).
 */
function mergeGuestCartToUserDbCart(PDO $pdo, int $userId, array $guestCartItems): bool
{
    if (empty($guestCartItems)) {
        return true; // Nothing to merge
    }

    $dbUserCartId = getOrCreateUserCart($pdo, $userId);
    if (!$dbUserCartId) {
        error_log("Failed to get or create cart for user ID {$userId} during merge.", 3, LOGS_PATH . 'cart_errors.log');
        return false; // Failed to get/create a DB cart for the user
    }

    $all_successful = true;
    foreach ($guestCartItems as $item) {
        if (!isset($item['product_id'], $item['quantity'], $item['price_at_addition'])) {
            error_log("Skipping malformed guest cart item during merge for user ID {$userId}.", 3, LOGS_PATH . 'cart_errors.log');
            continue;
        }
        // Optional: Re-check product existence and stock here before adding if desired, though addItemToCart might implicitly handle some of this
        // For simplicity, addItemToCart will attempt to add/update.
        // The price_at_addition from the guest cart is preserved.
        if (!addItemToCart($pdo, $dbUserCartId, $item['product_id'], $item['quantity'], (float) $item['price_at_addition'])) {
            error_log("Failed to merge item ID {$item['product_id']} for user ID {$userId}.", 3, LOGS_PATH . 'cart_errors.log');
            $all_successful = false; // Mark if any item fails
        }
    }
    return $all_successful; // Returns true if all items were processed without addItemToCart returning false
}


/**
 * Gets the total number of items in the cart (sum of quantities).
 * Handles both guest (session) and logged-in (DB) users.
 *
 * @param PDO $pdo The PDO database connection object.
 * @return int Total number of items in the cart.
 */
function getCartDisplayItemCount(PDO $pdo): int
{
    $cartItemCount = 0;
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $cartId = getOrCreateUserCart($pdo, $userId); // Ensure cart exists or get ID
        if ($cartId) {
            $stmt = $pdo->prepare("SELECT SUM(quantity) as total_items FROM cart_items WHERE cart_id = ?");
            $stmt->execute([$cartId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $cartItemCount = $result && $result['total_items'] ? (int) $result['total_items'] : 0;
        }
    } elseif (isset($_SESSION['guest_cart']['items']) && is_array($_SESSION['guest_cart']['items'])) {
        foreach ($_SESSION['guest_cart']['items'] as $item) {
            if (isset($item['quantity'])) {
                $cartItemCount += $item['quantity'];
            }
        }
    }
    return $cartItemCount;
}
<?php
namespace AffiliateBasic\Core\Ecommerce;

use PDO;
use PDOException;

/**
 * Updates the order and payment statuses of an order.
 * Marks affiliate earnings as 'awaiting_clearance' or 'cancelled' based on new statuses.
 * @param PDO $pdo Database connection
 * @param int $orderId Order ID to update
 * @param string $newOrderStatus New order status
 * @param string $newPaymentStatus New payment status
 * @return bool Returns true on success, false on failure
 */
function updateOrderStatuses(PDO $pdo, int $orderId, string $newOrderStatus, string $newPaymentStatus): bool
{
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?");
    $success = $stmt->execute([$newOrderStatus, $newPaymentStatus, $orderId]);

    if ($success) {
        // If order is marked as delivered AND payment is confirmed (paid/paid_placeholder)
        if ($newOrderStatus === 'delivered' && ($newPaymentStatus === 'paid' || $newPaymentStatus === 'paid_placeholder')) {
            markAffiliateEarningsAsAwaitingClearance($pdo, $orderId);
        }
        // If order is cancelled OR payment failed/refunded
        elseif (
            $newOrderStatus === 'cancelled' ||
            $newPaymentStatus === 'failed' ||
            $newPaymentStatus === 'refunded'
        ) {
            cancelAffiliateEarningsForOrder($pdo, $orderId, $newOrderStatus, $newPaymentStatus);
        }
        // Note: If an order was 'delivered' and 'paid', and then ONLY order_status changes to 'shipped' or 'processing' again
        // (which is unusual but possible with the current dropdowns), the 'awaiting_clearance' earnings would remain.
        // They would only be 'cancelled' if the order is explicitly cancelled or payment refunded.
    }
    return $success;
}

/**
 * Marks pending affiliate earnings for an order as 'awaiting_clearance' and sets the order_payment_confirmed_at timestamp.
 * This does NOT add to the affiliate's balance yet.
 * @param PDO $pdo Database connection
 * @param int $orderId Order ID
 * @return bool
 */
function markAffiliateEarningsAsAwaitingClearance(PDO $pdo, int $orderId): bool
{
    $stmt = $pdo->prepare(
        "UPDATE affiliate_earnings 
         SET status = 'awaiting_clearance', order_payment_confirmed_at = NOW() 
         WHERE order_id = ? AND status = 'pending'"
    );
    try {
        return $stmt->execute([$orderId]);
    } catch (PDOException $e) {
        error_log("Error marking earnings as awaiting_clearance for order ID $orderId: " . $e->getMessage(), 3, LOGS_PATH . 'affiliate_errors.log');
        return false;
    }
}

/**
 * Cancels affiliate earnings for an order.
 * If earnings were 'cleared' (balance updated), it deducts from the affiliate's balance.
 * @param PDO $pdo Database connection
 * @param int $orderId Order ID
 * @param string $finalOrderStatus The order status that triggered cancellation (e.g., 'cancelled')
 * @param string $finalPaymentStatus The payment status that triggered cancellation (e.g., 'refunded')
 * @return bool
 */
function cancelAffiliateEarningsForOrder(PDO $pdo, int $orderId, string $finalOrderStatus, string $finalPaymentStatus): bool
{
    // Find earnings for this order that are 'pending', 'awaiting_clearance', or 'cleared'
    $stmtEarnings = $pdo->prepare("SELECT id, user_id, earned_amount, status FROM affiliate_earnings WHERE order_id = ? AND status IN ('pending', 'awaiting_clearance', 'cleared')");
    $stmtEarnings->execute([$orderId]);
    $earningsToCancel = $stmtEarnings->fetchAll(PDO::FETCH_ASSOC);

    if (empty($earningsToCancel)) {
        return true; // No applicable earnings to cancel
    }

    $pdo->beginTransaction();
    try {
        foreach ($earningsToCancel as $earning) {
            $currentEarningStatus = $earning['status'];

            // Update earning status to 'cancelled'
            // Set processed_at as this is a final resolution for this earning from this action
            $stmtUpdateEarning = $pdo->prepare(
                "UPDATE affiliate_earnings 
                 SET status = 'cancelled', 
                     cleared_at = NULL, -- Nullify cleared_at if it was awaiting_clearance or pending
                     order_payment_confirmed_at = CASE WHEN status = 'pending' THEN NULL ELSE order_payment_confirmed_at END, -- Nullify if it was only pending
                     processed_at = NOW() 
                 WHERE id = ?"
            );
            $stmtUpdateEarning->execute([$earning['id']]);

            // If the earning was already 'cleared' (meaning balance was previously updated), deduct from balance
            if ($currentEarningStatus === 'cleared') {
                $stmtUpdateBalance = $pdo->prepare("UPDATE users SET affiliate_balance = GREATEST(0, affiliate_balance - ?) WHERE id = ?");
                $stmtUpdateBalance->execute([$earning['earned_amount'], $earning['user_id']]);
            }
        }
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error cancelling affiliate earnings for order ID $orderId (Trigger: OrderStatus=$finalOrderStatus, PaymentStatus=$finalPaymentStatus): " . $e->getMessage(), 3, LOGS_PATH . 'affiliate_errors.log');
        return false;
    }
}

// --- Other existing functions (getAllOrders, getOrdersByUserId, etc.) ---
// Make sure to copy them from your previous version of this file to keep them.
// For brevity, I am not re-listing all of them here, but they should be included.

/**
 * Retrieves all orders with customer and referrer details
 * @param PDO $pdo Database connection
 * @return array Returns an array of orders with customer and referrer names
 */
function getAllOrders(PDO $pdo)
{
    $stmt = $pdo->query("
        SELECT o.*, u.username as customer_name, ru.username as referrer_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        LEFT JOIN users ru ON o.referrer_user_id = ru.id
        ORDER BY o.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retrieves orders for a specific user.
 * @param PDO $pdo The PDO database connection object.
 * @param int $userId The ID of the user whose orders are to be retrieved.
 * @param int $limit The maximum number of orders per page.
 * @param int $offset The offset for pagination.
 * @return array An array of orders for the specified user.
 */
function getOrdersByUserId(PDO $pdo, int $userId, int $limit = 10, int $offset = 0): array
{
    $stmt = $pdo->prepare("
        SELECT o.id, o.total_amount, o.order_status, o.payment_status, o.created_at
        FROM orders o
        WHERE o.user_id = :user_id
        ORDER BY o.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Counts the total number of orders for a specific user.
 * @param PDO $pdo The PDO database connection object.
 * @param int $userId The ID of the user.
 * @return int The total number of orders.
 */
function getTotalOrdersByUserId(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :user_id");
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}


/**
 * Retrieves a specific order by ID with customer and referrer details
 * @param PDO $pdo Database connection
 * @param int $orderId Order ID to fetch
 * @return array|null Returns order details or null if not found
 */
function getOrderDetails(PDO $pdo, int $orderId)
{
    $orderStmt = $pdo->prepare("
        SELECT o.*, u.username as customer_name, u.email as customer_email, ru.username as referrer_name
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        LEFT JOIN users ru ON o.referrer_user_id = ru.id
        WHERE o.id = ?
    ");
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order)
        return null;

    $itemsStmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.image_url 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    return $order;
}

/**
 * Creates a new order in the database
 * @param PDO $pdo Database connection
 * @param int $userId User ID placing the order
 * @param float $totalAmount Total amount for the order
 * @param string $shippingAddress Shipping address for the order
 * @param string $paymentMethod Payment method used (e.g., 'cod', 'placeholder_card')
 * @param string $paymentStatus Initial payment status (e.g., 'pending_payment', 'paid_placeholder')
 * @param array $cartItems Array of cart items with product_id, quantity, and price_at_addition
 * @param int|null $referrerUserId Optional referrer user ID for affiliate tracking
 * @param string|null $affiliateCodeUsed Optional affiliate code used for the order
 * @return int|null Returns the new order ID or null on failure
 */
function createOrder(PDO $pdo, int $userId, float $totalAmount, string $shippingAddress, string $paymentMethod, string $paymentStatus, array $cartItems, ?int $referrerUserId = null, ?string $affiliateCodeUsed = null): ?int
{
    $pdo->beginTransaction();
    try {
        foreach ($cartItems as $item) {
            $stmtStock = $pdo->prepare("SELECT name, stock_quantity FROM products WHERE id = ? FOR UPDATE");
            $stmtStock->execute([$item['product_id']]);
            $product = $stmtStock->fetch(PDO::FETCH_ASSOC);

            if (!$product || $item['quantity'] > $product['stock_quantity']) {
                $pdo->rollBack();
                $productName = $product ? htmlspecialchars($product['name']) : 'Unknown Product ID ' . $item['product_id'];
                $availableStock = $product ? $product['stock_quantity'] : 'N/A';
                throw new \Exception("Insufficient stock for product: " . $productName . ". Requested: {$item['quantity']}, Available: " . $availableStock);
            }
        }

        $stmtOrder = $pdo->prepare(
            "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, payment_status, referrer_user_id, affiliate_code_used, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmtOrder->execute([$userId, $totalAmount, $shippingAddress, $paymentMethod, $paymentStatus, $referrerUserId, $affiliateCodeUsed]);
        $orderId = (int) $pdo->lastInsertId();

        $stmtOrderItem = $pdo->prepare(
            "INSERT INTO order_items (order_id, product_id, quantity, price_per_unit, created_at, updated_at) 
             VALUES (?, ?, ?, ?, NOW(), NOW())"
        );
        $stmtUpdateStock = $pdo->prepare(
            "UPDATE products SET stock_quantity = stock_quantity - ?, updated_at = NOW() WHERE id = ?"
        );

        foreach ($cartItems as $item) {
            $stmtOrderItem->execute([$orderId, $item['product_id'], $item['quantity'], $item['price_at_addition']]);
            $stmtUpdateStock->execute([$item['quantity'], $item['product_id']]);
        }

        $pdo->commit();
        return $orderId;

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Order creation DB error: " . $e->getMessage(), 3, LOGS_PATH . 'order_errors.log');
        return null;
    } catch (\Exception $e) {
        $pdo->rollBack();
        error_log("Order creation failed: " . $e->getMessage(), 3, LOGS_PATH . 'order_errors.log');
        throw $e;
    }
}

/**
 * Formats the status text for display
 * @param string|null $status
 * @return string
 */
function formatStatusText($status)
{
    if ($status === null)
        return 'N/A';
    $status = str_replace('_', ' ', $status);
    $status = ucwords(strtolower($status));
    $status = str_replace('Cod', 'COD', $status);
    $status = str_replace('Paid Placeholder', 'Paid (Test)', $status);
    return htmlspecialchars($status);
}

/**
 * Helper function to get the CSS class for a status badge
 * @param string|null $status
 * @return string
 */
function getStatusBadgeClass($status)
{
    if ($status === null)
        return 'secondary';
    switch (strtolower($status)) {
        case 'pending':
        case 'pending_payment':
        case 'pending_cod_confirmation':
        case 'awaiting_clearance': // New status
            return 'warning text-dark';
        case 'processing':
            return 'info text-dark';
        case 'shipped':
            return 'primary';
        case 'paid_placeholder':
        case 'paid':
        case 'cleared': // For earnings, can reuse
            return 'success';
        case 'delivered':
            return 'success';
        case 'cancelled':
        case 'failed':
        case 'refunded': // New status for payment
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Retrieves order items for a specific order. Used for calculating affiliate earnings.
 * @param PDO $pdo Database connection
 * @param int $orderId Order ID to fetch items for
 * @return array Returns an array of order items (id, product_id, quantity, price_per_unit).
 */
function getOrderItemsForOrder(PDO $pdo, int $orderId): array
{
    $stmt = $pdo->prepare("SELECT id, product_id, quantity, price_per_unit FROM order_items WHERE order_id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


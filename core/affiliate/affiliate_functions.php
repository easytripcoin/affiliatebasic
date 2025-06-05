<?php
namespace AffiliateBasic\Core\Affiliate;

use PDO;
use PDOException;

/**
 * Generates a unique affiliate code for a user.
 * The code consists of alphanumeric characters and is checked against existing codes in the database.
 *
 * @param PDO $pdo The PDO connection object.
 * @return string A unique affiliate code.
 */
function generateUniqueUserAffiliateCode(PDO $pdo): string
{
    $code_length = 8;
    $max_attempts = 10;
    $attempt = 0;
    do {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < $code_length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        // Check if code already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE user_affiliate_code = ?");
        $stmt->execute([$code]);
        $exists = $stmt->fetchColumn();
        $attempt++;
    } while ($exists && $attempt < $max_attempts);

    if ($exists) {
        // Fallback if unique code generation failed multiple times
        return 'REF' . time() . rand(100, 999);
    }
    return $code;
}

/**
 * Creates a new affiliate earning record in the database.
 *
 * @param PDO $pdo The PDO connection object.
 * @param int $userId The ID of the user earning the commission.
 * @param int $orderId The ID of the order associated with the earning.
 * @param int $orderItemId The ID of the order item associated with the earning.
 * @param int $productId The ID of the product for which the commission is earned.
 * @param float $earnedAmount The amount earned as commission.
 * @param float $commissionRate The commission rate applied to the product.
 * @param string $status The status of the earning (default is 'pending').
 * @return bool True on success, false on failure.
 */
function createAffiliateEarning(PDO $pdo, int $userId, int $orderId, int $orderItemId, int $productId, float $earnedAmount, float $commissionRate, string $status = 'pending'): bool
{
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO affiliate_earnings (user_id, order_id, order_item_id, product_id, earned_amount, commission_rate, status, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        return $stmt->execute([$userId, $orderId, $orderItemId, $productId, $earnedAmount, $commissionRate, $status]);
    } catch (PDOException $e) {
        error_log("Error creating affiliate earning: " . $e->getMessage(), 3, LOGS_PATH . 'affiliate_errors.log');
        return false;
    }
}

/**
 * Retrieves affiliate earnings for a specific user, with optional filtering by status.
 *
 * @param PDO $pdo The PDO connection object.
 * @param int $userId The ID of the user whose earnings are to be retrieved.
 * @param string|null $statusFilter Optional filter for earning status.
 * @param int $limit The maximum number of records to retrieve.
 * @param int $offset The offset for pagination.
 * @return array An array of affiliate earnings records.
 */
function getUserAffiliateEarnings(PDO $pdo, int $userId, string $statusFilter = null, int $limit = 20, int $offset = 0): array
{
    $sql = "SELECT ae.*, p.name as product_name, o.created_at as order_date 
            FROM affiliate_earnings ae
            JOIN products p ON ae.product_id = p.id
            JOIN orders o ON ae.order_id = o.id
            WHERE ae.user_id = :user_id";

    $params = [':user_id' => $userId];

    if ($statusFilter && in_array($statusFilter, ['pending', 'awaiting_clearance', 'cleared', 'paid', 'cancelled'])) {
        $sql .= " AND ae.status = :status";
        $params[':status'] = $statusFilter;
    }

    $sql .= " ORDER BY ae.created_at DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => &$val) {
        $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    unset($val);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retrieves the total number of affiliate earnings for a specific user, with optional filtering by status.
 *
 * @param PDO $pdo The PDO connection object.
 * @param int $userId The ID of the user whose earnings count is to be retrieved.
 * @param string|null $statusFilter Optional filter for earning status.
 * @return int The total count of affiliate earnings for the user.
 */
function getTotalUserAffiliateEarnings(PDO $pdo, int $userId, string $statusFilter = null): int
{
    $sql = "SELECT COUNT(*) FROM affiliate_earnings WHERE user_id = :user_id";
    $params = [':user_id' => $userId];

    if ($statusFilter && in_array($statusFilter, ['pending', 'awaiting_clearance', 'cleared', 'paid', 'cancelled'])) {
        $sql .= " AND status = :status";
        $params[':status'] = $statusFilter;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/**
 * Retrieves withdrawal requests made by users, with optional filtering by status.
 *
 * @param PDO $pdo The PDO connection object.
 * @param string|null $statusFilter Optional filter for request status.
 * @param int $limit The maximum number of records to retrieve.
 * @param int $offset The offset for pagination.
 * @return array An array of withdrawal request records.
 */
function getWithdrawalRequests(PDO $pdo, string $statusFilter = null, int $limit = 20, int $offset = 0): array
{
    $sql = "SELECT wr.*, u.username as user_username, u.email as user_email 
            FROM withdrawal_requests wr
            JOIN users u ON wr.user_id = u.id";

    $params = [];
    if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
        $sql .= " WHERE wr.status = :status";
        $params[':status'] = $statusFilter;
    }
    $sql .= " ORDER BY wr.requested_at DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => &$val) {
        $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    unset($val);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retrieves the total number of withdrawal requests, with optional filtering by status.
 *
 * @param PDO $pdo The PDO connection object.
 * @param string|null $statusFilter Optional filter for request status.
 * @return int The total count of withdrawal requests.
 */
function getTotalWithdrawalRequests(PDO $pdo, string $statusFilter = null): int
{
    $sql = "SELECT COUNT(*) FROM withdrawal_requests";
    $params = [];
    if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
        $sql .= " WHERE status = :status";
        $params[':status'] = $statusFilter;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/**
 * Retrieves earnings that are 'awaiting_clearance'.
 * Optionally filters for those where order_payment_confirmed_at is older than the refund period.
 *
 * @param PDO $pdo
 * @param int $refundPeriodDays Number of days for the refund period.
 * @param bool $onlyPastRefundPeriod If true, only get earnings past the refund period.
 * @param int $limit
 * @param int $offset
 * @return array
 */
function getEarningsAwaitingClearance(PDO $pdo, int $refundPeriodDays, bool $onlyPastRefundPeriod = false, int $limit = 20, int $offset = 0): array
{
    $sql = "SELECT ae.*, u.username as affiliate_username, p.name as product_name 
            FROM affiliate_earnings ae
            JOIN users u ON ae.user_id = u.id
            JOIN products p ON ae.product_id = p.id
            WHERE ae.status = 'awaiting_clearance'";

    $params = [];

    if ($onlyPastRefundPeriod) {
        $sql .= " AND ae.order_payment_confirmed_at <= DATE_SUB(NOW(), INTERVAL :refund_days DAY)";
        $params[':refund_days'] = $refundPeriodDays;
    }

    $sql .= " ORDER BY ae.order_payment_confirmed_at ASC, ae.id ASC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => &$val) {
        $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    unset($val);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Gets the total count of earnings 'awaiting_clearance'.
 * Optionally filters for those where order_payment_confirmed_at is older than the refund period.
 * @param PDO $pdo
 * @param int $refundPeriodDays
 * @param bool $onlyPastRefundPeriod
 * @return int
 */
function getTotalEarningsAwaitingClearance(PDO $pdo, int $refundPeriodDays, bool $onlyPastRefundPeriod = false): int
{
    $sql = "SELECT COUNT(*) 
            FROM affiliate_earnings 
            WHERE status = 'awaiting_clearance'";

    $params = [];
    if ($onlyPastRefundPeriod) {
        $sql .= " AND order_payment_confirmed_at <= DATE_SUB(NOW(), INTERVAL :refund_days DAY)";
        $params[':refund_days'] = $refundPeriodDays;
    }

    $stmt = $pdo->prepare($sql);
    if ($onlyPastRefundPeriod) {
        $stmt->bindValue(':refund_days', $refundPeriodDays, PDO::PARAM_INT);
    }
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}


/**
 * Finalizes a specific affiliate earning that is 'awaiting_clearance'.
 * Updates status to 'cleared', sets cleared_at, and updates affiliate's balance.
 * @param PDO $pdo
 * @param int $earningId The ID of the affiliate_earnings record.
 * @return bool True on success, false on failure.
 */
function finalizeAffiliateEarning(PDO $pdo, int $earningId): bool
{
    // Fetch the earning to ensure it's in the correct state and get details
    $stmtEarning = $pdo->prepare(
        "SELECT id, user_id, earned_amount, status 
         FROM affiliate_earnings 
         WHERE id = ? AND status = 'awaiting_clearance'"
    );
    $stmtEarning->execute([$earningId]);
    $earning = $stmtEarning->fetch(PDO::FETCH_ASSOC);

    if (!$earning) {
        // Not found, or not in 'awaiting_clearance' status
        error_log("Attempt to finalize earning ID $earningId failed: Not found or not awaiting clearance.", 3, LOGS_PATH . 'affiliate_errors.log');
        return false;
    }

    $pdo->beginTransaction();
    try {
        // Update earning status to 'cleared' and set cleared_at timestamp
        $stmtUpdateEarning = $pdo->prepare("UPDATE affiliate_earnings SET status = 'cleared', cleared_at = NOW() WHERE id = ?");
        $stmtUpdateEarning->execute([$earning['id']]);

        // Add the earned amount to the affiliate user's balance
        $stmtUpdateBalance = $pdo->prepare("UPDATE users SET affiliate_balance = affiliate_balance + ? WHERE id = ?");
        $stmtUpdateBalance->execute([$earning['earned_amount'], $earning['user_id']]);

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error finalizing affiliate earning ID $earningId: " . $e->getMessage(), 3, LOGS_PATH . 'affiliate_errors.log');
        return false;
    }
}

<?php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Core\Affiliate\getUserAffiliateEarnings;
use function AffiliateBasic\Core\Affiliate\getTotalUserAffiliateEarnings;
use function AffiliateBasic\Core\Ecommerce\getStatusBadgeClass;

require_once PROJECT_ROOT_PATH . '/core/affiliate/affiliate_functions.php';
require_once PROJECT_ROOT_PATH . '/core/ecommerce/order_functions.php'; // For getStatusBadgeClass

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirectWithMessage('login', 'danger', 'Please log in to view this page.');
}
if (!isset($_SESSION['is_affiliate']) || $_SESSION['is_affiliate'] !== true) {
    redirectWithMessage('dashboard', 'info', 'You are not currently an affiliate. Contact admin to apply.');
}

global $pdo;
$userId = (int) $_SESSION['user_id'];

$stmtUser = $pdo->prepare("SELECT username, email, user_affiliate_code, affiliate_balance FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$userAffiliate = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$userAffiliate) {
    redirectWithMessage('dashboard', 'danger', 'Could not retrieve affiliate details.');
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$statusFilter = $_GET['status_filter'] ?? null;
if ($statusFilter && !in_array($statusFilter, ['pending', 'cleared', 'paid', 'cancelled'])) {
    $statusFilter = null;
}

$earnings = getUserAffiliateEarnings($pdo, $userId, $statusFilter, $limit, $offset);
$totalEarnings = getTotalUserAffiliateEarnings($pdo, $userId, $statusFilter);
$totalPages = ceil($totalEarnings / $limit);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Affiliate Dashboard | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>

    <main class="container py-5">
        <h1 class="mb-4">Affiliate Dashboard</h1>
        <?php echo displayMessage(); ?>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Your Referral Code</h5>
                        <p class="card-text">Share this code with others:</p>
                        <input type="text" class="form-control mb-2"
                            value="<?php echo htmlspecialchars($userAffiliate['user_affiliate_code']); ?>" readonly>
                        <p class="card-text">Example referral link:
                            <br><code><?php echo SITE_URL; ?>/product?id=PRODUCT_ID&ref=<?php echo htmlspecialchars($userAffiliate['user_affiliate_code']); ?></code><br>
                            Or simply:
                            <code><?php echo SITE_URL; ?>/?ref=<?php echo htmlspecialchars($userAffiliate['user_affiliate_code']); ?></code>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Current Balance</h5>
                        <p class="card-text fs-3 fw-bold text-success">
                            $<?php echo htmlspecialchars(number_format($userAffiliate['affiliate_balance'], 2)); ?></p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#withdrawalModal">
                            Request Withdrawal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-5 mb-3">Earnings History</h3>

        <form method="get" action="<?php echo SITE_URL; ?>/affiliate-dashboard" class="row g-3 mb-3">
            <div class="col-md-4">
                <select name="status_filter" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo ($statusFilter === 'pending' ? 'selected' : ''); ?>>Pending
                    </option>
                    <option value="cleared" <?php echo ($statusFilter === 'cleared' ? 'selected' : ''); ?>>Cleared
                    </option>
                    <option value="paid" <?php echo ($statusFilter === 'paid' ? 'selected' : ''); ?>>Paid</option>
                    <option value="cancelled" <?php echo ($statusFilter === 'cancelled' ? 'selected' : ''); ?>>Cancelled
                    </option>
                </select>
            </div>
        </form>

        <?php if (empty($earnings)): ?>
            <p>No earnings recorded
                yet<?php echo $statusFilter ? ' for status "' . htmlspecialchars($statusFilter) . '"' : ''; ?>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Amount Earned</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th>Date Earned</th>
                            <th>Date Cleared</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($earnings as $earning): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($earning['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($earning['product_name']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($earning['earned_amount'], 2)); ?></td>
                                <td><?php echo htmlspecialchars(number_format($earning['commission_rate'], 2)); ?>%</td>
                                <td>
                                    <span
                                        class="badge bg-<?php echo htmlspecialchars(getStatusBadgeClass($earning['status'])); ?>">
                                        <?php echo htmlspecialchars(ucfirst($earning['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars(date('M j, Y, g:i a', strtotime($earning['created_at']))); ?>
                                </td>
                                <td>
                                    <?php echo $earning['cleared_at'] ? htmlspecialchars(date('M j, Y, g:i a', strtotime($earning['cleared_at']))) : 'N/A'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Earnings pagination">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="<?php echo SITE_URL; ?>/affiliate-dashboard?page=<?php echo $i; ?><?php echo $statusFilter ? '&status_filter=' . $statusFilter : ''; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

    </main>

    <div class="modal fade" id="withdrawalModal" tabindex="-1" aria-labelledby="withdrawalModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="withdrawalModalLabel">Request Withdrawal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo SITE_URL; ?>/request-withdrawal-action" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-body">
                        <p>Your current withdrawable balance is:
                            <strong>$<?php echo htmlspecialchars(number_format($userAffiliate['affiliate_balance'], 2)); ?></strong>
                        </p>
                        <div class="mb-3">
                            <label for="withdrawal_amount" class="form-label">Amount to Withdraw</label>
                            <input type="number" class="form-control" id="withdrawal_amount" name="withdrawal_amount"
                                step="0.01" min="1.00"
                                max="<?php echo htmlspecialchars($userAffiliate['affiliate_balance']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="payment_details" class="form-label">Payment Details (e.g., PayPal Email)</label>
                            <textarea class="form-control" id="payment_details" name="payment_details" rows="3" required
                                placeholder="Provide your PayPal email or other preferred payment method details."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" <?php echo ($userAffiliate['affiliate_balance'] <= 0) ? 'disabled' : ''; ?>>Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>

</html>
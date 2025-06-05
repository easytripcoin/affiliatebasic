<?php
// affiliatebasic/pages/admin_finalize_earnings.php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Core\Affiliate\getEarningsAwaitingClearance; // New function needed
use function AffiliateBasic\Core\Affiliate\getTotalEarningsAwaitingClearance; // New function needed
use function AffiliateBasic\Core\Ecommerce\getStatusBadgeClass; // Re-use for status styling

// Ensure admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}

global $pdo;
require_once PROJECT_ROOT_PATH . '/core/affiliate/affiliate_functions.php';
// We'll also need getStatusBadgeClass from ecommerce functions if not already loaded
require_once PROJECT_ROOT_PATH . '/core/ecommerce/order_functions.php';

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$earnings = getEarningsAwaitingClearance($pdo, AFFILIATE_REFUND_PERIOD_DAYS, true, $limit, $offset); // Get only those past refund period
$totalEarnings = getTotalEarningsAwaitingClearance($pdo, AFFILIATE_REFUND_PERIOD_DAYS, true);
$totalPages = ceil($totalEarnings / $limit);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Finalize Affiliate Earnings | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5">
        <h1>Finalize Affiliate Earnings</h1>
        <p class="lead">Earnings below are in 'Awaiting Clearance' status and their associated order was confirmed as
            delivered & paid more than <?php echo AFFILIATE_REFUND_PERIOD_DAYS; ?> days ago.</p>
        <?php echo displayMessage(); ?>

        <?php if (empty($earnings)): ?>
            <div class="alert alert-info">No earnings are currently eligible for finalization.</div>
        <?php else: ?>
            <form action="<?php echo SITE_URL; ?>/admin-finalize-earnings-action" method="post" id="finalizeEarningsForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllEarnings"></th>
                                <th>Earning ID</th>
                                <th>Affiliate</th>
                                <th>Order ID</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Order Confirmed At</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($earnings as $earning): ?>
                                <tr>
                                    <td><input type="checkbox" name="earning_ids[]" value="<?php echo $earning['id']; ?>"
                                            class="earning-checkbox"></td>
                                    <td><?php echo $earning['id']; ?></td>
                                    <td><?php echo htmlspecialchars($earning['affiliate_username']); ?></td>
                                    <td>#<?php echo htmlspecialchars($earning['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($earning['product_name']); ?></td>
                                    <td>$<?php echo htmlspecialchars(number_format($earning['earned_amount'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($earning['order_payment_confirmed_at']))); ?>
                                    </td>
                                    <td><span
                                            class="badge bg-<?php echo getStatusBadgeClass($earning['status']); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $earning['status']))); ?></span>
                                    </td>
                                    <td>
                                        <button type="submit" name="action"
                                            value="finalize_single_<?php echo $earning['id']; ?>"
                                            class="btn btn-sm btn-success">Finalize This</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="action" value="finalize_selected" class="btn btn-primary mb-3"
                    id="finalizeSelectedBtn" disabled>Finalize Selected</button>
            </form>
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Finalize earnings pagination">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="<?php echo SITE_URL; ?>/admin-finalize-earnings?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('selectAllEarnings').addEventListener('change', function (e) {
            document.querySelectorAll('.earning-checkbox').forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
            toggleFinalizeSelectedButton();
        });

        document.querySelectorAll('.earning-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', toggleFinalizeSelectedButton);
        });

        function toggleFinalizeSelectedButton() {
            const finalizeSelectedBtn = document.getElementById('finalizeSelectedBtn');
            const anyChecked = Array.from(document.querySelectorAll('.earning-checkbox')).some(cb => cb.checked);
            finalizeSelectedBtn.disabled = !anyChecked;
        }
        toggleFinalizeSelectedButton(); // Initial check
    </script>
</body>

</html>
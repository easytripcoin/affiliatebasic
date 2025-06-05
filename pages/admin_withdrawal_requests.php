<?php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Core\Affiliate\getWithdrawalRequests;
use function AffiliateBasic\Core\Affiliate\getTotalWithdrawalRequests;
use function AffiliateBasic\Core\Ecommerce\getStatusBadgeClass; // Re-use for status styling

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !$_SESSION['is_admin']) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}

global $pdo;
require_once PROJECT_ROOT_PATH . '/core/affiliate/affiliate_functions.php';

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$statusFilter = $_GET['status_filter'] ?? 'pending'; // Default to pending
if ($statusFilter && !in_array($statusFilter, ['pending', 'approved', 'rejected', 'all'])) {
    $statusFilter = 'pending';
}
$actualFilter = ($statusFilter === 'all') ? null : $statusFilter;


$requests = getWithdrawalRequests($pdo, $actualFilter, $limit, $offset);
$totalRequests = getTotalWithdrawalRequests($pdo, $actualFilter);
$totalPages = ceil($totalRequests / $limit);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Withdrawal Requests | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5">
        <h1>Manage Withdrawal Requests</h1>
        <?php echo displayMessage(); ?>

        <form method="get" action="<?php echo SITE_URL; ?>/admin-withdrawal-requests" class="row g-3 mb-3">
            <div class="col-md-3">
                <label for="status_filter" class="form-label">Filter by Status:</label>
                <select name="status_filter" id="status_filter" class="form-select" onchange="this.form.submit()">
                    <option value="pending" <?php echo ($statusFilter === 'pending' ? 'selected' : ''); ?>>Pending
                    </option>
                    <option value="approved" <?php echo ($statusFilter === 'approved' ? 'selected' : ''); ?>>Approved
                    </option>
                    <option value="rejected" <?php echo ($statusFilter === 'rejected' ? 'selected' : ''); ?>>Rejected
                    </option>
                    <option value="all" <?php echo ($statusFilter === 'all' ? 'selected' : ''); ?>>All</option>
                </select>
            </div>
        </form>

        <?php if (empty($requests)): ?>
            <p>No withdrawal requests found
                <?php echo $actualFilter ? 'with status "' . htmlspecialchars($actualFilter) . '"' : ''; ?>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Payment Details</th>
                            <th>Requested At</th>
                            <th>Status</th>
                            <th>Processed At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo $request['id']; ?></td>
                                <td><?php echo htmlspecialchars($request['user_username']); ?>
                                    (<?php echo htmlspecialchars($request['user_email']); ?>)</td>
                                <td>$<?php echo htmlspecialchars(number_format($request['requested_amount'], 2)); ?></td>
                                <td>
                                    <pre><?php echo htmlspecialchars($request['payment_details']); ?></pre>
                                </td>
                                <td><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($request['requested_at']))); ?>
                                </td>
                                <td><span
                                        class="badge bg-<?php echo getStatusBadgeClass($request['status']); ?>"><?php echo htmlspecialchars(ucfirst($request['status'])); ?></span>
                                </td>
                                <td><?php echo $request['processed_at'] ? htmlspecialchars(date('M j, Y H:i', strtotime($request['processed_at']))) : 'N/A'; ?>
                                </td>
                                <td>
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <form action="<?php echo SITE_URL; ?>/admin-process-withdrawal-action" method="post"
                                            class="d-inline-block me-1">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success"
                                                onclick="return confirm('Approve this withdrawal? This will deduct from user balance.');">Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#rejectModal_<?php echo $request['id']; ?>">Reject</button>

                                        <div class="modal fade" id="rejectModal_<?php echo $request['id']; ?>" tabindex="-1"
                                            aria-labelledby="rejectModalLabel_<?php echo $request['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="<?php echo SITE_URL; ?>/admin-process-withdrawal-action"
                                                    method="post">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="rejectModalLabel_<?php echo $request['id']; ?>">Reject
                                                                Withdrawal #<?php echo $request['id']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="csrf_token"
                                                                value="<?php echo generateCSRFToken(); ?>">
                                                            <input type="hidden" name="request_id"
                                                                value="<?php echo $request['id']; ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <div class="mb-3">
                                                                <label for="admin_notes_<?php echo $request['id']; ?>"
                                                                    class="form-label">Reason for Rejection (Optional)</label>
                                                                <textarea class="form-control"
                                                                    id="admin_notes_<?php echo $request['id']; ?>"
                                                                    name="admin_notes" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                    <?php else: ?>
                                        Processed
                                        <?php if (!empty($request['admin_notes'])): ?>
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="<?php echo htmlspecialchars($request['admin_notes']); ?>">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Withdrawal requests pagination">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="<?php echo SITE_URL; ?>/admin-withdrawal-requests?page=<?php echo $i; ?><?php echo $statusFilter ? '&status_filter=' . $statusFilter : ''; ?>"><?php echo $i; ?></a>
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
        // Initialize tooltips for admin notes if any
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>

</html>
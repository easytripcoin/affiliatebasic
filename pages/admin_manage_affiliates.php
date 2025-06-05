<?php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Config\redirectWithMessage;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !$_SESSION['is_admin']) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}
global $pdo;
require_once PROJECT_ROOT_PATH . '/core/affiliate/affiliate_functions.php';

// Fetch all users
$stmtUsers = $pdo->query("SELECT id, username, email, is_affiliate, user_affiliate_code, affiliate_balance FROM users ORDER BY username ASC");
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Affiliates | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5">
        <h1>Manage Affiliates</h1>
        <?php echo displayMessage(); ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Is Affiliate?</th>
                        <th>Affiliate Code</th>
                        <th>Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['is_affiliate'] ? 'Yes' : 'No'; ?></td>
                            <td><?php echo htmlspecialchars($user['user_affiliate_code'] ?? 'N/A'); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($user['affiliate_balance'], 2)); ?></td>
                            <td>
                                <form action="<?php echo SITE_URL; ?>/admin-manage-affiliates-action" method="post"
                                    class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <?php if ($user['is_affiliate']): ?>
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="btn btn-sm btn-warning">Deactivate</button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
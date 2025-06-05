<?php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Core\Ecommerce\getAllProductsForAdmin; // Assuming this function exists now

// IMPORTANT: Include the functions file before calling its functions
require_once PROJECT_ROOT_PATH . '/core/ecommerce/product_functions.php';

global $pdo;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirectWithMessage('login', 'danger', 'Access denied. You must be an admin to view this page.');
    exit;
}

$products = getAllProductsForAdmin($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Products | Admin | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Products</h1>
            <a href="<?php echo SITE_URL; ?>/admin-add-product" class="btn btn-success"><i
                    class="bi bi-plus-circle"></i> Add New Product</a>
        </div>
        <?php echo displayMessage(); ?>

        <?php if (empty($products)): ?>
            <div class="alert alert-info">No products found. <a href="<?php echo SITE_URL; ?>/admin-add-product">Add the
                    first product</a>.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Is Affiliate?</th>
                            <th>Bonus %</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($product['stock_quantity']); ?></td>
                                <td><?php echo ((float) $product['affiliate_bonus_percentage'] > 0) ? 'Yes' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars(number_format($product['affiliate_bonus_percentage'], 2)); ?>%
                                </td>
                                <td><?php echo htmlspecialchars($product['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($product['updated_at'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="<?php echo SITE_URL; ?>/admin-edit-product?id=<?php echo $product['id']; ?>"
                                        class="btn btn-sm btn-warning me-1" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    <form action="<?php echo SITE_URL; ?>/admin-product-delete-action" method="post"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
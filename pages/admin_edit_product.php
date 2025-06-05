<?php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Core\Ecommerce\getProductById;

// IMPORTANT: Include the functions file before calling its functions
require_once PROJECT_ROOT_PATH . '/core/ecommerce/product_functions.php';

global $pdo;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirectWithMessage('login', 'danger', 'Access denied.');
    exit;
}

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$productId) {
    redirectWithMessage('admin-products', 'danger', 'Invalid product ID.');
}

$product = getProductById($pdo, $productId);
if (!$product) {
    redirectWithMessage('admin-products', 'danger', 'Product not found.');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Product | Admin | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5">
        <h1>Edit Product: <?php echo htmlspecialchars($product['name']); ?></h1>
        <?php echo displayMessage(); ?>
        <form action="<?php echo SITE_URL; ?>/admin-product-edit-action" method="post" enctype="multipart/form-data"
            class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name"
                    value="<?php echo htmlspecialchars($product['name']); ?>" required>
                <div class="invalid-feedback">Please provide a product name.</div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"
                    rows="3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="price" name="price"
                            value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" min="0" required>
                    </div>
                    <div class="invalid-feedback">Please provide a valid price.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stock_quantity" class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity"
                        value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" min="0" required>
                    <div class="invalid-feedback">Please provide a valid stock quantity.</div>
                </div>
            </div>

            <div class="mb-3">
                <label for="image_file" class="form-label">Change Product Image (Optional)</label>
                <input type="file" class="form-control" id="image_file" name="image_file">
                <small class="form-text text-muted">Current image:
                    <?php if (!empty($product['image_url'])): ?>
                        <a href="<?php echo SITE_URL . '/' . htmlspecialchars($product['image_url']); ?>"
                            target="_blank"><?php echo htmlspecialchars($product['image_url']); ?></a>
                    <?php else: ?>
                        None
                    <?php endif; ?>
                </small>
            </div>

            <div class="mb-3">
                <label for="affiliate_bonus_percentage" class="form-label">Affiliate Bonus Percentage (%)</label>
                <input type="number" class="form-control" id="affiliate_bonus_percentage"
                    name="affiliate_bonus_percentage"
                    value="<?php echo htmlspecialchars($product['affiliate_bonus_percentage'] ?? '0.00'); ?>"
                    step="0.01" min="0" max="100">
                <div class="form-text">Enter a percentage (e.g., 5.00 for 5%). Set to 0 if not an affiliate product.
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="<?php echo SITE_URL; ?>/admin-products" class="btn btn-secondary">Cancel</a>
        </form>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>

</html>
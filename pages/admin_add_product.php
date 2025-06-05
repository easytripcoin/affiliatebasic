<?php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;

// Admin access check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirectWithMessage('login', 'danger', 'Access denied.');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Product | Admin | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">

</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5">
        <h1>Add New Product</h1>
        <?php echo displayMessage(); ?>
        <form action="<?php echo SITE_URL; ?>/admin-product-add-action" method="post" enctype="multipart/form-data"
            class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback">Please provide a product name.</div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="invalid-feedback">Please provide a valid price.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stock_quantity" class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0"
                        value="0" required>
                    <div class="invalid-feedback">Please provide a valid stock quantity.</div>
                </div>
            </div>

            <div class="mb-3">
                <label for="image_file" class="form-label">Product Image</label>
                <input type="file" class="form-control" id="image_file" name="image_file" accept=".jpg,.jpeg,.png,.gif">
                <small class="form-text text-muted">Optional. Max 2MB. JPG, PNG, GIF.</small>
            </div>

            <div class="mb-3">
                <label for="affiliate_bonus_percentage" class="form-label">Affiliate Bonus Percentage (%)</label>
                <input type="number" class="form-control" id="affiliate_bonus_percentage"
                    name="affiliate_bonus_percentage" step="0.01" min="0" max="100" value="0.00">
                <div class="form-text">Enter a percentage (e.g., 5.00 for 5%). Set to 0 if not an affiliate product.
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Add Product</button>
            <a href="<?php echo SITE_URL; ?>/admin-products" class="btn btn-secondary">Cancel</a>
        </form>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Standard Bootstrap validation script
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
<?php
use function AffiliateBasic\Core\Ecommerce\getProductById;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\generateCSRFToken;

// IMPORTANT: Include the functions file before calling its functions
require_once PROJECT_ROOT_PATH . '/core/ecommerce/product_functions.php';

global $pdo;

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
if ($productId) {
    $product = getProductById($pdo, $productId);
}

if (!$product) {
    redirectWithMessage('products', 'danger', 'Product not found.');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5">
        <?php echo displayMessage(); ?>
        <div class="row">
            <div class="col-md-6">
                <?php if (!empty($product['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars(SITE_URL . '/' . $product['image_url']); ?>"
                        class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.png" class="img-fluid rounded"
                        alt="No image available">
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="lead"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <p class="h4">Price: $<?php echo htmlspecialchars($product['price']); ?></p>
                <p><small>Stock: <?php echo htmlspecialchars($product['stock_quantity']); ?></small></p>
                <hr>
                <form action="<?php echo SITE_URL; ?>/cart-add-action" method="post" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="col-md-4">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="number" name="quantity" id="quantity" value="1" min="1"
                            max="<?php echo $product['stock_quantity']; ?>" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </form>
                <div class="mt-3">
                    <a href="<?php echo SITE_URL; ?>/products" class="btn btn-outline-secondary">Back to Products</a>
                </div>
            </div>
        </div>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
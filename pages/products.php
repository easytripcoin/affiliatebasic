<?php
use function AffiliateBasic\Core\Ecommerce\getAllProducts;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;

// IMPORTANT: Include the functions file before calling its functions
require_once PROJECT_ROOT_PATH . '/core/ecommerce/product_functions.php';

global $pdo; // Assuming $pdo is globally available from config.php

$products = getAllProducts($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Products | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5">
        <h1 class="mb-4">Our Products</h1>
        <?php echo displayMessage(); ?>
        <div class="row">
            <?php if (empty($products)): ?>
                <p>No products available at the moment.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars(SITE_URL . '/' . $product['image_url']); ?>"
                                    class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    style="max-height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.png" class="card-img-top"
                                    alt="No image available" style="max-height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text">
                                    <?php echo nl2br(htmlspecialchars(substr($product['description'], 0, 100))) . (strlen($product['description']) > 100 ? '...' : ''); ?>
                                </p>
                                <p class="card-text"><strong>Price: $<?php echo htmlspecialchars($product['price']); ?></strong>
                                </p>
                                <p class="card-text"><small>Stock:
                                        <?php echo htmlspecialchars($product['stock_quantity']); ?></small></p>
                                <div class="mt-auto">
                                    <a href="<?php echo SITE_URL; ?>/product?id=<?php echo $product['id']; ?>"
                                        class="btn btn-sm btn-outline-secondary me-2">View Details</a>
                                    <form action="<?php echo SITE_URL; ?>/cart-add-action" method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-sm btn-primary">Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
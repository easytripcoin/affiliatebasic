<?php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Core\Ecommerce\getAllProducts;

require_once dirname(__DIR__) . '/core/ecommerce/product_functions.php';

global $pdo;

// ... (PHP code for fetching products and slider images remains the same as your last version) ...
try {
    $allProducts = getAllProducts($pdo);
    $products = array_slice($allProducts, 0, 6);
} catch (\PDOException $e) {
    error_log("Error fetching products for homepage: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    $products = [];
}

$sliderImages = [];
$localSliderImageFiles = ['slide1.jpg', 'slide2.jpg', 'slide3.jpg'];
$slideDataDefaults = [
    ['alt' => 'Special Offer', 'caption_title' => 'Amazing Deals This Week', 'caption_text' => 'Don\'t miss out on our exclusive discounts.', 'placeholder_text' => 'Special Offer!'],
    ['alt' => 'New Collection', 'caption_title' => 'Fresh Summer Styles', 'caption_text' => 'Explore the latest trends for the season.', 'placeholder_text' => 'New Collection!'],
    ['alt' => 'Free Shipping', 'caption_title' => 'Enjoy Free Shipping', 'caption_text' => 'On all orders over $75 for a limited time.', 'placeholder_text' => 'Free Shipping!'],
];
$sliderDir = PROJECT_ROOT_PATH . '/assets/images/slider/';
$sliderBaseUrl = SITE_URL . '/assets/images/slider/';
foreach ($localSliderImageFiles as $index => $filename) {
    $localImagePath = $sliderDir . $filename;
    $slideMeta = $slideDataDefaults[$index] ?? ['alt' => 'Slide ' . ($index + 1), 'caption_title' => 'Slide ' . ($index + 1), 'caption_text' => 'Description', 'placeholder_text' => 'Slide ' . ($index + 1)];
    if (file_exists($localImagePath) && is_readable($localImagePath)) {
        $sliderImages[] = ['url' => $sliderBaseUrl . rawurlencode($filename), 'alt' => $slideMeta['alt'], 'caption_title' => $slideMeta['caption_title'], 'caption_text' => $slideMeta['caption_text']];
    } else {
        $placeholderUrl = 'https://placehold.co/1200x450/';
        $colors = [['E9ECEF', '6C757D'], ['6C757D', 'FFFFFF'], ['0D6EFD', 'FFFFFF']];
        $colorSet = $colors[$index % count($colors)];
        $placeholderUrl .= $colorSet[0] . '/' . $colorSet[1] . '/png?text=' . urlencode($slideMeta['placeholder_text']);
        $sliderImages[] = ['url' => $placeholderUrl, 'alt' => $slideMeta['alt'] . ' (Placeholder)', 'caption_title' => $slideMeta['caption_title'], 'caption_text' => $slideMeta['caption_text']];
    }
}
if (empty($sliderImages)) {
    $sliderImages = [
        ['url' => 'https://placehold.co/1200x450/E9ECEF/6C757D/png?text=Special+Offer!', 'alt' => 'Special Offer', 'caption_title' => 'Amazing Deals This Week', 'caption_text' => 'Don\'t miss out on our exclusive discounts.'],
        ['url' => 'https://placehold.co/1200x450/6C757D/FFFFFF/png?text=New+Collection', 'alt' => 'New Collection', 'caption_title' => 'Fresh Summer Styles', 'caption_text' => 'Explore the latest trends for the season.'],
        ['url' => 'https://placehold.co/1200x400/0D6EFD/FFFFFF/png?text=Free+Shipping', 'alt' => 'Free Shipping', 'caption_title' => 'Enjoy Free Shipping', 'caption_text' => 'On all orders over $75 for a limited time.'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        .carousel-item {
            background-color: #f0f0f0;
            text-align: center;
        }

        .carousel-item img {
            max-height: 450px;
            height: auto;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }

        .carousel-caption {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 1rem 1.5rem;
            border-radius: .5rem;
        }

        .product-card-img {
            height: 220px;
            object-fit: cover;
        }

        .card-title a {
            color: inherit;
            text-decoration: none;
        }

        .card-title a:hover {
            color: var(--bs-primary);
        }

        /* Updated Styles for Back to Top Button */
        #backToTopBtn {
            /* display: none; /* Initially hidden by JS now */
            opacity: 0;
            /* Start transparent for fade-in */
            visibility: hidden;
            /* Start hidden for fade-in */
            position: fixed;
            bottom: 25px;
            /* Adjusted slightly */
            right: 25px;
            /* Adjusted slightly */
            z-index: 1030;
            /* Ensure it's above most elements, Bootstrap modal z-index is 1050+ */
            border: 1px solid white;
            /* White border/line */
            outline: none;
            background-color: #212529;
            /* Dark background */
            color: white;
            cursor: pointer;
            padding: 0;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out, background-color 0.3s ease-in-out;
            display: flex;
            /* Use flexbox to center icon */
            align-items: center;
            justify-content: center;
        }

        #backToTopBtn.show {
            /* Class to control visibility and opacity with JS */
            opacity: 1;
            visibility: visible;
        }

        #backToTopBtn:hover {
            background-color: #495057;
            /* Slightly lighter dark on hover (Bootstrap's gray-700) */
        }

        #backToTopBtn i.bi-arrow-up-short {
            font-size: 32px;
            /* Make icon bigger */
            line-height: 1;
            /* Ensure icon is centered within its own linebox */
            vertical-align: middle;
            /* Helps with some icon font alignments */
        }
    </style>
</head>

<body>
    <?php
    if (!defined('PROJECT_ROOT_PATH')) {
        define('PROJECT_ROOT_PATH', dirname(__DIR__));
    }
    include PROJECT_ROOT_PATH . '/templates/navbar.php';
    ?>

    <?php if (!empty($sliderImages)): ?>
        <div id="homePageCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach ($sliderImages as $index => $image): ?>
                    <button type="button" data-bs-target="#homePageCarousel" data-bs-slide-to="<?php echo $index; ?>"
                        class="<?php echo $index === 0 ? 'active' : ''; ?>"
                        aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                        aria-label="Slide <?php echo $index + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($sliderImages as $index => $image): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($image['url']); ?>" class="d-block"
                            alt="<?php echo htmlspecialchars($image['alt']); ?>">
                        <?php if (!empty($image['caption_title']) || !empty($image['caption_text'])): ?>
                            <div class="carousel-caption d-none d-md-block">
                                <?php if (!empty($image['caption_title'])): ?>
                                    <h5><?php echo htmlspecialchars($image['caption_title']); ?></h5>
                                <?php endif; ?>
                                <?php if (!empty($image['caption_text'])): ?>
                                    <p><?php echo htmlspecialchars($image['caption_text']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#homePageCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#homePageCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    <?php endif; ?>


    <main class="py-4 bg-light">
        <div class="container">
            <?php echo displayMessage(); ?>

            <div class="row text-center mb-4">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold mb-3">Featured Products</h2>
                    <p class="lead text-muted">Discover our handpicked selection of popular items.</p>
                </div>
            </div>
            <div class="row">
                <?php if (empty($products)): ?>
                    <div class="col">
                        <div class="alert alert-info text-center" role="alert">
                            No featured products available at the moment. Please check back soon!
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 shadow-sm product-card">
                                <a href="<?php echo SITE_URL; ?>/product?id=<?php echo $product['id']; ?>">
                                    <?php
                                    $imageUrl = SITE_URL . '/assets/images/placeholder.png'; // Default placeholder
                                    if (!empty($product['image_url'])) {
                                        if (filter_var($product['image_url'], FILTER_VALIDATE_URL)) {
                                            $imageUrl = htmlspecialchars($product['image_url']);
                                        } elseif (file_exists(PROJECT_ROOT_PATH . '/' . trim($product['image_url'], '/'))) {
                                            $imageUrl = SITE_URL . '/' . trim(htmlspecialchars($product['image_url']), '/');
                                        }
                                    }
                                    ?>
                                    <img src="<?php echo $imageUrl; ?>" class="card-img-top product-card-img"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </a>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">
                                        <a href="<?php echo SITE_URL; ?>/product?id=<?php echo $product['id']; ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h5>
                                    <p class="card-text text-muted small">
                                        <?php echo nl2br(htmlspecialchars(substr($product['description'] ?? '', 0, 80))) . (strlen($product['description'] ?? '') > 80 ? '...' : ''); ?>
                                    </p>
                                    <p class="card-text fs-5 fw-bold text-primary mt-auto mb-2">
                                        $<?php echo htmlspecialchars(number_format((float) $product['price'], 2)); ?></p>
                                    <form action="<?php echo SITE_URL; ?>/cart-add-action" method="post" class="d-grid">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                            <button type="submit" class="btn btn-primary"><i class="bi bi-cart-plus me-2"></i>Add to
                                                Cart</button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary" disabled><i
                                                    class="bi bi-x-circle me-2"></i>Out of Stock</button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if (!empty($products) && count($allProducts) > 6): ?>
                <div class="text-center mt-4 pt-2">
                    <a href="<?php echo SITE_URL; ?>/products" class="btn btn-outline-primary btn-lg">View All Products</a>
                </div>
            <?php endif; ?>

            <hr class="my-5">

            <div class="row mt-5">
                <div class="col-lg-8 mx-auto text-center mb-4">
                    <h2 class="display-5 fw-bold">Platform Highlights</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-shield-lock fs-1 text-primary mb-3"></i>
                            <h3 class="card-title h5">Secure Authentication</h3>
                            <p class="card-text">Industry-standard security practices including password hashing and
                                CSRF protection.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-person-check fs-1 text-primary mb-3"></i>
                            <h3 class="card-title h5">User Management</h3>
                            <p class="card-text">Easy registration, login, password reset, email verification, and
                                profile management.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-cart-check fs-1 text-primary mb-3"></i>
                            <h3 class="card-title h5">Seamless Shopping</h3>
                            <p class="card-text">Intuitive product Browse, a user-friendly cart, and a simple
                                (simulated) checkout.</p>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-5">

            <div class="row mt-5">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="card-title text-center mb-4">Why Choose AffiliateBasic System?</h2>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Secure
                                            by design</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Easy
                                            to implement & extend</li>
                                        <li class="mb-2"><i
                                                class="bi bi-check-circle-fill text-success me-2"></i>Mobile-friendly &
                                            responsive</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i
                                                class="bi bi-check-circle-fill text-success me-2"></i>E-commerce Ready
                                            Core</li>
                                        <li class="mb-2"><i
                                                class="bi bi-check-circle-fill text-success me-2"></i>Scalable
                                            Architecture</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Modern
                                            PHP & Best Practices</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>

    <button onclick="scrollToTop()" id="backToTopBtn" title="Go to top">
        <i class="bi bi-arrow-up-short"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
    <script>
        // Back to Top Button JavaScript
        var backToTopButton = document.getElementById("backToTopBtn");

        window.onscroll = function () { scrollFunction() };

        function scrollFunction() {
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                if (!backToTopButton.classList.contains('show')) {
                    backToTopButton.classList.add('show');
                }
            } else {
                if (backToTopButton.classList.contains('show')) {
                    backToTopButton.classList.remove('show');
                }
            }
        }

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>

</html>
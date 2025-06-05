<?php
// Ensure SITE_URL and PROJECT_ROOT_PATH are available (typically from config.php via index.php)
if (!defined('PROJECT_ROOT_PATH')) {
    // Fallback if not run through index.php, though this setup assumes front controller
    define('PROJECT_ROOT_PATH', dirname(__DIR__));
}
if (!defined('SITE_URL')) {
    // Fallback, should be defined in config.php
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $subdirectory = ''; // Define or load your subdirectory if applicable
    define('SITE_URL', rtrim($protocol . $host . $subdirectory, '/'));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>

    <main class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="display-4 fw-bold mb-4">About AffiliateBasic System</h1>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-bullseye text-primary me-2"></i>Our Mission</h2>
                            <p>To provide a secure, reliable, and easy-to-use platform combining robust user
                                authentication with essential e-commerce functionalities. AffiliateBasic aims to offer a
                                solid foundation for web applications that require user management and online retail
                                capabilities, all while adhering to industry best practices for web application
                                security.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-lightning-charge-fill text-primary me-2"></i>Key Features
                            </h2>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><i
                                        class="bi bi-shield-lock-fill text-success me-2"></i>Secure user registration
                                    with email verification</li>
                                <li class="list-group-item"><i class="bi bi-key-fill text-success me-2"></i>Password
                                    hashing (bcrypt) and secure login</li>
                                <li class="list-group-item"><i
                                        class="bi bi-arrow-clockwise text-success me-2"></i>Password reset functionality
                                </li>
                                <li class="list-group-item"><i
                                        class="bi bi-person-badge-fill text-success me-2"></i>User profile management
                                </li>
                                <li class="list-group-item"><i
                                        class="bi bi-shield-fill-check text-success me-2"></i>CSRF protection on forms
                                </li>
                                <li class="list-group-item"><i
                                        class="bi bi-bootstrap-fill text-success me-2"></i>Responsive design with
                                    Bootstrap 5</li>
                                <li class="list-group-item"><i class="bi bi-box-seam text-success me-2"></i>Product
                                    listing and detail pages</li>
                                <li class="list-group-item"><i class="bi bi-cart-fill text-success me-2"></i>Shopping
                                    cart functionality (add, update, remove)</li>
                                <li class="list-group-item"><i
                                        class="bi bi-credit-card-fill text-success me-2"></i>Basic checkout process
                                    (simulated payment)</li>
                                <li class="list-group-item"><i
                                        class="bi bi-clipboard-data-fill text-success me-2"></i>Order creation and
                                    persistence</li>
                                <li class="list-group-item"><i class="bi bi-person-gear text-success me-2"></i>Admin
                                    panel for product and order management</li>
                                <li class="list-group-item"><i class="bi bi-envelope-fill text-success me-2"></i>Email
                                    notifications using PHPMailer</li>
                                <li class="list-group-item"><i
                                        class="bi bi-file-earmark-code-fill text-success me-2"></i>Clean URL routing via
                                    a front controller</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h2 class="h4"><i class="bi bi-people-fill text-primary me-2"></i>The Team</h2>
                            <p>AffiliateBasic is developed by a dedicated team passionate about creating secure and
                                functional web applications. We believe in providing developers with a reliable starting
                                point for their projects.</p>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 60px; height: 60px;">
                                                <i class="bi bi-person fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="mb-0">Dev Lead</h5>
                                            <p class="mb-0 text-muted">Overseeing development</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 60px; height: 60px;">
                                                <i class="bi bi-shield-shaded fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="mb-0">Security Analyst</h5>
                                            <p class="mb-0 text-muted">Ensuring best practices</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="<?php echo SITE_URL; ?>/contact" class="btn btn-lg btn-outline-primary">Get In
                            Touch</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>

</html>
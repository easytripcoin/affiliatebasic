<?php
// affiliatebasic/pages/login.php
// This file primarily renders the login form.
// The core logic for handling login and cart merging is in core/auth/login.php (action script).

use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\redirectWithMessage;

// Redirect to home if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    redirectWithMessage('home', 'info', 'You are already logged in.');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php
    if (!defined('PROJECT_ROOT_PATH')) {
        define('PROJECT_ROOT_PATH', dirname(__DIR__));
    }
    include PROJECT_ROOT_PATH . '/templates/navbar.php';
    ?>

    <main class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white text-center">
                            <h2 class="card-title mb-0">Sign In</h2>
                        </div>
                        <div class="card-body p-4">
                            <?php echo displayMessage(); ?>

                            <form action="<?php echo SITE_URL; ?>/login-action" method="post" novalidate
                                class="needs-validation">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : '';
                                    unset($_SESSION['form_data']['email']); ?>" required>
                                    <div class="invalid-feedback">Please provide a valid email.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password"
                                            required aria-describedby="password-toggle">
                                        <span class="input-group-text" id="password-toggle">
                                            <button type="button" class="btn p-0 border-0" data-role="togglepassword"
                                                data-target="#password" title="Show password" tabindex="-1">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback" id="password-feedback">Please provide your password.
                                    </div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Sign In</button>
                                </div>

                                <div class="mt-3 text-center">
                                    <a href="<?php echo SITE_URL; ?>/forgot-password"
                                        class="text-decoration-none">Forgot password?</a>
                                </div>
                            </form>

                            <div class="mt-3 text-center">
                                <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/register"
                                        class="text-decoration-none">Register</a></p>
                                <p><a href="<?php echo SITE_URL; ?>/home" class="text-decoration-none">Back to Home</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js?v=40"></script>
</body>

</html>
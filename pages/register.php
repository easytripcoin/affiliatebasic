<?php
// affiliatebasic/pages/register.php
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Config\displayMessage;

// If already logged in, perhaps redirect to dashboard or home
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Assuming AffiliateBasic\Config\redirectWithMessage is available
    if (function_exists('AffiliateBasic\Config\redirectWithMessage')) {
        AffiliateBasic\Config\redirectWithMessage('home', 'info', 'You are already logged in.');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | AffiliateBasic System</title>
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
                            <h2 class="card-title mb-0">Create Account</h2>
                        </div>
                        <div class="card-body p-4">
                            <?php echo displayMessage(); ?>

                            <form action="<?php echo SITE_URL; ?>/register-action" method="post" novalidate
                                class="needs-validation">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="<?php echo isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : ''; ?>"
                                        required pattern="^[a-zA-Z0-9_]{3,50}$"
                                        title="Username must be 3-50 characters and can include letters, numbers, and underscores.">
                                    <div class="invalid-feedback">Please choose a username (3-50 characters, letters,
                                        numbers, underscores).</div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>"
                                        required>
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
                                    <div class="invalid-feedback" id="password-feedback">Please provide a password.
                                    </div>
                                    <div class="password-strength-container mt-2">
                                        <small>Password Strength: <span
                                                class="password-strength-text fw-bold text-danger">Very
                                                Weak</span></small>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="password-strength-bar progress-bar bg-danger" role="progressbar"
                                                style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Minimum 8 characters with at least one number
                                        and one letter.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password"
                                            name="confirm_password" required aria-describedby="confirm-password-toggle">
                                        <span class="input-group-text" id="confirm-password-toggle">
                                            <button type="button" class="btn p-0 border-0" data-role="togglepassword"
                                                data-target="#confirm_password" title="Show password" tabindex="-1">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback" id="confirm-password-feedback">Please confirm your
                                        password.</div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Register</button>
                                </div>
                            </form>

                            <div class="mt-3 text-center">
                                <p>Already have an account? <a href="<?php echo SITE_URL; ?>/login"
                                        class="text-decoration-none">Sign in</a>
                                </p>
                                <p><a href="<?php echo SITE_URL; ?>/home" class="text-decoration-none">Back to Home</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php
    // Clear form data after rendering to prevent stale data on reload if errors occurred
    if (isset($_SESSION['form_data'])) {
        unset($_SESSION['form_data']);
    }
    ?>

    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>

</html>
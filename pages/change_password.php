<?php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Config\displayMessage;

// Config is included by the main index.php (front controller)
// require_once __DIR__ . '/../config/config.php'; // This line is redundant if called by index.php

// Check if user is logged in (This check is also often done in index.php for protected routes)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // If using the front controller, redirect to the 'login' route key
    redirectWithMessage('login', 'danger', 'Please login to change your password.');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php
    // Assuming PROJECT_ROOT_PATH is defined in config.php (loaded by index.php)
    if (defined('PROJECT_ROOT_PATH')) {
        include PROJECT_ROOT_PATH . '/templates/navbar.php';
    } else {
        // Fallback if not run through index.php, though this setup assumes front controller
        include __DIR__ . '/../templates/navbar.php';
    }
    ?>

    <main class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white text-center">
                            <h2 class="card-title mb-0">Change Password</h2>
                        </div>
                        <div class="card-body p-4">
                            <?php echo displayMessage(); // Assuming displayMessage is globally available or use \AuthBasic\Config\displayMessage(); ?>

                            <form action="<?php echo SITE_URL; ?>/change-password-action" method="post" novalidate
                                class="needs-validation">
                                <input type="hidden" name="csrf_token"
                                    value="<?php echo generateCSRFToken(); // Assuming generateCSRFToken is globally available or use \AuthBasic\Config\generateCSRFToken(); ?>">

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password"
                                            name="current_password" required aria-describedby="current-password-toggle">
                                        <span class="input-group-text" id="current-password-toggle">
                                            <button type="button" class="btn p-0 border-0" data-role="togglepassword"
                                                data-target="#current_password" title="Show password" tabindex="-1">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">Please provide your current password.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password"
                                            name="new_password" required aria-describedby="new-password-toggle">
                                        <span class="input-group-text" id="new-password-toggle">
                                            <button type="button" class="btn p-0 border-0" data-role="togglepassword"
                                                data-target="#new_password" title="Show password" tabindex="-1">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback" id="password-feedback">Please provide a new password.
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
                                        and
                                        one letter.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
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
                                    <div class="invalid-feedback" id="confirm-password-feedback">Passwords must match.
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </div>
                            </form>
                            <div class="mt-3 text-center">
                                <p><a href="<?php echo SITE_URL; ?>/dashboard" class="text-decoration-none">Back to
                                        Dashboard</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php
    if (defined('PROJECT_ROOT_PATH')) {
        include PROJECT_ROOT_PATH . '/templates/footer.php';
    } else {
        include __DIR__ . '/../templates/footer.php';
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
    <script>
        // Client-side form validation (specific to this page, if needed, or rely on global script.js)
        // The global script.js should handle the password toggles and strength indicator.
        // The `needs-validation` class on the form will trigger Bootstrap's default validation.
        // The custom validation logic in script.js for password strength and matching will also apply.

        // Example of how you might re-initialize or ensure components if loaded dynamically,
        // but for this static page, script.js loaded at the end should suffice.
        // document.addEventListener('DOMContentLoaded', function() {
        // if (typeof initPasswordVisibilityToggle === 'function') {
        // initPasswordVisibilityToggle();
        // }
        // if (typeof initPasswordStrength === 'function') {
        // initPasswordStrength();
        // }
        // if (typeof initFormValidation === 'function') {
        // initFormValidation();
        // }
        // });
    </script>
</body>

</html>
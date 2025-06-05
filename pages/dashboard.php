<?php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;
// CSRF token function will be needed for the new logout form
use function AffiliateBasic\Config\generateCSRFToken;


// Config is included by the main index.php (front controller)
// require_once __DIR__ . '/../config/config.php'; // This line is redundant if called by index.php

// Check if user is logged in (This check is also often done in index.php for protected routes)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // If using the front controller, redirect to the 'login' route key
    redirectWithMessage('login', 'danger', 'Please login to access the dashboard.');
}

// Remember me cookie check is handled by index.php before this page is loaded.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | AffiliateBasic System</title>
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
        // Fallback if not run through index.php
        include __DIR__ . '/../templates/navbar.php';
    }
    ?>

    <main class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7">
                    <?php echo displayMessage(); // Or \AuthBasic\Config\displayMessage(); ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white text-center">
                            <h2 class="card-title mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                            </h2>
                        </div>
                        <div class="card-body p-4">
                            <p class="lead text-center">You are now logged in to your account.</p>
                            <hr>
                            <div class="mb-3">
                                <p class="mb-1"><strong><i class="bi bi-person-fill me-2"></i>Username:</strong>
                                    <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                                <p class="mb-0"><strong><i class="bi bi-envelope-fill me-2"></i>Email:</strong>
                                    <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                            </div>
                            <hr>
                            <div class="d-grid gap-3 mt-4">
                                <a href="<?php echo SITE_URL; ?>/profile" class="btn btn-lg btn-outline-primary"><i
                                        class="bi bi-person-circle me-2"></i>View/Edit Profile</a>
                                <a href="<?php echo SITE_URL; ?>/change-password"
                                    class="btn btn-lg btn-outline-secondary"><i
                                        class="bi bi-shield-lock me-2"></i>Change Password</a>

                                <form action="<?php echo SITE_URL; ?>/logout-action" method="post" class="d-grid">
                                    <input type="hidden" name="csrf_token"
                                        value="<?php echo generateCSRFToken(); // Or \AuthBasic\Config\generateCSRFToken(); ?>">
                                    <button type="submit" class="btn btn-lg btn-danger"><i
                                            class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
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
</body>

</html>
<?php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    redirectWithMessage('forgot-password', 'danger', 'Invalid password reset link.');
}

$token = $_GET['token'];

// Verify token
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        redirectWithMessage('forgot-password', 'danger', 'Invalid or expired password reset link.');
    }
} catch (PDOException $e) {
    redirectWithMessage('forgot-password', 'danger', 'Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | AffiliateBasic System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../templates/navbar.php'; ?>

    <main class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white text-center">
                            <h2 class="card-title mb-0">Reset Password</h2>
                        </div>
                        <div class="card-body p-4">
                            <?php echo displayMessage(); ?>

                            <form action="<?php echo SITE_URL; ?>/reset-password-action" method="post" novalidate
                                class="needs-validation">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
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
                                        <div class="progress mt-1">
                                            <div class="password-strength-bar progress-bar bg-danger" role="progressbar"
                                                style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Minimum 8 characters with at least one number
                                        and one letter</small>
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
                                    <button type="submit" class="btn btn-primary">Reset Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>

</html>
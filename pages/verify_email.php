<?php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    redirectWithMessage('register', 'danger', 'Invalid verification link.');
}

$token = $_GET['token'];

try {
    global $pdo;
    // Check if token exists, user is not already verified, and token hasn't expired
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE verification_token = ? AND is_verified = 0 AND verification_token_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        redirectWithMessage('login', 'danger', 'Invalid or expired verification link.');
    }

    // Mark user as verified
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, verification_token_expires = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);

    redirectWithMessage('login', 'success', 'Email verified successfully! You can now login.');
} catch (PDOException $e) {
    redirectWithMessage('register', 'danger', 'Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification | AffiliateBasic System</title>
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
                            <h2 class="card-title mb-0">Email Verification</h2>
                        </div>
                        <div class="card-body p-4">
                            <?php echo displayMessage(); ?>
                            <p class="text-center">If you were not redirected, please <a href="<?php echo SITE_URL; ?>/login"
                                    class="text-decoration-none">click here to log in</a>.</p>
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
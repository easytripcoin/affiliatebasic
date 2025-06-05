<?php
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\redirectWithMessage;

// Redirect to login.php if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirectWithMessage('login', 'danger', 'Please log in to access your profile.');
}

// Fetch user data
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Invalidate session and redirect if user not found
        session_destroy();
        redirectWithMessage('login', 'danger', 'User not found. Please log in again.');
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    redirectWithMessage('login', 'danger', 'Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | AffiliateBasic System</title>
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
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white text-center">
                            <h2 class="card-title mb-0">Your Profile</h2>
                        </div>
                        <div class="card-body p-4">
                            <?php echo displayMessage(); ?>

                            <!-- Profile Information -->
                            <div class="mb-4">
                                <h4 class="mb-3">Profile Details</h4>
                                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                            </div>

                            <!-- Update Profile Form -->
                            <h4 class="mb-3">Update Profile</h4>
                            <form action="<?php echo SITE_URL; ?>/update-profile-action" method="post" novalidate
                                class="needs-validation">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    <div class="invalid-feedback">Please provide a valid username (at least 3
                                        characters).</div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    <div class="invalid-feedback">Please provide a valid email.</div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </div>
                            </form>

                            <!-- Change Password Link -->
                            <div class="mt-3 text-center">
                                <p>Want to change your password? <a href="<?php echo SITE_URL; ?>/change-password"
                                        class="text-decoration-none">Click here</a></p>
                            </div>

                            <div class="mt-3 text-center">
                                <p><a href="<?php echo SITE_URL; ?>/home" class="text-decoration-none">Back to Home</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
    <script>
        // Client-side form validation
        function validateForm() {
            const form = document.querySelector('form');
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // Bootstrap validation
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return false;
            }

            // Additional username validation
            if (username.length < 3) {
                document.getElementById('username').classList.add('is-invalid');
                return false;
            }

            // Additional email validation
            if (!emailRegex.test(email)) {
                document.getElementById('email').classList.add('is-invalid');
                return false;
            }

            return true;
        }

        // Ensure validateForm is called on form submission
        document.querySelector('form').addEventListener('submit', function (event) {
            if (!validateForm()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    </script>
</body>

</html>
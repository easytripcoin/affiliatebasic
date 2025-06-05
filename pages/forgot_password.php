<?php
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Config\displayMessage;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | AffiliateBasic System</title>
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
                            <h2 class="card-title mb-0">Forgot Password</h2>
                        </div>
                        <div class="card-body p-4">
                            <?php echo displayMessage(); ?>

                            <form action="<?php echo SITE_URL; ?>/forgot-password-action" method="post" novalidate
                                onsubmit="return validateForm()">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                    <div class="invalid-feedback">Please provide a valid email.</div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Reset Password</button>
                                </div>
                            </form>

                            <div class="mt-3 text-center">
                                <a href="<?php echo SITE_URL; ?>/login" class="text-decoration-none">Back to Login</a>
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
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // Check if form is valid (HTML5 validation)
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return false;
            }

            // Additional email validation
            if (!emailRegex.test(email)) {
                document.getElementById('email').classList.add('is-invalid');
                return false;
            }

            return true;
        }
    </script>
</body>

</html>
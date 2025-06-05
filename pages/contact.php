<?php
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Config\displayMessage;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | AffiliateBasic System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../templates/navbar.php'; ?>

    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4 text-center">Contact Us</h1>

                    <?php echo displayMessage(); ?>

                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form action="<?php echo SITE_URL; ?>/contact-action" method="post" novalidate
                                onsubmit="return validateForm()">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Your Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback">Please provide your name.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback">Please provide a valid email.</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                    <div class="invalid-feedback">Please provide a subject.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5"
                                        required></textarea>
                                    <div class="invalid-feedback">Please provide your message.</div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row mt-5">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-geo-alt fs-1 text-primary"></i>
                                    <h3 class="h5 mt-2">Address</h3>
                                    <p class="mb-0">123 Security Street<br>Webville, WD 12345</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-telephone fs-1 text-primary"></i>
                                    <h3 class="h5 mt-2">Phone</h3>
                                    <p class="mb-0">+1 (555) 123-4567<br>Mon-Fri, 9am-5pm</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-envelope fs-1 text-primary"></i>
                                    <h3 class="h5 mt-2">Email</h3>
                                    <p class="mb-0">support@authsystem.com<br>help@authsystem.com</p>
                                </div>
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
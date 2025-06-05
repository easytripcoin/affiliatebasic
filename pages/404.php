<?php // authbasic/pages/404.php
// Config and session are already started by index.php
// $currentPage is set to '404' by index.php
http_response_code(404); // Ensure correct HTTP status
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="py-5 bg-light">
        <div class="container text-center">
            <h1 class="display-1">404</h1>
            <h2>Page Not Found</h2>
            <p class="lead">Sorry, the page you are looking for does not exist.</p>
            <a href="<?php echo SITE_URL; ?>/home" class="btn btn-primary mt-3">Go to Homepage</a>
        </div>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>

</html>
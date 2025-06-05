<?php
// This is the main entry point for the application.
// It handles routing and includes the necessary page content or action script.

use function AffiliateBasic\Config\sanitizeInput;

// Ensure config is loaded first.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

// Include core initialization scripts (like remember_me handler)
require_once __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'auth' . DIRECTORY_SEPARATOR . 'remember_me.php';

// --- Capture Affiliate Referral Code ---
if (isset($_GET['ref'])) {
    $referral_code = trim(sanitizeInput($_GET['ref']));
    if (!empty($referral_code)) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id FROM users WHERE user_affiliate_code = ? AND is_affiliate = 1");
        $stmt->execute([$referral_code]);
        $referrer = $stmt->fetch();
        if ($referrer) {
            $_SESSION['referrer_user_id'] = $referrer['id'];
            $_SESSION['affiliate_code_used'] = $referral_code;
        }
    }
}

// --- Routing Logic ---
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '';
if (!empty($subdirectory) && strpos($requestUri, $subdirectory) === 0) {
    $basePath = $subdirectory;
}
if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestPath = strtok($requestUri, '?');
if (isset($_GET['ref'])) {
    $requestUriClean = preg_replace('/([&?]ref=[^&]*)|(ref=[^&]*&)/', '', $_SERVER['REQUEST_URI']);
    $requestPath = strtok(substr($requestUriClean, strlen($basePath)), '?');
}
$requestPath = trim($requestPath, '/');

$currentPage = '';

$availablePages = [
    '' => 'home.php',
    'home' => 'home.php',
    'about' => 'about.php',
    'contact' => 'contact.php',
    'login' => 'login.php',
    'register' => 'register.php',
    'dashboard' => 'dashboard.php',
    'profile' => 'profile.php',
    'privacy' => 'privacy.php',
    'terms' => 'terms.php',
    'change-password' => 'change_password.php',
    'forgot-password' => 'forgot_password.php',
    'reset-password' => 'reset_password.php',
    'verify-email' => 'verify_email.php',
    'products' => 'products.php',
    'product' => 'product.php',
    'cart' => 'cart.php',
    'checkout' => 'checkout.php',
    'order-confirmation' => 'order_confirmation.php',
    'my-orders' => 'my_orders.php', // New route for user's orders
    'order-detail' => 'order_detail_user.php', // New page for user to view their specific order detail
    'admin-products' => 'admin_products.php',
    'admin-add-product' => 'admin_add_product.php',
    'admin-edit-product' => 'admin_edit_product.php',
    'admin-orders' => 'admin_orders.php',
    'admin-order-detail' => 'admin_order_detail.php',
    'affiliate-dashboard' => 'affiliate_dashboard.php',
    'admin-manage-affiliates' => 'admin_manage_affiliates.php',
    'admin-withdrawal-requests' => 'admin_withdrawal_requests.php',
    'admin-finalize-earnings' => 'admin_finalize_earnings.php', // New Admin Page
];

$availableActions = [
    'login-action' => 'auth/login.php',
    'register-action' => 'auth/register.php',
    'contact-action' => 'contact/submit.php',
    'forgot-password-action' => 'auth/forgot-password.php',
    'reset-password-action' => 'auth/reset-password.php',
    'change-password-action' => 'auth/change-password.php',
    'update-profile-action' => 'auth/update-profile.php',
    'logout-action' => 'auth/logout.php',
    'cart-add-action' => 'ecommerce/cart_add_action.php',
    'cart-update-action' => 'ecommerce/cart_update_action.php',
    'cart-remove-action' => 'ecommerce/cart_remove_action.php',
    'order-place-action' => 'ecommerce/order_place_action.php',
    'admin-product-add-action' => 'ecommerce/admin_product_add_action.php',
    'admin-product-edit-action' => 'ecommerce/admin_product_edit_action.php',
    'admin-product-delete-action' => 'ecommerce/admin_product_delete_action.php',
    'admin-order-update-status-action' => 'ecommerce/admin_order_update_status_action.php',
    'request-withdrawal-action' => 'affiliate/request_withdrawal_action.php',
    'admin-process-withdrawal-action' => 'affiliate/admin_process_withdrawal_action.php',
    'admin-manage-affiliates-action' => 'affiliate/admin_manage_affiliates_action.php',
    'admin-finalize-earnings-action' => 'affiliate/admin_finalize_earnings_action.php', // New Admin Action
];

$scriptToInclude = null;

if (array_key_exists($requestPath, $availableActions)) {
    $actionFileRelativePath = $availableActions[$requestPath];
    $filePath = PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . $actionFileRelativePath;
    if (file_exists($filePath)) {
        $scriptToInclude = $filePath;
    }
} elseif (array_key_exists($requestPath, $availablePages)) {
    $pageFileName = $availablePages[$requestPath];
    $filePath = PAGES_PATH . DIRECTORY_SEPARATOR . $pageFileName;
    if (file_exists($filePath)) {
        $scriptToInclude = $filePath;
        $currentPage = $requestPath === '' ? 'home' : $requestPath;
    }
}

if ($scriptToInclude) {
    require $scriptToInclude;
} else {
    http_response_code(404);
    $currentPage = '404';
    $notFoundPagePath = PAGES_PATH . DIRECTORY_SEPARATOR . '404.php';
    if (file_exists($notFoundPagePath)) {
        require $notFoundPagePath;
    } else {
        echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>404 Not Found</title></head><body>";
        echo "<h1>404 Not Found</h1><p>The page or action you requested could not be found.</p>";
        echo "<p><a href='" . SITE_URL . "/home'>Go to Homepage</a></p>";
        echo "</body></html>";
    }
}

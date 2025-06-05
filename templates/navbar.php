<?php
// affiliatebasic/templates/navbar.php
use function AffiliateBasic\Config\generateCSRFToken;

global $currentPage;

if (!defined('SITE_URL')) {
    define('SITE_URL', '');
}
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(__DIR__));
}

if (!function_exists('AffiliateBasic\Core\Ecommerce\getCartDisplayItemCount')) {
    if (file_exists(PROJECT_ROOT_PATH . '/core/ecommerce/cart_functions.php')) {
        require_once PROJECT_ROOT_PATH . '/core/ecommerce/cart_functions.php';
    }
}

global $pdo;

$cartItemCount = 0;
if (function_exists('AffiliateBasic\Core\Ecommerce\getCartDisplayItemCount')) {
    $cartItemCount = AffiliateBasic\Core\Ecommerce\getCartDisplayItemCount($pdo);
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <?php
        if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
            echo '<div style="position: absolute; top: 5px; left: 5px; background-color: #ffc107; color: #000; padding: 2px 5px; font-size: 0.7rem; border-radius: 3px; z-index: 9999;">';
            echo 'DEBUG: is_admin: ' . (isset($_SESSION['is_admin']) ? ($_SESSION['is_admin'] ? 'true' : 'false') : 'not_set');
            echo ' | is_affiliate: ' . (isset($_SESSION['is_affiliate']) ? ($_SESSION['is_affiliate'] ? 'true' : 'false') : 'not_set');
            echo '</div>';
        }
        ?>
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>/home">AffiliateBasic</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'home') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'about') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'products') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/products">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'contact') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/contact">Contact</a>
                </li>
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/dashboard">Dashboard</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'cart') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/cart"><i class="bi bi-cart"></i> Cart
                        <?php if ($cartItemCount > 0): ?>
                            <span class="badge bg-danger ms-1 rounded-pill"><?php echo $cartItemCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['profile', 'change-password', 'my-orders']) ? 'active' : ''; ?>"
                            href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item <?php echo ($currentPage === 'profile') ? 'active' : ''; ?>"
                                    href="<?php echo SITE_URL; ?>/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item <?php echo ($currentPage === 'my-orders') ? 'active' : ''; ?>"
                                    href="<?php echo SITE_URL; ?>/my-orders"><i class="bi bi-list-check me-2"></i>My
                                    Orders</a></li>
                            <li><a class="dropdown-item <?php echo ($currentPage === 'change-password') ? 'active' : ''; ?>"
                                    href="<?php echo SITE_URL; ?>/change-password"><i class="bi bi-lock me-2"></i>Change
                                    Password</a></li>
                            <?php if (isset($_SESSION['is_affiliate']) && $_SESSION['is_affiliate'] === true): ?>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'affiliate-dashboard') ? 'active' : ''; ?>"
                                        href="<?php echo SITE_URL; ?>/affiliate-dashboard"><i
                                            class="bi bi-wallet2 me-2"></i>Affiliate Dashboard</a></li>
                            <?php endif; ?>
                            <hr class="dropdown-divider">
                    </li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                        <li><a class="dropdown-item <?php echo ($currentPage === 'admin-products') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/admin-products"><i class="bi bi-box-seam me-2"></i>Manage
                                Products</a></li>
                        <li><a class="dropdown-item <?php echo ($currentPage === 'admin-orders') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/admin-orders"><i class="bi bi-card-list me-2"></i>Manage
                                Orders</a></li>
                        <li><a class="dropdown-item <?php echo ($currentPage === 'admin-manage-affiliates') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/admin-manage-affiliates"><i class="bi bi-people me-2"></i>Manage
                                Affiliates</a></li>
                        <li><a class="dropdown-item <?php echo ($currentPage === 'admin-withdrawal-requests') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/admin-withdrawal-requests"><i
                                    class="bi bi-cash-stack me-2"></i>Withdrawal Requests</a></li>
                        <li><a class="dropdown-item <?php echo ($currentPage === 'admin-finalize-earnings') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/admin-finalize-earnings"><i
                                    class="bi bi-patch-check-fill me-2"></i>Finalize Earnings</a></li>
                        <li></li>
                        <hr class="dropdown-divider">
                        </li>
                    <?php endif; ?>
                    <li>
                        <form action="<?php echo SITE_URL; ?>/logout-action" method="post" class="d-inline" id="logoutForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'login') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/login"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'register') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/register"><i class="bi bi-person-plus me-1"></i>Register</a>
                </li>
            <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
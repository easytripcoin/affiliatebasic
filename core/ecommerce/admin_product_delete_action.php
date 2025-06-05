<?php
namespace AffiliateBasic\Core\Ecommerce;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;

require_once __DIR__ . '/product_functions.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('admin-products', 'danger', 'Invalid request method.');
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}
if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('admin-products', 'danger', 'CSRF token validation failed.');
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if (!$productId) {
    redirectWithMessage('admin-products', 'danger', 'Invalid product ID.');
}

// Optional: Before deleting, check if the product is part of any non-completed orders
// and decide on a deletion strategy (soft delete, prevent delete, etc.)
// For now, we'll proceed with direct deletion.

// Also, if the product has an image, you might want to delete it from the filesystem.
$product = getProductById($pdo, $productId);
if ($product && !empty($product['image_url'])) {
    $imagePath = PROJECT_ROOT_PATH . '/' . $product['image_url'];
    if (file_exists($imagePath) && is_writable(dirname($imagePath))) { // Check if directory is writable
        @unlink($imagePath); // Suppress error if unlink fails, but ideally log it
    }
}


if (deleteProduct($pdo, $productId)) {
    redirectWithMessage('admin-products', 'success', 'Product deleted successfully.');
} else {
    // The deleteProduct function should log specific SQL errors.
    redirectWithMessage('admin-products', 'danger', 'Failed to delete product. It might be referenced in existing orders.');
}
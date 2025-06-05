<?php
namespace AffiliateBasic\Core\Ecommerce;

use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\verifyCSRFToken;
use function AffiliateBasic\Config\sanitizeInput;

require_once __DIR__ . '/product_functions.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('admin-products', 'danger', 'Invalid request method.');
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}
if (!verifyCSRFToken($_POST['csrf_token'])) {
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    redirectWithMessage('admin-edit-product?id=' . $productId, 'danger', 'CSRF token validation failed.');
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$name = sanitizeInput($_POST['name'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
$stock_quantity = filter_input(INPUT_POST, 'stock_quantity', FILTER_VALIDATE_INT);
// New field
$affiliate_bonus_percentage = filter_input(INPUT_POST, 'affiliate_bonus_percentage', FILTER_VALIDATE_FLOAT);
if ($affiliate_bonus_percentage === false || $affiliate_bonus_percentage < 0 || $affiliate_bonus_percentage > 100) {
    $affiliate_bonus_percentage = 0.00; // Default or error
}
$image_url_new = null;

if (!$productId) {
    redirectWithMessage('admin-products', 'danger', 'Invalid product ID.');
}

// Basic Validation
if (empty($name) || $price === false || $price < 0 || $stock_quantity === false || $stock_quantity < 0) {
    redirectWithMessage('admin-edit-product?id=' . $productId, 'danger', 'Name, valid price, and valid stock are required.');
}

// Image Upload Handling (if a new image is provided)
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == UPLOAD_ERR_OK) {
    $target_dir = PROJECT_ROOT_PATH . "/assets/images/products/"; // Ensure this directory exists and is writable
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0775, true);
    }
    $imageFileType = strtolower(pathinfo($_FILES["image_file"]["name"], PATHINFO_EXTENSION));
    $safe_filename = uniqid('prod_', true) . '.' . $imageFileType;
    $target_file = $target_dir . $safe_filename;
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    $check = getimagesize($_FILES["image_file"]["tmp_name"]);
    if ($check === false) {
        redirectWithMessage('admin-edit-product?id=' . $productId, 'danger', 'New file is not an image.');
    }
    if ($_FILES["image_file"]["size"] > 2000000) { // 2MB
        redirectWithMessage('admin-edit-product?id=' . $productId, 'danger', 'New image file is too large.');
    }
    if (!in_array($imageFileType, $allowed_types)) {
        redirectWithMessage('admin-edit-product?id=' . $productId, 'danger', 'Sorry, only JPG, JPEG, PNG & GIF files are allowed for new image.');
    }

    // Before moving, delete old image if it exists and a new one is successfully uploaded
    $oldProduct = getProductById($pdo, $productId);
    if ($oldProduct && !empty($oldProduct['image_url']) && file_exists(PROJECT_ROOT_PATH . '/' . $oldProduct['image_url'])) {
        // Deletion can be tricky with permissions; ensure web server can write to the uploads dir
        // For now, we'll just overwrite the image_url. A robust solution might delete the old file.
        // unlink(PROJECT_ROOT_PATH . '/' . $oldProduct['image_url']);
    }

    if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
        $image_url_new = "assets/images/products/" . $safe_filename;
    } else {
        redirectWithMessage('admin-edit-product?id=' . $productId, 'danger', 'Sorry, there was an error uploading your new image.');
    }
}

// Pass the new field to updateProduct
if (updateProduct($pdo, $productId, $name, $description, (float) $price, (int) $stock_quantity, $image_url_new, (float) $affiliate_bonus_percentage)) {
    redirectWithMessage('admin-products', 'success', 'Product updated successfully.');
} else {
    redirectWithMessage('admin-edit-product?id=' . $productId, 'danger', 'Failed to update product. Please check logs.');
}
<?php
namespace AffiliateBasic\Core\Ecommerce;

use PDO;

/**
 * Fetches all products from the database, typically those in stock.
 * @param PDO $pdo The PDO database connection object.
 * @return array An array of products.
 */
function getAllProducts(PDO $pdo): array
{
    // You might want to add pagination for large numbers of products
    $stmt = $pdo->query("SELECT id, name, description, price, image_url, stock_quantity, affiliate_bonus_percentage FROM products WHERE stock_quantity > 0 ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetches a single product by its ID.
 * @param PDO $pdo The PDO database connection object.
 * @param int $productId The ID of the product to fetch.
 * @return array|false The product data as an associative array, or false if not found.
 */
function getProductById(PDO $pdo, int $productId)
{
    $stmt = $pdo->prepare("SELECT id, name, description, price, image_url, stock_quantity, affiliate_bonus_percentage FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// === Functions for Admin Product Management (Phase 3, but good to group here) ===

/**
 * Adds a new product to the database.
 * @param PDO $pdo The PDO database connection object.
 * @param string $name The name of the product.
 * @param string $description The description of the product.
 * @param float $price The price of the product.
 * @param int $stock_quantity The stock quantity of the product.
 * @param string|null $image_url Optional image URL for the product.
 * @param float $affiliate_bonus_percentage Optional affiliate bonus percentage for the product.
 * @return bool True on success, false on failure.
 */
function addProduct(PDO $pdo, string $name, string $description, float $price, int $stock_quantity, ?string $image_url = null, float $affiliate_bonus_percentage = 0.00): bool
{
    $sql = "INSERT INTO products (name, description, price, stock_quantity, image_url, affiliate_bonus_percentage, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$name, $description, $price, $stock_quantity, $image_url, $affiliate_bonus_percentage]);
    } catch (\PDOException $e) {
        error_log("Error adding product: " . $e->getMessage(), 3, LOGS_PATH . 'admin_errors.log');
        return false;
    }
}

/**
 * Updates an existing product in the database.
 * @param PDO $pdo The PDO database connection object.
 * @param int $productId The ID of the product to update.
 * @param string $name The new name of the product.
 * @param string $description The new description of the product.
 * @param float $price The new price of the product.
 * @param int $stock_quantity The new stock quantity of the product.
 * @param string|null $image_url Optional new image URL for the product.
 * @param float $affiliate_bonus_percentage Optional new affiliate bonus percentage for the product.
 * @return bool True on success, false on failure.
 */
function updateProduct(PDO $pdo, int $productId, string $name, string $description, float $price, int $stock_quantity, ?string $image_url = null, float $affiliate_bonus_percentage = 0.00): bool
{
    $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, affiliate_bonus_percentage = ?, updated_at = NOW()";
    $params = [$name, $description, $price, $stock_quantity, $affiliate_bonus_percentage];

    if ($image_url !== null) {
        $sql .= ", image_url = ?";
        $params[] = $image_url;
    }

    $sql .= " WHERE id = ?";
    $params[] = $productId;

    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (\PDOException $e) {
        error_log("Error updating product ID $productId: " . $e->getMessage(), 3, LOGS_PATH . 'admin_errors.log');
        return false;
    }
}

/**
 * Deletes a product from the database.
 * @param PDO $pdo
 * @param int $productId
 * @return bool True on success, false on failure.
 */
function deleteProduct(PDO $pdo, int $productId): bool
{
    $sql = "DELETE FROM products WHERE id = ?";
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$productId]);
    } catch (\PDOException $e) {
        // Log error, consider foreign key constraints if any cascade issues arise
        error_log("Error deleting product ID $productId: " . $e->getMessage(), 3, LOGS_PATH . 'admin_errors.log');
        return false;
    }
}

/**
 * Fetches all products for admin management, including those out of stock.
 * @param PDO $pdo The PDO database connection object.
 * @return array An array of products with all relevant fields.
 */
function getAllProductsForAdmin(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT id, name, price, stock_quantity, affiliate_bonus_percentage, created_at, updated_at FROM products ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
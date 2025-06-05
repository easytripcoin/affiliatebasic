-- Create the database if it doesn't already exist
CREATE DATABASE IF NOT EXISTS affiliatebasic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Switch to the newly created or existing database
USE affiliatebasic_db;

-- Drop existing tables in reverse order of dependency (or use SET FOREIGN_KEY_CHECKS=0)
DROP TABLE IF EXISTS withdrawal_requests;

DROP TABLE IF EXISTS affiliate_earnings;

DROP TABLE IF EXISTS order_items;

DROP TABLE IF EXISTS orders;

DROP TABLE IF EXISTS cart_items;

DROP TABLE IF EXISTS cart;

DROP TABLE IF EXISTS products;

DROP TABLE IF EXISTS contact_submissions;

DROP TABLE IF EXISTS users;

-- Create the users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    is_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(64) DEFAULT NULL,
    verification_token_expires DATETIME DEFAULT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    remember_token VARCHAR(64) DEFAULT NULL,
    remember_token_expires DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    is_affiliate TINYINT(1) DEFAULT 0,
    user_affiliate_code VARCHAR(50) NULL UNIQUE,
    affiliate_balance DECIMAL(10, 2) DEFAULT 0.00
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    `users`
VALUES (
        1,
        'admin',
        'easytripcoin@gmail.com',
        '$2y$10$8jB1XPS2g5FbzPmzJ2IrPuNmAIyaSjUDOGb6XMeT0i0qTemqvTS0y',
        1,
        1,
        NULL,
        NULL,
        NULL,
        NULL,
        '9f33c368793d850383876a86faff3f881479b8252055c4a7bfab76e54e770fdb',
        '2025-07-05 01:44:10',
        '2025-06-04 18:42:49',
        '2025-06-05 02:41:40',
        1,
        '5RCNXTQR',
        0.00
    ),
    (
        2,
        'kurt',
        'kurttunduk@gmail.com',
        '$2y$10$cl0uV1lyUG902pJn5Egh8u0F.cSbf1tEfou5n170p9Xz2NAXc5RiC',
        0,
        1,
        NULL,
        NULL,
        NULL,
        NULL,
        '19f490562cd106738a829d55330be2dbe9bc41978e082e454b756e710a819cb5',
        '2025-07-05 00:43:26',
        '2025-06-05 00:42:09',
        '2025-06-05 00:43:26',
        0,
        NULL,
        0.00
    );

-- Create the contact_submissions table
CREATE TABLE contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    affiliate_bonus_percentage DECIMAL(5, 2) DEFAULT 0.00
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the cart_items table
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price_at_addition DECIMAL(10, 2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    UNIQUE KEY (cart_id, product_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    order_status VARCHAR(50) DEFAULT 'pending',
    shipping_address TEXT,
    billing_address TEXT,
    payment_method VARCHAR(50),
    payment_status VARCHAR(50) DEFAULT 'pending',
    transaction_id VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    referrer_user_id INT NULL,
    affiliate_code_used VARCHAR(50) NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    FOREIGN KEY (referrer_user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the order_items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10, 2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the affiliate_earnings table
CREATE TABLE affiliate_earnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- The affiliate user who earned this
    order_id INT NOT NULL,
    order_item_id INT NOT NULL, -- Reference to the specific item in the order
    product_id INT NOT NULL,
    earned_amount DECIMAL(10, 2) NOT NULL,
    commission_rate DECIMAL(5, 2) NOT NULL, -- Percentage at time of earning
    status ENUM(
        'pending',
        'awaiting_clearance',
        'cleared',
        'paid',
        'cancelled'
    ) DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    order_payment_confirmed_at DATETIME NULL DEFAULT NULL, -- Timestamp when the order was marked 'delivered' AND 'paid'
    cleared_at DATETIME NULL DEFAULT NULL, -- Timestamp when the earning passed refund window and balance was updated
    processed_at DATETIME NULL DEFAULT NULL, -- Timestamp when this earning was part of a withdrawal marked as 'paid'
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE -- Consider ON DELETE SET NULL if products can be deleted but earnings should remain
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the withdrawal_requests table
CREATE TABLE withdrawal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_amount DECIMAL(10, 2) NOT NULL,
    status ENUM(
        'pending',
        'approved',
        'rejected'
    ) DEFAULT 'pending',
    payment_details TEXT NULL,
    admin_notes TEXT NULL,
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
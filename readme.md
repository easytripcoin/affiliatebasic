# AffiliateBasic: Secure PHP Login, Registration, E-commerce & Affiliate System

AffiliateBasic is a comprehensive and secure user authentication, e-commerce, and affiliate marketing system built with PHP, MySQL, and Bootstrap 5. It implements modern security best practices and provides a solid foundation for web applications requiring user management, online store capabilities, and a referral-based income system. The system features a front controller pattern for clean URLs, email verification, password reset functionality, product management, shopping cart, a checkout process, admin panels for managing products, orders, affiliates, and withdrawals. Configuration, including sensitive credentials, is managed via a `.env` file for enhanced security.

## Key Features

* **Secure User Authentication:**
    * User registration with email verification.
    * Secure login with password hashing (bcrypt).
    * "Remember Me" functionality with secure tokens.
    * Password reset via email with time-limited tokens.
    * Secure POST-based logout with CSRF protection.
* **E-commerce Functionality:**
    * Product listing and detailed product view pages.
    * Shopping cart (add, update, remove items for both guests and logged-in users).
    * Guest cart merging upon login.
    * Basic checkout process (simulated payment).
    * Order creation and storage.
    * User order history (can be added to dashboard).
* **Affiliate Marketing System:**
    * Users can be designated as affiliates by an admin.
    * Affiliates receive a unique referral code (`user_affiliate_code`).
    * Products can have an `affiliate_bonus_percentage` set by admin.
    * Referral tracking via URL parameter (`?ref=USER_AFFILIATE_CODE`).
    * Automatic calculation and recording of affiliate earnings for eligible products upon order completion.
    * Affiliate Dashboard for users to view their referral code, earnings (pending, cleared, paid), and current balance.
    * Withdrawal request system for affiliates.
* **Admin Panel (Basic & Expanded):**
    * Product Management (CRUD operations, including setting affiliate bonus percentage).
    * Order Management (View orders, view order details, update order status - updating to 'delivered' clears affiliate earnings for that order).
    * Affiliate Management (View users, activate/deactivate affiliate status, auto-generate affiliate codes).
    * Withdrawal Request Management (View pending requests, approve/reject requests, update user balances and earning statuses).
    * Admin access controlled via an `is_admin` flag in the `users` table.
* **Security Best Practices:**
    * Environment-Based Configuration (`.env`).
    * Password Hashing (bcrypt).
    * Prepared Statements (PDO) against SQL injection.
    * CSRF Protection on all state-changing forms.
    * Session Security (session regeneration).
    * Input Sanitization & Validation.
    * Rate Limiting (basic protection).
    * Secure Image Handling (Admin).
* **User Account Management:**
    * Profile viewing and updating.
    * Secure password change.
* **Email Handling:**
    * PHPMailer for reliable email sending.
    * HTML email templates.
    * Centralized SMTP configuration via `.env`.
* **Modern Architecture:**
    * Front Controller Pattern (`index.php`).
    * Organized core logic (`core/`).
    * Namespaced PHP code (under `AffiliateBasic`).
    * Centralized configuration.
* **User Interface & Experience:**
    * Responsive design with Bootstrap 5.
    * User-friendly forms.
    * Session-based feedback messages.
* **Logging:**
    * Logging for important events and errors in `logs/`.

## Project Structure

* **`affiliatebasic/`** (Project Root)
    * **`assets/`**: Static frontend assets.
    * **`config/`**: Application configuration.
    * **`core/`**: Core application logic.
        * `auth/`
        * `contact/`
        * `ecommerce/`
        * `affiliate/` (New: Affiliate system logic and actions)
            * `affiliate_functions.php`
            * `request_withdrawal_action.php`
            * `admin_process_withdrawal_action.php`
            * `admin_manage_affiliates_action.php`
    * **`logs/`**: Application log files.
    * **`pages/`**: View files.
        * (Existing pages...)
        * `affiliate_dashboard.php` (New)
        * `admin_manage_affiliates.php` (New)
        * `admin_withdrawal_requests.php` (New)
    * **`templates/`**: Reusable HTML partials.
    * **`vendor/`**: Composer dependencies.
    * `.env`, `.env.example`, `.htaccess`, `composer.json`, `database.sql`, `index.php`, `readme.md`, `LICENSE`

## Requirements

* PHP 7.4 or higher (PHP 8.x recommended).
    * PDO Extension (with MySQL driver).
    * OpenSSL Extension.
    * Mbstring Extension.
    * Fileinfo Extension (recommended for server-side MIME type validation of uploads).
* MySQL 5.7+ or MariaDB 10.2+.
* Composer.
* Web Server (Apache with `mod_rewrite`, or Nginx).

## Installation & Setup

1.  **Clone Repository & Install Dependencies:**
    ```bash
    git clone [https://github.com/yourusername/shopbasic.git](https://github.com/yourusername/shopbasic.git) # Update URL if needed
    cd shopbasic
    composer install
    ```

2.  **Database Setup:**
    * Create a database (e.g., `shopbasic_db`).
    * Import `database.sql`: `mysql -u your_db_user -p shopbasic_db < database.sql`
    * **Admin User:** After importing, add the `is_admin` column if not already in `database.sql` and set a user as admin:
        ```sql
        ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0 COMMENT '1 for admin, 0 for regular user' AFTER is_verified;
        -- Then update a user:
        -- UPDATE users SET is_admin = 1 WHERE email = 'your_admin_email@example.com';
        ```

3.  **Environment Configuration (`.env` file):**
    * Copy `.env.example` to `.env`.
    * Edit `.env` with your `APP_URL`, `DB_*` credentials, `MAIL_*` credentials, etc.
    * Ensure `APP_SUBDIRECTORY` is set correctly if your project is in a subfolder (e.g., `/shopbasic`).

4.  **Web Server Configuration:**
    * **Apache:** Ensure `mod_rewrite` is enabled. The `.htaccess` file provided should work. If in a subdirectory, adjust `RewriteBase` in `.htaccess`.
    * **Nginx:** Configure to route requests to `index.php`. See example in the original `readme.md`.

5.  **Permissions:**
    * Ensure `logs/` is writable.
    * Ensure `assets/images/products/` is created and writable by the web server user for product image uploads.

6.  **Access Application:** Navigate to your `APP_URL`.

## Usage Guide

The application uses clean URLs managed by `index.php`.

* **User Routes:**
    * `/` or `/home`: Homepage
    * `/products`: Product listing
    * `/product?id={ID}`: Single product view
    * `/cart`: View shopping cart
    * `/checkout`: Checkout page
    * `/order-confirmation`: Order success page
    * `/register`, `/login`, `/dashboard`, `/profile`, `/change-password`, etc.
    * `/affiliate-dashboard`: View affiliate earnings, balance, and request withdrawal.
* **Action Routes (POST requests):**
    * `/cart-add-action`, `/cart-update-action`, `/cart-remove-action`
    * `/order-place-action`
    * Other auth actions as before.
* **Admin Routes (Require admin privileges):**
    * `/admin-products`: Manage products
    * `/admin-add-product`: Form to add a new product
    * `/admin-edit-product?id={ID}`: Form to edit an existing product
    * `/admin-orders`: Manage orders
    * `/admin-order-detail?id={ID}`: View order details and update status
    * `/admin-manage-affiliates`: View users, activate/deactivate affiliate status.
    * `/admin-withdrawal-requests`: View and process affiliate withdrawal requests.
* **Admin Action Routes (POST requests, require admin):**
    * `/admin-product-add-action`
    * `/admin-product-edit-action`
    * `/admin-product-delete-action`
    * `/admin-order-update-status-action`
    * `/request-withdrawal-action` (User)
    * `/admin-process-withdrawal-action` (Admin)
    * `/admin-manage-affiliates-action` (Admin)

## E-commerce Functionality Details

* **Product Management:** Admins can create, read, update, and delete products, including names, descriptions, prices, stock quantities, and images.
* **Shopping Cart:** Logged-in users can add products to a persistent cart, update quantities, or remove items.
* **Checkout:** A simplified process to collect shipping information and select a (simulated) payment method.
* **Order Processing:** On checkout, an order is created, cart items are moved to order items, and product stock is updated.
* **Order History:** Admins can view all orders and update their statuses. Users should be able to view their own order history (typically via their dashboard).

## Production System Considerations

For a production-ready e-commerce system, the following aspects built upon in this project need further hardening and development:

* **Robust Input Validation:** All user and admin inputs must be rigorously validated on the server-side.
* **Detailed Error Handling & Logging:** Implement more specific error catching and user-friendly error messages.
* **Secure Image Handling:**
    * **MIME Type Validation:** Server-side validation of uploaded file MIME types (e.g., using `finfo_file`).
    * **Image Resizing/Optimization & Sanitization:** Process images to prevent XSS or other attacks via malicious image files.
    * **Secure Storage:** Store uploads outside the webroot if possible, or with strict access controls.
* **Fine-Grained Permissions/Roles:** Implement a Role-Based Access Control (RBAC) system for more granular admin permissions.
* **Payment Gateway Integration:** Integrate a secure payment gateway (e.g., Stripe, PayPal).
* **Inventory Management:** Handle race conditions for stock, manage backorders.
* **Transaction Management (Database):** Ensure critical multi-step database operations are atomic.
* **Email Notifications:** Expand email notifications (order confirmation, shipping, etc.).
* **Security Headers:** Implement CSP, HSTS, etc.
* **HTTPS:** Enforce HTTPS sitewide.
* **Scalability & Testing:** Consider database indexing, caching, and implement unit/integration tests.
* **Regular Audits & Updates:** Keep all components updated.

## Affiliate System Workflow

1.  **Becoming an Affiliate:** An admin marks a user as an affiliate (`is_affiliate = 1`) and a unique `user_affiliate_code` is generated (or can be manually set) via the "Manage Affiliates" admin page.
2.  **Promotion:** The affiliate shares their `user_affiliate_code` through links (e.g., `SITE_URL/?ref=CODE123` or `SITE_URL/product?id=1&ref=CODE123`).
3.  **Referral Tracking:** When a visitor uses such a link, the `ref` code is captured by `index.php` and the `referrer_user_id` is stored in the session.
4.  **Order Placement:** If the visitor makes a purchase during that session, the `referrer_user_id` and `affiliate_code_used` are saved with the order in the `orders` table.
5.  **Earning Calculation:** For each eligible product (with `affiliate_bonus_percentage > 0`) in that order, an earning record is created in `affiliate_earnings` for the `referrer_user_id` with status 'pending'.
6.  **Earning Clearance:** When an admin updates an order status to 'delivered' (or a similar final positive status), the system automatically changes the status of related 'pending' affiliate earnings to 'cleared' and adds the `earned_amount` to the affiliate's `affiliate_balance` in the `users` table. If an order is 'cancelled', associated earnings are marked 'cancelled', and if they were already 'cleared', the balance is adjusted.
7.  **Affiliate Dashboard:** Affiliates can view their referral code, total balance, and a history of their earnings (pending, cleared, paid, cancelled).
8.  **Withdrawal Request:** Affiliates can request to withdraw funds from their `affiliate_balance` via their dashboard. This creates a 'pending' request in `withdrawal_requests`.
9.  **Admin Withdrawal Processing:** Admins view pending withdrawal requests, can approve or reject them.
    * **Approval:** Updates request status to 'approved', updates relevant `affiliate_earnings` to 'paid', and deducts the amount from the user's `affiliate_balance`. (Manual payment by admin is assumed outside the system).
    * **Rejection:** Updates request status to 'rejected' (admin can add notes).

## Contributing

Contributions are welcome! Please fork the repository, create a feature branch, and submit a pull request.

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.
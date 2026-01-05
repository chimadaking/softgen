# Softgenix - Production-Ready Multi-API Reseller Platform

Softgenix is a robust, production-ready Multi-API Reseller Platform built with vanilla PHP following the MVC (Model-View-Controller) architecture. It allows users to purchase various digital services through integrated APIs and manage their reseller business.

## Features

- **Authentication System:** Secure login/register with role-based access control.
- **Product Management:** Complete CRUD for products and categories.
- **Order System:** Easy checkout, order tracking, and history.
- **Wallet System:** Balance management with Binance Pay integration (mock).
- **Affiliate Program:** Referral system with commission tracking.
- **Loyalty Program:** Points-based system to reward frequent customers.
- **Admin Dashboard:** Full control over users, orders, products, and API settings.
- **Multi-API Integration:** Support for multiple API providers with logging and monitoring.
- **Responsive Design:** Built with Bootstrap 5 for a mobile-friendly experience.

## Tech Stack

- **Backend:** PHP 8+
- **Database:** MySQL (MariaDB)
- **Frontend:** Bootstrap 5, Bootstrap Icons
- **Architecture:** MVC
- **Security:** CSRF Protection, Password Hashing (Bcrypt), SQL Injection Prevention (PDO)

## Requirements

- PHP 8.0 or higher
- MySQL / MariaDB
- Apache with `mod_rewrite` (optional but recommended for clean URLs)
- Composer (for autoloading)

## Setup Instructions

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-repo/softgenix.git
   ```

2. **Database Setup:**
   - Create a new database named `softgenix`.
   - Import the schema from `database/schema.sql`.

3. **Configuration:**
   - Open `config/database.php` and update your database credentials.
   - Update `config/app.php` with your site URL.

4. **Install Dependencies:**
   ```bash
   composer install
   ```
   *Note: If composer is not available, the project includes a basic fallback autoloader in `bootstrap.php`.*

5. **Run the Application:**
   - Place the project in your web server's root (e.g., `htdocs` for XAMPP).
   - Navigate to `http://localhost/softgenix/public/index.php`.

## API Documentation

The platform provides a RESTful API for external integration.
- **Endpoint:** `/public/api/v1/`
- **Authentication:** API Key & Secret (Manage in User Dashboard)

## License

This project is licensed under the MIT License.

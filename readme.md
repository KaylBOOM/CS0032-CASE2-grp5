# 📚 Online Bookstore System (OBS)

A full-stack PHP online bookstore where customers can browse, search, and purchase books while admins manage the catalog and orders.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Prerequisites](#prerequisites)
- [Installation & Setup](#installation--setup)
- [Database Schema](#database-schema)
- [How It Works](#how-it-works)
  - [User Registration & Login](#user-registration--login)
  - [Browsing & Searching Books](#browsing--searching-books)
  - [Shopping Cart](#shopping-cart)
  - [Checkout & Orders](#checkout--orders)
  - [Order History](#order-history)
  - [Admin Panel](#admin-panel)
- [Default Accounts](#default-accounts)
- [Configuration](#configuration)
- [Security](#security)

---

## Features

| Area | Capabilities |
|------|-------------|
| **Authentication** | Register with email/password, log in, log out; passwords hashed with bcrypt |
| **Book Catalog** | Browse by category, search by title / author / ISBN, view full book details |
| **Shopping Cart** | Add / remove / update quantities; automatic tax calculation |
| **Orders** | Checkout with shipping & payment info, stock validation, order confirmation page |
| **Order Tracking** | Personal dashboard listing past orders with status badges |
| **Admin — Books** | Add, edit, and delete books from the catalog |
| **Admin — Orders** | View every order and update its status (pending → processing → shipped → delivered / cancelled) |
| **Responsive UI** | Mobile-friendly layout with CSS Grid and media queries at 768 px and 480 px |

---

## Tech Stack

- **Backend:** PHP 8+ (plain PHP, no framework)
- **Database:** MySQL / MariaDB with PDO (prepared statements)
- **Frontend:** HTML5, CSS3 (responsive), vanilla PHP templates
- **Authentication:** bcrypt via `password_hash()` / `password_verify()`

---

## Project Structure

```
├── admin/                  # Admin panel pages
│   ├── index.php           #   Dashboard (stats + recent orders)
│   ├── books.php           #   List / delete books
│   ├── book_form.php       #   Add / edit a book
│   └── orders.php          #   View & update order statuses
├── assets/
│   ├── covers/             #   Book cover images (place files here)
│   └── css/
│       └── style.css       #   All application styles
├── config/
│   └── database.php        #   DB credentials, PDO connection, TAX_RATE
├── includes/
│   ├── auth.php            #   register, login, session helpers
│   ├── header.php          #   HTML head + navigation bar
│   └── footer.php          #   Page footer
├── sql/
│   └── schema.sql          #   Database schema + seed data
├── book_detail.php         # Single-book detail page
├── books.php               # Browse & search page
├── cart.php                # Shopping cart
├── checkout.php            # Shipping / payment form + order creation
├── index.php               # Home page (hero, categories, featured books)
├── login.php               # Login form
├── logout.php              # Destroy session and redirect
├── order_confirmation.php  # Post-purchase confirmation
├── orders.php              # User's order history
├── register.php            # Registration form
└── readme.md               # This file
```

---

## Prerequisites

- **PHP 8.0+** with the `pdo_mysql` extension enabled
- **MySQL 5.7+** or **MariaDB 10.3+**
- A web server such as **Apache** (with `mod_rewrite`) or **Nginx**, _or_ use the built-in PHP server for local development

---

## Installation & Setup

### 1. Clone the repository

```bash
git clone https://github.com/KaylBOOM/CS0032-CASE-2-TEST.git
cd CS0032-CASE-2-TEST
```

### 2. Create the database

Log in to MySQL and run the schema file:

```bash
mysql -u root -p < sql/schema.sql
```

This creates the `online_bookstore` database, all tables, seed categories, sample books, and a default admin user.

### 3. Configure database credentials

Open `config/database.php` and update the constants if your MySQL credentials differ from the defaults:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'online_bookstore');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Start the application

**Option A — PHP built-in server (development):**

```bash
php -S localhost:8000
```

Then open `http://localhost:8000` in your browser.

**Option B — Apache / Nginx:**

Point the document root to the project directory and access it through your configured virtual host.

### 5. (Optional) Add book cover images

Place image files in `assets/covers/`. The filename for each book is stored in the `cover_image` column of the `books` table (defaults to `default_cover.png`).

---

## Database Schema

The schema consists of six tables:

```
categories ──┐
              ├──< books ──< order_items >── orders
users ────────┤                                │
              └──< cart                        │
              └────────────────────────────────┘
```

| Table | Purpose |
|-------|---------|
| `categories` | Book categories (Fiction, Science, Technology, etc.) |
| `books` | Catalog entries with title, author, ISBN, price, description, stock, and category FK |
| `users` | Customer and admin accounts (role is `customer` or `admin`) |
| `cart` | Per-user cart items with a unique constraint on (user_id, book_id) |
| `orders` | Order headers with totals, shipping address, payment method, and status |
| `order_items` | Line items linking an order to books with quantity and price snapshot |

All foreign keys use `ON DELETE CASCADE`. The full SQL is in [`sql/schema.sql`](sql/schema.sql).

---

## How It Works

### User Registration & Login

1. A visitor opens **Register** and submits their name, email, and password.
2. The server validates the input (valid email, ≥ 6-char password, confirmation match), then hashes the password with `password_hash($password, PASSWORD_BCRYPT)` and inserts a row into the `users` table.
3. On **Login**, the server fetches the user by email and verifies the password with `password_verify()`. On success, the user's `id`, `name`, and `role` are stored in `$_SESSION`.
4. Protected pages call `requireLogin()` (redirects to login if unauthenticated) and admin pages additionally call `requireAdmin()`.

### Browsing & Searching Books

- **Home page (`index.php`):** Displays all categories as filter chips and the 8 most recently added books.
- **Books page (`books.php`):** Shows the full catalog with two filters that can be combined:
  - **Category:** Click a category chip to filter by `category_id`.
  - **Search:** Enter a query to match against `title`, `author`, or `isbn` using SQL `LIKE` with prepared-statement parameters.
- **Book detail (`book_detail.php`):** Shows complete information — cover image, title, author, ISBN, category, price, stock count, and description — with an _Add to Cart_ button.

### Shopping Cart

- **Adding items:** Clicking _Add to Cart_ on any book page sends `?action=add&book_id=N` to `cart.php`. The query uses `INSERT … ON DUPLICATE KEY UPDATE quantity = quantity + 1` so adding the same book increments its quantity.
- **Updating quantities:** The cart page shows a number input per item. Submitting the form updates all quantities at once.
- **Removing items:** Clicking _Remove_ deletes the cart row.
- **Totals:** The cart calculates:
  - **Subtotal** = sum of (price × quantity) for each item
  - **Tax** = subtotal × 8 % (configurable via `TAX_RATE` in `config/database.php`)
  - **Total** = subtotal + tax

### Checkout & Orders

1. The user clicks _Proceed to Checkout_ from the cart.
2. The checkout page shows an order summary on the right and a form on the left for:
   - **Shipping details:** name, address, city, ZIP code, country.
   - **Payment method:** Credit Card, PayPal, or Cash on Delivery (selection only — no live payment processing).
3. On submit the server:
   - Starts a **database transaction**.
   - Validates that each book has sufficient stock (`WHERE stock >= ?`).
   - Creates an `orders` row and one `order_items` row per cart item.
   - Decrements `books.stock` for each item.
   - Deletes all `cart` rows for the user.
   - Commits the transaction (or rolls back on any failure).
4. The user is redirected to the **Order Confirmation** page showing the full order summary and a success message.

### Order History

- **My Orders (`orders.php`):** Lists every order placed by the logged-in user with the order number, date, total, and a color-coded status badge.
- Clicking _View_ opens the order confirmation page for that order.

### Admin Panel

Access the admin panel at `/admin/` (requires an account with `role = 'admin'`).

| Page | What it does |
|------|-------------|
| **Dashboard** (`admin/index.php`) | Shows total books, orders, and users; lists the 5 most recent orders |
| **Manage Books** (`admin/books.php`) | Table of all books with _Edit_ and _Delete_ buttons |
| **Add / Edit Book** (`admin/book_form.php`) | Form for creating or updating a book (title, author, ISBN, price, category, stock, cover image filename, description) |
| **Manage Orders** (`admin/orders.php`) | Table of all orders with an inline dropdown to change the status and a _Save_ button |

---

## Default Accounts

The seed data in `sql/schema.sql` creates one admin user:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@bookstore.com` | `admin123` |

> **Tip:** Register a new account through the UI to test the customer experience.

---

## Configuration

All configuration lives in `config/database.php`:

| Constant | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | `localhost` | MySQL host |
| `DB_NAME` | `online_bookstore` | Database name |
| `DB_USER` | `root` | MySQL user |
| `DB_PASS` | _(empty)_ | MySQL password |
| `TAX_RATE` | `0.08` | Tax multiplier applied to cart and checkout totals |

---

## Security

| Measure | Implementation |
|---------|---------------|
| **Password hashing** | `password_hash()` with `PASSWORD_BCRYPT`; verified with `password_verify()` |
| **SQL injection prevention** | Every query uses PDO prepared statements with bound parameters |
| **XSS prevention** | All user-supplied output is escaped with `htmlspecialchars()` |
| **Stock integrity** | Checkout runs inside a DB transaction; stock is decremented with `WHERE stock >= ?` to prevent overselling |
| **Access control** | `requireLogin()` and `requireAdmin()` guards redirect unauthorized users |
| **Session management** | Sessions are properly started and fully destroyed on logout |

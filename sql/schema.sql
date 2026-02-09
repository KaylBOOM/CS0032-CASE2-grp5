-- Online Bookstore Database Schema

CREATE DATABASE IF NOT EXISTS online_bookstore;
USE online_bookstore;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Books table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255) DEFAULT 'default_cover.png',
    category_id INT NOT NULL,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, book_id)
) ENGINE=InnoDB;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL,
    shipping_name VARCHAR(100) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_zip VARCHAR(20) NOT NULL,
    shipping_country VARCHAR(100) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed categories
INSERT INTO categories (name) VALUES
('Fiction'),
('Science'),
('Technology'),
('History'),
('Biography'),
('Self-Help'),
('Children'),
('Mystery');

-- Seed sample books
INSERT INTO books (title, author, isbn, price, description, category_id, stock) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 12.99, 'A classic novel of the Jazz Age.', 1, 50),
('To Kill a Mockingbird', 'Harper Lee', '978-0061120084', 14.99, 'A novel about racial injustice in the Deep South.', 1, 40),
('A Brief History of Time', 'Stephen Hawking', '978-0553380163', 18.99, 'Explores the nature of the universe.', 2, 30),
('Clean Code', 'Robert C. Martin', '978-0132350884', 39.99, 'A handbook of agile software craftsmanship.', 3, 25),
('Sapiens', 'Yuval Noah Harari', '978-0062316097', 22.99, 'A brief history of humankind.', 4, 35),
('Steve Jobs', 'Walter Isaacson', '978-1451648539', 16.99, 'The exclusive biography of Steve Jobs.', 5, 20),
('Atomic Habits', 'James Clear', '978-0735211292', 16.99, 'Tiny changes, remarkable results.', 6, 45),
('The Cat in the Hat', 'Dr. Seuss', '978-0394800011', 9.99, 'A beloved children''s classic.', 7, 60),
('Gone Girl', 'Gillian Flynn', '978-0307588371', 14.99, 'A thriller about a missing wife.', 8, 30),
('The Pragmatic Programmer', 'David Thomas', '978-0135957059', 49.99, 'Your journey to mastery.', 3, 15);

-- Create default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@bookstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ========================================
-- COMPLETE Storyscape Database Schema
-- Includes all 30 books from the start
-- ========================================

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

-- ========================================
-- Seed categories
-- ========================================
INSERT INTO categories (name) VALUES
('Fiction'),
('Science'),
('Technology'),
('History'),
('Biography'),
('Self-Help'),
('Children'),
('Mystery');

-- ========================================
-- Seed ALL 30 Books (10 Original + 20 New)
-- ========================================

-- Original 10 Books (with detailed descriptions)
INSERT INTO books (title, author, isbn, price, description, cover_image, category_id, stock) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 12.99, 'Set in the summer of 1922, this masterpiece follows the mysterious millionaire Jay Gatsby and his obsession with the beautiful Daisy Buchanan. F. Scott Fitzgerald crafts a tale of decadence, idealism, and the American Dream in the Jazz Age. Through the eyes of narrator Nick Carraway, witness a world of lavish parties, forbidden love, and the dark underbelly of wealth and privilege in 1920s Long Island.', 'gatsby.jpg', 1, 50),

('To Kill a Mockingbird', 'Harper Lee', '978-0061120084', 14.99, 'Harper Lee''s Pulitzer Prize-winning novel is a gripping, heart-wrenching tale of racial injustice and childhood innocence in the Depression-era South. Through the eyes of six-year-old Scout Finch, we witness her father Atticus defend a Black man falsely accused of assault. A timeless classic about courage, compassion, and standing up for what''s right, even when the odds are stacked against you.', 'mockingbird.jpg', 1, 40),

('A Brief History of Time', 'Stephen Hawking', '978-0553380163', 18.99, 'From the Big Bang to black holes, Stephen Hawking takes readers on a journey through the cosmos. This landmark volume explores questions that have puzzled humanity for millennia: How did the universe begin? What is the nature of time? Is time travel possible? Written for non-scientists, Hawking makes complex physics accessible and engaging, showing how our understanding of the universe has evolved.', 'brief_history.jpg', 2, 30),

('Clean Code', 'Robert C. Martin', '978-0132350884', 39.99, 'Even bad code can function. But if code isn''t clean, it can bring a development organization to its knees. Every year, countless hours and significant resources are lost because of poorly written code. Robert C. Martin presents a revolutionary paradigm with Clean Code: A Handbook of Agile Software Craftsmanship. Learn the best practices for writing code that is easy to read, maintain, and extend. Essential reading for any software developer.', 'clean_code.jpg', 3, 25),

('Sapiens', 'Yuval Noah Harari', '978-0062316097', 22.99, '100,000 years ago, at least six human species inhabited the earth. Today there is just one: Homo sapiens. How did our species succeed in the battle for dominance? Why did our foraging ancestors come together to create cities and kingdoms? How did we come to believe in gods, nations, and human rights? Yuval Noah Harari challenges everything we know about being human in this international bestseller examining history, anthropology, and economics.', 'sapiens.jpg', 4, 35),

('Steve Jobs', 'Walter Isaacson', '978-1451648539', 16.99, 'Based on more than forty interviews with Steve Jobs over two years, as well as interviews with more than a hundred family members, friends, adversaries, competitors, and colleagues, Walter Isaacson has written a riveting story of the roller-coaster life and searingly intense personality of a creative entrepreneur whose passion for perfection revolutionized six industries: personal computers, animated movies, music, phones, tablet computing, and digital publishing.', 'jobs.jpg', 5, 20),

('Atomic Habits', 'James Clear', '978-0735211292', 16.99, 'No matter your goals, Atomic Habits offers a proven framework for improving every day. James Clear reveals practical strategies that will teach you exactly how to form good habits, break bad ones, and master the tiny behaviors that lead to remarkable results. If you''re having trouble changing your habits, the problem isn''t you—it''s your system. Learn how to make tiny changes that deliver big results and transform your life one atomic habit at a time.', 'atomic.jpg', 6, 45),

('The Cat in the Hat', 'Dr. Seuss', '978-0394800011', 9.99, 'The Cat in the Hat is a classic beginner book by Dr. Seuss that has delighted generations of young readers. When Sally and her brother are home alone on a rainy day, a mischievous cat in a hat arrives with his companions Thing One and Thing Two, turning their dull afternoon into a whirlwind of chaos and fun. With its playful rhymes and imaginative illustrations, this beloved story teaches children about responsibility, fun, and cleaning up your messes.', 'cat.jpg', 7, 60),

('Gone Girl', 'Gillian Flynn', '978-0307588371', 14.99, 'On a warm summer morning in North Carthage, Missouri, Amy Dunne goes missing. Her husband Nick becomes the prime suspect in her disappearance. But what really happened? Told in alternating perspectives between Nick and Amy''s diary entries, this psychological thriller keeps you guessing until the shocking twist ending. Gillian Flynn crafts a dark tale about marriage, media manipulation, and how well you really know the person you love. A modern thriller that will leave you breathless.', 'gone-girl.jpg', 8, 30),

('The Pragmatic Programmer', 'David Thomas', '978-0135957059', 49.99, 'Straight from the programming trenches, The Pragmatic Programmer cuts through the increasing specialization and technicalities of modern software development to examine the core process: transforming a requirement into working, maintainable code that delights users. Covering topics ranging from personal responsibility and career development to architectural techniques, this influential book has helped a generation of programmers examine the essence of software development, independent of any particular language, framework, or methodology.', 'pragmatic.jpg', 3, 15);

-- 20 New Books

-- Fiction (6 books)
INSERT INTO books (title, author, isbn, price, description, cover_image, category_id, stock) VALUES
('Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', '978-0590353427', 14.99, 'A young wizard begins his magical education at Hogwarts.', 'harry.jpg', 1, 70),
('The Lord of the Rings', 'J.R.R. Tolkien', '978-0544003415', 24.99, 'Epic fantasy trilogy about the quest to destroy the One Ring.', 'lord.jpg', 1, 48),
('Animal Farm', 'George Orwell', '978-0451526342', 10.99, 'A satirical allegory about totalitarianism.', 'animal.jpg', 1, 55),
('Fahrenheit 451', 'Ray Bradbury', '978-1451673319', 13.99, 'A dystopian novel about a future where books are burned.', '451.jpg', 1, 42),
('The Alchemist', 'Paulo Coelho', '978-0062315007', 15.99, 'A philosophical novel about following your dreams.', 'download (3).jpg', 1, 60),
('The Picture of Dorian Gray', 'Oscar Wilde', '978-0141439570', 11.99, 'A philosophical novel about beauty, youth, and morality.', 'dorian.jpg', 1, 35);

-- Science (3 books)
INSERT INTO books (title, author, isbn, price, description, cover_image, category_id, stock) VALUES
('The Origin of Species', 'Charles Darwin', '978-0451529060', 14.99, 'The foundational work of evolutionary biology.', 'origin.jpg', 2, 22),
('Sapiens: A Graphic History', 'Yuval Noah Harari', '978-0063051331', 26.99, 'The illustrated guide to human history.', 'sapiens_graphic.jpg', 2, 32),
('The Gene', 'Siddhartha Mukherjee', '978-1476733524', 18.99, 'An intimate history of genetics and the human genome.', 'gene.jpg', 2, 28);

-- Technology (3 books)
INSERT INTO books (title, author, isbn, price, description, cover_image, category_id, stock) VALUES
('Code Complete', 'Steve McConnell', '978-0735619678', 44.99, 'A practical handbook of software construction.', 'code.jpg', 3, 20),
('The Mythical Man-Month', 'Frederick P. Brooks Jr.', '978-0201835953', 34.99, 'Essays on software engineering and project management.', 'mythical.jpg', 3, 16),
('Artificial Intelligence', 'Stuart Russell', '978-0134610993', 79.99, 'A modern approach to artificial intelligence.', 'ai.jpg', 3, 12);

-- History (2 books)
INSERT INTO books (title, author, isbn, price, description, cover_image, category_id, stock) VALUES
('The Silk Roads', 'Peter Frankopan', '978-1101912379', 19.99, 'A new history of the world through the lens of trade routes.', 'silk.jpg', 4, 30),
('SPQR', 'Mary Beard', '978-1631492228', 18.99, 'A history of ancient Rome from its origins to 212 CE.', 'spor.jpg', 4, 26);

-- Biography (2 books)
INSERT INTO books (title, author, isbn, price, description, cover_image, category_id, stock) VALUES
('Long Walk to Freedom', 'Nelson Mandela', '978-0316548182', 17.99, 'The autobiography of South Africa''s first Black president.', 'longwalk.jpg', 5, 34),
('Becoming', 'Michelle Obama', '978-1524763138', 19.99, 'The memoir of former First Lady Michelle Obama.', 'becoming.jpg', 5, 52);

-- Self-Help (2 books)
INSERT INTO books (title, author, isbn, price, description, cover_image, category_id, stock) VALUES
('How to Win Friends', 'Dale Carnegie', '978-0671027032', 14.99, 'The classic guide to interpersonal relationships.', 'how.jpg', 6, 46),
('Mindset', 'Carol S. Dweck', '978-0345472328', 16.99, 'The new psychology of success and growth mindset.', 'mindset.jpg', 6, 38);

-- Children (1 book)
INSERT INTO books (title, author, isbn, price, description, cover_image, category_id, stock) VALUES
('Green Eggs and Ham', 'Dr. Seuss', '978-0394800165', 8.99, 'A classic tale of trying new things.', 'green.jpg', 7, 68);

-- Mystery (1 book)
INSERT INTO books (title, author, isbn, price, description, cover_image, category_id, stock) VALUES
('The Silent Patient', 'Alex Michaelides', '978-1250301697', 15.99, 'A woman shoots her husband and never speaks again.', 'silent.jpg', 8, 44);

-- ========================================
-- Create default admin user (password: admin123)
-- ========================================
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@bookstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');


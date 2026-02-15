<?php
/**
 * Home Page — Storyscape
 */
session_start();
$pageTitle = 'Home — Storyscape';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

// Fetch categories
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

// Fetch featured books (latest 8)
$stmt = $pdo->query('SELECT b.*, c.name AS category_name FROM books b JOIN categories c ON b.category_id = c.id ORDER BY b.created_at DESC LIMIT 8');
$featuredBooks = $stmt->fetchAll();
?>

<div class="hero">
    <h1>Welcome to Storyscape!</h1>
    <p>Discover thousands of books across every genre. Browse, search, and order with ease.</p>
    <a href="books.php" class="btn btn-primary mt-1">Browse All Books</a>
</div>

<h2>Categories</h2>
<div class="category-list">
    <?php foreach ($categories as $cat): ?>
        <a href="books.php?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
    <?php endforeach; ?>
</div>

<h2>Featured Books</h2>
<div class="book-grid">
    <?php foreach ($featuredBooks as $book): ?>
        <div class="book-card">
            <img src="assets/covers/<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
            <div class="book-card-body">
                <h3><a href="book_detail.php?id=<?= $book['id'] ?>"><?= htmlspecialchars($book['title']) ?></a></h3>
                <span class="author"><?= htmlspecialchars($book['author']) ?></span>
                <span class="price">$<?= number_format($book['price'], 2) ?></span>
                <a href="cart.php?action=add&book_id=<?= $book['id'] ?>" class="btn btn-primary btn-sm">Add to Cart</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

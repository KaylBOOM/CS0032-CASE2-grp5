<?php
/**
 * Books Browsing & Search Page
 */
$pageTitle = 'Books — Online Bookstore';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

// Fetch categories for filter
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

// Build query
$where  = [];
$params = [];

// Category filter
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;
if ($categoryId > 0) {
    $where[]  = 'b.category_id = ?';
    $params[] = $categoryId;
}

// Search query
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $where[]  = '(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql = 'SELECT b.*, c.name AS category_name FROM books b JOIN categories c ON b.category_id = c.id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY b.title';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();
?>

<h2>Browse Books</h2>

<!-- Search Bar -->
<form method="GET" action="books.php" class="search-bar">
    <input type="text" name="q" class="form-control" placeholder="Search by title, author, or ISBN…" value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-primary">Search</button>
</form>

<!-- Category Filter -->
<div class="category-list">
    <a href="books.php" class="<?= $categoryId === 0 ? 'active' : '' ?>">All</a>
    <?php foreach ($categories as $cat): ?>
        <a href="books.php?category=<?= $cat['id'] ?>" class="<?= $categoryId === (int) $cat['id'] ? 'active' : '' ?>">
            <?= htmlspecialchars($cat['name']) ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($books)): ?>
    <div class="alert alert-info">No books found. Try a different search or category.</div>
<?php else: ?>
    <div class="book-grid">
        <?php foreach ($books as $book): ?>
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
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * Book Detail Page
 */
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

$bookId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT b.*, c.name AS category_name FROM books b JOIN categories c ON b.category_id = c.id WHERE b.id = ?');
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: books.php');
    exit;
}

$pageTitle = htmlspecialchars($book['title']) . ' — Storyscape';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card" style="display:flex; flex-wrap:wrap; gap:2rem;">
    <div style="flex:0 0 350px; text-align:center;">
        <img src="assets/covers/<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" style="border-radius:8px; width:100%; max-width:350px; height:auto; object-fit:contain; background:#f8fafc; padding:1rem;">
    </div>
    <div style="flex:1; min-width:250px;">
        <h1><?= htmlspecialchars($book['title']) ?></h1>
        <p class="author" style="font-size:1.1rem;">by <?= htmlspecialchars($book['author']) ?></p>
        <p><strong>ISBN:</strong> <?= htmlspecialchars($book['isbn']) ?></p>
        <p><strong>Category:</strong> <a href="books.php?category=<?= $book['category_id'] ?>"><?= htmlspecialchars($book['category_name']) ?></a></p>
        <p class="price" style="font-size:1.5rem;">$<?= number_format($book['price'], 2) ?></p>
        <p><strong>In Stock:</strong> <?= $book['stock'] > 0 ? $book['stock'] . ' copies' : '<span style="color:red">Out of stock</span>' ?></p>
        <p class="mt-1"><?= nl2br(htmlspecialchars($book['description'])) ?></p>

        <?php if ($book['stock'] > 0): ?>
            <a href="cart.php?action=add&book_id=<?= $book['id'] ?>" class="btn btn-primary mt-1">Add to Cart</a>
        <?php endif; ?>

        <a href="books.php" class="btn btn-secondary mt-1">← Back to Books</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

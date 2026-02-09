<?php
/**
 * Admin — Manage Books (List / Delete)
 */
session_start();
$baseUrl = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireAdmin();

$pdo = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM books WHERE id = ?');
    $stmt->execute([$deleteId]);
    header('Location: books.php?deleted=1');
    exit;
}

$books = $pdo->query('SELECT b.*, c.name AS category_name FROM books b JOIN categories c ON b.category_id = c.id ORDER BY b.title')->fetchAll();

$pageTitle = 'Manage Books — Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Manage Books</h2>
<a href="book_form.php" class="btn btn-success mb-1">+ Add New Book</a>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Book deleted successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Book saved successfully.</div>
<?php endif; ?>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= $book['id'] ?></td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td><?= htmlspecialchars($book['isbn']) ?></td>
                    <td><?= htmlspecialchars($book['category_name']) ?></td>
                    <td>$<?= number_format($book['price'], 2) ?></td>
                    <td><?= $book['stock'] ?></td>
                    <td>
                        <a href="book_form.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="books.php?delete=<?= $book['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this book?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

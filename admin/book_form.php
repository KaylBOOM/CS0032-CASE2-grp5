<?php
/**
 * Admin — Add / Edit Book Form
 */
session_start();
$baseUrl = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireAdmin();

$pdo = getDBConnection();

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$bookId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$book   = null;

if ($bookId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $author      = trim($_POST['author'] ?? '');
    $isbn        = trim($_POST['isbn'] ?? '');
    $price       = (float) ($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $categoryId  = (int) ($_POST['category_id'] ?? 0);
    $stock       = (int) ($_POST['stock'] ?? 0);
    $coverImage  = trim($_POST['cover_image'] ?? 'default_cover.png');

    if ($title === '' || $author === '' || $isbn === '' || $price <= 0 || $categoryId === 0) {
        $error = 'Please fill in all required fields.';
    } else {
        if ($bookId > 0) {
            // Update
            $stmt = $pdo->prepare('UPDATE books SET title=?, author=?, isbn=?, price=?, description=?, category_id=?, stock=?, cover_image=? WHERE id=?');
            $stmt->execute([$title, $author, $isbn, $price, $description, $categoryId, $stock, $coverImage, $bookId]);
        } else {
            // Insert
            $stmt = $pdo->prepare('INSERT INTO books (title, author, isbn, price, description, category_id, stock, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $author, $isbn, $price, $description, $categoryId, $stock, $coverImage]);
        }
        header('Location: books.php?saved=1');
        exit;
    }
}

$pageTitle = ($bookId > 0 ? 'Edit' : 'Add') . ' Book — Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<h2><?= $bookId > 0 ? 'Edit Book' : 'Add New Book' ?></h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width:600px;">
    <form method="POST">
        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($book['title'] ?? $_POST['title'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="author">Author *</label>
            <input type="text" id="author" name="author" class="form-control" value="<?= htmlspecialchars($book['author'] ?? $_POST['author'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="isbn">ISBN *</label>
            <input type="text" id="isbn" name="isbn" class="form-control" value="<?= htmlspecialchars($book['isbn'] ?? $_POST['isbn'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="price">Price ($) *</label>
            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0.01" value="<?= htmlspecialchars($book['price'] ?? $_POST['price'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" class="form-control" required>
                <option value="">— Select Category —</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (($book['category_id'] ?? $_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" class="form-control" min="0" value="<?= htmlspecialchars($book['stock'] ?? $_POST['stock'] ?? '0') ?>">
        </div>
        <div class="form-group">
            <label for="cover_image">Cover Image Filename</label>
            <input type="text" id="cover_image" name="cover_image" class="form-control" value="<?= htmlspecialchars($book['cover_image'] ?? $_POST['cover_image'] ?? 'default_cover.png') ?>">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control"><?= htmlspecialchars($book['description'] ?? $_POST['description'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Save Book</button>
        <a href="books.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

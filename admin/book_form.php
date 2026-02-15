<?php
/**
 * Admin - Add/Edit Book
 */
session_start();
$baseUrl = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireAdmin();

$pdo = getDBConnection();

$bookId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$book   = null;

// Load existing book if editing
if ($bookId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect Data
    $title       = trim($_POST['title']);
    $author      = trim($_POST['author']);
    $isbn        = trim($_POST['isbn']);
    $price       = (float) $_POST['price'];
    $categoryId  = (int) $_POST['category_id'];
    $stock       = (int) $_POST['stock'];
    $description = trim($_POST['description']);
    $coverImage  = trim($_POST['cover_image']) ?: 'default_cover.png';

    if ($bookId > 0) {
        $stmt = $pdo->prepare('UPDATE books SET title=?, author=?, isbn=?, price=?, category_id=?, stock=?, description=?, cover_image=? WHERE id=?');
        $stmt->execute([$title, $author, $isbn, $price, $categoryId, $stock, $description, $coverImage, $bookId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO books (title, author, isbn, price, category_id, stock, description, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $author, $isbn, $price, $categoryId, $stock, $description, $coverImage]);
    }
    header('Location: books.php?saved=1');
    exit;
}

$pageTitle = $bookId ? 'Edit Book' : 'Add Book';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 800px; margin: 3rem auto; padding: 0 2rem;">
    <div style="margin-bottom: 3rem; padding-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0;">
        <h2 style="margin: 0; color: #1e293b; font-size: 2rem;"><?= $bookId ? '✏️ Edit Book' : '✨ Add New Book' ?></h2>
    </div>

    <div style="background: white; padding: 3rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.75rem; color: #475569; font-weight: 600;">Book Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title'] ?? '') ?>" required style="width: 100%; padding: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem;">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.75rem; color: #475569; font-weight: 600;">Author</label>
                    <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($book['author'] ?? '') ?>" required style="width: 100%; padding: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.75rem; color: #475569; font-weight: 600;">Price ($)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($book['price'] ?? '') ?>" required style="width: 100%; padding: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem;">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.75rem; color: #475569; font-weight: 600;">Stock Qty</label>
                    <input type="number" name="stock" class="form-control" value="<?= htmlspecialchars($book['stock'] ?? 0) ?>" required style="width: 100%; padding: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem;">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.75rem; color: #475569; font-weight: 600;">Category</label>
                    <select name="category_id" class="form-control" required style="width: 100%; padding: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem;">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($book['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.75rem; color: #475569; font-weight: 600;">ISBN</label>
                <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>" required style="width: 100%; padding: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.75rem; color: #475569; font-weight: 600;">Cover Image Filename</label>
                <input type="text" name="cover_image" class="form-control" value="<?= htmlspecialchars($book['cover_image'] ?? 'default_cover.png') ?>" placeholder="e.g. gatsby.jpg" style="width: 100%; padding: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem;">
                <small style="color: #64748b; display: block; margin-top: 0.5rem;">Place the image file in <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 3px;">assets/covers/</code> folder first.</small>
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.75rem; color: #475569; font-weight: 600;">Description</label>
                <textarea name="description" rows="6" class="form-control" style="width: 100%; padding: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; font-family: sans-serif; font-size: 1rem; line-height: 1.6;"><?= htmlspecialchars($book['description'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <button type="submit" class="btn btn-success" style="flex: 1; padding: 14px; background: #16a34a; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer;">Save Book</button>
                <a href="books.php" class="btn btn-secondary" style="padding: 14px 28px; background: #f1f5f9; color: #475569; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 1rem; display: flex; align-items: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
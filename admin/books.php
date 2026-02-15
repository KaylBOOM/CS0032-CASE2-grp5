<?php
/**
 * Admin - Manage Books (Fixed Delete Logic)
 */
session_start();
$baseUrl = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireAdmin();

$pdo = getDBConnection();
$error = ''; // Variable to store errors

// Handle Delete
if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    
    try {
        $stmt = $pdo->prepare('DELETE FROM books WHERE id = ?');
        $stmt->execute([$deleteId]);
        header('Location: books.php?deleted=1');
        exit;
    } catch (PDOException $e) {
        // If it fails (e.g., book is in an order), catch the error!
        if ($e->getCode() == '23000') {
            $error = "Cannot delete this book because it is part of an existing order or cart. <br>To remove it, you must first delete the orders containing this book.";
        } else {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

// ... rest of your code (Fetch Books) ...

// Fetch Books
$books = $pdo->query('SELECT b.*, c.name AS category_name FROM books b JOIN categories c ON b.category_id = c.id ORDER BY b.title')->fetchAll();

$pageTitle = 'Manage Books';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1200px; margin: 3rem auto; padding: 0 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; padding-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0;">
        <h2 style="margin: 0; color: #1e293b; font-size: 2rem;">Manage Catalog</h2>
        <a href="book_form.php" class="btn btn-success" style="background: #10b981; padding: 14px 28px; border-radius: 8px; text-decoration: none; color: white; font-weight: 600; font-size: 1rem;">+ Add New Book</a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div style="background: #f0fdf4; color: #15803d; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #bbf7d0;">
            ✅ Book removed successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['saved'])): ?>
        <div style="background: #eff6ff; color: #1e40af; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #bfdbfe;">
            ✅ Book saved successfully.
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #fecaca;">
            ⚠️ <?= $error ?>
        </div>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <tr>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Cover</th>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Title / Author</th>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Category</th>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Price</th>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Stock</th>
                    <th style="padding: 1.5rem; text-align: right; color: #475569; font-weight: 600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 1.5rem;">
                            <img src="../assets/covers/<?= htmlspecialchars($book['cover_image']) ?>" alt="Cover" style="width: 50px; height: 75px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        </td>
                        <td style="padding: 1.5rem;">
                            <strong style="display: block; color: #1e293b; margin-bottom: 0.25rem;"><?= htmlspecialchars($book['title']) ?></strong>
                            <span style="color: #64748b; font-size: 0.9rem;"><?= htmlspecialchars($book['author']) ?></span>
                        </td>
                        <td style="padding: 1.5rem; color: #475569;"><span style="background: #f1f5f9; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem;"><?= htmlspecialchars($book['category_name']) ?></span></td>
                        <td style="padding: 1.5rem; font-weight: 600; color: #1e293b;">$<?= number_format($book['price'], 2) ?></td>
                        <td style="padding: 1.5rem;">
                            <span style="color: <?= $book['stock'] < 5 ? '#ef4444' : '#15803d' ?>; font-weight: 600;">
                                <?= $book['stock'] ?>
                            </span>
                        </td>
                        <td style="padding: 1.5rem; text-align: right;">
                            <a href="book_form.php?id=<?= $book['id'] ?>" style="color: #3b82f6; text-decoration: none; margin-right: 1.5rem; font-weight: 500; padding: 8px 16px; background: #eff6ff; border-radius: 6px; display: inline-block;">Edit</a>
                            <a href="books.php?delete=<?= $book['id'] ?>" onclick="return confirm('Are you sure you want to delete this book?')" style="color: #ef4444; text-decoration: none; font-weight: 500; padding: 8px 16px; background: #fee2e2; border-radius: 6px; display: inline-block;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
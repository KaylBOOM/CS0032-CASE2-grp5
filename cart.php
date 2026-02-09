<?php
/**
 * Shopping Cart Page
 */
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

requireLogin();

$pdo    = getDBConnection();
$userId = $_SESSION['user_id'];

// Handle actions
$action = $_GET['action'] ?? '';
$bookId = isset($_GET['book_id']) ? (int) $_GET['book_id'] : 0;

if ($action === 'add' && $bookId > 0) {
    $stmt = $pdo->prepare('INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1');
    $stmt->execute([$userId, $bookId]);
    header('Location: cart.php');
    exit;
}

if ($action === 'remove' && $bookId > 0) {
    $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ? AND book_id = ?');
    $stmt->execute([$userId, $bookId]);
    header('Location: cart.php');
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantities = $_POST['qty'] ?? [];
    foreach ($quantities as $bid => $qty) {
        $qty = max(1, (int) $qty);
        $stmt = $pdo->prepare('UPDATE cart SET quantity = ? WHERE user_id = ? AND book_id = ?');
        $stmt->execute([$qty, $userId, (int) $bid]);
    }
    header('Location: cart.php');
    exit;
}

// Fetch cart items
$stmt = $pdo->prepare('SELECT c.*, b.title, b.author, b.price, b.cover_image FROM cart c JOIN books b ON c.book_id = b.id WHERE c.user_id = ? ORDER BY c.created_at DESC');
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax   = round($subtotal * TAX_RATE, 2);
$total = $subtotal + $tax;

$pageTitle = 'Shopping Cart — Online Bookstore';
require_once __DIR__ . '/includes/header.php';
?>

<h2>Shopping Cart</h2>

<?php if (empty($cartItems)): ?>
    <div class="alert alert-info">Your cart is empty. <a href="books.php">Browse books</a> to add some!</div>
<?php else: ?>
    <form method="POST" action="cart.php?action=update">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <a href="book_detail.php?id=<?= $item['book_id'] ?>">
                                    <?= htmlspecialchars($item['title']) ?>
                                </a><br>
                                <small class="author"><?= htmlspecialchars($item['author']) ?></small>
                            </td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <input type="number" name="qty[<?= $item['book_id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="99" class="form-control" style="width:70px;">
                            </td>
                            <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            <td>
                                <a href="cart.php?action=remove&book_id=<?= $item['book_id'] ?>" class="btn btn-danger btn-sm">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card mt-1" style="max-width:350px; margin-left:auto;">
            <p><strong>Subtotal:</strong> $<?= number_format($subtotal, 2) ?></p>
            <p><strong>Tax (<?= TAX_RATE * 100 ?>%):</strong> $<?= number_format($tax, 2) ?></p>
            <p style="font-size:1.2rem;"><strong>Total:</strong> $<?= number_format($total, 2) ?></p>
            <button type="submit" class="btn btn-secondary btn-sm">Update Quantities</button>
            <a href="checkout.php" class="btn btn-success" style="width:100%; margin-top:0.5rem;">Proceed to Checkout</a>
        </div>
    </form>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

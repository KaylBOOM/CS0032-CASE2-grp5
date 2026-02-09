<?php
/**
 * Order Confirmation Page
 */
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

requireLogin();

$pdo     = getDBConnection();
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

$stmt = $pdo->prepare('SELECT oi.*, b.title, b.author FROM order_items oi JOIN books b ON oi.book_id = b.id WHERE oi.order_id = ?');
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$pageTitle = 'Order Confirmed — Online Bookstore';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card text-center" style="max-width:600px; margin:2rem auto;">
    <h2 style="color:#16a34a;">✓ Order Placed Successfully!</h2>
    <p>Your order <strong>#<?= $orderId ?></strong> has been received.</p>
    <p>A confirmation email will be sent to your registered address.</p>

    <div style="text-align:left; margin-top:1.5rem;">
        <h3>Order Details</h3>
        <table>
            <thead>
                <tr><th>Book</th><th>Qty</th><th>Price</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="mt-1"><strong>Tax:</strong> $<?= number_format($order['tax_amount'], 2) ?></p>
        <p><strong>Total:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
        <p><strong>Shipping To:</strong> <?= htmlspecialchars($order['shipping_name']) ?>, <?= htmlspecialchars($order['shipping_address']) ?>, <?= htmlspecialchars($order['shipping_city']) ?> <?= htmlspecialchars($order['shipping_zip']) ?>, <?= htmlspecialchars($order['shipping_country']) ?></p>
        <p><strong>Payment:</strong> <?= htmlspecialchars(ucwords(str_replace('_', ' ', $order['payment_method']))) ?></p>
        <p><strong>Status:</strong> <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></p>
    </div>

    <a href="orders.php" class="btn btn-primary mt-1">View My Orders</a>
    <a href="books.php" class="btn btn-secondary mt-1">Continue Shopping</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

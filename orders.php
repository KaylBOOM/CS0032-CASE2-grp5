<?php
/**
 * Order History Dashboard
 */
session_start();

// Prevent browser caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

requireLogin();

$pdo    = getDBConnection();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders — Storyscape';
require_once __DIR__ . '/includes/header.php';
?>

<h2>My Orders</h2>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">You have no orders yet. <a href="books.php">Start shopping!</a></div>
<?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                        <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                        <td><a href="order_confirmation.php?id=<?= $order['id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

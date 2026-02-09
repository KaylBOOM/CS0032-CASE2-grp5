<?php
/**
 * Admin Dashboard
 */
session_start();
$baseUrl = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireAdmin();

$pdo = getDBConnection();

$bookCount  = $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
$orderCount = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$userCount  = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

$recentOrders = $pdo->query('SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5')->fetchAll();

$pageTitle = 'Admin Dashboard — Online Bookstore';
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Admin Dashboard</h2>

<div style="display:flex; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
    <div class="card" style="flex:1; min-width:180px; text-align:center;">
        <h3><?= $bookCount ?></h3>
        <p>Books in Catalog</p>
        <a href="books.php" class="btn btn-primary btn-sm">Manage Books</a>
    </div>
    <div class="card" style="flex:1; min-width:180px; text-align:center;">
        <h3><?= $orderCount ?></h3>
        <p>Total Orders</p>
        <a href="orders.php" class="btn btn-primary btn-sm">Manage Orders</a>
    </div>
    <div class="card" style="flex:1; min-width:180px; text-align:center;">
        <h3><?= $userCount ?></h3>
        <p>Registered Users</p>
    </div>
</div>

<h3>Recent Orders</h3>
<?php if (empty($recentOrders)): ?>
    <div class="alert alert-info">No orders yet.</div>
<?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                        <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

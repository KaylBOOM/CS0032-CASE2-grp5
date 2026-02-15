<?php
/**
 * Admin - Order Detail
 */
session_start();
$baseUrl = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireAdmin();

$pdo = getDBConnection();
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

$stmt = $pdo->prepare('SELECT oi.*, b.title FROM order_items oi JOIN books b ON oi.book_id = b.id WHERE oi.order_id = ?');
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$pageTitle = 'Order #' . $orderId;
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 900px; margin: 2rem auto; padding: 0 2rem;">
    <a href="orders.php" style="color: #3b82f6; text-decoration: none; margin-bottom: 1rem; display: inline-block;">← Back to Orders</a>

    <div style="background: white; padding: 2rem; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 2rem;">
        <h2 style="margin: 0 0 1.5rem 0; color: #1e293b;">Order #<?= $order['id'] ?></h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div>
                <h3 style="color: #475569; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;">Shipping Address</h3>
                <p style="margin: 0; color: #1e293b;">
                    <?= htmlspecialchars($order['shipping_name']) ?><br>
                    <?= htmlspecialchars($order['shipping_address']) ?><br>
                    <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_zip']) ?><br>
                    <?= htmlspecialchars($order['shipping_country']) ?>
                </p>
            </div>
            <div>
                <h3 style="color: #475569; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;">Order Info</h3>
                <p style="margin: 0.5rem 0; color: #1e293b;"><strong>Status:</strong> <?= htmlspecialchars(ucfirst($order['status'])) ?></p>
                <p style="margin: 0.5rem 0; color: #1e293b;"><strong>Payment:</strong> <?= htmlspecialchars(ucfirst($order['payment_method'])) ?></p>
                <p style="margin: 0.5rem 0; color: #1e293b;"><strong>Date:</strong> <?= date('M d, Y H:i', strtotime($order['created_at'])) ?></p>
            </div>
        </div>

        <h3 style="color: #1e293b; margin-bottom: 1rem;">Order Items</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #475569;">Book</th>
                    <th style="padding: 0.75rem; text-align: center; font-weight: 600; color: #475569;">Qty</th>
                    <th style="padding: 0.75rem; text-align: right; font-weight: 600; color: #475569;">Price</th>
                    <th style="padding: 0.75rem; text-align: right; font-weight: 600; color: #475569;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 0.75rem; color: #1e293b;"><?= htmlspecialchars($item['title']) ?></td>
                        <td style="padding: 0.75rem; text-align: center; color: #1e293b;"><?= $item['quantity'] ?></td>
                        <td style="padding: 0.75rem; text-align: right; color: #1e293b;">$<?= number_format($item['price'], 2) ?></td>
                        <td style="padding: 0.75rem; text-align: right; color: #1e293b; font-weight: 600;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="text-align: right; padding-top: 1rem; border-top: 2px solid #e2e8f0;">
            <p style="margin: 0.5rem 0; color: #1e293b;"><strong>Subtotal:</strong> $<?= number_format($order['total_amount'] - $order['tax_amount'], 2) ?></p>
            <p style="margin: 0.5rem 0; color: #1e293b;"><strong>Tax:</strong> $<?= number_format($order['tax_amount'], 2) ?></p>
            <p style="margin: 1rem 0 0 0; font-size: 1.25rem; color: #16a34a; font-weight: bold;">Total: $<?= number_format($order['total_amount'], 2) ?></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
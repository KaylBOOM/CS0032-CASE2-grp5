<?php
/**
 * Admin - Manage Orders
 */
session_start();

// Prevent browser caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$baseUrl = '../';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireAdmin();

$pdo = getDBConnection();

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    header('Location: orders.php?updated=1');
    exit;
}

// Handle Order Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    try {
        // Delete order items first (foreign key constraint)
        $stmt = $pdo->prepare('DELETE FROM order_items WHERE order_id = ?');
        $stmt->execute([$orderId]);

        // Delete the order
        $stmt = $pdo->prepare('DELETE FROM orders WHERE id = ?');
        $stmt->execute([$orderId]);

        header('Location: orders.php?deleted=1');
        exit;
    } catch (Exception $e) {
        header('Location: orders.php?error=1');
        exit;
    }
}

$orders = $pdo->query('SELECT o.*, u.name AS customer_name, u.email AS customer_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC')->fetchAll();

$pageTitle = 'Manage Orders';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1200px; margin: 3rem auto; padding: 0 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; padding-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0;">
        <h2 style="margin: 0; color: #1e293b; font-size: 2rem;">📦 Manage Orders</h2>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div style="background: #eff6ff; color: #1d4ed8; padding: 1.25rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #bfdbfe;">
            ✅ Order status updated successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div style="background: #dcfce7; color: #166534; padding: 1.25rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #bbf7d0;">
            ✅ Order deleted successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1.25rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #fecaca;">
            ❌ Error deleting order. Please try again.
        </div>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <tr>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Order #</th>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Customer</th>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Date</th>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Total</th>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Current Status</th>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Update Status</th>
                    <th style="padding: 1.5rem; text-align: left; color: #475569; font-weight: 600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 1.5rem; font-weight: bold; color: #3b82f6; font-size: 1.05rem;">#<?= $order['id'] ?></td>
                        <td style="padding: 1.5rem;">
                            <div style="font-weight: 500; color: #1e293b; margin-bottom: 0.25rem;"><?= htmlspecialchars($order['customer_name']) ?></div>
                            <div style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($order['customer_email']) ?></div>
                        </td>
                        <td style="padding: 1.5rem; color: #64748b;"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td style="padding: 1.5rem; font-weight: 600; font-size: 1.05rem;">$<?= number_format($order['total_amount'], 2) ?></td>
                        <td style="padding: 1.5rem;">
                            <?php
                                $statusStyles = [
                                    'pending' => ['bg' => '#fef9c3', 'color' => '#854d0e'],
                                    'processing' => ['bg' => '#dbeafe', 'color' => '#1e40af'],
                                    'shipped' => ['bg' => '#e0f2fe', 'color' => '#075985'],
                                    'delivered' => ['bg' => '#dcfce7', 'color' => '#166534'],
                                    'cancelled' => ['bg' => '#fee2e2', 'color' => '#991b1b']
                                ];
                                $style = $statusStyles[$order['status']] ?? ['bg' => '#f1f5f9', 'color' => '#475569'];
                            ?>
                            <span style="padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.85rem;
                                background: <?= $style['bg'] ?>; color: <?= $style['color'] ?>;">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td style="padding: 1.5rem;">
                            <form method="POST" style="display: flex; gap: 0.75rem;">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.95rem;">
                                    <?php
                                    $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
                                    foreach ($statuses as $s): ?>
                                        <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Save</button>
                            </form>
                        </td>
                        <td style="padding: 1.5rem;">
                            <a href="orders.php?action=delete&id=<?= $order['id'] ?>"
                               onclick="return confirm('Are you sure you want to delete order #<?= $order['id'] ?>? This cannot be undone.');"
                               style="padding: 8px 16px; background: #ef4444; color: white; border: none; border-radius: 6px; text-decoration: none; display: inline-block; font-weight: 500; font-size: 0.95rem;">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

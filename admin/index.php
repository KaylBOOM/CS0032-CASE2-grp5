<?php
/**
 * Admin Dashboard - Modern Design
 */
session_start();
$baseUrl = '../'; // Go up one level to find includes
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireAdmin();

$pdo = getDBConnection();

// Fetch Stats
$bookCount  = $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
$orderCount = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$revenue    = $pdo->query('SELECT SUM(total_amount) FROM orders')->fetchColumn(); 
$userCount  = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

// Fetch Recent Orders
$recentOrders = $pdo->query('SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5')->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin: 0; color: #1e293b;">Admin Dashboard</h2>
        <span style="color: #64748b;">Welcome back, <strong>Admin</strong></span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1.5rem; border-radius: 12px; border: none;">
            <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Total Revenue</p>
            <h3 style="margin: 0.5rem 0 0; font-size: 2rem;">$<?= number_format($revenue, 2) ?></h3>
        </div>

        <div class="card" style="background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin: 0; color: #64748b; font-size: 0.9rem;">Total Orders</p>
                    <h3 style="margin: 0.5rem 0 0; font-size: 1.8rem; color: #1e293b;"><?= $orderCount ?></h3>
                </div>
                <div style="background: #eff6ff; padding: 10px; border-radius: 50%; color: #3b82f6;">📦</div>
            </div>
            <a href="orders.php" style="display: inline-block; margin-top: 1rem; color: #3b82f6; text-decoration: none; font-size: 0.9rem;">Manage Orders →</a>
        </div>

        <div class="card" style="background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin: 0; color: #64748b; font-size: 0.9rem;">Books in Stock</p>
                    <h3 style="margin: 0.5rem 0 0; font-size: 1.8rem; color: #1e293b;"><?= $bookCount ?></h3>
                </div>
                <div style="background: #fff7ed; padding: 10px; border-radius: 50%; color: #f97316;">📚</div>
            </div>
            <a href="books.php" style="display: inline-block; margin-top: 1rem; color: #f97316; text-decoration: none; font-size: 0.9rem;">Manage Inventory →</a>
        </div>

        <div class="card" style="background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin: 0; color: #64748b; font-size: 0.9rem;">Registered Users</p>
                    <h3 style="margin: 0.5rem 0 0; font-size: 1.8rem; color: #1e293b;"><?= $userCount ?></h3>
                </div>
                <div style="background: #f0fdf4; padding: 10px; border-radius: 50%; color: #16a34a;">👤</div>
            </div>
        </div>
    </div>

    <h3 style="color: #1e293b; margin-bottom: 1rem;">Recent Orders</h3>
    
    <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <?php if (empty($recentOrders)): ?>
            <div style="padding: 2rem; text-align: center; color: #64748b;">No orders found.</div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 1rem; text-align: left; color: #475569; font-weight: 600;">Order ID</th>
                        <th style="padding: 1rem; text-align: left; color: #475569; font-weight: 600;">Customer</th>
                        <th style="padding: 1rem; text-align: left; color: #475569; font-weight: 600;">Total</th>
                        <th style="padding: 1rem; text-align: left; color: #475569; font-weight: 600;">Status</th>
                        <th style="padding: 1rem; text-align: left; color: #475569; font-weight: 600;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem; color: #3b82f6; font-weight: 500;">#<?= $order['id'] ?></td>
                            <td style="padding: 1rem; color: #334155;"><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td style="padding: 1rem; font-weight: 600;">$<?= number_format($order['total_amount'], 2) ?></td>
                            <td style="padding: 1rem;">
                                <?php
                                    $statusColors = [
                                        'pending' => '#eab308',
                                        'processing' => '#3b82f6',
                                        'shipped' => '#06b6d4',
                                        'delivered' => '#22c55e',
                                        'cancelled' => '#ef4444'
                                    ];
                                    $color = $statusColors[$order['status']] ?? '#64748b';
                                ?>
                                <span style="background: <?= $color ?>20; color: <?= $color ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 1rem; color: #64748b; font-size: 0.9rem;"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div style="margin-top: 1rem; text-align: right;">
        <a href="orders.php" class="btn btn-primary" style="background: #334155; border: none;">View All Orders</a>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
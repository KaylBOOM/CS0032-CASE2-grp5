<?php
/**
 * Checkout Page
 */
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

requireLogin();

$pdo    = getDBConnection();
$userId = $_SESSION['user_id'];

// Fetch cart
$stmt = $pdo->prepare('SELECT c.*, b.title, b.price, b.stock FROM cart c JOIN books b ON c.book_id = b.id WHERE c.user_id = ?');
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax   = round($subtotal * TAX_RATE, 2);
$total = $subtotal + $tax;

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingName    = trim($_POST['shipping_name'] ?? '');
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $shippingCity    = trim($_POST['shipping_city'] ?? '');
    $shippingZip     = trim($_POST['shipping_zip'] ?? '');
    $shippingCountry = trim($_POST['shipping_country'] ?? '');
    $paymentMethod   = trim($_POST['payment_method'] ?? '');

    if ($shippingName === '' || $shippingAddress === '' || $shippingCity === '' || $shippingZip === '' || $shippingCountry === '' || $paymentMethod === '') {
        $error = 'All fields are required.';
    } else {
        try {
            $pdo->beginTransaction();

            // Create order
            $stmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, tax_amount, shipping_name, shipping_address, shipping_city, shipping_zip, shipping_country, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "pending")');
            $stmt->execute([$userId, $total, $tax, $shippingName, $shippingAddress, $shippingCity, $shippingZip, $shippingCountry, $paymentMethod]);
            $orderId = $pdo->lastInsertId();

            // Validate stock and add order items
            foreach ($cartItems as $item) {
                if ($item['stock'] < $item['quantity']) {
                    throw new Exception('Insufficient stock for "' . $item['title'] . '". Only ' . $item['stock'] . ' available.');
                }

                $stmt = $pdo->prepare('INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)');
                $stmt->execute([$orderId, $item['book_id'], $item['quantity'], $item['price']]);

                $stmt = $pdo->prepare('UPDATE books SET stock = stock - ? WHERE id = ? AND stock >= ?');
                $stmt->execute([$item['quantity'], $item['book_id'], $item['quantity']]);

                if ($stmt->rowCount() === 0) {
                    throw new Exception('Stock changed for "' . $item['title'] . '". Please try again.');
                }
            }

            // Clear cart
            $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
            $stmt->execute([$userId]);

            $pdo->commit();

            // Redirect to confirmation
            header('Location: order_confirmation.php?id=' . $orderId);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'An error occurred while placing your order. Please try again.';
        }
    }
}

$pageTitle = 'Checkout — Online Bookstore';
require_once __DIR__ . '/includes/header.php';
?>

<h2>Checkout</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display:flex; flex-wrap:wrap; gap:2rem;">
    <!-- Shipping & Payment Form -->
    <div style="flex:1; min-width:300px;">
        <form method="POST" action="checkout.php">
            <div class="card">
                <h3>Shipping Details</h3>
                <div class="form-group">
                    <label for="shipping_name">Full Name</label>
                    <input type="text" id="shipping_name" name="shipping_name" class="form-control" value="<?= htmlspecialchars($_POST['shipping_name'] ?? $_SESSION['user_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="shipping_address">Address</label>
                    <input type="text" id="shipping_address" name="shipping_address" class="form-control" value="<?= htmlspecialchars($_POST['shipping_address'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="shipping_city">City</label>
                    <input type="text" id="shipping_city" name="shipping_city" class="form-control" value="<?= htmlspecialchars($_POST['shipping_city'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="shipping_zip">ZIP / Postal Code</label>
                    <input type="text" id="shipping_zip" name="shipping_zip" class="form-control" value="<?= htmlspecialchars($_POST['shipping_zip'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="shipping_country">Country</label>
                    <input type="text" id="shipping_country" name="shipping_country" class="form-control" value="<?= htmlspecialchars($_POST['shipping_country'] ?? '') ?>" required>
                </div>
            </div>

            <div class="card">
                <h3>Payment Method</h3>
                <div class="form-group">
                    <label>
                        <input type="radio" name="payment_method" value="credit_card" <?= ($_POST['payment_method'] ?? '') === 'credit_card' ? 'checked' : '' ?> required>
                        Credit Card
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="radio" name="payment_method" value="paypal" <?= ($_POST['payment_method'] ?? '') === 'paypal' ? 'checked' : '' ?>>
                        PayPal
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="radio" name="payment_method" value="cod" <?= ($_POST['payment_method'] ?? '') === 'cod' ? 'checked' : '' ?>>
                        Cash on Delivery
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-success" style="width:100%;">Place Order — $<?= number_format($total, 2) ?></button>
        </form>
    </div>

    <!-- Order Summary -->
    <div style="flex:0 0 320px;">
        <div class="card">
            <h3>Order Summary</h3>
            <?php foreach ($cartItems as $item): ?>
                <p>
                    <?= htmlspecialchars($item['title']) ?> × <?= $item['quantity'] ?>
                    <span style="float:right;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                </p>
            <?php endforeach; ?>
            <hr>
            <p><strong>Subtotal:</strong> <span style="float:right;">$<?= number_format($subtotal, 2) ?></span></p>
            <p><strong>Tax (<?= TAX_RATE * 100 ?>%):</strong> <span style="float:right;">$<?= number_format($tax, 2) ?></span></p>
            <p style="font-size:1.1rem;"><strong>Total:</strong> <span style="float:right;"><strong>$<?= number_format($total, 2) ?></strong></span></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

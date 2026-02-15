<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Storyscape' : 'Storyscape Bookstore' ?></title>
    <link rel="stylesheet" href="<?= isset($baseUrl) ? $baseUrl : '' ?>assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>index.php" class="logo">📚 Storyscape</a>
            <nav class="main-nav">
                <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>index.php">Home</a>
                <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>books.php">Books</a>
                <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>cart.php">Cart</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>orders.php">My Orders</a>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>admin/">Admin</a>
                    <?php endif; ?>
                    <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>logout.php">Logout</a>
                <?php else: ?>
                    <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>login.php">Login</a>
                    <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">

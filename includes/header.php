<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Online Bookstore') ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?? '' ?>assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= $baseUrl ?? '' ?>index.php" class="logo">📚 Online Bookstore</a>
        <nav class="main-nav" role="navigation" aria-label="Main navigation">
            <a href="<?= $baseUrl ?? '' ?>index.php">Home</a>
            <a href="<?= $baseUrl ?? '' ?>books.php">Books</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= $baseUrl ?? '' ?>cart.php">Cart</a>
                <a href="<?= $baseUrl ?? '' ?>orders.php">My Orders</a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="<?= $baseUrl ?? '' ?>admin/index.php">Admin</a>
                <?php endif; ?>
                <a href="<?= $baseUrl ?? '' ?>logout.php">Logout (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
            <?php else: ?>
                <a href="<?= $baseUrl ?? '' ?>login.php">Login</a>
                <a href="<?= $baseUrl ?? '' ?>register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">

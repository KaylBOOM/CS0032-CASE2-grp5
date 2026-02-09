<?php
/**
 * Authentication helpers.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Register a new user.
 *
 * @param string $name
 * @param string $email
 * @param string $password
 * @return array ['success' => bool, 'message' => string]
 */
function registerUser(string $name, string $email, string $password): array
{
    $pdo = getDBConnection();

    // Check if email already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }

    // Hash password with bcrypt (NFR-03)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "customer")');
    $stmt->execute([$name, $email, $hashedPassword]);

    return ['success' => true, 'message' => 'Registration successful.'];
}

/**
 * Authenticate a user.
 *
 * @param string $email
 * @param string $password
 * @return array ['success' => bool, 'message' => string, 'user' => ?array]
 */
function loginUser(string $email, string $password): array
{
    $pdo = getDBConnection();

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.', 'user' => null];
    }

    return ['success' => true, 'message' => 'Login successful.', 'user' => $user];
}

/**
 * Check if the current session user is logged in.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if the current session user is an admin.
 */
function isAdmin(): bool
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require login — redirect to login page if not authenticated.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Require admin — redirect to index if not admin.
 */
function requireAdmin(): void
{
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit;
    }
}

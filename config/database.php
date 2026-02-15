<?php
/**
 * Database Configuration
 *
 * Update these settings to match your local/production environment.
 */

define('DB_HOST', 'localhost;port=3307');
define('DB_NAME', 'online_bookstore');
define('DB_USER', 'root');
define('DB_PASS', '');

define('TAX_RATE', 0.08); // 8% sales tax

/**
 * Get a PDO database connection.
 *
 * @return PDO
 */
function getDBConnection(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

<?php
/**
 * Database configuration and connection (XAMPP / phpMyAdmin)
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'website_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP default is empty
define('DB_CHARSET', 'utf8mb4');

function db_connect(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $pdo;
}

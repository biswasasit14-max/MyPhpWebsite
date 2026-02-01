<?php
/**
 * Application config (paths, etc.)
 */
define('BASE_PATH', '/website');
define('BASE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_PATH);

function url(string $path = ''): string {
    $path = ltrim($path, '/');
    return BASE_PATH . ($path ? '/' . $path : '');
}

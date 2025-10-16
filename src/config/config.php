<?php
/**
 * src/config/config.php
 * Global configuration for Speedy Wheels
 */

// Error display for dev (turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Base URL: adjust automatically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_url = rtrim($protocol . '://' . $host . $script_dir, '/') . '/';
define('BASE_URL', $base_url);

// App metadata
define('APP_NAME', 'Speedy Wheels Car Rental');
define('APP_VERSION', '1.0');

// Helper - build absolute URL relative to project
if (!function_exists('url')) {
    function url($path = '') {
        return BASE_URL . ltrim($path, '/');
    }
}

// Include database connection (creates $pdo)
require_once __DIR__ . '/database.php';
?>

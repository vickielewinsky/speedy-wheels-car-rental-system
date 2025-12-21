<?php
// config/database.php

/**
 * Database Configuration for Speedy Wheels Car Rental System
 */

class DatabaseConfig {
    // Database connection settings
    const HOST = 'localhost';
    const DBNAME = 'your_database_name';
    const USERNAME = 'YOUR_DB_USERNAME';
    const PASSWORD = '';
    const CHARSET = 'utf8mb4';

    // PDO options
    const OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
}

/**
 * Create and return a PDO database connection
 */
function getDatabaseConnection() {
    try {
        $dsn = "mysql:host=" . DatabaseConfig::HOST . ";dbname=" . DatabaseConfig::DBNAME . ";charset=" . DatabaseConfig::CHARSET;

        $pdo = new PDO(
            $dsn,
            DatabaseConfig::USERNAME,
            DatabaseConfig::PASSWORD,
            DatabaseConfig::OPTIONS
        );

        return $pdo;

    } catch (PDOException $e) {
        // Log error and display user-friendly message
        error_log("Database Connection Failed: " . $e->getMessage());
        throw new Exception("Unable to connect to database. Please try again later.");
    }
}

/**
 * Check if database connection is working
 */
function testDatabaseConnection() {
    try {
        $pdo = getDatabaseConnection();
        return [
            'success' => true,
            'message' => 'Database connection successful!',
            'database' => DatabaseConfig::DBNAME
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Auto-create connection if this file is included
try {
    $pdo = getDatabaseConnection();
} catch (Exception $e) {
    // Connection will be handled by individual pages
}
?>
<?php
// check_database_tables.php
require_once 'src/config/database.php';

echo "=== Database Structure Check ===\n";

try {
    $pdo = getDatabaseConnection();
    echo "✅ Connected to database: " . DatabaseConfig::DBNAME . "\n\n";
    
    // Get all tables
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Available Tables: " . implode(', ', $tables) . "\n\n";
    
    // Check each table structure
    foreach ($tables as $table) {
        echo "--- $table ---\n";
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo "  {$column['Field']} - {$column['Type']} ({$column['Null']})\n";
        }
        echo "\n";
    }
    
    // Check sample data counts
    echo "=== Sample Data Counts ===\n";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "$table: $count records\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>
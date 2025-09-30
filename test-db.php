<?php
require_once "src/config/database.php";

echo "<h3>Database Connection Test</h3>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<div class='alert alert-success'>✅ Database connection successful!</div>";
        
        // Test if tables exist
        $tables = ['vehicles', 'customers', 'bookings', 'users'];
        foreach ($tables as $table) {
            $stmt = $conn->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<p>✅ Table '$table' exists</p>";
            } else {
                echo "<p>❌ Table '$table' missing - import database schema first</p>";
            }
        }
        
    } else {
        echo "<div class='alert alert-danger'>❌ Database connection failed</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<p>Make sure to import the database schema first via phpMyAdmin</p>";
}
?>
<a href="index.php" class="btn btn-primary">Go to Homepage</a>

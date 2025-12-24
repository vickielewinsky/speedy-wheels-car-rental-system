<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=speedy_wheels;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h3>Checking payments table structure</h3>";
    
    // Check payments table columns
    $sql = "DESCRIBE payments";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Payments Table Columns:</h4>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check sample data
    $sql = "SELECT * FROM payments LIMIT 5";
    $stmt = $pdo->query($sql);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Sample Payments Data (up to 5 records):</h4>";
    if (empty($payments)) {
        echo "No payments found in the table.<br>";
    } else {
        echo "<pre>";
        print_r($payments);
        echo "</pre>";
    }
    
    // Also check if there's a status column with different name
    $sql = "SHOW COLUMNS FROM payments LIKE '%status%'";
    $stmt = $pdo->query($sql);
    $statusColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Status-related columns in payments table:</h4>";
    if (empty($statusColumns)) {
        echo "No status column found.<br>";
    } else {
        echo "<pre>";
        print_r($statusColumns);
        echo "</pre>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>

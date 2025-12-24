<?php
require 'src/config/database.php';
$pdo = getDatabaseConnection();

echo "<h3>Database Explorer</h3>";

// List all tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "<p><strong>Tables in database:</strong> " . implode(', ', $tables) . "</p>";

// Show bookings table structure
echo "<h4>Bookings Table Structure:</h4>";
$columns = $pdo->query("DESCRIBE bookings")->fetchAll();
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
foreach ($columns as $col) {
    echo "<tr>";
    echo "<td>" . $col['Field'] . "</td>";
    echo "<td>" . $col['Type'] . "</td>";
    echo "<td>" . $col['Null'] . "</td>";
    echo "<td>" . $col['Key'] . "</td>";
    echo "<td>" . $col['Default'] . "</td>";
    echo "<td>" . $col['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Show sample data from bookings
echo "<h4>Sample Booking Data (first 5 rows):</h4>";
$bookings = $pdo->query("SELECT * FROM bookings LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
if (!empty($bookings)) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>";
    foreach (array_keys($bookings[0]) as $key) {
        echo "<th>$key</th>";
    }
    echo "</tr>";
    foreach ($bookings as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No bookings found</p>";
}

// Show vehicles table structure
echo "<h4>Vehicles Table Structure:</h4>";
$columns = $pdo->query("DESCRIBE vehicles")->fetchAll();
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
foreach ($columns as $col) {
    echo "<tr>";
    echo "<td>" . $col['Field'] . "</td>";
    echo "<td>" . $col['Type'] . "</td>";
    echo "<td>" . $col['Null'] . "</td>";
    echo "<td>" . $col['Key'] . "</td>";
    echo "<td>" . $col['Default'] . "</td>";
    echo "<td>" . $col['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

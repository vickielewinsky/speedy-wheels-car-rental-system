<?php
// test_passwords.php
require_once "src/config/database.php";

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=speedy_wheels;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get the users
    $stmt = $pdo->prepare("SELECT username, password_hash FROM users WHERE username IN ('admin', 'john_doe')");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Password Verification Test</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Username</th><th>Password Hash</th><th>admin123 test</th><th>customer123 test</th></tr>";

    foreach ($users as $user) {
        $admin_test = password_verify('admin123', $user['password_hash']) ? '✅ MATCH' : '❌ NO MATCH';
        $customer_test = password_verify('customer123', $user['password_hash']) ? '✅ MATCH' : '❌ NO MATCH';
        
        echo "<tr>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['password_hash']}</td>";
        echo "<td>{$admin_test}</td>";
        echo "<td>{$customer_test}</td>";
        echo "</tr>";
    }

    echo "</table>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
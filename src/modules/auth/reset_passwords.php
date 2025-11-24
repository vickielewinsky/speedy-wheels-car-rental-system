<?php
require_once __DIR__ . '/../../config/database.php';

$pdo = getDatabaseConnection();

echo "<h3>Resetting Default User Passwords...</h3>";

// List of users to reset with their new passwords
$users = [
    'admin' => 'admin123',
    'john_doe' => 'customer123'
];

foreach ($users as $username => $password) {
    // Create proper password hash
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Update the user in database
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
    $stmt->execute([$hash, $username]);
    
    $rows = $stmt->rowCount();
    
    if ($rows > 0) {
        echo "✅ Reset password for <strong>$username</strong> to: <strong>$password</strong><br>";
        
        // Verify it works
        $verify_stmt = $pdo->prepare("SELECT password_hash FROM users WHERE username = ?");
        $verify_stmt->execute([$username]);
        $user = $verify_stmt->fetch();
        
        if (password_verify($password, $user['password_hash'])) {
            echo "&nbsp;&nbsp;✅ Password verification SUCCESS<br>";
        } else {
            echo "&nbsp;&nbsp;❌ Password verification FAILED<br>";
        }
    } else {
        echo "❌ User <strong>$username</strong> not found in database<br>";
    }
    echo "<br>";
}

echo "<h3>Reset Complete!</h3>";
echo "<p>Try logging in with:</p>";
echo "<ul>";
echo "<li><strong>admin / admin123</strong></li>";
echo "<li><strong>john_doe / customer123</strong></li>";
echo "</ul>";
echo '<p><a href="login.php" class="btn btn-primary">Go to Login</a></p>';
?>
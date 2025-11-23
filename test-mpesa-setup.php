<?php
// test_mpesa_setup.php
echo "<h3>🔧 MPESA Setup Test - Speedy Wheels</h3>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<div class='container'>";

// Test 1: Database Connection
echo "<h4>🗄️ Database Connection</h4>";
try {
    require_once "src/config/database.php";
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<div class='alert alert-success'>✅ Database connection successful</div>";
        
        // Check all tables
        $tables = ['customers', 'vehicles', 'bookings', 'users', 'payments'];
        foreach ($tables as $table) {
            $stmt = $conn->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<div class='alert alert-success'>✅ Table '$table' exists</div>";
            } else {
                echo "<div class='alert alert-danger'>❌ Table '$table' missing</div>";
            }
        }
        
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Database error: " . $e->getMessage() . "</div>";
}

// Test 2: File Structure
echo "<h4>📁 File Structure</h4>";
$required_files = [
    'src/config/database.php' => 'Database config',
    'src/config/mpesa.php' => 'MPESA config', 
    'src/modules/payments/mpesa_processor.php' => 'MPESA processor'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='alert alert-success'>✅ $description - EXISTS</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ $description - MISSING</div>";
    }
}

echo "</div><hr>";

// Next steps
echo "<div class='container'>";
echo "<h4>🎯 NEXT STEPS:</h4>";

if (!file_exists('src/config/mpesa.php')) {
    echo "<p><strong>1. Create MPESA Config File:</strong></p>";
    echo "<pre style='background:#f8f9fa;padding:10px;'>mkdir src/config/
# Create src/config/mpesa.php</pre>";
}

if (!file_exists('src/modules/payments/mpesa_processor.php')) {
    echo "<p><strong>2. Create Payments Folder:</strong></p>";
    echo "<pre style='background:#f8f9fa;padding:10px;'>mkdir src/modules/payments/
# Create src/modules/payments/mpesa_processor.php</pre>";
}

echo "<a href='index.php' class='btn btn-primary'>Back to Home</a>";
echo "</div>";
?>
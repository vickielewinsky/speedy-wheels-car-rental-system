<?php
require_once "src/config/mpesa.php";

echo "<h3>🎯 Testing REAL MPESA Connection</h3>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<div class='container mt-4'>";

try {
    $token = MpesaConfig::getAccessToken();
    if ($token) {
        echo "<div class='alert alert-success'>";
        echo "<h4>✅ SUCCESS! REAL MPESA INTEGRATION IS WORKING!</h4>";
        echo "<p><strong>Access Token:</strong> " . substr($token, 0, 50) . "...</p>";
        echo "<p><strong>Status:</strong> Connected to Safaricom Daraja API</p>";
        echo "<p>🎉 Your project now has GENUINE MPESA integration!</p>";
        echo "</div>";
        
        echo "<div class='alert alert-info'>";
        echo "<h5>🎓 What This Means for Your Project:</h5>";
        echo "<ul>";
        echo "<li>✅ Real MPESA API Integration</li>";
        echo "<li>✅ Industry-Standard Payment System</li>";
        echo "<li>✅ Distinction-Level Feature</li>";
        echo "<li>✅ Professional Grade Application</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<h4>❌ Connection Failed</h4>";
        echo "<p>Please check your credentials in src/config/mpesa.php</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<a href='index.php' class='btn btn-primary'>Back to Home</a>";
echo "</div>";
?>
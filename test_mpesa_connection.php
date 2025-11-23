<?php
require_once "src/config/mpesa.php";

echo "<h3>Testing MPESA Connection</h3>";

$token = MpesaConfig::getAccessToken();
if ($token) {
    echo "<p style='color:green;'>✅ MPESA Access Token: " . substr($token, 0, 20) . "...</p>";
} else {
    echo "<p style='color:red;'>❌ Failed to get MPESA token</p>";
}
?>
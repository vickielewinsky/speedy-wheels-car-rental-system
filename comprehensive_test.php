<?php
// Simulate what happens when accessing bookings page
session_start();

// Load files in correct order (as bookings.php does)
require_once 'src/config/database.php';
require_once 'src/includes/auth.php';
require_once 'src/config/config.php';  // This defines BASE_URL
require_once 'src/helpers/url_helper.php'; // This should use BASE_URL constant

echo "<h3>Comprehensive Test (Simulating Bookings Page Load)</h3>";
echo "Loaded config.php - BASE_URL = " . BASE_URL . "<br>";
echo "Loaded url_helper.php - base_url() = " . base_url() . "<br><br>";

// Test the exact URL from your error
$test_url = base_url('src/modules/bookings/index.php');
echo "Bookings page URL should be: <a href='$test_url' target='_blank'>$test_url</a><br><br>";

// Test the exact URL from your logout error
$logout_url = base_url('src/modules/auth/logout.php');
echo "Logout URL should be: <a href='$logout_url' target='_blank'>$logout_url</a><br><br>";

echo "<h4>If these links don't work, the issue is:</h4>";
echo "1. File doesn't exist at that path<br>";
echo "2. Apache configuration issue<br>";
echo "3. .htaccess redirect problem<br>";

// Check if files exist
echo "<h4>File existence check:</h4>";
$bookings_file = __DIR__ . '/src/modules/bookings/index.php';
$logout_file = __DIR__ . '/src/modules/auth/logout.php';

echo "Bookings file: " . (file_exists($bookings_file) ? '✅ EXISTS' : '❌ MISSING') . "<br>";
echo "Logout file: " . (file_exists($logout_file) ? '✅ EXISTS' : '❌ MISSING') . "<br>";
?>

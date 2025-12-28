<?php
// Load config
require_once 'src/config/config.php';

echo "<h3>Debug URLs</h3>";
echo "Project folder: " . basename(dirname(__FILE__)) . "<br>";
echo "BASE_URL constant: <strong>" . BASE_URL . "</strong><br><br>";

// Test different base_url() functions
if (file_exists('src/helpers/url_helper.php')) {
    require_once 'src/helpers/url_helper.php';
    echo "url_helper.php base_url(): " . base_url() . "<br>";
}

// Test the actual bookings URL
$bookings_url = BASE_URL . '/src/modules/bookings/index.php';
echo "<br>Bookings page should be at: <a href='$bookings_url' target='_blank'>$bookings_url</a><br>";

// Test what the browser is actually accessing
echo "<br>Current browser URL: " . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

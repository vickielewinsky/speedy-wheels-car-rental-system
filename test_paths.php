<?php
// test_paths.php - Place this in your root folder
echo "<h1>File Path Debug</h1>";

$base_path = '/speedy-wheels-car-rental-system';

$files_to_check = [
    'Home Page' => $base_path . '/index.php',
    'Create Booking File' => $base_path . '/src/modules/bookings/create_booking.php',
    'Bookings Index' => $base_path . '/src/modules/bookings/index.php',
    'Vehicles Index' => $base_path . '/src/modules/vehicles/index.php'
];

echo "<h3>Checking if files actually exist:</h3>";
foreach ($files_to_check as $name => $path) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . $path;
    $exists = file_exists($full_path);
    
    echo "<p><strong>$name:</strong><br>";
    echo "Path: $full_path<br>";
    echo "Status: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
    echo "<hr>";
}

echo "<h3>Test Direct URLs:</h3>";
foreach ($files_to_check as $name => $path) {
    $url = 'http://localhost' . $path;
    echo "<p><a href='$url' target='_blank'>$name</a> - $url</p>";
}
?>
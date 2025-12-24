<?php
// auto_cleanup.php - Run this periodically
require 'src/config/database.php';
$pdo = getDatabaseConnection();

// Complete expired bookings
$pdo->exec("UPDATE bookings SET status='completed', payment_status='paid' WHERE status IN ('confirmed', 'active') AND end_date < CURDATE()");

// Free vehicles
$pdo->exec("UPDATE vehicles SET status='available' WHERE status='booked' AND vehicle_id IN (SELECT vehicle_id FROM bookings WHERE status='completed' AND end_date < CURDATE())");

echo "Auto-cleanup complete at " . date('Y-m-d H:i:s');
<?php
// src/modules/chauffeur/update_booking.php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';

session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . base_url('src/modules/auth/login.php'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $driver_assigned = $_POST['driver_assigned'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE chauffeur_bookings 
            SET driver_assigned = ?, status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$driver_assigned, $status, $booking_id]);
        
        $_SESSION['success_message'] = "Booking updated successfully!";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error updating booking: " . $e->getMessage();
    }
}

header('Location: ' . base_url('src/modules/chauffeur/index.php'));
exit();
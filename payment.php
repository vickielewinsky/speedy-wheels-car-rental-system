<?php
// payment.php - Main payment entry point

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: src/modules/auth/login.php");
    exit();
}

// Include the actual payment module based on user role
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Admin sees payment history
    require_once __DIR__ . '/src/modules/payments/payment.php';
} else {
    // Regular user sees payment form
    require_once __DIR__ . '/src/modules/payments/payment_form.php';
}
?>

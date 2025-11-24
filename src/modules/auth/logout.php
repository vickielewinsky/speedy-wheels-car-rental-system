<?php
// src/modules/auth/logout.php

// Start session and include auth functions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../../includes/auth.php";

// Define base_url function if not exists
if (!function_exists('base_url')) {
    function base_url($path = '') {
        // Detect environment for flexible path handling
        $is_local = ($_SERVER['HTTP_HOST'] ?? 'localhost') === 'localhost';
        $base_path = $is_local ? '/speedy-wheels-car-rental-system' : '';
        
        // Clean path and ensure proper formatting
        $clean_path = $path ? '/' . ltrim($path, '/') : '';
        return $base_path . $clean_path;
    }
}

// Logout the user
logoutUser();

// Redirect to home page
header("Location: " . base_url('index.php'));
exit();
?>
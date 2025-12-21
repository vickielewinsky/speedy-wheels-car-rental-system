<?php


// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../helpers/url_helper.php';

/**
 * Require authentication for protected pages
 */
function requireAuthentication() {
    // Skip authentication check for login/register pages
    $current_file = basename($_SERVER['PHP_SELF']);
    $excluded_pages = ['login.php', 'register.php', 'logout.php', 'reset_passwords.php'];

    if (in_array($current_file, $excluded_pages)) {
        return;
    }

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
        // Store the current page to redirect back after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: " . base_url('src/modules/auth/login.php'));
        exit;
    }
}

/**
 * Require admin role for admin-only pages
 */
function requireAdmin() {
    requireAuthentication();

    if (!hasRole('admin')) {
        $_SESSION['error_message'] = "Access denied. Admin privileges required.";
        header("Location: " . base_url('src/modules/auth/dashboard.php'));
        exit;
    }
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['is_logged_in']) && 
           $_SESSION['is_logged_in'] === true;
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isAuthenticated() && 
           isset($_SESSION['user_role']) && 
           strtolower($_SESSION['user_role']) === strtolower($role);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return hasRole('admin') || hasRole('superadmin');
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Logout user
 */
function logout() {
    session_destroy();
    session_start(); // Start fresh session for messages
    $_SESSION['success_message'] = "You have been logged out successfully.";
    header("Location: " . base_url('src/modules/auth/login.php'));
    exit;
}
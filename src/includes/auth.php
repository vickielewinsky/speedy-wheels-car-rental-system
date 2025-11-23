<?php
// includes/auth.php

/**
 * Authentication and Authorization Functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuthentication() {
    if (!isAuthenticated()) {
        header('Location: /speedy-wheels-car-rental-system/login.php');
        exit();
    }
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    if (!isAuthenticated()) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'] ?? 'user';
    return $userRole === $role;
}

/**
 * Login user
 */
function loginUser($userId, $username, $role = 'user') {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['user_role'] = $role;
    $_SESSION['login_time'] = time();
}

/**
 * Logout user
 */
function logoutUser() {
    session_destroy();
    session_start(); // Start fresh session for messages
    $_SESSION['message'] = 'You have been logged out successfully.';
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? 'Guest';
}

/**
 * Simple authentication check wrapper
 */
function check_authentication() {
    requireAuthentication();
}
?>
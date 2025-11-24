<?php
// src/includes/auth.php

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
        header('Location: ' . base_url('src/modules/auth/login.php'));
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
    
    $userRole = $_SESSION['user_role'] ?? 'customer';
    return $userRole === $role;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Require admin role - redirect if not admin
 */
function requireAdmin() {
    requireAuthentication();
    if (!isAdmin()) {
        header('Location: ' . base_url('index.php'));
        exit();
    }
}

/**
 * Login user
 */
function loginUser($userId, $username, $role = 'customer') {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['user_role'] = $role;
    $_SESSION['login_time'] = time();
    
    // Set a flash message for successful login
    $_SESSION['flash_message'] = 'Login successful!';
    $_SESSION['flash_type'] = 'success';
}

/**
 * Logout user
 */
function logoutUser() {
    // Store logout message before destroying session
    $message = 'You have been logged out successfully.';
    
    // Destroy the session
    session_destroy();
    
    // Start a fresh session for messages
    session_start();
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = 'info';
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
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? 'guest';
}

/**
 * Simple authentication check wrapper
 */
function check_authentication() {
    requireAuthentication();
}

/**
 * Display flash messages if any exist
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        echo '<div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        // Clear the flash message after displaying
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Set flash message for next request
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get user display name (first name + last name if available)
 */
function getUserDisplayName() {
    if (!isAuthenticated()) {
        return 'Guest';
    }
    
    // Check if we have first_name and last_name in session
    if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
        return $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    }
    
    // Fallback to username
    return getCurrentUsername();
}

/**
 * Check if current user can access admin features
 */
function canAccessAdmin() {
    return isAuthenticated() && (isAdmin() || hasRole('admin'));
}

/**
 * Redirect to login with return URL
 */
function redirectToLogin($returnUrl = null) {
    $loginUrl = base_url('src/modules/auth/login.php');
    
    if ($returnUrl) {
        $loginUrl .= '?return=' . urlencode($returnUrl);
    }
    
    header('Location: ' . $loginUrl);
    exit();
}

/**
 * Get return URL from request or default to home
 */
function getReturnUrl($default = 'index.php') {
    return $_GET['return'] ?? $default;
}
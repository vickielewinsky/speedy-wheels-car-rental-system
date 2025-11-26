<?php
// src/includes/auth.php

/**
 * Authentication and Authorization Functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate the correct base path for includes
$base_dir = dirname(__DIR__); // Goes up from src/includes to src/

// Include database config with correct path
require_once $base_dir . '/config/database.php';

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

/**
 * Get database connection
 */
function getAuthDatabaseConnection() {
    try {
        $pdo = getDatabaseConnection();
        return $pdo;
    } catch (Exception $e) {
        error_log("Auth Database Connection Failed: " . $e->getMessage());
        throw new Exception("Unable to connect to database. Please try again later.");
    }
}

/**
 * Verify user credentials
 */
function verifyUserCredentials($username, $password) {
    try {
        $pdo = getAuthDatabaseConnection();
        
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash, user_role, first_name, last_name FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['user_role'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ]
            ];
        }
        
        return ['success' => false, 'message' => 'Invalid username or password'];
        
    } catch (Exception $e) {
        error_log("User verification error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

/**
 * Check if user exists by username or email
 */
function userExists($username, $email) {
    try {
        $pdo = getAuthDatabaseConnection();
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        return $stmt->fetch() !== false;
        
    } catch (Exception $e) {
        error_log("User existence check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create new user
 */
function createUser($userData) {
    try {
        $pdo = getAuthDatabaseConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, first_name, last_name, phone, user_role, is_active, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'customer', 1, NOW())
        ");
        
        $success = $stmt->execute([
            $userData['username'],
            $userData['email'],
            password_hash($userData['password'], PASSWORD_DEFAULT),
            $userData['first_name'],
            $userData['last_name'],
            $userData['phone'] ?? null
        ]);
        
        return $success ? $pdo->lastInsertId() : false;
        
    } catch (Exception $e) {
        error_log("User creation error: " . $e->getMessage());
        return false;
    }
}
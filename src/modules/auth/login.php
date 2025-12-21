<?php
// src/modules/auth/login.php

// Define this constant BEFORE including any files
define('LOGIN_PAGE', true);

// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Turn off error display for users (but log them)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Debug session
error_log("Login page accessed. Session ID: " . session_id());
error_log("Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));

require_once "../../config/database.php";
require_once "../../includes/auth.php";
require_once "Auth.php";

// Redirect if already logged in - but only if not on login page
if (isAuthenticated() && !defined('LOGIN_PAGE')) {
    header("Location: " . base_url('index.php'));
    exit;
}

// Handle form submission BEFORE including header
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if both fields exist and are not empty
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        $error = "Please enter both username and password.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error = "Please enter both username and password.";
        } else {
            try {
                $auth = new Auth();
                $result = $auth->login($username, $password);

                if ($result['success']) {
                    $_SESSION['success_message'] = "Login successful! Welcome back, " . htmlspecialchars($username) . "!";
                    
                    // Check if there's a redirect URL stored
                    $redirect_url = $_SESSION['redirect_url'] ?? 'index.php';
                    if (isset($_SESSION['redirect_url'])) {
                        unset($_SESSION['redirect_url']);
                    }
                    
                    header("Location: " . base_url($redirect_url));
                    exit;
                } else {
                    // Generic error message for security
                    $error = "Invalid username or password.";
                }
            } catch (Exception $e) {
                // Log the error but show generic message to user
                error_log("Login error: " . $e->getMessage());
                $error = "An error occurred during login. Please try again.";
            }
        }
    }
}

$page_title = "Sign in to Speedy Wheels";

// Get background image - try multiple paths
$image_url = '';
$possible_paths = [
    'src/assets/images/hero-car.png',
    'assets/images/hero-car.png',
    'images/hero-car.png',
    'hero-car.png'
];

foreach ($possible_paths as $path) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/speedy-wheels-car-rental-system/' . ltrim($path, '/');
    if (file_exists($full_path)) {
        $image_url = base_url($path);
        break;
    }
}

// If still not found, check relative to this file
if (empty($image_url)) {
    $local_path = __DIR__ . '/../../../src/assets/images/hero-car.png';
    if (file_exists($local_path)) {
        $image_url = base_url('src/assets/images/hero-car.png');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Speedy Wheels</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Your Main CSS -->
    <link href="<?php echo base_url('src/assets/css/main.css'); ?>" rel="stylesheet">
    
    <style>
        /* ============================================
           BACKGROUND WITH CAR IMAGE
        ============================================ */
        
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            padding-top: 80px; /* For navbar */
        }
        
        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -3;
            overflow: hidden;
        }
        
        <?php if (!empty($image_url)): ?>
        .background-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('<?php echo $image_url; ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            opacity: 0.18;
            filter: blur(0.5px);
        }
        <?php endif; ?>
        
        .background-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, 
                rgba(248, 249, 250, 0.92) 0%, 
                rgba(248, 249, 250, 0.96) 100%);
            z-index: -1;
        }
        
        .login-wrapper {
            min-height: calc(100vh - 300px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }
        
        .login-box {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 500px;
            padding: 45px 40px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-top: 5px solid #667eea;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* ============================================
           ALERT BOXES
        ============================================ */
        .alert-box {
            border-radius: 12px;
            border: none;
            margin-bottom: 30px;
            padding: 18px 20px;
            animation: fadeIn 0.5s ease-out;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
            color: #c00;
            border-left: 5px solid #dc3545;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #f0fff4 0%, #e6ffe6 100%);
            color: #0a5;
            border-left: 5px solid #28a745;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        /* ============================================
           PROFESSIONAL FORM STYLES
        ============================================ */
        
        .form-container {
            margin-top: 30px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }
        
        .form-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .form-icon i {
            font-size: 28px;
            color: white;
        }
        
        .form-header h2 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 24px;
        }
        
        .form-subtitle {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 0;
            font-weight: 400;
        }
        
        .form-field-group {
            margin-bottom: 28px;
        }
        
        .field-label-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-label i {
            color: #667eea;
            font-size: 14px;
        }
        
        .required-indicator {
            color: #dc3545;
            font-size: 16px;
            font-weight: bold;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 16px;
            z-index: 2;
            transition: color 0.3s ease;
        }
        
        .password-field .input-icon {
            left: 18px;
        }
        
        .modern-input {
            padding: 16px 20px 16px 50px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
            font-family: 'Poppins', sans-serif;
            height: 54px;
            color: #2c3e50;
            width: 100%;
            box-sizing: border-box;
        }
        
        .modern-input:focus {
            border-color: #667eea;
            background: white;
            outline: none;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
            padding-left: 50px;
        }
        
        .modern-input::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }
        
        .input-focus-line {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        
        .modern-input:focus ~ .input-focus-line {
            width: 100%;
        }
        
        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
            transition: color 0.3s ease;
            z-index: 2;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        .field-hint {
            color: #6c757d;
            font-size: 12px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            padding-left: 5px;
        }
        
        .field-hint i {
            color: #667eea;
            font-size: 11px;
        }
        
        .form-options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 25px 0 30px;
        }
        
        .modern-checkbox {
            display: flex;
            align-items: center;
            cursor: pointer;
            position: relative;
            user-select: none;
        }
        
        .modern-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }
        
        .checkmark {
            position: relative;
            height: 20px;
            width: 20px;
            background-color: white;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        
        .modern-checkbox:hover input ~ .checkmark {
            border-color: #667eea;
        }
        
        .modern-checkbox input:checked ~ .checkmark {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        
        .modern-checkbox input:checked ~ .checkmark:after {
            display: block;
        }
        
        .checkbox-label {
            color: #495057;
            font-size: 14px;
            font-weight: 500;
        }
        
        .forgot-password-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .forgot-password-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .forgot-password-link i {
            font-size: 13px;
        }
        
        .submit-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            width: 100%;
            padding: 18px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 30px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
            height: 56px;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .submit-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }
        
        .submit-button:active {
            transform: translateY(-1px);
        }
        
        .button-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: opacity 0.3s ease;
        }
        
        .button-text {
            letter-spacing: 0.5px;
        }
        
        .button-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .divider-with-text {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: #6c757d;
            font-size: 13px;
            font-weight: 500;
        }
        
        .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, #e1e5e9, transparent);
        }
        
        .divider-text {
            padding: 0 15px;
            background: white;
            position: relative;
        }
        
        .social-login-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .social-button {
            flex: 1;
            padding: 14px;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: 2px solid #e1e5e9;
            background: white;
            color: #333;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
        }
        
        .social-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .google-button {
            color: #DB4437;
            border-color: #e1e5e9;
        }
        
        .google-button:hover {
            background: #f8f9fa;
            border-color: #DB4437;
        }
        
        .microsoft-button {
            color: #00a4ef;
            border-color: #e1e5e9;
        }
        
        .microsoft-button:hover {
            background: #f8f9fa;
            border-color: #00a4ef;
        }
        
        /* Registration Section */
        .registration-section {
            padding: 25px 0;
            border-top: 1px solid #eaeaea;
            margin-top: 25px;
        }
        
        .registration-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .registration-icon {
            font-size: 20px;
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            padding: 12px;
            border-radius: 50%;
        }
        
        .registration-text {
            display: flex;
            flex-direction: column;
        }
        
        .registration-question {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .registration-link {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        
        .registration-link:hover {
            color: #764ba2;
            gap: 10px;
        }
        
        .registration-link i {
            font-size: 12px;
            transition: transform 0.2s ease;
        }
        
        .registration-link:hover i {
            transform: translateX(4px);
        }
        
        /* Demo Accounts Section */
        .demo-section {
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
            padding: 25px;
            border-radius: 15px;
            margin-top: 35px;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }
        
        .demo-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .demo-icon {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 10px;
            display: block;
        }
        
        .demo-title {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 18px;
        }
        
        .demo-subtitle {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 0;
        }
        
        .demo-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .demo-card {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
            transition: transform 0.3s ease;
        }
        
        .demo-card:hover {
            transform: translateY(-5px);
        }
        
        .demo-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .demo-card-header i {
            font-size: 18px;
        }
        
        .admin-card .demo-card-header i {
            color: #ffc107;
        }
        
        .customer-card .demo-card-header i {
            color: #28a745;
        }
        
        .demo-card-title {
            font-weight: 600;
            color: #495057;
            font-size: 15px;
        }
        
        .demo-credential {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .credential-label {
            color: #6c757d;
            font-size: 13px;
            min-width: 70px;
        }
        
        .credential-value {
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #495057;
            border: 1px solid #e1e5e9;
        }
        
        .demo-note {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #e1e5e9;
            color: #6c757d;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .demo-security-notice {
            background: rgba(220, 53, 69, 0.05);
            border: 1px solid rgba(220, 53, 69, 0.2);
            border-radius: 8px;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: #dc3545;
        }
        
        .demo-security-notice i {
            font-size: 14px;
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .demo-cards {
                flex-direction: column;
            }
            
            .social-login-buttons {
                flex-direction: column;
            }
            
            .form-options-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .registration-container {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .registration-icon {
                margin-bottom: 5px;
            }
        }
        
        @media (max-width: 480px) {
            .form-header h2 {
                font-size: 20px;
            }
            
            .form-subtitle {
                font-size: 13px;
            }
            
            .modern-input {
                height: 50px;
                font-size: 14px;
            }
            
            .submit-button {
                height: 52px;
                font-size: 15px;
            }
            
            .login-box {
                padding: 35px 25px;
                max-width: 95%;
                margin: 20px;
            }
        }
    </style>
</head>
<body>

<!-- Background container -->
<div class="background-container"></div>

<!-- Login Wrapper -->
<div class="login-wrapper">
    <div class="login-box">
        <!-- Login Header -->
        <div class="login-header">
            <i class="fas fa-sign-in-alt"></i>
            <h1>Sign in to Speedy Wheels</h1>
            <p>Enter your credentials to access your account</p>
        </div>
        
        <!-- Error Message -->
        <?php if (!empty($error)): ?>
            <div class="alert-box alert-error">
                <div style="display: flex; align-items: center;">
                    <i class="fas fa-exclamation-triangle me-3" style="font-size: 20px;"></i>
                    <div>
                        <strong style="display: block; margin-bottom: 3px;">Error</strong>
                        <?= htmlspecialchars($error) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert-box alert-success">
                <div style="display: flex; align-items: center;">
                    <i class="fas fa-check-circle me-3" style="font-size: 20px;"></i>
                    <div>
                        <?= htmlspecialchars($_SESSION['success_message']) ?>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- PROFESSIONAL FORM -->
        <form method="POST" action="" id="loginForm" class="form-container">
            <!-- Form Header -->
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h2>Account Access</h2>
                <p class="form-subtitle">Enter your credentials to continue</p>
            </div>

            <!-- Username/Email Field -->
            <div class="form-field-group">
                <div class="field-label-container">
                    <label class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email or Username
                    </label>
                    <span class="required-indicator">*</span>
                </div>
                <div class="input-with-icon">
                    <i class="input-icon fas fa-user"></i>
                    <input
                        type="text"
                        name="username"
                        class="form-control modern-input"
                        placeholder="john.doe@example.com or johndoe"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                        autofocus
                        autocomplete="username"
                    >
                    <div class="input-focus-line"></div>
                </div>
                <div class="field-hint">
                    <i class="fas fa-info-circle"></i>
                    Enter your registered email address or username
                </div>
            </div>

            <!-- Password Field -->
            <div class="form-field-group">
                <div class="field-label-container">
                    <label class="form-label">
                        <i class="fas fa-key"></i>
                        Password
                    </label>
                    <span class="required-indicator">*</span>
                </div>
                <div class="input-with-icon password-field">
                    <i class="input-icon fas fa-lock"></i>
                    <input
                        type="password"
                        name="password"
                        class="form-control modern-input"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                        id="passwordInput"
                    >
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                    <div class="input-focus-line"></div>
                </div>
                <div class="field-hint">
                    <i class="fas fa-info-circle"></i>
                    Minimum 8 characters with letters and numbers
                </div>
            </div>

            <!-- Options Row -->
            <div class="form-options-row">
                <div class="checkbox-container">
                    <label class="modern-checkbox">
                        <input type="checkbox" id="rememberMe" name="rememberMe">
                        <span class="checkmark"></span>
                        <span class="checkbox-label">Keep me signed in</span>
                    </label>
                </div>
                <a href="#" class="forgot-password-link">
                    <i class="fas fa-question-circle"></i>
                    Trouble signing in?
                </a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">
                <span class="button-content">
                    <i class="fas fa-sign-in-alt"></i>
                    <span class="button-text">Sign In to Dashboard</span>
                </span>
                <div class="button-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </button>

            <!-- Divider -->
            <div class="divider-with-text">
                <span class="divider-line"></span>
                <span class="divider-text">or continue with</span>
                <span class="divider-line"></span>
            </div>

            <!-- Social Login Buttons -->
            <div class="social-login-buttons">
                <button type="button" class="social-button google-button">
                    <i class="fab fa-google"></i>
                    <span>Google</span>
                </button>
                <button type="button" class="social-button microsoft-button">
                    <i class="fab fa-microsoft"></i>
                    <span>Microsoft</span>
                </button>
            </div>

            <!-- Registration Link -->
            <div class="registration-section">
                <div class="registration-container">
                    <i class="fas fa-user-plus registration-icon"></i>
                    <div class="registration-text">
                        <span class="registration-question">New to Speedy Wheels?</span>
                        <a href="<?= base_url('src/modules/auth/register.php'); ?>" class="registration-link">
                            Create an account
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Demo Accounts Section -->
            <div class="demo-section">
                <div class="demo-header">
                    <i class="fas fa-vial demo-icon"></i>
                    <h3 class="demo-title">Test Accounts</h3>
                    <p class="demo-subtitle">Use these credentials for testing</p>
                </div>
                
                <div class="demo-cards">
                    <div class="demo-card admin-card">
                        <div class="demo-card-header">
                            <i class="fas fa-crown"></i>
                            <span class="demo-card-title">Administrator</span>
                        </div>
                        <div class="demo-card-content">
                            <div class="demo-credential">
                                <span class="credential-label">Username:</span>
                                <code class="credential-value">admin</code>
                            </div>
                            <div class="demo-credential">
                                <span class="credential-label">Password:</span>
                                <code class="credential-value">admin123</code>
                            </div>
                            <div class="demo-note">
                                <i class="fas fa-shield-alt"></i>
                                Full system access
                            </div>
                        </div>
                    </div>
                    
                    <div class="demo-card customer-card">
                        <div class="demo-card-header">
                            <i class="fas fa-user-tie"></i>
                            <span class="demo-card-title">Customer</span>
                        </div>
                        <div class="demo-card-content">
                            <div class="demo-credential">
                                <span class="credential-label">Username:</span>
                                <code class="credential-value">john_doe</code>
                            </div>
                            <div class="demo-credential">
                                <span class="credential-label">Password:</span>
                                <code class="credential-value">customer123</code>
                            </div>
                            <div class="demo-note">
                                <i class="fas fa-car"></i>
                                Booking & rental access
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="demo-security-notice">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>These are demo accounts. For production, use strong, unique passwords.</span>
                </div>
            </div>
        </form>
        <!-- END OF PROFESSIONAL FORM -->
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Password visibility toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    }
    
    // Form submission loading state
    const loginForm = document.getElementById('loginForm');
    const submitButton = loginForm.querySelector('.submit-button');
    const buttonContent = submitButton.querySelector('.button-content');
    const buttonLoading = submitButton.querySelector('.button-loading');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = this.querySelector('[name="username"]').value.trim();
            const password = this.querySelector('[name="password"]').value;
            
            if (!username || !password) {
                e.preventDefault();
                showError('Please fill in both fields');
                return false;
            }
            
            // Show loading state
            buttonContent.style.opacity = '0';
            buttonLoading.style.opacity = '1';
            submitButton.disabled = true;
            
            return true;
        });
    }
    
    function showError(message) {
        // Create error alert if not exists
        let errorAlert = document.querySelector('.alert-error');
        if (!errorAlert) {
            const formHeader = document.querySelector('.form-header');
            errorAlert = document.createElement('div');
            errorAlert.className = 'alert-box alert-error';
            errorAlert.innerHTML = `
                <div style="display: flex; align-items: center;">
                    <i class="fas fa-exclamation-triangle me-3" style="font-size: 20px;"></i>
                    <div>
                        <strong style="display: block; margin-bottom: 3px;">Error</strong>
                        ${message}
                    </div>
                </div>
            `;
            formHeader.parentNode.insertBefore(errorAlert, formHeader.nextSibling);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                errorAlert.remove();
            }, 5000);
        }
    }
    
    // Add focus effects
    const inputs = document.querySelectorAll('.modern-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
    
    // Social button handlers
    document.querySelectorAll('.social-button').forEach(button => {
        button.addEventListener('click', function() {
            const provider = this.classList.contains('google-button') ? 'Google' : 'Microsoft';
            showError(`${provider} login is not yet implemented. Please use the form above.`);
        });
    });
</script>

</body>
</html>

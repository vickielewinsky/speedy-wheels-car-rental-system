<?php
// src/modules/auth/login.php

// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Turn off error display for users (but log them)
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once "../../config/database.php";
require_once "../../includes/auth.php";
require_once "Auth.php";

// Redirect if already logged in
if (isAuthenticated()) {
    header("Location: " . base_url('index.php'));
    exit;
}

$page_title = "Sign in to Speedy Wheels";
ob_start(); // Start output buffering to catch any errors
require_once "../../includes/header.php";
ob_end_clean(); // Discard any output

$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                    header("Location: " . base_url('index.php'));
                    exit;
                } else {
                    $error = "Invalid username or password.";
                }
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $error = "An error occurred during login. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to Speedy Wheels</title>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================
           EXACT MYHIRE.CO.KE LOGIN FORM REPLICA
           Clean, modern, professional design
        ============================================ */
        
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border: #d1d5db;
            --border-focus: #2563eb;
            --background: #ffffff;
            --radius: 8px;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-dark);
            line-height: 1.5;
        }
        
        /* Background container */
        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -3;
            overflow: hidden;
        }
        
        <?php 
        // Get background image
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
        
        if (empty($image_url)) {
            $local_path = __DIR__ . '/../../../src/assets/images/hero-car.png';
            if (file_exists($local_path)) {
                $image_url = base_url('src/assets/images/hero-car.png');
            }
        }
        
        if (!empty($image_url)): ?>
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
        
        /* Main Login Container */
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.4s ease-out;
        }
        
        /* Login Card - Exact myhire style */
        .login-card {
            background: var(--background);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px;
            border: 1px solid var(--border);
        }
        
        /* Header */
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        
        .login-header p {
            font-size: 14px;
            color: var(--text-light);
        }
        
        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 6px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background: var(--background);
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .form-control::placeholder {
            color: #9ca3af;
        }
        
        /* Remember Me */
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .remember-me input {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            cursor: pointer;
        }
        
        .remember-me label {
            font-size: 14px;
            color: var(--text-dark);
            cursor: pointer;
        }
        
        /* Login Button */
        .login-button {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 15px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.2s ease;
            margin-bottom: 24px;
        }
        
        .login-button:hover {
            background: var(--primary-hover);
        }
        
        .login-button:active {
            transform: translateY(1px);
        }
        
        /* Divider */
        .divider {
            text-align: center;
            position: relative;
            margin: 24px 0;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border);
        }
        
        .divider span {
            background: var(--background);
            padding: 0 16px;
            position: relative;
        }
        
        /* Social Buttons */
        .social-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .social-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--background);
            color: var(--text-dark);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .social-button:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }
        
        .social-button.facebook {
            color: #1877f2;
        }
        
        .social-button.google {
            color: #ea4335;
        }
        
        .social-button i {
            font-size: 18px;
        }
        
        /* Register Link */
        .register-section {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            margin-top: 24px;
        }
        
        .register-section p {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 8px;
        }
        
        .register-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.2s ease;
        }
        
        .register-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
        
        /* Demo Accounts */
        .demo-accounts {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            margin-top: 24px;
        }
        
        .demo-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .demo-title i {
            color: var(--primary);
        }
        
        .demo-account {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }
        
        .demo-account:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .account-type {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 4px;
        }
        
        .account-details {
            font-size: 12px;
            color: var(--text-light);
        }
        
        .account-details strong {
            color: var(--text-dark);
            font-weight: 500;
        }
        
        /* Error Message */
        .error-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: var(--radius);
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #dc2626;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }
        
        .error-message i {
            font-size: 16px;
        }
        
        /* Success Message */
        .success-message {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            border-radius: var(--radius);
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #059669;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }
        
        .success-message i {
            font-size: 16px;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 480px) {
            body {
                padding: 16px;
                background: white;
            }
            
            .login-card {
                padding: 32px 24px;
                box-shadow: none;
                border: none;
                background: transparent;
            }
            
            .login-wrapper {
                max-width: 100%;
            }
            
            .background-container {
                display: none;
            }
            
            body {
                background: white;
            }
        }
    </style>
</head>
<body>

<!-- Background container -->
<div class="background-container"></div>

<div class="login-wrapper">
    <div class="login-card">
        <!-- Header -->
        <div class="login-header">
            <h1>Sign in to your account</h1>
            <p>Enter your credentials to continue</p>
        </div>
        
        <!-- Error Message -->
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['success_message']) ?></span>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="loginForm">
            <!-- Email or Username -->
            <div class="form-group">
                <label class="form-label">Email or Username</label>
                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Enter your email or username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    required
                    autofocus
                >
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label">Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <!-- Remember Me -->
            <div class="remember-me">
                <input type="checkbox" id="rememberMe" name="rememberMe">
                <label for="rememberMe">Remember me</label>
            </div>

            <!-- Login Button -->
            <button type="submit" class="login-button">
                Log in
            </button>
        </form>

        <!-- Divider -->
        <div class="divider">
            <span>or sign in with</span>
        </div>

        <!-- Social Login Buttons -->
        <div class="social-buttons">
            <a href="#" class="social-button facebook">
                <i class="fab fa-facebook-f"></i>
                Continue with Facebook
            </a>
            <a href="#" class="social-button google">
                <i class="fab fa-google"></i>
                Log in with Google
            </a>
        </div>

        <!-- Register Link -->
        <div class="register-section">
            <p>Don't have an account?</p>
            <a href="<?= base_url('src/modules/auth/register.php'); ?>" class="register-link">
                Create an account
            </a>
        </div>

        <!-- Demo Accounts -->
        <div class="demo-accounts">
            <div class="demo-title">
                <i class="fas fa-info-circle"></i>
                <span>Demo Accounts</span>
            </div>
            <div class="demo-account">
                <div class="account-type">Admin Account</div>
                <div class="account-details">
                    <strong>Username:</strong> admin &nbsp;|&nbsp;
                    <strong>Password:</strong> admin123
                </div>
            </div>
            <div class="demo-account">
                <div class="account-type">Customer Account</div>
                <div class="account-details">
                    <strong>Username:</strong> john_doe &nbsp;|&nbsp;
                    <strong>Password:</strong> customer123
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Form validation
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const username = this.querySelector('[name="username"]').value.trim();
        const password = this.querySelector('[name="password"]').value;
        const submitBtn = this.querySelector('.login-button');
        
        if (!username || !password) {
            e.preventDefault();
            
            // Highlight empty fields
            const inputs = this.querySelectorAll('.form-control');
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = '#dc2626';
                    input.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
                    
                    setTimeout(() => {
                        input.style.borderColor = '';
                        input.style.boxShadow = '';
                    }, 2000);
                }
            });
            
            return false;
        }
        
        // Add loading state
        submitBtn.innerHTML = 'Logging in...';
        submitBtn.style.opacity = '0.7';
        submitBtn.disabled = true;
        
        return true;
    });
    
    // Input focus effects
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-1px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
    
    // Social button hover effects
    const socialButtons = document.querySelectorAll('.social-button');
    socialButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
</script>

</body>
</html>
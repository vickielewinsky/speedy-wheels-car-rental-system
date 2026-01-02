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
            --success: #10b981;
            --success-bg: #d1fae5;
            --success-border: #a7f3d0;
            --info: #3b82f6;
            --info-bg: #dbeafe;
            --info-border: #bfdbfe;
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

        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -3;
            overflow: hidden;
        }

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
            width: 100%;
            max-width: 440px;
            animation: fadeIn 0.4s ease-out;
        }

        /* Login Card */
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
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
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
            transition: all 0.2s ease;
            margin-top: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-button:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .login-button:active {
            transform: translateY(0);
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
            background: var(--success-bg);
            border: 1px solid var(--success-border);
            border-radius: var(--radius);
            padding: 12px 16px;
            margin-bottom: 20px;
            color: var(--success);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        .success-message i {
            font-size: 16px;
        }

        /* Email/Info Message */
        .email-message {
            background: var(--info-bg);
            border: 1px solid var(--info-border);
            border-radius: var(--radius);
            padding: 12px 16px;
            margin-bottom: 20px;
            color: var(--info);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        .email-message i {
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
        @media (max-width: 576px) {
            body {
                padding: 16px;
                background: white;
            }

            .login-card {
                padding: 30px 20px;
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

        @media (max-width: 768px) {
            .login-card {
                padding: 30px;
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
            <h1>
                <i class="fas fa-sign-in-alt"></i>
                Sign in to Speedy Wheels
            </h1>
            <p>Enter your credentials to access your account</p>
        </div>
        
        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Success Message from Registration -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <!-- Email Message from Registration -->
        <?php if (!empty($_SESSION['email_message'])): ?>
            <div class="email-message">
                <i class="fas fa-envelope"></i>
                <span><?php echo htmlspecialchars($_SESSION['email_message']); ?></span>
            </div>
            <?php unset($_SESSION['email_message']); ?>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="loginForm">
            <!-- Username -->
            <div class="form-group">
                <label class="form-label">Username or Email</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="username" 
                    name="username"
                    placeholder="Enter your username or email"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                    required
                >
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label">Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <!-- Submit Button -->
            <button type="submit" class="login-button">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <!-- Register Link -->
        <div class="register-section">
            <p>Don't have an account?</p>
            <a href="<?= base_url('src/modules/auth/register.php'); ?>" class="register-link">
                Create an account
            </a>
        </div>
    </div>
</div>

<script>
    // Form validation
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const username = this.querySelector('#username');
        const password = this.querySelector('#password');
        const submitBtn = this.querySelector('.login-button');
        
        // Check if fields are filled
        if (!username.value.trim() || !password.value.trim()) {
            e.preventDefault();
            
            if (!username.value.trim()) {
                username.style.borderColor = '#dc2626';
                username.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
                setTimeout(() => {
                    username.style.borderColor = '';
                    username.style.boxShadow = '';
                }, 2000);
            }
            
            if (!password.value.trim()) {
                password.style.borderColor = '#dc2626';
                password.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
                setTimeout(() => {
                    password.style.borderColor = '';
                    password.style.boxShadow = '';
                }, 2000);
            }
            
            return false;
        }
        
        // Add loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
        submitBtn.style.opacity = '0.7';
        submitBtn.disabled = true;
        
        return true;
    });

    // Input focus effects
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.transform = 'translateY(-1px)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
        });
        
        input.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });

    // Auto-focus username field
    document.getElementById('username')?.focus();
</script>

</body>
</html>

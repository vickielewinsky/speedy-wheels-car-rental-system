<?php
// src/modules/auth/register.php - MODIFIED VERSION

// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
require_once "../../config/database.php";

// Function to check if email exists
function emailExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}

// Function to check if username exists
function usernameExists($pdo, $username) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch() !== false;
}

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $user_data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? '')
    ];
    
    // Validation
    $required = ['username', 'email', 'password', 'first_name', 'last_name'];
    foreach ($required as $field) {
        if (empty($user_data[$field])) {
            $error = "All fields marked with * are required";
            break;
        }
    }
    
    if (!$error && !filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    }
    
    if (!$error && strlen($user_data['password']) < 6) {
        $error = "Password must be at least 6 characters";
    }
    
    if (!$error && usernameExists($pdo, $user_data['username'])) {
        $error = "Username already exists";
    }
    
    if (!$error && emailExists($pdo, $user_data['email'])) {
        $error = "Email already registered";
    }
    
    // Proceed with registration
    if (!$error) {
        try {
            // Hash password
            $password_hash = password_hash($user_data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, first_name, last_name, phone, user_role, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'user', 1, NOW())
            ");
            
            $stmt->execute([
                $user_data['username'],
                $user_data['email'],
                $password_hash,
                $user_data['first_name'],
                $user_data['last_name'],
                !empty($user_data['phone']) ? $user_data['phone'] : null
            ]);
            
            $_SESSION['success_message'] = "Registration successful! You can now login.";
            header("Location: login.php");
            exit();
            
        } catch (PDOException $e) {
            $error = "Registration failed. Please try again.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}

// Simple base_url function
function base_url($path = '') {
    $base = 'http://' . $_SERVER['HTTP_HOST'];
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    
    if ($script_dir !== '/') {
        $base .= $script_dir;
    }
    
    $base = rtrim($base, '/');
    $path = ltrim($path, '/');
    
    return $base . '/' . $path;
}

// Get background image
$image_url = '';
$possible_paths = [
    'src/assets/images/hero-car.png',
    'assets/images/hero-car.png',
    'images/hero-car.png'
];

foreach ($possible_paths as $path) {
    $full_path = __DIR__ . '/../../' . $path;
    if (file_exists($full_path)) {
        $image_url = base_url($path);
        break;
    }
}
?>

<!-- YOUR EXISTING HTML AND CSS CODE STARTS HERE -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Speedy Wheels</title>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================
           PROFESSIONAL REGISTER FORM
           Consistent with login design
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
            --success-hover: #059669;
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
        
        /* Main Register Container */
        .register-wrapper {
            width: 100%;
            max-width: 500px;
            animation: fadeIn 0.4s ease-out;
        }
        
        /* Register Card */
        .register-card {
            background: var(--background);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px;
            border: 1px solid var(--border);
        }
        
        /* Header */
        .register-header {
            text-align: center;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }
        
        .register-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .register-header p {
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
        
        .form-label .required {
            color: #ef4444;
            margin-left: 2px;
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
        
        .form-text {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 4px;
            display: block;
        }
        
        /* Two Column Layout */
        .row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .col {
            flex: 1;
        }
        
        /* Register Button */
        .register-button {
            width: 100%;
            padding: 14px;
            background: var(--success);
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
        
        .register-button:hover {
            background: var(--success-hover);
            transform: translateY(-1px);
        }
        
        .register-button:active {
            transform: translateY(0);
        }
        
        /* Login Link */
        .login-section {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            margin-top: 24px;
        }
        
        .login-section p {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 8px;
        }
        
        .login-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.2s ease;
        }
        
        .login-link:hover {
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
        @media (max-width: 576px) {
            body {
                padding: 16px;
                background: white;
            }
            
            .register-card {
                padding: 30px 20px;
                box-shadow: none;
                border: none;
                background: transparent;
            }
            
            .register-wrapper {
                max-width: 100%;
            }
            
            .background-container {
                display: none;
            }
            
            body {
                background: white;
            }
            
            .row {
                flex-direction: column;
                gap: 0;
                margin-bottom: 0;
            }
            
            .col {
                margin-bottom: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .register-card {
                padding: 30px;
            }
        }
    </style>
</head>
<body>

<!-- Background container -->
<div class="background-container"></div>

<div class="register-wrapper">
    <div class="register-card">
        <!-- Header -->
        <div class="register-header">
            <h1>
                <i class="fas fa-user-plus"></i>
                Create Account
            </h1>
            <p>Register for Speedy Wheels</p>
        </div>
        
        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Success Message -->
        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Success Message from Session -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <!-- Register Form -->
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="registerForm">
            <!-- Name Fields -->
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label class="form-label">First Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="first_name" 
                            name="first_name"
                            placeholder="Enter your first name"
                            value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" 
                            required
                        >
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="form-label">Last Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="last_name" 
                            name="last_name"
                            placeholder="Enter your last name"
                            value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" 
                            required
                        >
                    </div>
                </div>
            </div>
            
            <!-- Username -->
            <div class="form-group">
                <label class="form-label">Username <span class="required">*</span></label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="username" 
                    name="username"
                    placeholder="Choose a username"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                    required
                >
                <span class="form-text">Choose a unique username</span>
            </div>
            
            <!-- Email -->
            <div class="form-group">
                <label class="form-label">Email <span class="required">*</span></label>
                <input 
                    type="email" 
                    class="form-control" 
                    id="email" 
                    name="email"
                    placeholder="Enter your email address"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                    required
                >
            </div>
            
            <!-- Phone Number -->
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input 
                    type="tel" 
                    class="form-control" 
                    id="phone" 
                    name="phone"
                    placeholder="2547XXXXXXXX"
                    value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                >
                <span class="form-text">Optional - format: 2547XXXXXXXX</span>
            </div>
            
            <!-- Password -->
            <div class="form-group">
                <label class="form-label">Password <span class="required">*</span></label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password"
                    placeholder="Create a password"
                    required
                >
                <span class="form-text">Minimum 6 characters</span>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="register-button">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <!-- Login Link -->
        <div class="login-section">
            <p>Already have an account?</p>
            <a href="<?= base_url('src/modules/auth/login.php'); ?>" class="login-link">
                Login here
            </a>
        </div>
    </div>
</div>

<script>
    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;
        const submitBtn = this.querySelector('.register-button');
        
        // Check required fields
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = '#dc2626';
                field.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
                
                setTimeout(() => {
                    field.style.borderColor = '';
                    field.style.boxShadow = '';
                }, 2000);
            }
        });
        
        // Validate email format
        const emailField = this.querySelector('#email');
        if (emailField.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailField.value)) {
                isValid = false;
                emailField.style.borderColor = '#dc2626';
                emailField.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
                
                setTimeout(() => {
                    emailField.style.borderColor = '';
                    emailField.style.boxShadow = '';
                }, 2000);
            }
        }
        
        // Validate password length
        const passwordField = this.querySelector('#password');
        if (passwordField.value.trim() && passwordField.value.length < 6) {
            isValid = false;
            passwordField.style.borderColor = '#dc2626';
            passwordField.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
            
            setTimeout(() => {
                passwordField.style.borderColor = '';
                passwordField.style.boxShadow = '';
            }, 2000);
        }
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        
        // Add loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
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
    
    // Real-time validation for password
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            if (this.value.length < 6 && this.value.length > 0) {
                this.style.borderColor = '#f59e0b';
                this.nextElementSibling.style.color = '#f59e0b';
                this.nextElementSibling.textContent = 'Password should be at least 6 characters';
            } else if (this.value.length >= 6) {
                this.style.borderColor = '#10b981';
                this.nextElementSibling.style.color = '#10b981';
                this.nextElementSibling.textContent = 'Password is strong ✓';
            } else {
                this.style.borderColor = '';
                this.nextElementSibling.style.color = '';
                this.nextElementSibling.textContent = 'Minimum 6 characters';
            }
        });
    }
</script>

</body>
</html>
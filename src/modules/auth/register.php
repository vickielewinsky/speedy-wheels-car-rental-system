<?php
// Speedy Wheels Car Rental System
// src/modules/auth/register.php - FIXED VERSION

// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Include database connection
require_once __DIR__ . '/../../config/database.php';

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

function emailExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}

function usernameExists($pdo, $username) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch() !== false;
}

// FIXED EMAIL SENDING FUNCTION
function sendRegistrationEmail($email, $name, $username) {
    try {
        // Get project root directory (3 levels up from src/modules/auth)
        $root_dir = dirname(__DIR__, 3);
        
        // Load PHPMailer
        require_once $root_dir . '/vendor/autoload.php';
        
        // Load email config
        require_once $root_dir . '/src/config/email_config.php';
        
        // Create PHPMailer instance
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = EmailConfig::SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = EmailConfig::SMTP_USERNAME;
        $mail->Password   = EmailConfig::SMTP_PASSWORD;
        $mail->SMTPSecure = EmailConfig::SMTP_SECURE;
        $mail->Port       = EmailConfig::SMTP_PORT;
        
        // Enable debugging to log file
        $mail->SMTPDebug = 0; // Set to 2 for debugging
        $mail->Debugoutput = function($str, $level) {
            file_put_contents(
                dirname(__DIR__, 3) . '/logs/email_debug.log',
                date('Y-m-d H:i:s') . " [$level] $str\n",
                FILE_APPEND
            );
        };
        
        // Timeout settings
        $mail->Timeout = 30;
        
        // Sender
        $mail->setFrom(EmailConfig::FROM_EMAIL, EmailConfig::FROM_NAME);
        
        // Recipient
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Account Created Successfully - Speedy Wheels Car Rental';
        
        // HTML email body
        $html_body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
                .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 30px; background: #f9f9f9; border-radius: 0 0 10px 10px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; border-top: 1px solid #ddd; margin-top: 20px; }
                .btn { display: inline-block; background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 15px 0; }
                .details { background: white; padding: 20px; border-radius: 5px; border-left: 4px solid #007bff; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🚗 Speedy Wheels Car Rental</h1>
                <h2>Welcome to Our Family!</h2>
            </div>
            
            <div class="content">
                <p>Dear <strong>' . htmlspecialchars($name) . '</strong>,</p>
                
                <p>✅ <strong>Congratulations! Your account has been successfully created.</strong></p>
                
                <div class="details">
                    <h3 style="margin-top: 0; color: #007bff;">Your Account Details:</h3>
                    <p><strong>👤 Username:</strong> ' . htmlspecialchars($username) . '</p>
                    <p><strong>📧 Email:</strong> ' . htmlspecialchars($email) . '</p>
                    <p><strong>📅 Account Created:</strong> ' . date('F j, Y') . '</p>
                    <p><strong>⏰ Time:</strong> ' . date('g:i A') . '</p>
                </div>
                
                <p>You can now access all features of Speedy Wheels:</p>
                <ul>
                    <li>🚗 Browse and book available vehicles</li>
                    <li>📋 Manage your bookings</li>
                    <li>💳 Make secure payments via MPESA</li>
                    <li>📊 Track your rental history</li>
                    <li>🛟 24/7 customer support</li>
                </ul>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . base_url('src/modules/auth/login.php') . '" class="btn">
                        🔑 Login to Your Account
                    </a>
                </div>
                
                <p><strong>Need Help?</strong></p>
                <p>Our support team is here to assist you:</p>
                <ul>
                    <li><strong>📞 Phone:</strong> 254712345678</li>
                    <li><strong>📧 Email:</strong> support@speedywheels.com</li>
                    <li><strong>🕐 Hours:</strong> Monday - Sunday, 7:00 AM - 10:00 PM</li>
                </ul>
                
                <p style="color: #666; font-size: 12px; border-top: 1px solid #eee; padding-top: 15px; margin-top: 25px;">
                    <em>This is an automated message. Please do not reply to this email.</em>
                </p>
            </div>
            
            <div class="footer">
                <p>Speedy Wheels Car Rental &copy; ' . date('Y') . '</p>
                <p>Mombasa, Kenya | Making Car Rental Easy & Convenient</p>
            </div>
        </body>
        </html>';
        
        $mail->Body = $html_body;
        
        // Plain text version
        $plain_text = "ACCOUNT CREATION SUCCESSFUL - SPEEDY WHEELS CAR RENTAL\n\n";
        $plain_text .= "Dear " . $name . ",\n\n";
        $plain_text .= "Congratulations! Your account has been successfully created.\n\n";
        $plain_text .= "ACCOUNT DETAILS:\n";
        $plain_text .= "Username: " . $username . "\n";
        $plain_text .= "Email: " . $email . "\n";
        $plain_text .= "Account Created: " . date('F j, Y') . " at " . date('g:i A') . "\n\n";
        $plain_text .= "You can now login to your account and start booking vehicles.\n\n";
        $plain_text .= "Login URL: " . base_url('src/modules/auth/login.php') . "\n\n";
        $plain_text .= "Need help? Contact us:\n";
        $plain_text .= "Phone: 254712345678\n";
        $plain_text .= "Email: support@speedywheels.com\n\n";
        $plain_text .= "Speedy Wheels Car Rental © " . date('Y') . "\n";
        $plain_text .= "This is an automated message. Please do not reply.\n";
        
        $mail->AltBody = $plain_text;
        
        // Send email
        if ($mail->send()) {
            error_log("✅ Registration email sent to: " . $email);
            return true;
        } else {
            error_log("❌ Failed to send registration email to " . $email . ": " . $mail->ErrorInfo);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("💥 Email sending error for " . $email . ": " . $e->getMessage());
        return false;
    }
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
            // Start transaction
            $pdo->beginTransaction();
            
            // Hash password
            $password_hash = password_hash($user_data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, first_name, last_name, phone, user_role, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'customer', 1, NOW())
            ");
            
            $stmt->execute([
                $user_data['username'],
                $user_data['email'],
                $password_hash,
                $user_data['first_name'],
                $user_data['last_name'],
                !empty($user_data['phone']) ? $user_data['phone'] : null
            ]);
            
            $user_id = $pdo->lastInsertId();
            
            // Commit transaction
            $pdo->commit();
            
            // Try to send registration confirmation email
            $full_name = $user_data['first_name'] . ' ' . $user_data['last_name'];
            $email_sent = sendRegistrationEmail($user_data['email'], $full_name, $user_data['username']);
            
            // Store messages in session
            $_SESSION['success_message'] = "✅ Registration successful! You can now login.";
            
            if ($email_sent) {
                $_SESSION['email_message'] = "📧 A confirmation email has been sent to " . $user_data['email'];
            } else {
                $_SESSION['email_message'] = "⚠️ Registration successful, but confirmation email could not be sent.";
                // Log detailed error
                error_log("Email failed for user: " . $user_data['email']);
            }
            
            // Redirect to login
            header("Location: login.php");
            exit();
            
        } catch (PDOException $e) {
            // Rollback on error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Registration failed. Please try again.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}

// Get background image
$image_url = '';
$possible_paths = [
    'src/assets/images/hero-car.png',
    'assets/images/hero-car.png',
    'images/hero-car.png'
];

foreach ($possible_paths as $path) {
    $full_path = dirname(__DIR__, 2) . '/' . $path;
    if (file_exists($full_path)) {
        $image_url = base_url($path);
        break;
    }
}
?>

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
        
        .register-wrapper {
            width: 100%;
            max-width: 500px;
            animation: fadeIn 0.4s ease-out;
        }
        
        .register-card {
            background: var(--background);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px;
            border: 1px solid var(--border);
        }
        
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
        
        .row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .col {
            flex: 1;
        }
        
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
        
        .email-notice {
            background: #dbeafe;
            border: 1px solid #bfdbfe;
            border-radius: var(--radius);
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #1d4ed8;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }
        
        .email-notice i {
            font-size: 16px;
        }
        
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
            <p>Join Speedy Wheels and start booking vehicles</p>
        </div>
        
        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Email Notice -->
        <div class="email-notice">
            <i class="fas fa-envelope"></i>
            <span>📧 You will receive a confirmation email after registration</span>
        </div>
        
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
                <span class="form-text">This will be your login username</span>
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
                <span class="form-text">We'll send your account confirmation to this email</span>
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
                <span class="form-text">Optional - Kenyan format: 2547XXXXXXXX</span>
            </div>
            
            <!-- Password -->
            <div class="form-group">
                <label class="form-label">Password <span class="required">*</span></label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password"
                    placeholder="Create a strong password"
                    required
                >
                <span class="form-text">Minimum 6 characters</span>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="register-button">
                <i class="fas fa-user-plus"></i> Create Account & Get Confirmation Email
            </button>
        </form>
        
        <!-- Login Link -->
        <div class="login-section">
            <p>Already have an account?</p>
            <a href="<?= base_url('src/modules/auth/login.php'); ?>" class="login-link">
                <i class="fas fa-sign-in-alt"></i> Login here
            </a>
        </div>
    </div>
</div>

<script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('.register-button');
        
        // Validate email format
        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Please enter a valid email address');
            return false;
        }
        
        // Validate password length
        const password = document.getElementById('password').value;
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters');
            return false;
        }
        
        // Add loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account & Sending Email...';
        submitBtn.style.opacity = '0.7';
        submitBtn.disabled = true;
        
        return true;
    });
    
    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
        const strengthText = this.nextElementSibling;
        const length = this.value.length;
        
        if (length === 0) {
            strengthText.textContent = 'Minimum 6 characters';
            strengthText.style.color = '';
        } else if (length < 6) {
            strengthText.textContent = 'Too short - need at least 6 characters';
            strengthText.style.color = '#dc2626';
        } else if (length < 8) {
            strengthText.textContent = 'Fair';
            strengthText.style.color = '#f59e0b';
        } else if (length < 10) {
            strengthText.textContent = 'Good';
            strengthText.style.color = '#10b981';
        } else {
            strengthText.textContent = 'Strong ✓';
            strengthText.style.color = '#059669';
        }
    });
</script>

</body>
</html>
<?php
//

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include path config
$config_path = __DIR__ . '/../../config/path_config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    // Fallback base_url function
    if (!function_exists('base_url')) {
        function base_url($path = '') {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            
            // For PHP built-in server
            $script_dir = dirname($_SERVER['SCRIPT_NAME']);
            if ($script_dir === '/' || $script_dir === '\\') {
                return $protocol . $host . '/' . ltrim($path, '/');
            } else {
                return $protocol . $host . $script_dir . '/' . ltrim($path, '/');
            }
        }
    }
}

// App constants
if (!defined('APP_NAME')) define('APP_NAME', 'Speedy Wheels Car Rental');

// Default page title
if (!isset($page_title)) {
    $page_title = APP_NAME;
}

// Check user login
$is_logged_in = false;
$is_admin = false;

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $is_logged_in = true;
    
    // Check if user is admin
    if (isset($_SESSION['user_role']) || isset($_SESSION['role'])) {
        $user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : $_SESSION['role'];
        $is_admin = (strtolower($user_role) === 'admin' || strtolower($user_role) === 'superadmin');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS - USING ABSOLUTE PATHS -->
    <link href="/speedy-wheels-car-rental-system/src/assets/css/main.css" rel="stylesheet">
    <link href="/speedy-wheels-car-rental-system/src/assets/css/components/auth.css" rel="stylesheet">
    <link href="/speedy-wheels-car-rental-system/src/assets/css/components/buttons.css" rel="stylesheet">
    <link href="/speedy-wheels-car-rental-system/src/assets/css/components/cards.css" rel="stylesheet">
    <link href="/speedy-wheels-car-rental-system/src/assets/css/components/forms.css" rel="stylesheet">
    <link href="/speedy-wheels-car-rental-system/src/assets/css/components/tables.css" rel="stylesheet">

    <!-- Inline CSS for immediate styling -->
    <style>
        body { 
            background: #f8f9fa; 
            padding-top: 80px; 
            min-height: 100vh; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .custom-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 12px 0;
        }
        
        .brand-logo {
            font-size: 1.8rem;
            color: white;
            font-weight: bold;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            padding: 8px 16px !important;
            margin: 0 4px;
            border-radius: 25px;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .nav-link.active {
            background: rgba(255,255,255,0.2);
        }
        
        @media (max-width:768px) { 
            body { 
                padding-top: 70px; 
            } 
        }
    </style>
</head>
<body>

<!-- SIMPLIFIED NAVBAR FOR NOW -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top custom-navbar">
    <div class="container">
        <a class="navbar-brand brand-logo" href="/speedy-wheels-car-rental-system/">
            <i class="fas fa-car me-2"></i>Speedy Wheels
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="/speedy-wheels-car-rental-system/">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="/speedy-wheels-car-rental-system/about.php">
                        <i class="fas fa-info-circle me-1"></i> About
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="/speedy-wheels-car-rental-system/contact.php">
                        <i class="fas fa-phone me-1"></i> Contact
                    </a>
                </li>
                
                <?php if (!$is_logged_in): ?>
                    <li class="nav-item ms-2">
                        <a class="btn btn-success" href="/speedy-wheels-car-rental-system/src/modules/auth/login.php">
                            <i class="fas fa-user me-1"></i> Sign In
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary" href="/speedy-wheels-car-rental-system/src/modules/auth/dashboard.php">
                            <i class="fas fa-user-circle me-1"></i> Dashboard
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Main content -->
<div class="container-fluid p-0">
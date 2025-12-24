<?php
// src/includes/header.php - FIXED VERSION

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base_url if not already defined
if (!function_exists('base_url')) {
    function base_url($path = '') {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $project_path = dirname(dirname(dirname(__FILE__)));
        $project_name = basename(dirname(dirname($project_path)));
        
        // If running in localhost with project folder
        if ($host == 'localhost') {
            return $protocol . '://' . $host . '/' . $project_name . '/' . ltrim($path, '/');
        }
        
        // For production without project folder
        return $protocol . '://' . $host . '/' . ltrim($path, '/');
    }
}

// Include helpers
$helpers_dir = __DIR__ . '/../helpers';
if (file_exists($helpers_dir . '/url_helper.php')) {
    require_once $helpers_dir . '/url_helper.php';
}

// Include auth helper if exists
$auth_file = __DIR__ . '/auth.php';
if (file_exists($auth_file)) {
    require_once $auth_file;
}

// App constants
if (!defined('APP_NAME')) define('APP_NAME', 'Speedy Wheels Car Rental');

// Default page title
if (!isset($page_title)) {
    $page_title = APP_NAME;
}

// Check user login - FIXED SESSION CHECK
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

// Debug info (remove in production)
// error_log("Header Debug: is_logged_in=" . ($is_logged_in ? 'true' : 'false') . ", is_admin=" . ($is_admin ? 'true' : 'false'));
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
    
    <!-- Custom CSS -->
    <link href="<?php echo base_url('src/assets/css/main.css'); ?>" rel="stylesheet">
    
    <!-- Auth CSS -->
    <link href="<?php echo base_url('src/assets/css/components/auth.css'); ?>" rel="stylesheet">
    
    <!-- Additional component CSS -->
    <link href="<?php echo base_url('src/assets/css/components/buttons.css'); ?>" rel="stylesheet">
    <link href="<?php echo base_url('src/assets/css/components/cards.css'); ?>" rel="stylesheet">
    <link href="<?php echo base_url('src/assets/css/components/forms.css'); ?>" rel="stylesheet">
    <link href="<?php echo base_url('src/assets/css/components/tables.css'); ?>" rel="stylesheet">

    <style>
        body { 
            background: #f8f9fa; 
            padding-top: 80px; 
            min-height: 100vh; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        @media (max-width:768px) { 
            body { 
                padding-top: 70px; 
            } 
        }

        /* Navigation styles */
        .custom-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .custom-navbar.scrolled {
            padding: 8px 0;
        }

        .brand-logo {
            font-size: 1.8rem;
            background: linear-gradient(45deg, #fff, #e3f2fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all 0.3s ease;
        }
        
        .brand-logo:hover {
            transform: scale(1.05);
        }

        .custom-toggler {
            border: 2px solid rgba(255,255,255,0.3);
            padding: 6px 10px;
        }
        
        .custom-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.25);
        }
        
        .custom-toggler .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .nav-item-custom {
            color: rgba(255,255,255,0.85) !important;
            font-weight: 500;
            padding: 8px 16px !important;
            margin: 0 4px;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-item-custom:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
            color: white !important;
        }
        
        .nav-item-custom.active {
            background: rgba(255,255,255,0.2);
            color: white !important;
            font-weight: 600;
        }
        
        .nav-item-custom.active::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            background: white;
            border-radius: 50%;
        }

        /* Admin specific styles */
        .admin-dropdown-toggle {
            background: linear-gradient(45deg, #ff6b6b, #ffa726);
            border-radius: 25px;
            margin: 0 8px;
        }
        
        .admin-dropdown-toggle:hover {
            background: linear-gradient(45deg, #ff5252, #ff9800);
            transform: translateY(-2px);
        }

        /* User buttons */
        .signin-btn {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            border: none;
            border-radius: 25px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            color: white;
        }
        
        .signin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
            background: linear-gradient(45deg, #45a049, #4CAF50);
            color: white;
        }
        
        .btn-gradient-user {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
            color: white;
        }
        
        .btn-gradient-user:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.4);
            background: linear-gradient(45deg, #1976D2, #2196F3);
            color: white;
        }

        /* Dropdown menus */
        .dropdown-menu-custom {
            background: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            padding: 0.5rem;
            overflow: hidden;
        }
        
        .dropdown-item-custom {
            color: #495057;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .dropdown-item-custom:hover {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-left: 3px solid #667eea;
            padding-left: 25px;
        }
        
        .dropdown-item-custom.text-danger:hover {
            background: linear-gradient(45deg, #fee, #fdd);
            border-left: 3px solid #dc3545;
        }

        /* Admin badge */
        .admin-badge {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #212529;
            font-size: 0.7em;
            padding: 0.2em 0.6em;
            margin-left: 0.5em;
            border-radius: 10px;
            font-weight: bold;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .navbar-nav {
                padding: 10px 0;
            }
            
            .nav-item-custom {
                margin: 5px 0;
                text-align: center;
            }
            
            .dropdown-menu-custom {
                text-align: center;
            }
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Loading spinner */
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        /* Alert customizations */
        .alert-custom {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
    
    <!-- JavaScript for navbar scroll effect -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.custom-navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</head>
<body>

<!-- ENHANCED ATTRACTIVE NAVIGATION -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top custom-navbar">
    <div class="container">
        <!-- Brand with enhanced styling -->
        <a class="navbar-brand fw-bold brand-logo" href="<?php echo base_url('index.php'); ?>">
            <i class="fas fa-car me-2"></i>Speedy Wheels
        </a>

        <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Main Navigation Links -->
                <li class="nav-item">
                    <a class="nav-link nav-item-custom <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" 
                       href="<?php echo base_url('index.php'); ?>">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link nav-item-custom <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>" 
                       href="<?php echo base_url('about.php'); ?>">
                        <i class="fas fa-info-circle me-1"></i> About Us
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link nav-item-custom" href="#how-it-works">
                        <i class="fas fa-play-circle me-1"></i> How It Works
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link nav-item-custom <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>" 
                       href="<?php echo base_url('contact.php'); ?>">
                        <i class="fas fa-phone me-1"></i> Contact
                    </a>
                </li>

                <?php if ($is_admin): ?>
                    <!-- ADMIN LINKS - Only show for admin users -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle nav-item-custom admin-dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-crown me-1"></i> Admin Panel
                        </a>
                        <ul class="dropdown-menu dropdown-menu-custom">
                            <li>
                                <a class="dropdown-item dropdown-item-custom" 
                                   href="<?php echo base_url('src/modules/vehicles/index.php'); ?>">
                                    <i class="fas fa-car text-primary me-2"></i> Manage Vehicles
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" 
                                   href="<?php echo base_url('src/modules/bookings/index.php'); ?>">
                                    <i class="fas fa-calendar-check text-success me-2"></i> Manage Bookings
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" 
                                   href="<?php echo base_url('src/modules/customers/index.php'); ?>">
                                    <i class="fas fa-users text-info me-2"></i> Manage Customers
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" 
                                   href="<?php echo base_url('src/modules/payments/payment.php'); ?>">
                                    <i class="fas fa-file-invoice-dollar text-warning me-2"></i> Payment History
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" 
                                   href="<?php echo base_url('src/modules/reports/index.php'); ?>">
                                    <i class="fas fa-chart-line text-danger me-2"></i> Reports
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" 
                                   href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- USER AUTH LINKS -->
                <?php if (!$is_logged_in): ?>
                    <li class="nav-item ms-2">
                        <a class="btn signin-btn" href="<?php echo base_url('src/modules/auth/login.php'); ?>">
                            <i class="fas fa-user me-1"></i> Sign In
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown ms-2">
                        <a class="btn btn-gradient-user dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['username'] ?? $_SESSION['first_name'] ?? 'User'); ?>
                            <?php if ($is_admin): ?>
                                <span class="admin-badge">ADMIN</span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-custom">
                            <li>
                                <a class="dropdown-item dropdown-item-custom" 
                                   href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" 
                                   href="<?php echo base_url('src/modules/bookings/create_booking.php'); ?>">
                                    <i class="fas fa-plus me-2"></i> New Booking
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" 
                                   href="<?php echo base_url('src/modules/bookings/index.php'); ?>">
                                    <i class="fas fa-calendar-alt me-2"></i> My Bookings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom text-danger" 
                                   href="<?php echo base_url('src/modules/auth/logout.php'); ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Page content will go here -->
<div class="container-fluid p-0">
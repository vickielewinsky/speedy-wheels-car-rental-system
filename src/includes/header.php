<?php
// src/includes/header.php
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Speedy Wheels Car Rental');
}

if (!isset($page_title)) {
    $page_title = APP_NAME;
}

// Calculate the correct base path for includes
$base_dir = dirname(__DIR__); // Goes up from src/includes to src/

// Include auth functions with correct path
require_once $base_dir . '/includes/auth.php';

// Include database config with correct path  
require_once $base_dir . '/config/database.php';

function base_url($path = '') {
    $is_local = ($_SERVER['HTTP_HOST'] ?? 'localhost') === 'localhost';
    $base_path = $is_local ? '/speedy-wheels-car-rental-system' : '';
    $clean_path = $path ? '/' . ltrim($path, '/') : '';
    return $base_path . $clean_path;
}

// Navigation items
$nav_items = [
    'home' => ['url' => base_url('index.php'), 'title' => 'Home', 'icon' => 'fas fa-home'],
    'vehicles' => ['url' => base_url('src/modules/vehicles/index.php'), 'title' => 'Vehicles', 'icon' => 'fas fa-car'],
    'bookings' => ['url' => base_url('src/modules/bookings/index.php'), 'title' => 'Bookings', 'icon' => 'fas fa-calendar-check'],
    'customers' => ['url' => base_url('src/modules/customers/index.php'), 'title' => 'Customers', 'icon' => 'fas fa-users'],
    'payments' => ['url' => base_url('src/modules/payments/payment.php'), 'title' => 'MPESA', 'icon' => 'fas fa-money-bill-wave']
];

if (isAuthenticated()) {
    $nav_items['notifications'] = ['url' => base_url('src/modules/notifications/index.php'), 'title' => 'Notifications', 'icon' => 'fas fa-envelope'];
}

if (isAuthenticated() && hasRole('admin')) {
    $nav_items['reports'] = ['url' => base_url('src/modules/reports/index.php'), 'title' => 'Reports', 'icon' => 'fas fa-chart-line'];
}

$current_script = $_SERVER['PHP_SELF'] ?? '';
function is_active_nav($url, $current_script) {
    return strpos($current_script, basename($url)) !== false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    :root {
      --primary-color: #667eea;
      --secondary-color: #764ba2;
    }
    
    body { 
      background: #f8f9fa; 
      padding-top: 120px;
      min-height: 100vh;
    }
    
    /* Simple Two-Row Navbar */
    .navbar-main {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 1030;
    }
    
    /* Top Row - Brand */
    .navbar-top {
      background: rgba(0,0,0,0.1);
      padding: 10px 0;
      text-align: center;
      border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    
    .brand-title {
      color: white;
      font-size: 28px;
      font-weight: bold;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }
    
    .brand-title:hover {
      color: white;
      transform: scale(1.05);
      transition: transform 0.3s ease;
    }
    
    .brand-tagline {
      color: rgba(255,255,255,0.8);
      font-size: 14px;
      margin-top: 2px;
    }
    
    /* Bottom Row - Navigation */
    .navbar-bottom {
      padding: 8px 0;
    }
    
    .nav-links-container {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      gap: 5px;
    }
    
    .nav-item-custom {
      margin: 0 2px;
    }
    
    .nav-link-custom {
      color: white !important;
      padding: 8px 16px !important;
      border-radius: 6px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 14px;
      text-decoration: none;
    }
    
    .nav-link-custom:hover {
      background: rgba(255,255,255,0.15);
      transform: translateY(-1px);
    }
    
    .nav-link-custom.active {
      background: rgba(255,255,255,0.2);
      font-weight: 600;
    }
    
    /* User Section */
    .user-section {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
    }
    
    .notification-badge {
      background: #ff4757;
      color: white;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      font-size: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: absolute;
      top: -5px;
      right: -5px;
    }
    
    .admin-badge {
      background: #ffd700;
      color: black;
      font-size: 10px;
      padding: 2px 6px;
      border-radius: 10px;
      margin-left: 5px;
    }
    
    /* Mobile Styles */
    @media (max-width: 768px) {
      body {
        padding-top: 100px;
      }
      
      .nav-links-container {
        flex-direction: column;
        gap: 5px;
      }
      
      .nav-link-custom {
        justify-content: center;
        width: 100%;
      }
      
      .user-section {
        position: static;
        transform: none;
        margin-top: 10px;
        text-align: center;
      }
      
      .brand-title {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>

<!-- Simple Two-Row Navbar -->
<nav class="navbar-main">
  <!-- Top Row: Brand -->
  <div class="navbar-top">
    <div class="container">
      <a href="<?php echo base_url('index.php'); ?>" class="brand-title">
        <i class="fas fa-bolt"></i>
        Speedy Wheels
      </a>
      <div class="brand-tagline">Premium Car Rentals</div>
    </div>
  </div>
  
  <!-- Bottom Row: Navigation -->
  <div class="navbar-bottom">
    <div class="container position-relative">
      <div class="nav-links-container">
        <?php foreach ($nav_items as $key => $item): ?>
          <div class="nav-item-custom">
            <a href="<?php echo $item['url']; ?>" 
               class="nav-link-custom <?php echo is_active_nav($item['url'], $current_script) ? 'active' : ''; ?>">
              <i class="<?php echo $item['icon']; ?>"></i>
              <span><?php echo $item['title']; ?></span>
              <?php if ($key === 'notifications'): ?>
                <span class="notification-badge">3</span>
              <?php endif; ?>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
      
      <!-- User Section -->
      <div class="user-section">
        <?php if (isAuthenticated()): ?>
          <div class="dropdown">
            <a class="nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user"></i>
              <?php echo getCurrentUsername(); ?>
              <?php if (hasRole('admin')): ?>
                <span class="admin-badge">Admin</span>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
              </a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('src/modules/auth/logout.php'); ?>">
                <i class="fas fa-sign-out-alt"></i> Logout
              </a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?php echo base_url('src/modules/auth/login.php'); ?>" class="nav-link-custom">
            <i class="fas fa-sign-in-alt"></i> Login
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<main class="container mt-4">
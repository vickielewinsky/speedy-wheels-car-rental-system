<?php
// src/includes/header.php
// Enhanced configuration with error handling
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Speedy Wheels Car Rental');
}

if (!isset($page_title)) {
    $page_title = APP_NAME;
}

// Enhanced base_url function with environment awareness
function base_url($path = '') {
    // Detect environment for flexible path handling
    $is_local = ($_SERVER['HTTP_HOST'] ?? 'localhost') === 'localhost';
    $base_path = $is_local ? '/speedy-wheels-car-rental-system' : '';
    
    // Clean path and ensure proper formatting
    $clean_path = $path ? '/' . ltrim($path, '/') : '';
    return $base_path . $clean_path;
}

// Navigation configuration for maintainability - FIXED PATHS
$nav_items = [
    'home' => [
        'url' => base_url('index.php'),
        'title' => 'Home',
        'icon' => 'fas fa-home'
    ],
    'vehicles' => [
        'url' => base_url('src/modules/vehicles/index.php'),  // FIXED: Added index.php
        'title' => 'Vehicles',
        'icon' => 'fas fa-car'
    ],
    'bookings' => [
        'url' => base_url('src/modules/bookings/index.php'),  // FIXED: Added index.php
        'title' => 'Bookings',
        'icon' => 'fas fa-calendar-check'
    ],
    'customers' => [
        'url' => base_url('src/modules/customers/index.php'), // FIXED: Added index.php
        'title' => 'Customers',
        'icon' => 'fas fa-users'
    ],
    'payments' => [
        'url' => base_url('src/modules/payments/payment.php'),
        'title' => 'MPESA Payment',
        'icon' => 'fas fa-money-bill-wave'
    ],
    'system_test' => [
        'url' => base_url('test-db.php'),
        'title' => 'System Test',
        'icon' => 'fas fa-vial'
    ]
];

// Auto-detect active page for highlighting
$current_script = $_SERVER['PHP_SELF'] ?? '';
function is_active_nav($url, $current_script) {
    $clean_url = rtrim($url, '/');
    $clean_script = rtrim($current_script, '/');
    return strpos($clean_script, basename($clean_url)) !== false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo htmlspecialchars($page_title); ?></title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --primary-gradient-hover: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
      --shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    body { 
      background: #f7f8fb; 
      padding-top: 80px; /* Account for fixed navbar */
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    main {
      flex: 1;
    }
    
    .navbar {
      background: var(--primary-gradient) !important;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 1030;
    }
    
    .navbar.scrolled {
      padding: 0.5rem 0;
      backdrop-filter: blur(10px);
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%) !important;
    }
    
    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      transition: transform 0.3s ease;
    }
    
    .navbar-brand:hover {
      transform: scale(1.05);
    }
    
    .nav-link {
      position: relative;
      margin: 0 4px;
      border-radius: 8px;
      transition: all 0.3s ease;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .nav-link:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
    }
    
    .nav-link.active {
      background: rgba(255, 255, 255, 0.2);
      font-weight: 600;
    }
    
    .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 15%;
      width: 70%;
      height: 2px;
      background: white;
      border-radius: 2px;
    }
    
    .navbar-toggler {
      border: none;
      padding: 4px 8px;
    }
    
    .navbar-toggler:focus {
      box-shadow: none;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 991.98px) {
      .navbar-nav {
        padding: 1rem 0;
        text-align: center;
      }
      
      .nav-link {
        margin: 2px 0;
        justify-content: center;
      }
      
      .nav-link i {
        width: 20px;
      }
    }
    
    /* Badge for notifications */
    .nav-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: #ff4757;
      color: white;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      font-size: 0.7rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?php echo base_url('index.php'); ?>">
      <i class="fas fa-bolt me-2"></i>
      <span>Speedy Wheels</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto">
        <?php foreach ($nav_items as $key => $item): ?>
          <li class="nav-item position-relative">
            <a class="nav-link <?php echo is_active_nav($item['url'], $current_script) ? 'active' : ''; ?>" 
               href="<?php echo $item['url']; ?>">
              <i class="<?php echo $item['icon']; ?>"></i>
              <span><?php echo $item['title']; ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container mt-4">
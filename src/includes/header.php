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

// Navigation items (for other pages that might need it)
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
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  
  <!-- Main CSS -->
  <link href="<?php echo base_url('src/assets/css/main.css'); ?>" rel="stylesheet">

  <style>
    body { 
      background: #f8f9fa; 
      padding-top: 80px; /* Reduced for fixed navbar */
      min-height: 100vh;
    }
    
    /* Mobile body padding adjustment */
    @media (max-width: 768px) {
      body {
        padding-top: 70px; /* Reduced for mobile */
      }
    }
  </style>
</head>
<body>

<!-- OLD NAVBAR REMOVED - Using new navbar from index.php -->

<main class="container mt-4">
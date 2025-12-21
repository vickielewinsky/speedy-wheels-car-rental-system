<?php
// src/includes/header.php

require_once __DIR__ . '/../helpers/url_helper.php';
require_once __DIR__ . '/auth.php';

if (!defined('APP_NAME')) define('APP_NAME', 'Speedy Wheels Car Rental');

if (!isset($page_title)) $page_title = APP_NAME;

// Check user login
$is_logged_in = isset($_SESSION['user_id']) && isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
$is_admin = $is_logged_in && isset($_SESSION['user_role']) && (strtolower($_SESSION['user_role']) === 'admin' || strtolower($_SESSION['user_role']) === 'superadmin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($page_title); ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<!-- Main CSS -->
<link href="<?php echo base_url('src/assets/css/main.css'); ?>" rel="stylesheet">

<!-- Auth CSS -->
<link href="<?php echo base_url('src/assets/css/components/auth.css'); ?>" rel="stylesheet">

<style>
body { 
    background: #f8f9fa; 
    padding-top: 80px; 
    min-height: 100vh; 
}
@media (max-width:768px) { 
    body { 
        padding-top: 70px; 
    } 
}

/* Navigation styles from index.php */
.custom-navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.brand-logo {
    font-size: 1.8rem;
    color: white !important;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.custom-toggler {
    border-color: rgba(255,255,255,0.5);
}

.custom-toggler .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

.nav-item-custom {
    color: rgba(255,255,255,0.85) !important;
    font-weight: 500;
    padding: 0.5rem 1rem !important;
    margin: 0 0.2rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.nav-item-custom:hover,
.nav-item-custom.active {
    color: white !important;
    background: rgba(255,255,255,0.15);
    transform: translateY(-2px);
}

.nav-btn {
    border-radius: 25px;
    padding: 0.5rem 1.5rem !important;
    font-weight: 600;
    transition: all 0.3s ease;
}

.nav-btn-login {
    background: rgba(255,255,255,0.15);
    border: 2px solid rgba(255,255,255,0.3);
}

.nav-btn-register {
    background: white;
    color: #667eea !important;
    border: 2px solid white;
}

.nav-btn-login:hover {
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.5);
}

.nav-btn-register:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.dropdown-menu-custom {
    background: white;
    border: none;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    padding: 0.5rem;
}

.dropdown-item-custom {
    color: #495057;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.dropdown-item-custom:hover {
    background: #f8f9fa;
    color: #667eea;
}

/* Admin badge in dropdown */
.admin-badge {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: #212529;
    font-size: 0.7em;
    padding: 0.2em 0.6em;
    margin-left: 0.5em;
    border-radius: 10px;
    font-weight: bold;
}
</style>
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
          <a class="nav-link nav-item-custom <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
             href="<?php echo base_url('index.php'); ?>">
            <i class="fas fa-home me-1"></i> Home
          </a>
        </li>

        <?php if ($is_admin): ?>
          <!-- For Admin Users -->
          <li class="nav-item">
            <a class="nav-link nav-item-custom" 
               href="<?php echo base_url('src/modules/vehicles/admin.php'); ?>">
              <i class="fas fa-car me-1"></i> Manage Vehicles
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link nav-item-custom" 
               href="<?php echo base_url('src/modules/bookings/admin.php'); ?>">
              <i class="fas fa-calendar-alt me-1"></i> Manage Bookings
            </a>
          </li>
        <?php else: ?>
          <!-- For Regular Users -->
          <li class="nav-item">
            <a class="nav-link nav-item-custom" 
               href="<?php echo base_url('src/modules/vehicles/index.php'); ?>">
              <i class="fas fa-car me-1"></i> Browse Vehicles
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link nav-item-custom" 
               href="<?php echo base_url('src/modules/bookings/index.php'); ?>">
              <i class="fas fa-calendar-alt me-1"></i> My Bookings
            </a>
          </li>
        <?php endif; ?>

        <?php if ($is_logged_in): ?>
          <!-- User is logged in -->
          <li class="nav-item dropdown">
            <a class="nav-link nav-item-custom dropdown-toggle" href="#" id="userDropdown" 
               role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user me-1"></i> 
              <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
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
                   href="<?php echo base_url('src/modules/auth/profile.php'); ?>">
                  <i class="fas fa-user-edit me-2"></i> My Profile
                </a>
              </li>
              <?php if ($is_admin): ?>
                <li>
                  <a class="dropdown-item dropdown-item-custom" 
                     href="<?php echo base_url('src/modules/customers/index.php'); ?>">
                    <i class="fas fa-users me-2"></i> Manage Customers
                  </a>
                </li>
                <li>
                  <a class="dropdown-item dropdown-item-custom" 
                     href="<?php echo base_url('src/modules/payments/index.php'); ?>">
                    <i class="fas fa-money-bill-wave me-2"></i> Payments
                  </a>
                </li>
                <li>
                  <a class="dropdown-item dropdown-item-custom" 
                     href="<?php echo base_url('src/modules/reports/index.php'); ?>">
                    <i class="fas fa-chart-bar me-2"></i> Reports
                  </a>
                </li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item dropdown-item-custom text-danger" 
                   href="<?php echo base_url('src/modules/auth/logout.php'); ?>">
                  <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
              </li>
            </ul>
          </li>
        <?php else: ?>
          <!-- User is not logged in -->
          <li class="nav-item">
            <a class="nav-link nav-btn nav-btn-login" 
               href="<?php echo base_url('src/modules/auth/login.php'); ?>">
              <i class="fas fa-sign-in-alt me-1"></i> Login
            </a>
          </li>
          <li class="nav-item ms-2">
            <a class="nav-link nav-btn nav-btn-register" 
               href="<?php echo base_url('src/modules/auth/register.php'); ?>">
              <i class="fas fa-user-plus me-1"></i> Register
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Page content will go here -->
<div class="container-fluid p-0">
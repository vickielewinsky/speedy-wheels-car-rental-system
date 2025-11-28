<?php
// contact.php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/config/database.php';
$page_title = "Contact Us - Speedy Wheels";

include __DIR__ . '/src/includes/header.php';

// Check user login (session already started in header.php)
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<!-- ENHANCED ATTRACTIVE NAVIGATION -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top custom-navbar">
  <div class="container">
    <!-- Brand with enhanced styling -->
    <a class="navbar-brand fw-bold brand-logo" href="<?= base_url('index.php') ?>">
      <i class="fas fa-car me-2"></i>Speedy Wheels
    </a>
    
    <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <!-- Main Navigation Links -->
        <li class="nav-item">
          <a class="nav-link nav-item-custom" href="<?= base_url('index.php') ?>">
            <i class="fas fa-home me-1"></i>Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-item-custom" href="<?= base_url('about.php') ?>">
            <i class="fas fa-info-circle me-1"></i>About Us
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-item-custom" href="<?= base_url('index.php#how-it-works') ?>">
            <i class="fas fa-play-circle me-1"></i>How It Works
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-item-custom active" href="<?= base_url('contact.php') ?>">
            <i class="fas fa-phone me-1"></i>Contact
          </a>
        </li>
        
        <!-- ADMIN LINKS - Only show for admin users -->
        <?php if ($is_admin): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle nav-item-custom admin-dropdown" href="#" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-crown me-1"></i>Admin Panel
            </a>
            <ul class="dropdown-menu admin-dropdown-menu">
              <li><a class="dropdown-item" href="<?= base_url('src/modules/vehicles/index.php') ?>">
                <i class="fas fa-car text-primary me-2"></i>Manage Vehicles
              </a></li>
              <li><a class="dropdown-item" href="<?= base_url('src/modules/bookings/index.php') ?>">
                <i class="fas fa-calendar-check text-success me-2"></i>Manage Bookings
              </a></li>
              <li><a class="dropdown-item" href="<?= base_url('src/modules/customers/index.php') ?>">
                <i class="fas fa-users text-info me-2"></i>Manage Customers
              </a></li>
              <li><a class="dropdown-item" href="<?= base_url('src/modules/payments/payment.php') ?>">
                <i class="fas fa-file-invoice-dollar text-warning me-2"></i>Payment History
              </a></li>
              <li><a class="dropdown-item" href="<?= base_url('src/modules/reports/index.php') ?>">
                <i class="fas fa-chart-line text-danger me-2"></i>Reports
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= base_url('src/modules/auth/dashboard.php') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
              </a></li>
            </ul>
          </li>
        <?php endif; ?>
        
        <!-- USER AUTH LINKS -->
        <?php if (!$is_logged_in): ?>
          <li class="nav-item ms-2">
            <a class="btn btn-gradient-primary signin-btn" href="<?= base_url('src/modules/auth/login.php') ?>">
              <i class="fas fa-user me-1"></i>Sign In
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item dropdown ms-2">
            <a class="btn btn-gradient-user user-dropdown-toggle dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
            </a>
            <ul class="dropdown-menu user-dropdown-menu">
              <li><a class="dropdown-item" href="<?= base_url('src/modules/auth/dashboard.php') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
              </a></li>
              <li><a class="dropdown-item" href="<?= base_url('src/modules/bookings/create_booking.php') ?>">
                <i class="fas fa-plus me-2"></i>New Booking
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= base_url('src/modules/auth/logout.php') ?>">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
              </a></li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- REST OF YOUR CONTACT.PHP CONTENT REMAINS THE SAME -->
<!-- [Keep all your existing contact page content] -->

<!-- ADD THE SAME STYLES AND SCRIPTS FROM INDEX.PHP -->
<style>
/* Copy all the CSS styles from index.php here */
</style>

<script>
// Copy all the JavaScript from index.php here
</script>

<?php include __DIR__ . '/src/includes/footer.php'; ?>
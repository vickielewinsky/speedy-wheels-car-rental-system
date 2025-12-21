<?php
// about.php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/config/database.php';
$page_title = "About Us - Speedy Wheels";

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
          <a class="nav-link nav-item-custom active" href="<?= base_url('about.php') ?>">
            <i class="fas fa-info-circle me-1"></i>About Us
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-item-custom" href="<?= base_url('index.php#how-it-works') ?>">
            <i class="fas fa-play-circle me-1"></i>How It Works
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-item-custom" href="<?= base_url('contact.php') ?>">
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

<!-- ABOUT US HERO -->
<section class="hero-section" style="
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 60vh;
    display: flex;
    align-items: center;
    color: white;
    padding-top: 80px;">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <h1 class="display-3 fw-bold mb-4">About Speedy Wheels</h1>
        <p class="lead">Mombasa's Premier Car Rental Service - Trusted by 5000+ Customers</p>
      </div>
    </div>
  </div>
</section>

<!-- ABOUT CONTENT -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <h2 class="display-5 mb-4">Our Story</h2>
        <p class="lead mb-4">
          Founded with a vision to revolutionize car rental services in Mombasa, Speedy Wheels has been serving customers 
          with reliability and excellence since our establishment.
        </p>
        <p class="mb-4">
          We understand that every journey is important, whether it's a business trip, family vacation, or special occasion. 
          That's why we maintain a diverse fleet of well-serviced vehicles to meet all your transportation needs.
        </p>
        <div class="mb-4">
          <h4 class="mb-3">Our Mission</h4>
          <p>To provide affordable, reliable, and convenient car rental solutions with exceptional customer service.</p>
        </div>
        <div class="mb-4">
          <h4 class="mb-3">Our Vision</h4>
          <p>To be the most trusted car rental service in Kenya, known for quality and customer satisfaction.</p>
        </div>
      </div>
      <div class="col-lg-6">
        <img src="<?= base_url('src/assets/images/about-hero.jpg') ?>" 
             alt="About Speedy Wheels" 
             class="img-fluid rounded shadow"
             onerror="this.src='https://via.placeholder.com/600x400/667eea/ffffff?text=Speedy+Wheels+Team'">
      </div>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US -->
<section class="py-5" style="background: #f8f9fa;">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-12">
        <h2 class="display-4">Why Choose Speedy Wheels?</h2>
        <p class="lead text-muted">Here's what makes us different</p>
      </div>
    </div>
    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
            <h4>Fully Insured</h4>
            <p class="text-muted">Comprehensive insurance coverage on all our vehicles for your peace of mind.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <i class="fas fa-tools fa-3x text-warning mb-3"></i>
            <h4>Well Maintained</h4>
            <p class="text-muted">Regular servicing and safety checks ensure our vehicles are always in top condition.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <i class="fas fa-headset fa-3x text-info mb-3"></i>
            <h4>24/7 Support</h4>
            <p class="text-muted">Round-the-clock customer service to assist you whenever you need help.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-3 mb-4">
        <h2 class="text-primary display-4 mb-2">50+</h2>
        <p class="text-muted fw-bold">Vehicles</p>
      </div>
      <div class="col-md-3 mb-4">
        <h2 class="text-primary display-4 mb-2">5000+</h2>
        <p class="text-muted fw-bold">Happy Customers</p>
      </div>
      <div class="col-md-3 mb-4">
        <h2 class="text-primary display-4 mb-2">24/7</h2>
        <p class="text-muted fw-bold">Support</p>
      </div>
      <div class="col-md-3 mb-4">
        <h2 class="text-primary display-4 mb-2">5★</h2>
        <p class="text-muted fw-bold">Rating</p>
      </div>
    </div>
  </div>
</section>

<!-- STICKY WHATSAPP BUTTON -->
<a href="https://wa.me/254799692055" target="_blank" class="whatsapp-float">
  <i class="fab fa-whatsapp"></i>
  <span>Need Help?</span>
</a>

<!-- NAVBAR STYLES -->
<style>
/* Enhanced Navbar Styles */
.custom-navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  padding: 12px 0;
  transition: all 0.3s ease;
}

.custom-navbar.scrolled {
  padding: 8px 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.brand-logo {
  font-size: 1.5rem;
  background: linear-gradient(45deg, #fff, #e3f2fd);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  transition: all 0.3s ease;
}

.brand-logo:hover {
  transform: scale(1.05);
}

.nav-item-custom {
  font-weight: 500;
  padding: 8px 16px !important;
  margin: 0 4px;
  border-radius: 25px;
  transition: all 0.3s ease;
  position: relative;
  color: rgba(255,255,255,0.9) !important;
}

.nav-item-custom:hover {
  background: rgba(255,255,255,0.1);
  transform: translateY(-2px);
  color: #fff !important;
}

.nav-item-custom.active {
  background: rgba(255,255,255,0.2);
  color: #fff !important;
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
  background: #fff;
  border-radius: 50%;
}

/* Admin Dropdown */
.admin-dropdown {
  background: linear-gradient(45deg, #ff6b6b, #ffa726);
  border-radius: 25px;
  margin: 0 8px;
}

.admin-dropdown:hover {
  background: linear-gradient(45deg, #ff5252, #ff9800);
  transform: translateY(-2px);
}

.admin-dropdown-menu {
  border: none;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  border-radius: 15px;
  overflow: hidden;
}

.admin-dropdown-menu .dropdown-item {
  padding: 12px 20px;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
}

.admin-dropdown-menu .dropdown-item:hover {
  background: linear-gradient(45deg, #f8f9fa, #e9ecef);
  border-left: 3px solid #667eea;
  padding-left: 25px;
}

/* User Buttons */
.signin-btn {
  background: linear-gradient(45deg, #4CAF50, #45a049);
  border: none;
  border-radius: 25px;
  padding: 10px 24px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.signin-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
  background: linear-gradient(45deg, #45a049, #4CAF50);
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

.user-dropdown-menu {
  border: none;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  border-radius: 15px;
  overflow: hidden;
}

.user-dropdown-menu .dropdown-item {
  padding: 12px 20px;
  transition: all 0.3s ease;
}

.user-dropdown-menu .dropdown-item:hover {
  background: linear-gradient(45deg, #f8f9fa, #e9ecef);
  padding-left: 25px;
}

/* Custom Toggler */
.custom-toggler {
  border: 2px solid rgba(255,255,255,0.3);
  padding: 6px 10px;
}

.custom-toggler:focus {
  box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.25);
}

/* WhatsApp button */
.whatsapp-float {
  position: fixed; 
  bottom: 20px; 
  right: 20px; 
  background: #25D366; 
  color: white; 
  border-radius: 50px; 
  padding: 15px 20px; 
  text-decoration: none; 
  box-shadow: 2px 2px 10px rgba(0,0,0,0.3);
  z-index: 1000;
  display: flex;
  align-items: center;
  gap: 8px;
}

.whatsapp-float:hover {
  background: #128C7E;
  color: white;
  text-decoration: none;
  transform: translateY(-2px);
  transition: all 0.3s ease;
}
</style>

<script>
// Smooth scrolling for navigation
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
});
</script>

<?php include __DIR__ . '/src/includes/footer.php'; ?>
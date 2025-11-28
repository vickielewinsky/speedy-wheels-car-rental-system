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

<!-- CONTACT HERO -->
<section class="hero-section" style="
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 50vh;
    display: flex;
    align-items: center;
    color: white;
    padding-top: 80px;">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <h1 class="display-3 fw-bold mb-4">Contact Us</h1>
        <p class="lead">Get in touch with our team - We're here to help you 24/7</p>
      </div>
    </div>
  </div>
</section>

<!-- CONTACT CONTENT -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="row">
      <div class="col-lg-8 mx-auto">
        <div class="row">
          <!-- Contact Information -->
          <div class="col-md-6 mb-5">
            <h3 class="mb-4">Get In Touch</h3>
            
            <div class="d-flex mb-4">
              <div class="flex-shrink-0">
                <i class="fas fa-map-marker-alt text-primary fa-2x"></i>
              </div>
              <div class="flex-grow-1 ms-3">
                <h5>Our Location</h5>
                <p class="text-muted mb-0">
                  Mombasa CBD<br>
                  Near Nyali Cinemax<br>
                  Mombasa, Kenya
                </p>
              </div>
            </div>

            <div class="d-flex mb-4">
              <div class="flex-shrink-0">
                <i class="fas fa-phone text-success fa-2x"></i>
              </div>
              <div class="flex-grow-1 ms-3">
                <h5>Phone Numbers</h5>
                <p class="text-muted mb-0">
                  +254 799 692 055<br>
                  +254 700 000 000
                </p>
              </div>
            </div>

            <div class="d-flex mb-4">
              <div class="flex-shrink-0">
                <i class="fas fa-envelope text-warning fa-2x"></i>
              </div>
              <div class="flex-grow-1 ms-3">
                <h5>Email Address</h5>
                <p class="text-muted mb-0">
                  lewinskyvictoria45@gmail.com<br>
                  info@speedywheels.com
                </p>
              </div>
            </div>

            <div class="d-flex mb-4">
              <div class="flex-shrink-0">
                <i class="fas fa-clock text-info fa-2x"></i>
              </div>
              <div class="flex-grow-1 ms-3">
                <h5>Working Hours</h5>
                <p class="text-muted mb-0">
                  Monday - Sunday: 24/7<br>
                  Emergency Support: Always Available
                </p>
              </div>
            </div>
          </div>

          <!-- Contact Form -->
          <div class="col-md-6">
            <h3 class="mb-4">Send Message</h3>
            <form id="contactForm">
              <div class="mb-3">
                <label for="name" class="form-label">Your Name</label>
                <input type="text" class="form-control" id="name" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Your Email</label>
                <input type="email" class="form-control" id="email" required>
              </div>
              <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" required>
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" rows="5" required></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="fas fa-paper-plane me-2"></i>Send Message
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- MAP SECTION -->
<section class="py-5" style="background: #f8f9fa;">
  <div class="container">
    <div class="row">
      <div class="col-12 text-center mb-4">
        <h2 class="display-5">Find Us</h2>
        <p class="lead text-muted">Visit our office in Mombasa</p>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="card-body p-0">
            <!-- Simple map placeholder -->
            <div style="height: 400px; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
              <div class="text-center">
                <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Mombasa CBD Location</h4>
                <p class="text-muted">Near Nyali Cinemax, Mombasa</p>
                <a href="https://maps.google.com/?q=Mombasa+CBD+Kenya" target="_blank" class="btn btn-primary">
                  <i class="fas fa-directions me-2"></i>Get Directions
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STICKY WHATSAPP BUTTON -->
<a href="https://wa.me/254799692055" target="_blank" class="whatsapp-float">
  <i class="fab fa-whatsapp"></i>
  <span>Need Help?</span>
</a>

<!-- NAVBAR STYLES (Same as about.php) -->
<style>
/* Copy all the CSS styles from about.php here */
</style>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
  e.preventDefault();
  alert('Thank you for your message! We will get back to you soon.');
  this.reset();
});

// Copy the JavaScript from about.php here
</script>

<?php include __DIR__ . '/src/includes/footer.php'; ?>
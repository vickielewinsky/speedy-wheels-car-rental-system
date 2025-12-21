<?php
// index.php - WITH ATTRACTIVE NAVBAR
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/config/database.php';
$page_title = "Home - Speedy Wheels";

include __DIR__ . '/src/includes/header.php';

try {
    $stmt = $pdo->prepare("SELECT vehicle_id, plate_no, model, make, year, color, daily_rate, status FROM vehicles WHERE status = 'available' ORDER BY daily_rate ASC");
    $stmt->execute();
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $vehicles = [];
}

// Clean vehicle name (remove duplicate make)
function getCleanVehicleName($make, $model) {
    if (strpos(strtolower($model), strtolower($make)) !== false) {
        return $model;
    }
    return $make . ' ' . $model;
}

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
          <a class="nav-link nav-item-custom <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="<?= base_url('index.php') ?>">
            <i class="fas fa-home me-1"></i>Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-item-custom <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>" href="<?= base_url('about.php') ?>">
            <i class="fas fa-info-circle me-1"></i>About Us
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-item-custom" href="#how-it-works">
            <i class="fas fa-play-circle me-1"></i>How It Works
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-item-custom <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>" href="<?= base_url('contact.php') ?>">
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

<!-- HERO SECTION WITH CAR BACKGROUND -->
<section id="home" class="hero-section" style="
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                url('<?= base_url('src/assets/images/hero-car.png') ?>');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
    display: flex;
    align-items: center;
    color: white;
    text-align: center;
    padding-top: 80px;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h1 class="display-3 fw-bold mb-4">Trusted by 5000+ Customers</h1>
        <p class="lead mb-5" style="font-size: 1.3rem;">
          Experience the best car rental service in Mombasa. From economy cars to luxury SUVs — choose the perfect vehicle for your journey.
        </p>
        <div class="hero-buttons">
          <a href="#our-fleet" class="btn btn-light btn-lg me-3 px-4">
            <i class="fas fa-car me-2"></i>View Vehicles
          </a>
          <a href="<?= base_url('src/modules/bookings/create_booking.php') ?>" class="btn btn-primary btn-lg px-4">
            <i class="fas fa-calendar-check me-2"></i>Book Now
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATS SECTION -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-4 mb-4">
        <h2 class="text-primary mb-2">50+</h2>
        <p class="text-muted fw-bold">Vehicles</p>
      </div>
      <div class="col-md-4 mb-4">
        <h2 class="text-primary mb-2">24/7</h2>
        <p class="text-muted fw-bold">Support</p>
      </div>
      <div class="col-md-4 mb-4">
        <h2 class="text-primary mb-2">5★</h2>
        <p class="text-muted fw-bold">Rating</p>
      </div>
    </div>
  </div>
</section>

<!-- FLEET SECTION -->
<section id="our-fleet" class="py-5" style="background:#f8f9fa;">
  <div class="container">
    <div class="row mb-5">
      <div class="col-12 text-center">
        <h2 class="display-4 mb-3">Our Car Rental Fleet</h2>
        <p class="lead text-muted">Choose from our selection of quality vehicles for hire</p>
      </div>
    </div>

    <div class="row">
      <?php if (!empty($vehicles)): ?>
        <?php foreach ($vehicles as $vehicle): ?>
          <?php
          $model_raw = trim($vehicle['model']);      
          $model_parts = explode(' ', $model_raw);
          $model = strtolower(end($model_parts));
          $color = strtolower(trim($vehicle['color']));
          $vehicle_name = getCleanVehicleName($vehicle['make'], $vehicle['model']);
          $monthly_rate = $vehicle['daily_rate'] * 30;
          ?>

          <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100 shadow border-0">
              <div style="height:250px; overflow:hidden;">
                <?php if ($model == 'fit'): ?>
                  <img src="<?= base_url('src/assets/images/vehicles/honda-fit.jpg') ?>" class="w-100 h-100" style="object-fit:cover;">
                <?php elseif ($model == 'axio'): ?>
                  <img src="<?= base_url('src/assets/images/vehicles/toyota-axio.jpg') ?>" class="w-100 h-100" style="object-fit:cover;">
                <?php elseif ($model == 'cr-v'): ?>
                  <img src="<?= base_url('src/assets/images/vehicles/honda-cr-v.jpg') ?>" class="w-100 h-100" style="object-fit:cover;">
                <?php elseif ($model == 'cx-5'): ?>
                  <img src="<?= base_url('src/assets/images/vehicles/mazda-cx-5.jpg') ?>" class="w-100 h-100" style="object-fit:cover;">
                <?php elseif ($model == 'rav4'): ?>
                  <img src="<?= base_url('src/assets/images/vehicles/toyota-rav4.jpg') ?>" class="w-100 h-100" style="object-fit:cover;">
                <?php elseif ($model == 'forester'): ?>
                  <img src="<?= base_url('src/assets/images/vehicles/subaru-forester.jpg') ?>" class="w-100 h-100" style="object-fit:cover;">
                <?php elseif ($model == 'x-trail' && $color == 'black'): ?>
                  <img src="<?= base_url('src/assets/images/vehicles/nissan-x-trail-black.jpg') ?>" class="w-100 h-100" style="object-fit:cover;">
                <?php elseif ($model == 'x-trail'): ?>
                  <img src="<?= base_url('src/assets/images/vehicles/nissan-x-trail.jpg') ?>" class="w-100 h-100" style="object-fit:cover;">
                <?php else: ?>
                  <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-secondary text-white">
                    <i class="fas fa-car fa-3x"></i>
                  </div>
                <?php endif; ?>
              </div>

              <div class="card-body p-3">
                <h5 class="card-title mb-2"><?= $vehicle_name ?></h5>
                <div class="mb-3">
                  <div class="d-flex justify-content-between mb-1">
                    <small>Daily Rate:</small>
                    <span class="text-primary fw-bold">Ksh <?= number_format($vehicle['daily_rate'], 0) ?></span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <small>Monthly Rate:</small>
                    <span class="text-success fw-bold">Ksh <?= number_format($monthly_rate, 0) ?></span>
                  </div>
                </div>
                <div class="mb-2">
                  <small class="text-muted">
                    <?= $vehicle['year'] ?>  <?= $vehicle['color'] ?>  <?= $vehicle['plate_no'] ?>
                  </small>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <a href="<?= base_url('src/modules/bookings/create_booking.php?vehicle_id=' . $vehicle['vehicle_id']) ?>" class="btn btn-primary btn-sm">
                    Book Now
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center">
          <div class="alert alert-warning">
            <h5>No vehicles currently available</h5>
            <p>Please check back later or contact us for availability.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS SECTION -->
<section id="how-it-works" class="py-5 bg-white">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-12">
        <h2 class="display-4">How It Works</h2>
        <p class="lead text-muted">Rent a car in 3 simple steps</p>
      </div>
    </div>
    <div class="row text-center">
      <div class="col-md-4 mb-4">
        <div class="step-number">1</div>
        <h4>Choose Vehicle</h4>
        <p>Select from our wide range of quality vehicles</p>
      </div>
      <div class="col-md-4 mb-4">
        <div class="step-number">2</div>
        <h4>Book & Pay</h4>
        <p>Complete your booking with secure payment</p>
      </div>
      <div class="col-md-4 mb-4">
        <div class="step-number">3</div>
        <h4>Pick Up</h4>
        <p>Collect your vehicle or request delivery</p>
      </div>
    </div>
  </div>
</section>

<!-- ADMIN PANEL SECTION (Only for logged-in admins) -->
<?php if ($is_admin): ?>
<section class="py-5 bg-light">
  <div class="container">
    <div class="row text-center mb-4">
      <div class="col-12">
        <h2 class="display-5">Admin Panel</h2>
        <p class="lead text-muted">Quick access to management tools</p>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <i class="fas fa-car fa-3x text-primary mb-3"></i>
            <h5>Manage Vehicles</h5>
            <p class="text-muted small">Add, edit, or remove vehicles from fleet</p>
            <a href="<?= base_url('src/modules/vehicles/index.php') ?>" class="btn btn-primary btn-sm">Go to Vehicles</a>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
            <h5>Manage Bookings</h5>
            <p class="text-muted small">View and manage all bookings</p>
            <a href="<?= base_url('src/modules/bookings/index.php') ?>" class="btn btn-success btn-sm">Go to Bookings</a>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <i class="fas fa-users fa-3x text-info mb-3"></i>
            <h5>Manage Customers</h5>
            <p class="text-muted small">View and manage customer records</p>
            <a href="<?= base_url('src/modules/customers/index.php') ?>" class="btn btn-info btn-sm">Go to Customers</a>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <i class="fas fa-file-invoice-dollar fa-3x text-warning mb-3"></i>
            <h5>Payment History</h5>
            <p class="text-muted small">View payment transactions</p>
            <a href="<?= base_url('src/modules/payments/payment.php') ?>" class="btn btn-warning btn-sm">View Payments</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- STICKY WHATSAPP BUTTON -->
<a href="https://wa.me/254750515354" 
   target="_blank" 
   class="whatsapp-float"
   style="position: fixed; 
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
          gap: 8px;">
  <i class="fab fa-whatsapp" style="font-size: 24px;"></i>
  <span style="font-weight: bold;">Need Help?</span>
</a>

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

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Step numbers */
.step-number {
  width: 60px;
  height: 60px;
  background: #667eea;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: bold;
  margin: 0 auto 20px;
}

/* WhatsApp button */
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
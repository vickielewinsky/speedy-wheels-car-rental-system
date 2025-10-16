<?php
// index.php - main
require_once __DIR__ . '/src/config/config.php';
$page_title = "Home - Speedy Wheels";
include __DIR__ . '/src/includes/header.php';
?>

<section class="hero mb-4" style="background:linear-gradient(135deg,#667eea,#764ba2);">
  <div class="container text-center">
    <h1 class="display-5"><i class="fas fa-car-side me-2"></i>Speedy Wheels Car Rental</h1>
    <p class="lead mb-3">Reliable, affordable car rentals — sample project for Technical University of Mombasa</p>
    <a href="<?php echo url('src/modules/vehicles/'); ?>" class="btn btn-light btn-lg me-2"><i class="fas fa-car"></i> View Fleet</a>
    <a href="<?php echo url('src/modules/bookings/create.php'); ?>" class="btn btn-outline-light btn-lg"><i class="fas fa-calendar-check"></i> Make Booking</a>
  </div>
</section>

<div class="row gy-4">
  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body text-center">
        <i class="fas fa-car-side fa-2x text-primary mb-2"></i>
        <h5>Vehicle Management</h5>
        <p class="small text-muted">Manage fleet and pricing</p>
        <a href="<?php echo url('src/modules/vehicles/'); ?>" class="btn btn-primary btn-sm">Open</a>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body text-center">
        <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
        <h5>Bookings</h5>
        <p class="small text-muted">Reservations & rental periods</p>
        <a href="<?php echo url('src/modules/bookings/'); ?>" class="btn btn-success btn-sm">Open</a>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body text-center">
        <i class="fas fa-users fa-2x text-info mb-2"></i>
        <h5>Customers</h5>
        <p class="small text-muted">Customer records & history</p>
        <a href="<?php echo url('src/modules/customers/'); ?>" class="btn btn-info btn-sm">Open</a>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body text-center">
        <i class="fas fa-chart-bar fa-2x text-warning mb-2"></i>
        <h5>System Info</h5>
        <p class="small text-muted">Database & config tests</p>
        <a href="<?php echo url('test-db.php'); ?>" class="btn btn-warning btn-sm">Run</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/src/includes/footer.php'; ?>

<?php
// index.php - MINIMAL HERO HEIGHT VERSION
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
?>

<!-- HERO SECTION -->
<section class="hero" style="background:linear-gradient(135deg,#667eea,#764ba2); padding:20px 0; height:80px; display:flex; align-items:center;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 text-center text-white">
        <h4 class="mb-1">Speedy Wheels Car Rental</h4>
        <p class="mb-0 small">Premium Car Rentals in Mombasa</p>
      </div>
    </div>
  </div>
</section>

<!-- FLEET SECTION -->
<section id="our-fleet" class="py-5" style="background:#f8f9fa;">
  <div class="container">
    <div class="row mb-5">
      <div class="col-12 text-center">
        <h2 class="display-5 mb-3">Our Car Rental Fleet</h2>
        <p class="lead text-muted">Choose from our selection of quality vehicles for hire</p>
      </div>
    </div>

    <div class="row">

      <?php if (!empty($vehicles)): ?>
        <?php foreach ($vehicles as $vehicle): ?>

          <?php
          // Extract model (last word)
          $model_raw = trim($vehicle['model']);      
          $model_parts = explode(' ', $model_raw);
          $model = strtolower(end($model_parts));

          // Color
          $color = strtolower(trim($vehicle['color']));

          // Clean name
          $vehicle_name = getCleanVehicleName($vehicle['make'], $vehicle['model']);

          // Pricing
          $monthly_rate = $vehicle['daily_rate'] * 30;
          ?>

          <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100 shadow border-0">

              <!-- IMAGE SECTION -->
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

              <!-- CARD BODY -->
              <div class="card-body p-3">
                <h5 class="card-title mb-2"><?= $vehicle_name ?></h5>

                <!-- PRICING -->
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

                <!-- DETAILS -->
                <div class="mb-2">
                  <small class="text-muted">
                    <?= $vehicle['year'] ?> • <?= $vehicle['color'] ?> • <?= $vehicle['plate_no'] ?>
                  </small>
                </div>

                <!-- BOOK NOW -->
                <div class="d-flex justify-content-between align-items-center">
                  <small class="text-muted">
                    <i class="fas fa-phone me-1"></i> +254 799 692 055
                  </small>
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

<!-- WHY CHOOSE US -->
<section class="py-4 bg-white">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-3 mb-3">
        <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
        <h6>Fully Insured</h6>
      </div>
      <div class="col-md-3 mb-3">
        <i class="fas fa-tools fa-2x text-warning mb-2"></i>
        <h6>Well Maintained</h6>
      </div>
      <div class="col-md-3 mb-3">
        <i class="fas fa-headset fa-2x text-info mb-2"></i>
        <h6>24/7 Support</h6>
      </div>
      <div class="col-md-3 mb-3">
        <i class="fas fa-bolt fa-2x text-danger mb-2"></i>
        <h6>Quick Booking</h6>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/src/includes/footer.php'; ?>

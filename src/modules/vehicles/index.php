<?php
// src/modules/vehicles/index.php - FOR REGULAR USERS
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

// Require authentication (but NOT admin role)
requireAuthentication();

$page_title = "Available Vehicles - Speedy Wheels";
require_once __DIR__ . '/../../includes/header.php';

// Get database connection
try {
    $pdo = getDatabaseConnection();

    // Fetch available vehicles
    $vehicles_stmt = $pdo->query("
        SELECT * FROM vehicles 
        WHERE status = 'available' 
        ORDER BY created_at DESC
    ");
    $vehicles = $vehicles_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $vehicles = [];
    error_log("Database error in vehicles index: " . $e->getMessage());
}
?>

<div class="container-fluid mt-4">
    <!-- Header with Back to Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-car me-2 text-primary"></i>Available Vehicles
            </h1>
            <p class="text-muted mb-0">Browse our selection of premium vehicles</p>
        </div>
        <div>
            <a href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Vehicles Grid -->
    <div class="row">
        <?php if (!empty($vehicles)): ?>
            <?php foreach ($vehicles as $vehicle): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <!-- Vehicle Image -->
                        <div class="position-relative" style="height: 200px; overflow: hidden;">
                            <img src="<?php 
                                // Try to get vehicle image
                                $image_path = 'src/assets/images/vehicles/' . ($vehicle['image'] ?? 'hero-car.png');
                                $full_path = $_SERVER['DOCUMENT_ROOT'] . '/speedy-wheels-car-rental-system/' . $image_path;
                                if (file_exists($full_path)) {
                                    echo base_url($image_path);
                                } else {
                                    echo base_url('src/assets/images/hero-car.png');
                                }
                            ?>" 
                            class="card-img-top h-100 w-100" 
                            alt="<?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>"
                            style="object-fit: cover;">

                            <!-- Status Badge -->
                            <span class="position-absolute top-0 end-0 m-2 badge bg-success">
                                Available
                            </span>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title text-primary">
                                <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ')'); ?>
                            </h5>

                            <div class="mb-3">
                                <div class="row small text-muted">
                                    <div class="col-6">
                                        <i class="fas fa-users me-1"></i>
                                        <?php echo $vehicle['seats'] ?? 5; ?> Seats
                                    </div>
                                    <div class="col-6">
                                        <i class="fas fa-gas-pump me-1"></i>
                                        <?php echo htmlspecialchars($vehicle['fuel_type'] ?? 'Petrol'); ?>
                                    </div>
                                    <div class="col-6">
                                        <i class="fas fa-cog me-1"></i>
                                        <?php echo htmlspecialchars($vehicle['transmission'] ?? 'Automatic'); ?>
                                    </div>
                                    <div class="col-6">
                                        <i class="fas fa-palette me-1"></i>
                                        <?php echo htmlspecialchars($vehicle['color'] ?? 'Various'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <h4 class="text-success mb-0">
                                        Ksh <?php echo number_format($vehicle['daily_rate'], 2); ?>
                                    </h4>
                                    <small class="text-muted">per day</small>
                                </div>
                                <a href="<?php echo base_url('src/modules/bookings/create_booking.php?vehicle_id=' . $vehicle['vehicle_id']); ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-calendar-plus me-1"></i> Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-car fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Vehicles Available</h4>
                    <p class="text-muted">All our vehicles are currently booked. Please check back later.</p>
                    <a href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
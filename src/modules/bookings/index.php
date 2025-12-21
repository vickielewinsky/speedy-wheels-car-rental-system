<?php
// src/modules/bookings/index.php - FOR REGULAR USERS
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

// Require authentication (but NOT admin role)
requireAuthentication();

$page_title = "My Bookings - Speedy Wheels";
require_once __DIR__ . '/../../includes/header.php';

// Get database connection
$user_id = $_SESSION['user_id'];

try {
    $pdo = getDatabaseConnection();
    
    // Fetch user's bookings
    $bookings_stmt = $pdo->prepare("
        SELECT 
            b.*,
            v.make as vehicle_make,
            v.model as vehicle_model,
            v.daily_rate,
            v.image as vehicle_image,
            v.plate_no as vehicle_plate
        FROM bookings b
        LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
        WHERE b.user_id = :user_id
        ORDER BY b.created_at DESC
    ");
    $bookings_stmt->execute(['user_id' => $user_id]);
    $bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $bookings = [];
    error_log("Database error in bookings index: " . $e->getMessage());
}
?>

<div class="container-fluid mt-4">
    <!-- Header with Back to Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-calendar-check me-2 text-success"></i>My Bookings
            </h1>
            <p class="text-muted mb-0">View and manage your rental bookings</p>
        </div>
        <div class="btn-group">
            <a href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <a href="<?php echo base_url('src/modules/vehicles/index.php'); ?>" class="btn btn-primary">
                <i class="fas fa-car me-1"></i> Book New Vehicle
            </a>
        </div>
    </div>

    <!-- Bookings Cards -->
    <?php if (!empty($bookings)): ?>
        <div class="row">
            <?php foreach ($bookings as $booking): 
                // Calculate days and total
                $start_date = new DateTime($booking['start_date'] ?? $booking['booking_date']);
                $end_date = new DateTime($booking['end_date'] ?? $booking['return_date']);
                $interval = $start_date->diff($end_date);
                $days = $interval->days ?: 1;
                $total = $days * $booking['daily_rate'];
            ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Booking #<?php echo $booking['id'] ?? $booking['booking_id']; ?>
                            </h5>
                            <?php
                            $status_badge = match($booking['status']) {
                                'confirmed', 'active' => 'bg-success',
                                'pending' => 'bg-warning',
                                'cancelled' => 'bg-danger',
                                'completed' => 'bg-info',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?php echo $status_badge; ?>">
                                <?php echo ucfirst($booking['status'] ?? 'Pending'); ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <!-- Vehicle Info -->
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <div class="vehicle-icon" style="width: 60px; height: 40px; background: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-car text-muted"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">
                                        <?php echo htmlspecialchars($booking['vehicle_make'] . ' ' . $booking['vehicle_model']); ?>
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Plate: <?php echo htmlspecialchars($booking['vehicle_plate'] ?? 'N/A'); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Booking Details -->
                            <div class="row small mb-3">
                                <div class="col-6">
                                    <strong>Start Date:</strong><br>
                                    <?php echo date('M j, Y', strtotime($booking['start_date'] ?? $booking['booking_date'])); ?>
                                </div>
                                <div class="col-6">
                                    <strong>End Date:</strong><br>
                                    <?php echo date('M j, Y', strtotime($booking['end_date'] ?? $booking['return_date'])); ?>
                                </div>
                                <div class="col-6 mt-2">
                                    <strong>Duration:</strong><br>
                                    <?php echo $days; ?> day<?php echo $days != 1 ? 's' : ''; ?>
                                </div>
                                <div class="col-6 mt-2">
                                    <strong>Daily Rate:</strong><br>
                                    Ksh <?php echo number_format($booking['daily_rate'], 2); ?>
                                </div>
                            </div>
                            
                            <!-- Total and Actions -->
                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <div>
                                    <h5 class="text-success mb-0">
                                        Ksh <?php echo number_format($total, 2); ?>
                                    </h5>
                                    <small class="text-muted">Total Amount</small>
                                </div>
                                <div class="btn-group">
                                    <a href="<?php echo base_url('src/modules/bookings/view_booking.php?id=' . ($booking['id'] ?? $booking['booking_id'])); ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                    <?php if (($booking['status'] == 'pending' || $booking['status'] == 'confirmed') && $booking['payment_status'] != 'paid'): ?>
                                        <a href="<?php echo base_url('src/modules/payments/payment.php?booking_id=' . ($booking['id'] ?? $booking['booking_id'])); ?>" 
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-credit-card me-1"></i> Pay Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Bookings Yet</h4>
            <p class="text-muted">You haven't made any bookings yet. Browse our vehicles to get started.</p>
            <div class="mt-4">
                <a href="<?php echo base_url('src/modules/vehicles/index.php'); ?>" class="btn btn-primary me-3">
                    <i class="fas fa-car me-1"></i> Browse Vehicles
                </a>
                <a href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
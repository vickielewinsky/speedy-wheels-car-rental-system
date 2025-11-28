<?php
// src/modules/bookings/index.php - ADD BACK TO DASHBOARD BUTTON
require_once "../../config/database.php";
require_once "../../includes/auth.php";

// Require authentication and admin role
requireAuthentication();
if (!hasRole('admin')) {
    header('Location: ' . base_url('src/modules/auth/login.php'));
    exit();
}

$page_title = "Manage Bookings - Speedy Wheels";
require_once "../../includes/header.php";

// Get database connection
try {
    $pdo = getDatabaseConnection();
    
    // Fetch bookings with customer and vehicle details
    $bookings_stmt = $pdo->query("
        SELECT 
            b.*,
            c.name as customer_name,
            c.phone as customer_phone,
            v.make as vehicle_make,
            v.model as vehicle_model,
            v.plate_no as vehicle_plate
        FROM bookings b
        LEFT JOIN customers c ON b.customer_id = c.customer_id
        LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
        ORDER BY b.created_at DESC
    ");
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
                <i class="fas fa-calendar-check me-2 text-success"></i>Manage Bookings
            </h1>
            <p class="text-muted mb-0">View and manage all rental bookings</p>
        </div>
        <div class="btn-group">
            <a href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <a href="create_booking.php" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> New Booking
            </a>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>All Bookings
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($bookings)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Rental Period</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo $booking['booking_id']; ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['customer_phone']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['vehicle_make'] . ' ' . $booking['vehicle_model']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['vehicle_plate']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($booking['start_date'])); ?> 
                                        to 
                                        <?php echo date('M j, Y', strtotime($booking['end_date'])); ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php 
                                            $days = ceil((strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24));
                                            echo $days . ' day' . ($days != 1 ? 's' : '');
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-success">Ksh <?php echo number_format($booking['total_amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badge = match($booking['status']) {
                                            'confirmed' => 'bg-success',
                                            'pending' => 'bg-warning',
                                            'cancelled' => 'bg-danger',
                                            'completed' => 'bg-info',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $status_badge; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view_booking.php?id=<?php echo $booking['booking_id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_booking.php?id=<?php echo $booking['booking_id']; ?>" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Bookings Found</h4>
                    <p class="text-muted">Create your first booking to get started.</p>
                    <a href="create_booking.php" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Create First Booking
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>
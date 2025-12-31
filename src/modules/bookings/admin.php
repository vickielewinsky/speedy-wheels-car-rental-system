<?php
// src/modules/bookings/admin.php - ADMIN ONLY VERSION
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . "/../../helpers/url_helper.php";
require_once __DIR__ . '/../../includes/auth.php';

// Require admin role
requireAdmin();

$page_title = "Manage Bookings - Admin Panel";
require_once __DIR__ . '/../../includes/header.php';

// Get database connection
try {
    $pdo = getDatabaseConnection();

    $bookings_stmt = $pdo->query("
        SELECT 
            b.*,
            u.username as customer_name,
            u.email as customer_email,
            u.phone as customer_phone,
            v.make as vehicle_make,
            v.model as vehicle_model,
            v.plate_no as vehicle_plate
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
        ORDER BY b.created_at DESC
    ");
    $bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $bookings = [];
    error_log("Database error in bookings admin: " . $e->getMessage());
}
?>

<div class="container-fluid mt-4">
    <!-- Admin Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-calendar-check me-2 text-success"></i>Manage Bookings
                <span class="badge bg-danger ms-2">Admin</span>
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
                            <?php foreach ($bookings as $booking): 
                                // Calculate days and total
                                $days = ceil((strtotime($booking['end_date'] ?? $booking['return_date']) - strtotime($booking['start_date'] ?? $booking['booking_date'])) / (60 * 60 * 24));
                                $days = $days ?: 1;
                                $total = $days * ($booking['daily_rate'] ?? $booking['total_amount'] / $days);
                            ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo $booking['id'] ?? $booking['booking_id']; ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['customer_email']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['vehicle_make'] . ' ' . $booking['vehicle_model']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['vehicle_plate'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($booking['start_date'] ?? $booking['booking_date'])); ?> 
                                        to 
                                        <?php echo date('M j, Y', strtotime($booking['end_date'] ?? $booking['return_date'])); ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo $days . ' day' . ($days != 1 ? 's' : ''); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-success">Ksh <?php echo number_format($total, 2); ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badge = match($booking['status']) {
                                            'confirmed' => 'bg-success',
                                            'pending' => 'bg-warning',
                                            'cancelled' => 'bg-danger',
                                            'completed' => 'bg-info',
                                            'active' => 'bg-primary',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $status_badge; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view_booking.php?id=<?php echo $booking['id'] ?? $booking['booking_id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_booking.php?id=<?php echo $booking['id'] ?? $booking['booking_id']; ?>" 
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
                    <p class="text-muted">No bookings have been made yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
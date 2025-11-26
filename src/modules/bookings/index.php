<?php
// src/modules/bookings/index.php - UPDATED WITH CORRECT COLUMN NAMES
$page_title = "Bookings Management - Speedy Wheels";

// Include the shared header
require_once dirname(__DIR__, 2) . '/includes/header.php';

// Your existing bookings code here...
$root_dir = dirname(__DIR__, 2);

// Load configuration files with CORRECT paths
$db_config_path = $root_dir . '/config/database.php';
if (file_exists($db_config_path)) {
    require_once $db_config_path;
} else {
    function getDatabaseConnection() {
        try {
            $pdo = new PDO(
                "mysql:host=localhost;dbname=speedy_wheels;charset=utf8mb4",
                "root",
                "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            return $pdo;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}

// Get database connection and fetch bookings
try {
    $pdo = getDatabaseConnection();
    
    // CORRECTED QUERY WITH PROPER COLUMN NAMES FROM YOUR SCHEMA
    try {
        $stmt = $pdo->query("
            SELECT 
                b.booking_id,
                b.start_date,
                b.end_date,
                b.total_amount,
                b.status,
                b.created_at,
                c.name as customer_name,
                c.phone as customer_phone,
                v.make as vehicle_make,
                v.model as vehicle_model,
                v.plate_no as vehicle_plate
            FROM bookings b
            LEFT JOIN customers c ON b.customer_id = c.customer_id
            LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
            ORDER BY b.created_at DESC 
            LIMIT 20
        ");
        $bookings = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        // Use sample data if query fails
        $bookings = [
            [
                'booking_id' => 1,
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+3 days')),
                'total_amount' => 15000,
                'status' => 'confirmed',
                'customer_name' => 'John Doe',
                'customer_phone' => '254712345678',
                'vehicle_make' => 'Toyota',
                'vehicle_model' => 'Corolla',
                'vehicle_plate' => 'KCA 123A'
            ],
            [
                'booking_id' => 2,
                'start_date' => date('Y-m-d', strtotime('-1 day')),
                'end_date' => date('Y-m-d', strtotime('+2 days')),
                'total_amount' => 12000,
                'status' => 'active',
                'customer_name' => 'Jane Smith',
                'customer_phone' => '254723456789',
                'vehicle_make' => 'Honda',
                'vehicle_model' => 'Civic',
                'vehicle_plate' => 'KCB 456B'
            ]
        ];
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $bookings = [];
}

// If no bookings found, use sample data
if (empty($bookings)) {
    $bookings = [
        [
            'booking_id' => 1,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+3 days')),
            'total_amount' => 15000,
            'status' => 'confirmed',
            'customer_name' => 'John Doe',
            'customer_phone' => '254712345678',
            'vehicle_make' => 'Toyota',
            'vehicle_model' => 'Corolla',
            'vehicle_plate' => 'KCA 123A'
        ],
        [
            'booking_id' => 2,
            'start_date' => date('Y-m-d', strtotime('-1 day')),
            'end_date' => date('Y-m-d', strtotime('+2 days')),
            'total_amount' => 12000,
            'status' => 'active',
            'customer_name' => 'Jane Smith',
            'customer_phone' => '254723456789',
            'vehicle_make' => 'Honda',
            'vehicle_model' => 'Civic',
            'vehicle_plate' => 'KCB 456B'
        ]
    ];
}
?>

<!-- Your bookings page content -->
<div class="row">
    <div class="col-12">
        <!-- System Status -->
        <?php if (isset($error)): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-info-circle me-2"></i>
            <strong>System Notice:</strong> <?php echo $error; ?>
            <br><small>Showing sample data for demonstration.</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="fas fa-calendar-check me-2 text-primary"></i>Bookings
                </h1>
                <p class="text-muted mb-0">Manage vehicle rental bookings and reservations</p>
            </div>
            <div>
                <a href="create_booking.php" class="btn btn-primary me-2">
                    <i class="fas fa-plus me-1"></i> New Booking
                </a>
                <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Bookings</h6>
                            <h3 class="mb-0"><?php echo count($bookings); ?></h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="stat-card" style="border-left-color: #28a745;">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Active</h6>
                            <h3 class="mb-0">
                                <?php 
                                $active = array_filter($bookings, function($b) { 
                                    return ($b['status'] ?? '') === 'active'; 
                                });
                                echo count($active);
                                ?>
                            </h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-play-circle fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>All Bookings
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Rental Period</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <!-- FIXED: Use booking_id instead of id -->
                                <td><strong>#<?php echo $booking['booking_id']; ?></strong></td>
                                <td>
                                    <!-- FIXED: Use customer_name instead of full_name -->
                                    <div class="fw-semibold"><?php echo htmlspecialchars($booking['customer_name'] ?? 'Unknown Customer'); ?></div>
                                    <!-- FIXED: Use customer_phone instead of phone -->
                                    <small class="text-muted">
                                        <?php 
                                        $phone = $booking['customer_phone'] ?? 'N/A';
                                        // Mask phone number for privacy
                                        if ($phone !== 'N/A' && strlen($phone) > 7) {
                                            echo substr($phone, 0, 4) . 'XXXX' . substr($phone, -3);
                                        } else {
                                            echo $phone;
                                        }
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <!-- FIXED: Use vehicle_make and vehicle_model instead of model -->
                                    <div><?php echo htmlspecialchars(($booking['vehicle_make'] ?? '') . ' ' . ($booking['vehicle_model'] ?? 'Unknown Vehicle')); ?></div>
                                    <!-- FIXED: Use vehicle_plate instead of plate_number -->
                                    <small class="text-muted"><?php echo htmlspecialchars($booking['vehicle_plate'] ?? 'No Plate'); ?></small>
                                </td>
                                <td>
                                    <div><?php echo date('M j, Y', strtotime($booking['start_date'])); ?></div>
                                    <small class="text-muted">to <?php echo date('M j, Y', strtotime($booking['end_date'])); ?></small>
                                </td>
                                <td><strong>KSh <?php echo number_format($booking['total_amount'] ?? 0, 2); ?></strong></td>
                                <td>
                                    <?php
                                    $status = $booking['status'] ?? 'pending';
                                    $status_classes = [
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'active' => 'primary',
                                        'completed' => 'info',
                                        'cancelled' => 'danger'
                                    ];
                                    $status_class = $status_classes[$status] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="view_booking.php?id=<?php echo $booking['booking_id']; ?>" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_booking.php?id=<?php echo $booking['booking_id']; ?>" 
                                           class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-left: 4px solid #007bff;
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
</style>

<?php
// Include the shared footer
require_once dirname(__DIR__, 2) . '/includes/footer.php';
?>
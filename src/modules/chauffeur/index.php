<?php
// src/modules/chauffeur/index.php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';

session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . base_url('src/modules/auth/login.php'));
    exit();
}

$page_title = "Chauffeur Bookings Management";

try {
    // Check if table exists first
    $table_check = $pdo->query("SHOW TABLES LIKE 'chauffeur_bookings'");
    $table_exists = $table_check->rowCount() > 0;
    
    if ($table_exists) {
        // Get all chauffeur bookings
        $stmt = $pdo->prepare("
            SELECT cb.*, u.username, u.email as user_email 
            FROM chauffeur_bookings cb 
            LEFT JOIN users u ON cb.customer_id = u.user_id 
            ORDER BY cb.created_at DESC
        ");
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $driver_stmt = $pdo->prepare("SELECT * FROM chauffeur_drivers ORDER BY status, driver_name");
        $driver_stmt->execute();
        $drivers = $driver_stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $bookings = [];
        $drivers = [];
        $table_error = "Chauffeur tables not created yet. Run the SQL file: database/chauffeur_tables.sql";
    }
    
} catch (Exception $e) {
    $bookings = [];
    $drivers = [];
    $error = "Database error: " . $e->getMessage();
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-gradient-chauffeur text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-user-tie me-2"></i>Chauffeur Bookings Management
                        </h4>
                        <a href="<?= base_url('chauffeur.php') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i>New Booking
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (isset($table_error)): ?>
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Setup Required</h5>
                            <p><?= htmlspecialchars($table_error) ?></p>
                            <a href="<?= base_url('database/chauffeur_tables.sql') ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-download me-1"></i>Download SQL File
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h2 class="text-primary mb-0">
                                        <?= isset($bookings) ? count(array_filter($bookings, fn($b) => $b['status'] === 'pending')) : 0 ?>
                                    </h2>
                                    <small class="text-muted">Pending Bookings</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h2 class="text-success mb-0">
                                        <?= isset($bookings) ? count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')) : 0 ?>
                                    </h2>
                                    <small class="text-muted">Confirmed</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h2 class="text-info mb-0">
                                        <?= isset($drivers) ? count(array_filter($drivers, fn($d) => $d['status'] === 'available')) : 0 ?>
                                    </h2>
                                    <small class="text-muted">Available Drivers</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h2 class="text-warning mb-0">
                                        Ksh <?= isset($bookings) ? number_format(array_sum(array_column($bookings, 'total_cost'))) : 0 ?>
                                    </h2>
                                    <small class="text-muted">Total Revenue</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isset($bookings) && !empty($bookings)): ?>
                    <!-- Bookings Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Route</th>
                                    <th>Date & Time</th>
                                    <th>Duration</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <strong>CH<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($booking['customer_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($booking['customer_phone']) ?></small>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-muted">From:</small> 
                                                <?= htmlspecialchars(substr($booking['pickup_location'], 0, 20)) ?>...
                                            </div>
                                            <div>
                                                <small class="text-muted">To:</small> 
                                                <?= htmlspecialchars(substr($booking['destination'], 0, 20)) ?>...
                                            </div>
                                        </td>
                                        <td>
                                            <div><?= date('M d, Y', strtotime($booking['date'])) ?></div>
                                            <small class="text-muted"><?= date('h:i A', strtotime($booking['time'])) ?></small>
                                        </td>
                                        <td><?= $booking['duration_hours'] ?> hrs</td>
                                        <td>
                                            <strong>Ksh <?= number_format($booking['total_cost'], 2) ?></strong>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_badge = [
                                                'pending' => 'warning',
                                                'confirmed' => 'success',
                                                'completed' => 'info',
                                                'cancelled' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $status_badge[$booking['status']] ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <h5>No Chauffeur Bookings Yet</h5>
                            <p>Once customers start booking chauffeur services, they will appear here.</p>
                            <a href="<?= base_url('chauffeur.php') ?>" class="btn btn-primary">
                                <i class="fas fa-user-tie me-2"></i>View Chauffeur Booking Page
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-chauffeur {
    background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%) !important;
}

.table th {
    background-color: #8B4513;
    color: white;
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

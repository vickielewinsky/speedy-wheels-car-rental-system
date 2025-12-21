<?php
// src/modules/vehicles/admin.php - ADMIN ONLY VERSION
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

// Require admin role
requireAdmin();

$page_title = "Manage Vehicles - Admin Panel";
require_once __DIR__ . '/../../includes/header.php';

// Get database connection
try {
    $pdo = getDatabaseConnection();

    // Fetch all vehicles
    $vehicles_stmt = $pdo->query("SELECT * FROM vehicles ORDER BY created_at DESC");
    $vehicles = $vehicles_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $vehicles = [];
    error_log("Database error in vehicles admin: " . $e->getMessage());
}
?>

<div class="container-fluid mt-4">
    <!-- Admin Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-car me-2 text-primary"></i>Manage Vehicles
                <span class="badge bg-danger ms-2">Admin</span>
            </h1>
            <p class="text-muted mb-0">Add, edit, or remove vehicles from the fleet</p>
        </div>
        <div class="btn-group">
            <a href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <a href="add_vehicle.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Vehicle
            </a>
        </div>
    </div>

    <!-- Vehicles Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>All Vehicles
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($vehicles)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Vehicle Details</th>
                                <th>Plate No</th>
                                <th>Daily Rate</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td>#<?php echo $vehicle['vehicle_id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($vehicle['year'] . '  ' . $vehicle['color']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($vehicle['plate_no']); ?></code>
                                    </td>
                                    <td>
                                        <strong class="text-success">Ksh <?php echo number_format($vehicle['daily_rate'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badge = $vehicle['status'] == 'available' ? 
                                            'bg-success' : 'bg-warning';
                                        ?>
                                        <span class="badge <?php echo $status_badge; ?>">
                                            <?php echo ucfirst($vehicle['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit_vehicle.php?id=<?php echo $vehicle['vehicle_id']; ?>" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="deleteVehicle(<?php echo $vehicle['vehicle_id']; ?>)" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-car fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Vehicles Found</h4>
                    <p class="text-muted">Add your first vehicle to get started.</p>
                    <a href="add_vehicle.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add First Vehicle
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteVehicle(vehicleId) {
    if (confirm('Are you sure you want to delete this vehicle? This action cannot be undone.')) {
        // Simple redirect to delete script
        window.location.href = 'delete_vehicle.php?id=' + vehicleId;
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
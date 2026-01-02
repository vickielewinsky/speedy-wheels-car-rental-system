<?php
// src/modules/reports/vehicles_report.php
require_once "../../config/database.php";
require_once "../../helpers/url_helper.php";
require_once "../../includes/auth.php";

// Require authentication and admin role
requireAuthentication();
if (!hasRole('admin')) {
    header('Location: ' . base_url('src/modules/auth/login.php'));
    exit();
}

// Get database connection
try {
    $pdo = getDatabaseConnection();
    
    // Total Vehicles
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM vehicles");
    $totalVehicles = $stmt->fetchColumn();
    
    // Available Vehicles
    $stmt = $pdo->query("SELECT COUNT(*) as available FROM vehicles WHERE status = 'available'");
    $availableVehicles = $stmt->fetchColumn();
    
    // Rented Vehicles
    $stmt = $pdo->query("SELECT COUNT(*) as rented FROM vehicles WHERE status = 'booked'");
    $rentedVehicles = $stmt->fetchColumn();
    
    // Maintenance Vehicles
    $stmt = $pdo->query("SELECT COUNT(*) as maintenance FROM vehicles WHERE status = 'maintenance'");
    $maintenanceVehicles = $stmt->fetchColumn();
    
    // Vehicle Status Breakdown
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM vehicles GROUP BY status ORDER BY status");
    $statusBreakdown = $stmt->fetchAll();
    
    // Top Earning Vehicles
    $stmt = $pdo->query("
        SELECT 
            v.*,
            COALESCE(SUM(b.total_amount), 0) as total_earnings,
            COUNT(b.booking_id) as total_bookings
        FROM vehicles v
        LEFT JOIN bookings b ON v.vehicle_id = b.vehicle_id AND b.status = 'completed'
        GROUP BY v.vehicle_id
        ORDER BY total_earnings DESC
        LIMIT 5
    ");
    $topVehicles = $stmt->fetchAll();
    
    // Vehicle Utilization (booked days vs total days in period)
    $stmt = $pdo->query("
        SELECT 
            v.vehicle_id,
            v.plate_no,
            v.model,
            v.make,
            COUNT(b.booking_id) as booking_count,
            SUM(DATEDIFF(b.end_date, b.start_date) + 1) as booked_days
        FROM vehicles v
        LEFT JOIN bookings b ON v.vehicle_id = b.vehicle_id AND b.status = 'completed'
        GROUP BY v.vehicle_id
        ORDER BY booking_count DESC
        LIMIT 10
    ");
    $utilizationData = $stmt->fetchAll();
    
    // Recent Vehicle Additions
    $stmt = $pdo->query("
        SELECT * FROM vehicles 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $recentVehicles = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Vehicles Report Error: " . $e->getMessage());
    // Set default values
    $totalVehicles = $availableVehicles = $rentedVehicles = $maintenanceVehicles = 0;
    $statusBreakdown = $topVehicles = $utilizationData = $recentVehicles = [];
}

$page_title = "Vehicles Report - Speedy Wheels";
require_once "../../includes/header.php";
?>

<div class="container-fluid mt-4">
    <!-- Header with Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-car me-2 text-info"></i>Vehicles Report
            </h1>
            <p class="text-muted mb-0">Fleet utilization and performance</p>
        </div>
        <div class="btn-group">
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <button class="btn btn-success" onclick="exportVehicles()">
                <i class="fas fa-download me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Vehicle Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Vehicles</h6>
                    <h3 class="card-title"><?php echo number_format($totalVehicles); ?></h3>
                    <small><i class="fas fa-car me-1"></i> In fleet</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Available</h6>
                    <h3 class="card-title"><?php echo number_format($availableVehicles); ?></h3>
                    <small><i class="fas fa-check-circle me-1"></i> Ready for rent</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Rented</h6>
                    <h3 class="card-title"><?php echo number_format($rentedVehicles); ?></h3>
                    <small><i class="fas fa-road me-1"></i> Currently rented</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Under Maintenance</h6>
                    <h3 class="card-title"><?php echo number_format($maintenanceVehicles); ?></h3>
                    <small><i class="fas fa-tools me-1"></i> Being serviced</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Charts and Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Fleet Status</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($statusBreakdown)): ?>
                        <div style="height: 250px;">
                            <canvas id="fleetChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No fleet data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Earning Vehicles</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($topVehicles)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($topVehicles as $index => $vehicle): ?>
                                <div class="list-group-item px-0 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">
                                                <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>
                                            </h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($vehicle['plate_no']); ?></small>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-success">KES <?php echo number_format($vehicle['total_earnings'], 2); ?></strong>
                                            <br>
                                            <small><?php echo $vehicle['total_bookings']; ?> bookings</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No earnings data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Utilization Stats</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Fleet Availability</small>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $totalVehicles > 0 ? ($availableVehicles / $totalVehicles * 100) : 0; ?>%"></div>
                        </div>
                        <small class="text-muted"><?php echo $availableVehicles; ?> of <?php echo $totalVehicles; ?> vehicles available</small>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Utilization Rate</small>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: <?php echo $totalVehicles > 0 ? ($rentedVehicles / $totalVehicles * 100) : 0; ?>%"></div>
                        </div>
                        <small class="text-muted"><?php echo $rentedVehicles; ?> of <?php echo $totalVehicles; ?> vehicles rented</small>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Maintenance Rate</small>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" style="width: <?php echo $totalVehicles > 0 ? ($maintenanceVehicles / $totalVehicles * 100) : 0; ?>%"></div>
                        </div>
                        <small class="text-muted"><?php echo $maintenanceVehicles; ?> of <?php echo $totalVehicles; ?> under maintenance</small>
                    </div>
                    
                    <hr>
                    <div>
                        <small class="text-muted">Average Daily Rate</small>
                        <div class="d-flex justify-content-between">
                            <span>All Vehicles</span>
                            <strong>
                                <?php 
                                $stmt = $pdo->query("SELECT COALESCE(AVG(daily_rate), 0) as avg_rate FROM vehicles");
                                $avgRate = $stmt->fetchColumn();
                                echo 'KES ' . number_format($avgRate, 2);
                                ?>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Utilization Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Vehicle Utilization</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($utilizationData)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Plate No.</th>
                                <th>Bookings</th>
                                <th>Booked Days</th>
                                <th>Status</th>
                                <th>Daily Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($utilizationData as $vehicle): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($vehicle['plate_no']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo $vehicle['booking_count']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $vehicle['booked_days'] ?? 0; ?> days
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        // Get current status from database
                                        $stmt = $pdo->prepare("SELECT status FROM vehicles WHERE vehicle_id = ?");
                                        $stmt->execute([$vehicle['vehicle_id']]);
                                        $status = $stmt->fetchColumn();
                                        
                                        $statusColors = [
                                            'available' => 'success',
                                            'booked' => 'warning',
                                            'maintenance' => 'danger'
                                        ];
                                        $color = $statusColors[$status] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold">
                                        KES <?php 
                                        $stmt = $pdo->prepare("SELECT daily_rate FROM vehicles WHERE vehicle_id = ?");
                                        $stmt->execute([$vehicle['vehicle_id']]);
                                        $rate = $stmt->fetchColumn();
                                        echo number_format($rate, 2);
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No utilization data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Vehicle Additions -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Vehicle Additions</h5>
            <a href="<?php echo base_url('src/modules/vehicles/admin.php'); ?>" class="btn btn-sm btn-outline-primary">
                Manage Vehicles
            </a>
        </div>
        <div class="card-body">
            <?php if (!empty($recentVehicles)): ?>
                <div class="row">
                    <?php foreach ($recentVehicles as $vehicle): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($vehicle['plate_no']); ?></small>
                                        </div>
                                        <span class="badge bg-<?php 
                                            echo $vehicle['status'] == 'available' ? 'success' : 
                                                   ($vehicle['status'] == 'booked' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($vehicle['status']); ?>
                                        </span>
                                    </div>
                                    <div class="row text-center mt-3">
                                        <div class="col-6">
                                            <small class="text-muted">Year</small>
                                            <p class="mb-0 fw-bold"><?php echo $vehicle['year']; ?></p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Daily Rate</small>
                                            <p class="mb-0 fw-bold text-success">KES <?php echo number_format($vehicle['daily_rate'], 2); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-car fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No vehicles found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($statusBreakdown)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Fleet Status Chart
const fleetCtx = document.getElementById('fleetChart').getContext('2d');
const fleetChart = new Chart(fleetCtx, {
    type: 'doughnut',
    data: {
        labels: [<?php echo implode(',', array_map(function($item) { 
            return "'" . ucfirst($item['status']) . "'"; 
        }, $statusBreakdown)); ?>],
        datasets: [{
            data: [<?php echo implode(',', array_map(function($item) { 
                return $item['count']; 
            }, $statusBreakdown)); ?>],
            backgroundColor: [
                '#4CAF50', // Available - Green
                '#FF9800', // Booked - Orange
                '#F44336'  // Maintenance - Red
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Export function
function exportVehicles() {
    const data = [
        ['Status', 'Count'],
        <?php foreach ($statusBreakdown as $item): ?>
        ['<?php echo ucfirst($item['status']); ?>', <?php echo $item['count']; ?>],
        <?php endforeach; ?>
    ];
    
    let csvContent = "data:text/csv;charset=utf-8,";
    data.forEach(row => {
        csvContent += row.join(",") + "\r\n";
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "vehicles_report_<?php echo date('Y-m-d'); ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
<?php endif; ?>

<?php require_once "../../includes/footer.php"; ?>
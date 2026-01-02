<?php
// src/modules/reports/index.php - ENHANCED VERSION WITH MPESA SUPPORT
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
    
    // Fetch quick statistics based on your actual schema
    $stats = [];
    
    // Total Bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $stats['total_bookings'] = $stmt->fetchColumn() ?? 0;
    
    // Revenue This Month (combined from payments and mpesa_transactions)
    $currentMonth = date('Y-m');
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as revenue 
        FROM payments 
        WHERE status = 'completed' AND DATE_FORMAT(created_at, '%Y-%m') = ?
        
        UNION ALL
        
        SELECT COALESCE(SUM(amount), 0) as revenue 
        FROM mpesa_transactions 
        WHERE status = 'completed' AND DATE_FORMAT(created_at, '%Y-%m') = ?
    ");
    $stmt->execute([$currentMonth, $currentMonth]);
    
    $stats['revenue_month'] = 0;
    while ($row = $stmt->fetch()) {
        $stats['revenue_month'] += $row['revenue'];
    }
    
    // Active Vehicles (available)
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM vehicles WHERE status = 'available'");
    $stats['active_vehicles'] = $stmt->fetchColumn() ?? 0;
    
    // Total Customers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM customers");
    $stats['total_customers'] = $stmt->fetchColumn() ?? 0;
    
    // Total Revenue (for dashboard display)
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'
        UNION ALL
        SELECT COALESCE(SUM(amount), 0) as total FROM mpesa_transactions WHERE status = 'completed'
    ");
    
    $stats['total_revenue'] = 0;
    while ($row = $stmt->fetch()) {
        $stats['total_revenue'] += $row['total'];
    }
    
} catch (PDOException $e) {
    // If tables don't exist yet or there's an error, set default values
    $stats = [
        'total_bookings' => 0,
        'revenue_month' => 0,
        'active_vehicles' => 0,
        'total_customers' => 0,
        'total_revenue' => 0
    ];
    // Log error if needed
    error_log("Reports statistics error: " . $e->getMessage());
}

$page_title = "Reports - Speedy Wheels";
require_once "../../includes/header.php";
?>

<div class="container-fluid mt-4">
    <!-- Header with Back to Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-chart-line me-2 text-danger"></i>Reports & Analytics
            </h1>
            <p class="text-muted mb-0">View business insights and analytics</p>
        </div>
        <div class="btn-group">
            <a href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <button class="btn btn-danger" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Reports
            </button>
        </div>
    </div>

    <!-- Report Cards -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 bg-primary text-white hover-card">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-3x mb-3 opacity-50"></i>
                    <h3>Revenue Report</h3>
                    <p>View income and payment analytics</p>
                    <a href="revenue_report.php" class="btn btn-light">View Report</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 bg-success text-white hover-card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x mb-3 opacity-50"></i>
                    <h3>Bookings Report</h3>
                    <p>Booking trends and performance</p>
                    <a href="bookings_report.php" class="btn btn-light">View Report</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 bg-info text-white hover-card">
                <div class="card-body text-center">
                    <i class="fas fa-car fa-3x mb-3 opacity-50"></i>
                    <h3>Vehicles Report</h3>
                    <p>Fleet utilization and performance</p>
                    <a href="vehicles_report.php" class="btn btn-light">View Report</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats - Now with clickable links -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Quick Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <a href="bookings_report.php" class="text-decoration-none">
                                <div class="card bg-light hover-shadow">
                                    <div class="card-body">
                                        <h3 class="text-primary"><?php echo htmlspecialchars($stats['total_bookings']); ?></h3>
                                        <p class="mb-0 text-dark">Total Bookings</p>
                                        <small class="text-muted">All time bookings</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="revenue_report.php" class="text-decoration-none">
                                <div class="card bg-light hover-shadow">
                                    <div class="card-body">
                                        <h3 class="text-success">KES <?php echo number_format($stats['revenue_month'], 2); ?></h3>
                                        <p class="mb-0 text-dark">Revenue This Month</p>
                                        <small class="text-muted"><?php echo date('F Y'); ?></small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="vehicles_report.php" class="text-decoration-none">
                                <div class="card bg-light hover-shadow">
                                    <div class="card-body">
                                        <h3 class="text-warning"><?php echo htmlspecialchars($stats['active_vehicles']); ?></h3>
                                        <p class="mb-0 text-dark">Active Vehicles</p>
                                        <small class="text-muted">Ready for rent</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo base_url('src/modules/customers/index.php'); ?>" class="text-decoration-none">
                                <div class="card bg-light hover-shadow">
                                    <div class="card-body">
                                        <h3 class="text-info"><?php echo htmlspecialchars($stats['total_customers']); ?></h3>
                                        <p class="mb-0 text-dark">Total Customers</p>
                                        <small class="text-muted">Registered customers</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Additional Stats Row -->
                    <div class="row text-center mt-3">
                        <div class="col-md-6 mb-3">
                            <a href="revenue_report.php" class="text-decoration-none">
                                <div class="card bg-light hover-shadow">
                                    <div class="card-body">
                                        <h4 class="text-danger">KES <?php echo number_format($stats['total_revenue'], 2); ?></h4>
                                        <p class="mb-0 text-dark">Total Revenue</p>
                                        <small class="text-muted">All time income</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h4 class="text-purple">
                                        <?php 
                                        // Calculate average booking value
                                        try {
                                            $stmt = $pdo->query("
                                                SELECT COALESCE(AVG(total_amount), 0) as avg_booking 
                                                FROM bookings 
                                                WHERE status = 'completed'
                                            ");
                                            $avgBooking = $stmt->fetchColumn();
                                            echo 'KES ' . number_format($avgBooking, 2);
                                        } catch (Exception $e) {
                                            echo 'KES 0.00';
                                        }
                                        ?>
                                    </h4>
                                    <p class="mb-0 text-dark">Avg. Booking Value</p>
                                    <small class="text-muted">Per completed booking</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Report Options -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Report Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <i class="fas fa-download fa-2x text-primary mb-3"></i>
                                    <h5>Export Reports</h5>
                                    <p class="text-muted">Export data to CSV, Excel, or PDF</p>
                                    <div class="btn-group">
                                        <button class="btn btn-outline-primary btn-sm" onclick="exportReport('csv')">
                                            CSV
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="exportReport('excel')">
                                            Excel
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="exportReport('pdf')">
                                            PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <i class="fas fa-filter fa-2x text-success mb-3"></i>
                                    <h5>Date Range Reports</h5>
                                    <p class="text-muted">Generate reports for specific periods</p>
                                    <div class="input-group mb-3">
                                        <input type="date" class="form-control form-control-sm" id="startDate">
                                        <input type="date" class="form-control form-control-sm" id="endDate">
                                    </div>
                                    <button class="btn btn-outline-success" onclick="generateDateRangeReport()">
                                        <i class="fas fa-filter me-1"></i> Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-pie fa-2x text-info mb-3"></i>
                                    <h5>Real-time Analytics</h5>
                                    <p class="text-muted">Live dashboard updates</p>
                                    <button class="btn btn-outline-info" onclick="refreshStats()">
                                        <i class="fas fa-sync-alt me-1"></i> Refresh Stats
                                    </button>
                                    <div class="mt-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="liveUpdate">
                                            <label class="form-check-label" for="liveUpdate">
                                                Live Updates
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    transition: all 0.3s ease;
}

.text-purple {
    color: #6f42c1 !important;
}
</style>

<script>
function exportReport(format) {
    alert(format.toUpperCase() + ' export feature coming soon!');
}

function generateDateRangeReport() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date cannot be after end date');
        return;
    }
    
    alert('Generating report for ' + startDate + ' to ' + endDate + '\nFeature coming soon!');
}

function refreshStats() {
    location.reload();
}

// Auto-refresh if live update is enabled
document.getElementById('liveUpdate')?.addEventListener('change', function(e) {
    if (e.target.checked) {
        alert('Live updates enabled - page will refresh every 30 seconds');
        setInterval(refreshStats, 30000);
    }
});

// Set default dates for date range (last 30 days)
const today = new Date();
const lastMonth = new Date();
lastMonth.setDate(today.getDate() - 30);

document.getElementById('startDate').value = lastMonth.toISOString().split('T')[0];
document.getElementById('endDate').value = today.toISOString().split('T')[0];
</script>

<?php require_once "../../includes/footer.php"; ?>
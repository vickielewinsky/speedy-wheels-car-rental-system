<?php
// src/modules/reports/index.php - ADD BACK TO DASHBOARD BUTTON
require_once "../../config/database.php";
require_once "../../helpers/url_helper.php";
require_once "../../includes/auth.php";

// Require authentication and admin role
requireAuthentication();
if (!hasRole('admin')) {
    header('Location: ' . base_url('src/modules/auth/login.php'));
    exit();
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
            <div class="card border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-3x mb-3 opacity-50"></i>
                    <h3>Revenue Report</h3>
                    <p>View income and payment analytics</p>
                    <a href="revenue_report.php" class="btn btn-light">View Report</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x mb-3 opacity-50"></i>
                    <h3>Bookings Report</h3>
                    <p>Booking trends and performance</p>
                    <a href="bookings_report.php" class="btn btn-light">View Report</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-car fa-3x mb-3 opacity-50"></i>
                    <h3>Vehicles Report</h3>
                    <p>Fleet utilization and performance</p>
                    <a href="vehicles_report.php" class="btn btn-light">View Report</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Quick Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-primary">-</h3>
                                    <p class="mb-0">Total Bookings</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-success">-</h3>
                                    <p class="mb-0">Revenue This Month</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-warning">-</h3>
                                    <p class="mb-0">Active Vehicles</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-info">-</h3>
                                    <p class="mb-0">Total Customers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>
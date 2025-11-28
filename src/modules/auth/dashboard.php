<?php
// src/modules/auth/dashboard.php
require_once "../../config/database.php";
require_once "Auth.php";
require_once "../../includes/auth.php";

// Require authentication
requireAuthentication();

$page_title = "Dashboard - Speedy Wheels";
require_once "../../includes/header.php";

$auth = new Auth();
$current_user = $auth->getCurrentUser();
$is_admin = hasRole('admin');
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <div class="btn-group">
                    <a href="<?php echo base_url('index.php'); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card dashboard-welcome-card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3>Welcome back, <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>! 👋</h3>
                            <p class="mb-0">You're logged in as 
                                <span class="badge bg-light text-dark">
                                    <?php echo ucfirst($current_user['user_role']); ?>
                                </span>
                            </p>
                            <?php if ($is_admin): ?>
                            <p class="mt-2"><small>You have access to administrative functions</small></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fas fa-user-circle fa-5x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ADMIN QUICK ACCESS PANEL -->
    <?php if ($is_admin): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Admin Quick Access</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-0 shadow-sm admin-card">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-car fa-3x text-primary mb-3"></i>
                                    <h6>Manage Vehicles</h6>
                                    <a href="<?= base_url('src/modules/vehicles/index.php') ?>" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-arrow-right me-1"></i>Go
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-0 shadow-sm admin-card">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                                    <h6>Manage Bookings</h6>
                                    <a href="<?= base_url('src/modules/bookings/index.php') ?>" class="btn btn-success btn-sm w-100">
                                        <i class="fas fa-arrow-right me-1"></i>Go
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-0 shadow-sm admin-card">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-users fa-3x text-info mb-3"></i>
                                    <h6>Manage Customers</h6>
                                    <a href="<?= base_url('src/modules/customers/index.php') ?>" class="btn btn-info btn-sm w-100">
                                        <i class="fas fa-arrow-right me-1"></i>Go
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-0 shadow-sm admin-card">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-file-invoice-dollar fa-3x text-warning mb-3"></i>
                                    <h6>Payment History</h6>
                                    <a href="<?= base_url('src/modules/payments/payment.php') ?>" class="btn btn-warning btn-sm w-100">
                                        <i class="fas fa-arrow-right me-1"></i>View
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-0 shadow-sm admin-card">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-chart-line fa-3x text-danger mb-3"></i>
                                    <h6>Reports</h6>
                                    <a href="<?= base_url('src/modules/reports/index.php') ?>" class="btn btn-danger btn-sm w-100">
                                        <i class="fas fa-arrow-right me-1"></i>Go
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-0 shadow-sm admin-card">
                                <div class="card-body text-center p-3">
                                    <i class="fas fa-envelope fa-3x text-secondary mb-3"></i>
                                    <h6>Notifications</h6>
                                    <a href="<?= base_url('src/modules/notifications/index.php') ?>" class="btn btn-secondary btn-sm w-100">
                                        <i class="fas fa-arrow-right me-1"></i>Go
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- USER INFO & QUICK ACTIONS -->
    <div class="row">
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Profile Information</h5>
                </div>
                <div class="card-body">
                    <table class="table profile-table">
                        <tr>
                            <th>Username:</th>
                            <td><?php echo htmlspecialchars($current_user['username']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($current_user['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?php echo htmlspecialchars($current_user['phone'] ?? 'Not provided'); ?></td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td>
                                <span class="badge bg-<?php echo $current_user['user_role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                    <?php echo ucfirst($current_user['user_role']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Member since:</th>
                            <td><?php echo date('F j, Y', strtotime($current_user['created_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card dashboard-card quick-actions-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-rocket"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo base_url('src/modules/vehicles/index.php'); ?>" class="btn btn-outline-primary text-start">
                            <i class="fas fa-car me-2"></i> Browse Vehicles
                        </a>
                        <a href="<?php echo base_url('src/modules/bookings/create_booking.php'); ?>" class="btn btn-outline-success text-start">
                            <i class="fas fa-calendar-plus me-2"></i> Make a Booking
                        </a>
                        <a href="<?php echo base_url('src/modules/auth/reset_password.php'); ?>" class="btn btn-outline-warning text-start">
                            <i class="fas fa-key me-2"></i> Change Password
                        </a>
                        <?php if ($is_admin): ?>
                            <a href="<?php echo base_url('src/modules/reports/index.php'); ?>" class="btn btn-outline-danger text-start">
                                <i class="fas fa-chart-bar me-2"></i> View Reports
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ADMIN STATS -->
    <?php if ($is_admin): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>System Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-primary">-</h3>
                                    <p class="mb-0">Total Vehicles</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-success">-</h3>
                                    <p class="mb-0">Total Bookings</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-warning">-</h3>
                                    <p class="mb-0">Total Customers</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-danger">-</h3>
                                    <p class="mb-0">Total Revenue</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted text-center mt-3"><small>Statistics will be available when database methods are implemented</small></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activity</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted text-center">Your recent bookings and payments will appear here.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-welcome-card.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.admin-card {
    transition: transform 0.2s ease-in-out;
}

.admin-card:hover {
    transform: translateY(-5px);
}

.profile-table th {
    width: 40%;
    font-weight: 600;
}

.dashboard-card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.quick-actions-card .btn {
    padding: 10px 15px;
    text-align: left;
}
</style>

<?php require_once "../../includes/footer.php"; ?>
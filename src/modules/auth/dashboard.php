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
            <div class="card dashboard-welcome-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3>Welcome back, <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>! 👋</h3>
                            <p class="mb-0">You're logged in as <strong><?php echo htmlspecialchars($current_user['user_role']); ?></strong></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fas fa-user-circle fa-5x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="row">
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header bg-info text-white">
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
                        <a href="<?php echo base_url('src/modules/payments/payment.php'); ?>" class="btn btn-outline-info text-start">
                            <i class="fas fa-money-bill-wave me-2"></i> Make Payment
                        </a>
                        <?php if (hasRole('admin')): ?>
                            <a href="<?php echo base_url('src/modules/customers/index.php'); ?>" class="btn btn-outline-warning text-start">
                                <i class="fas fa-users me-2"></i> Manage Customers
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity (Placeholder) -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activity</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted text-center">Your recent bookings and payments will appear here.</p>
                    <!-- This can be expanded to show actual user activity -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>
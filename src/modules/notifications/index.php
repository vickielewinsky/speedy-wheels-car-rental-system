<?php
// src/modules/notifications/index.php - ADD BACK TO DASHBOARD BUTTON
require_once "../../config/database.php";
require_once __DIR__ . "/../../helpers/url_helper.php";
require_once "../../includes/auth.php";

// Require authentication and admin role
requireAuthentication();
if (!hasRole('admin')) {
    header('Location: ' . base_url('src/modules/auth/login.php'));
    exit();
}

$page_title = "Notifications - Speedy Wheels";
require_once "../../includes/header.php";
?>

<div class="container-fluid mt-4">
    <!-- Header with Back to Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-envelope me-2 text-secondary"></i>Notifications
            </h1>
            <p class="text-muted mb-0">Manage system notifications and alerts</p>
        </div>
        <div class="btn-group">
            <a href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <button class="btn btn-secondary">
                <i class="fas fa-cog me-1"></i> Settings
            </button>
        </div>
    </div>

    <!-- Notifications Content -->
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Recent Notifications</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">New Booking Received</h6>
                                <p class="mb-1 text-muted">Customer John Doe booked Toyota RAV4 for 3 days</p>
                                <small class="text-muted">2 hours ago</small>
                            </div>
                            <span class="badge bg-success">New</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Payment Received</h6>
                                <p class="mb-1 text-muted">Payment of Ksh 15,000 received for booking #102</p>
                                <small class="text-muted">5 hours ago</small>
                            </div>
                            <span class="badge bg-info">Payment</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Vehicle Maintenance Due</h6>
                                <p class="mb-1 text-muted">Toyota Axio (KCD 123A) is due for service</p>
                                <small class="text-muted">1 day ago</small>
                            </div>
                            <span class="badge bg-warning">Maintenance</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Notification Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                        <label class="form-check-label" for="emailNotifications">
                            Email Notifications
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="smsNotifications" checked>
                        <label class="form-check-label" for="smsNotifications">
                            SMS Notifications
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="bookingAlerts" checked>
                        <label class="form-check-label" for="bookingAlerts">
                            New Booking Alerts
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="paymentAlerts" checked>
                        <label class="form-check-label" for="paymentAlerts">
                            Payment Alerts
                        </label>
                    </div>
                    <button class="btn btn-primary w-100">Save Settings</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>
<?php
// src/modules/notifications/index.php
require_once '../../config/database.php';  // Fixed path - was ../../../config/
require_once '../../includes/auth.php';
require_once '../../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$page_title = "Notifications - " . APP_NAME;
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h1><i class="fas fa-envelope"></i> Notifications & Email</h1>
            <p class="lead">Manage your email preferences and notifications</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-bell"></i> Notification Settings</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="bookingNotifications" checked>
                                <label class="form-check-label" for="bookingNotifications">
                                    Booking Confirmations
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="paymentNotifications" checked>
                                <label class="form-check-label" for="paymentNotifications">
                                    Payment Receipts
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="reminderNotifications">
                                <label class="form-check-label" for="reminderNotifications">
                                    Rental Reminders
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="promotionalNotifications">
                                <label class="form-check-label" for="promotionalNotifications">
                                    Promotional Offers
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-paper-plane"></i> Email Test</h5>
                </div>
                <div class="card-body">
                    <p>Test the email system by sending a test notification:</p>
                    <form method="POST" action="send_test_email.php">
                        <div class="mb-3">
                            <label for="testEmail" class="form-label">Test Email Address</label>
                            <input type="email" class="form-control" id="testEmail" name="test_email" 
                                   value="<?php echo $_SESSION['email'] ?? ''; ?>" required>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i> Send Test Email
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-cog"></i> Email System Status</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <strong>Email System: Active</strong>
                        <p class="mb-0 mt-2">The email notification system is ready to send:</p>
                        <ul class="mb-0">
                            <li>Booking confirmations</li>
                            <li>Payment receipts</li>
                            <li>Rental reminders</li>
                            <li>System notifications</li>
                        </ul>
                    </div>
                    <a href="<?php echo base_url('test_email_web.php'); ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-vial"></i> Run Email Diagnostics
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
<?php
// src/modules/auth/get_receipt.php
session_start();
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['booking_id'])) {
    die("Booking ID required");
}

$auth = new Auth();
$booking_id = $_GET['booking_id'];
$current_user = $auth->getCurrentUser();

$booking = $auth->getReceiptDetails($booking_id);

if (!$booking) {
    echo "<div class='alert alert-danger'>Receipt not found!</div>";
    exit;
}

// Check permissions
if (!$auth->isAdmin() && $booking['user_id'] != $current_user['id']) {
    echo "<div class='alert alert-danger'>Access denied!</div>";
    exit;
}

$days = ceil((strtotime($booking['return_date']) - strtotime($booking['booking_date'])) / (60 * 60 * 24));
$subtotal = $days * $booking['daily_rate'];
?>

<div class="receipt-container">
    <div class="text-center mb-4">
        <h2 class="text-success"><i class="fas fa-receipt"></i> Payment Receipt</h2>
        <h5>Speedy Wheels Car Rental</h5>
        <p>Receipt #<?php echo htmlspecialchars($booking['reference']); ?></p>
        <hr>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <h5><i class="fas fa-user me-2"></i>Customer Information</h5>
            <table class="table table-sm">
                <tr><th>Name:</th><td><?php echo htmlspecialchars($booking['customer_name']); ?></td></tr>
                <tr><th>Email:</th><td><?php echo htmlspecialchars($booking['email']); ?></td></tr>
                <tr><th>Phone:</th><td><?php echo htmlspecialchars($booking['phone']); ?></td></tr>
            </table>
        </div>
        <div class="col-md-6">
            <h5><i class="fas fa-file-invoice me-2"></i>Receipt Details</h5>
            <table class="table table-sm">
                <tr><th>Receipt No:</th><td><?php echo htmlspecialchars($booking['reference']); ?></td></tr>
                <tr><th>Date:</th><td><?php echo date('F j, Y', strtotime($booking['created_at'])); ?></td></tr>
                <tr><th>Payment Method:</th><td>M-Pesa</td></tr>
                <tr><th>Transaction ID:</th><td><?php echo $booking['transaction_id'] ? htmlspecialchars($booking['transaction_id']) : 'N/A'; ?></td></tr>
            </table>
        </div>
    </div>
    
    <div class="mb-4">
        <h5><i class="fas fa-car me-2"></i>Vehicle Information</h5>
        <table class="table table-sm">
            <tr><th>Vehicle:</th><td><?php echo htmlspecialchars($booking['vehicle_name']); ?></td></tr>
            <tr><th>Make/Model:</th><td><?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model']); ?></td></tr>
            <tr><th>Year:</th><td><?php echo htmlspecialchars($booking['year']); ?></td></tr>
        </table>
    </div>
    
    <div class="mb-4">
        <h5><i class="fas fa-calendar-alt me-2"></i>Rental Details</h5>
        <table class="table table-sm">
            <tr><th>Booking Date:</th><td><?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></td></tr>
            <tr><th>Return Date:</th><td><?php echo date('F j, Y', strtotime($booking['return_date'])); ?></td></tr>
            <tr><th>Duration:</th><td><?php echo $days; ?> days</td></tr>
        </table>
    </div>
    
    <div class="mb-4">
        <h5><i class="fas fa-money-bill-wave me-2"></i>Payment Details</h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Description</th>
                    <th>Days</th>
                    <th>Daily Rate</th>
                    <th>Amount (KES)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Vehicle Rental: <?php echo htmlspecialchars($booking['vehicle_name']); ?></td>
                    <td><?php echo $days; ?></td>
                    <td><?php echo number_format($booking['daily_rate'], 2); ?></td>
                    <td><?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                    <td><strong><?php echo number_format($subtotal, 2); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>Insurance:</strong></td>
                    <td><strong><?php echo number_format($booking['insurance_fee'] ?? 0, 2); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                    <td><strong class="text-success"><?php echo number_format($booking['total_price'], 2); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="text-center mt-4">
        <p class="text-muted">Thank you for choosing Speedy Wheels!</p>
        <p><small>Receipt generated on: <?php echo date('F j, Y \a\t g:i a'); ?></small></p>
    </div>
</div>
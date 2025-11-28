<?php
// src/modules/payments/payment.php - PAYMENT HISTORY FOR ADMINS
require_once "../../config/database.php";
require_once "../../includes/auth.php";

// Require authentication and admin role
requireAuthentication();
if (!hasRole('admin')) {
    header('Location: ' . base_url('src/modules/auth/login.php'));
    exit();
}

$page_title = "Payment History - Speedy Wheels";
require_once "../../includes/header.php";

// Get database connection
try {
    $pdo = getDatabaseConnection();
    
    // Fetch payment history with customer and booking details
    $payments_stmt = $pdo->query("
        SELECT 
            p.payment_id,
            p.booking_id,
            p.amount,
            p.payment_method,
            p.payment_status,
            p.transaction_id,
            p.payment_date,
            p.created_at,
            b.customer_id,
            b.vehicle_id,
            c.name as customer_name,
            c.phone as customer_phone,
            v.make as vehicle_make,
            v.model as vehicle_model,
            v.plate_no as vehicle_plate
        FROM payments p
        LEFT JOIN bookings b ON p.booking_id = b.booking_id
        LEFT JOIN customers c ON b.customer_id = c.customer_id
        LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
        ORDER BY p.created_at DESC
    ");
    $payments = $payments_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get payment statistics
    $stats_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_payments,
            SUM(amount) as total_revenue,
            AVG(amount) as average_payment,
            COUNT(CASE WHEN payment_status = 'completed' THEN 1 END) as completed_payments
        FROM payments
    ");
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $payments = [];
    $stats = ['total_payments' => 0, 'total_revenue' => 0, 'average_payment' => 0, 'completed_payments' => 0];
    error_log("Database error in payment.php: " . $e->getMessage());
}
?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Payment History
            </h1>
            <p class="text-muted mb-0">View and manage all payment transactions</p>
        </div>
        <div class="btn-group">
            <a href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?php echo number_format($stats['total_payments']); ?></h4>
                            <p class="card-text mb-0">Total Payments</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-receipt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">KES <?php echo number_format($stats['total_revenue'], 2); ?></h4>
                            <p class="card-text mb-0">Total Revenue</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">KES <?php echo number_format($stats['average_payment'], 2); ?></h4>
                            <p class="card-text mb-0">Average Payment</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?php echo number_format($stats['completed_payments']); ?></h4>
                            <p class="card-text mb-0">Completed Payments</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>All Payments
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="paymentsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Payment ID</th>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Transaction ID</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo htmlspecialchars($payment['payment_id']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($payment['booking_id']): ?>
                                            <a href="<?php echo base_url('src/modules/bookings/index.php?view=' . $payment['booking_id']); ?>" 
                                               class="text-decoration-none">
                                                #<?php echo htmlspecialchars($payment['booking_id']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($payment['customer_name']): ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($payment['customer_name']); ?></strong>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars($payment['customer_phone']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Customer not found</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($payment['vehicle_make']): ?>
                                            <?php echo htmlspecialchars($payment['vehicle_make'] . ' ' . $payment['vehicle_model']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($payment['vehicle_plate']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-success">KES <?php echo number_format($payment['amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars(ucfirst($payment['payment_method'] ?? 'MPESA')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badge = match($payment['payment_status']) {
                                            'completed' => 'bg-success',
                                            'pending' => 'bg-warning',
                                            'failed' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $status_badge; ?>">
                                            <?php echo htmlspecialchars(ucfirst($payment['payment_status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></code>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y H:i', strtotime($payment['payment_date'] ?? $payment['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Payments Found</h4>
                    <p class="text-muted">Payment records will appear here once customers start making payments.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>
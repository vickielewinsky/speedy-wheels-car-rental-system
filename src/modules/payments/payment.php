<?php
// src/modules/payments/payment.php - Payment History for Admin (FIXED)

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Check if user is admin - if not, redirect to payment form
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: payment_form.php");
    exit();
}

// Include helpers and config
require_once __DIR__ . '/../../helpers/url_helper.php';
require_once __DIR__ . '/../../config/database.php';

$page_title = "Payment History - Speedy Wheels";
include __DIR__ . '/../../includes/header.php';

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=speedy_wheels;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Fetch ALL payment history (admin sees everything) - FIXED column names
    $sql = "SELECT 
                p.payment_id,
                p.booking_id,
                p.amount,
                p.phone,
                p.status as payment_status,
                p.mpesa_receipt_number,
                p.transaction_date,
                p.created_at,
                b.customer_id,
                b.vehicle_id,
                b.start_date,
                b.end_date,
                b.total_amount,
                v.make,
                v.model,
                v.plate_no,
                c.name as customer_name,
                c.email as customer_email,
                c.phone as customer_phone
            FROM payments p
            LEFT JOIN bookings b ON p.booking_id = b.booking_id
            LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
            LEFT JOIN customers c ON b.customer_id = c.customer_id
            ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate statistics
    $total_payments = count($payments);
    $total_revenue = array_sum(array_column($payments, 'amount'));
    $completed_payments = count(array_filter($payments, function($p) {
        return ($p['payment_status'] ?? '') === 'completed';
    }));

} catch (PDOException $e) {
    $payments = [];
    $total_payments = 0;
    $total_revenue = 0;
    $completed_payments = 0;
    $error_message = "Error fetching payment data: " . $e->getMessage();
}
?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Payment History
            </h1>
            <p class="text-muted mb-0">Admin View - All Customer Payments</p>
            <small class="text-success">
                <i class="fas fa-user-shield me-1"></i>Logged in as Administrator
            </small>
        </div>
        <div class="btn-group">
            <a href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
            </a>
            <a href="<?php echo base_url('index.php'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-home me-1"></i> Home
            </a>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1"><?php echo number_format($total_payments); ?></h2>
                            <p class="mb-0 opacity-75">Total Payments</p>
                        </div>
                        <i class="fas fa-receipt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Ksh <?php echo number_format($total_revenue, 2); ?></h2>
                            <p class="mb-0 opacity-75">Total Revenue</p>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Ksh <?php echo $total_payments > 0 ? number_format($total_revenue / $total_payments, 2) : '0.00'; ?></h2>
                            <p class="mb-0 opacity-75">Average Payment</p>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-warning text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1"><?php echo number_format($completed_payments); ?></h2>
                            <p class="mb-0 opacity-75">Completed</p>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 py-3">
            <h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i>All Payment Transactions</span>
                <span class="badge bg-primary"><?php echo count($payments); ?> records</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Payment ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Booking Period</th>
                                <th>Amount</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Receipt No</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($payment['payment_id']); ?></strong></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($payment['transaction_date'] ?? $payment['created_at'])); ?></td>
                                    <td>
                                        <?php if (!empty($payment['customer_name']) || !empty($payment['customer_email'])): ?>
                                            <div><strong><?php echo htmlspecialchars($payment['customer_name'] ?? 'Customer'); ?></strong></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($payment['customer_email'] ?? 'No email'); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Customer ID: <?php echo htmlspecialchars($payment['customer_id']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($payment['make'])): ?>
                                            <div><?php echo htmlspecialchars($payment['make'] . ' ' . $payment['model']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($payment['plate_no'] ?? ''); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($payment['start_date']): ?>
                                            <div><?php echo date('Y-m-d', strtotime($payment['start_date'])); ?></div>
                                            <div>to <?php echo date('Y-m-d', strtotime($payment['end_date'])); ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success fs-6">
                                            Ksh <?php echo number_format($payment['amount'], 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($payment['phone'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badge = [
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger'
                                        ];
                                        $status = $payment['payment_status'] ?? 'pending';
                                        $badge_class = $status_badge[$status] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars(ucfirst($status)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($payment['mpesa_receipt_number']): ?>
                                            <code><?php echo htmlspecialchars($payment['mpesa_receipt_number']); ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info view-details" 
                                                    data-payment='<?php echo htmlspecialchars(json_encode($payment), ENT_QUOTES, 'UTF-8'); ?>'
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary -->
                <div class="card-footer bg-white border-top">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                <strong>Summary:</strong> 
                                <?php echo count($payments); ?> payments • 
                                Total: Ksh <?php echo number_format($total_revenue, 2); ?> •
                                Completed: <?php echo $completed_payments; ?>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                Last updated: <?php echo date('Y-m-d H:i:s'); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Payment Records Found</h4>
                    <p class="text-muted">No customers have made payments yet.</p>
                    <div class="mt-3">
                        <a href="<?php echo base_url('src/modules/bookings/create_booking.php'); ?>" class="btn btn-primary me-2">
                            <i class="fas fa-plus me-1"></i> Create Test Booking
                        </a>
                        <a href="<?php echo base_url('index.php'); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-home me-1"></i> Go to Homepage
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Payment Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            const paymentData = JSON.parse(this.getAttribute('data-payment'));
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            
            let html = `
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label text-muted">Payment ID</label>
                            <div class="form-control-plaintext">#${paymentData.payment_id}</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Customer</label>
                            <div class="form-control-plaintext">
                                <strong>${paymentData.customer_name || 'Customer'}</strong><br>
                                ${paymentData.customer_email || 'No email'}<br>
                                ${paymentData.customer_phone || ''}
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Vehicle</label>
                            <div class="form-control-plaintext">
                                ${paymentData.make || 'N/A'} ${paymentData.model || ''}<br>
                                ${paymentData.plate_no ? 'Plate: ' + paymentData.plate_no : ''}
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Booking Period</label>
                            <div class="form-control-plaintext">
                                ${paymentData.start_date ? new Date(paymentData.start_date).toLocaleDateString() + ' to ' + new Date(paymentData.end_date).toLocaleDateString() : 'N/A'}
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Amount</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-success fs-6">
                                        Ksh ${parseFloat(paymentData.amount).toFixed(2)}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Status</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-${paymentData.payment_status === 'completed' ? 'success' : paymentData.payment_status === 'pending' ? 'warning' : 'danger'}">
                                        ${paymentData.payment_status || 'pending'}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Phone</label>
                                <div class="form-control-plaintext">${paymentData.phone || ''}</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Payment Date</label>
                                <div class="form-control-plaintext">
                                    ${new Date(paymentData.transaction_date || paymentData.created_at).toLocaleString()}
                                </div>
                            </div>
                        </div>
                        
                        ${paymentData.mpesa_receipt_number ? `
                        <div class="mb-3">
                            <label class="form-label text-muted">M-PESA Receipt Number</label>
                            <div class="form-control-plaintext">
                                <code>${paymentData.mpesa_receipt_number}</code>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            document.getElementById('detailsContent').innerHTML = html;
            modal.show();
        });
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

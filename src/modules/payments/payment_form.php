<?php
// Payment form

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'payment.php';
    header("Location: ../../auth/login.php");
    exit();
}

// Check if user is admin - redirect to payment history
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: payment.php");
    exit();
}

require_once __DIR__ . '/../../helpers/url_helper.php';
require_once __DIR__ . '/../../config/database.php';

$page_title = "Make Payment - Speedy Wheels";
include __DIR__ . '/../../includes/header.php';

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=speedy_wheels;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get user info
    $userId = $_SESSION['user_id'];
    
    $sql = "SELECT email FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $customerId = null;
    $customerName = "";
    
    if ($user && isset($user['email'])) {
        $sql = "SELECT customer_id, name FROM customers WHERE email = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['email']]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($customer) {
            $customerId = $customer['customer_id'];
            $customerName = $customer['name'];
            
            // Update user_id if it's null
            $sql = "UPDATE customers SET user_id = ? WHERE customer_id = ? AND (user_id IS NULL OR user_id = '')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $customerId]);
        }
    }
    
    // Now get bookings for this customer
    $pendingBookings = [];
    $paymentHistory = [];
    
    if ($customerId) {
        // Get bookings without completed payments
        $sql = "SELECT b.booking_id, b.start_date, b.end_date, b.total_amount,
                       v.make, v.model, v.plate_no, v.daily_rate, b.status as booking_status
                FROM bookings b
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                WHERE b.customer_id = ? 
                AND b.status IN ('confirmed', 'pending')
                AND NOT EXISTS (
                    SELECT 1 FROM payments p 
                    WHERE p.booking_id = b.booking_id 
                    AND p.status = 'completed'
                )
                ORDER BY b.start_date DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customerId]);
        $pendingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get payment history
        $sql = "SELECT p.*, v.make, v.model
                FROM payments p
                JOIN bookings b ON p.booking_id = b.booking_id
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                WHERE b.customer_id = ?
                ORDER BY p.created_at DESC
                LIMIT 10";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customerId]);
        $paymentHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    $pendingBookings = [];
    $paymentHistory = [];
    error_log("Payment Form Error: " . $e->getMessage());
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-credit-card me-2 text-primary"></i>Make Payment
                    </h1>
                    <p class="text-muted mb-0">Complete payments for your bookings</p>
                </div>
                <a href="<?php echo base_url('index.php'); ?>" class="btn btn-outline-primary">
                    <i class="fas fa-home me-1"></i> Home
                </a>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Customer Info -->
            <div class="alert alert-success mb-3">
                <i class="fas fa-user me-2"></i>
                <strong>Welcome, <?php echo htmlspecialchars($customerName ?: 'Customer'); ?>!</strong>
                <span class="float-end">
                    Customer ID: <?php echo $customerId ?: 'Not found'; ?>
                </span>
            </div>

            <!-- Pending Bookings Section -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-calendar-check me-2 text-primary"></i>Your Bookings Needing Payment</span>
                        <span class="badge bg-primary"><?php echo count($pendingBookings); ?> pending</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($pendingBookings)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Vehicle</th>
                                        <th>Rental Period</th>
                                        <th>Daily Rate</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingBookings as $booking): ?>
                                        <tr>
                                            <td><strong>#<?php echo htmlspecialchars($booking['booking_id']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model']); ?></td>
                                            <td>
                                                <?php echo date('Y-m-d', strtotime($booking['start_date'])); ?> to 
                                                <?php echo date('Y-m-d', strtotime($booking['end_date'])); ?>
                                            </td>
                                            <td>Ksh <?php echo number_format($booking['daily_rate'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-success fs-6">
                                                    Ksh <?php echo number_format($booking['total_amount'], 2); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $booking['booking_status'] == 'confirmed' ? 'warning' : 'info'; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($booking['booking_status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-primary btn-sm make-payment" 
                                                        data-booking-id="<?php echo $booking['booking_id']; ?>"
                                                        data-amount="<?php echo $booking['total_amount']; ?>">
                                                    <i class="fas fa-credit-card me-1"></i> Pay Now
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4 class="text-success">No Payments Required</h4>
                            <p class="text-muted">All your bookings are paid or you don't have any bookings yet.</p>
                            <a href="<?php echo base_url('src/modules/bookings/create_booking.php'); ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Create New Booking
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Payment Section -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Payment
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($pendingBookings)): ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="p-3 border rounded bg-light">
                                    <h6>Pay with M-PESA</h6>
                                    <p class="small text-muted">Instant payment via mobile money</p>
                                    <select class="form-select mb-2" id="quickBookingSelect">
                                        <option value="">Select a booking to pay</option>
                                        <?php foreach ($pendingBookings as $booking): ?>
                                            <option value="<?php echo $booking['booking_id']; ?>" 
                                                    data-amount="<?php echo $booking['total_amount']; ?>">
                                                Booking #<?php echo $booking['booking_id']; ?> - 
                                                Ksh <?php echo number_format($booking['total_amount'], 2); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-success w-100" id="quickMpesaBtn">
                                        <i class="fab fa-mpesa me-1"></i> Pay Selected
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="p-3 border rounded bg-light">
                                    <h6>Other Options</h6>
                                    <p class="small text-muted">Alternative payment methods</p>
                                    <button class="btn btn-outline-primary w-100 mb-2" disabled>
                                        <i class="fas fa-credit-card me-1"></i> Credit Card (Coming Soon)
                                    </button>
                                    <button class="btn btn-outline-secondary w-100" disabled>
                                        <i class="fas fa-university me-1"></i> Bank Transfer (Coming Soon)
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <p class="text-muted mb-0">No bookings available for payment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Payment History -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2 text-secondary"></i>Recent Payments
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($paymentHistory)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($paymentHistory as $payment): ?>
                                <div class="list-group-item border-0 px-0 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($payment['make'] . ' ' . $payment['model']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($payment['transaction_date'] ?? $payment['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">Ksh <?php echo number_format($payment['amount'], 2); ?></span>
                                            <div>
                                                <small class="text-muted">
                                                    <?php 
                                                    $status = $payment['status'] ?? 'completed';
                                                    echo htmlspecialchars(ucfirst($status)); 
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="payment_status.php" class="btn btn-sm btn-outline-secondary">
                                View All Payments
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No payment history yet.</p>
                            <p class="small text-muted">Your payment history will appear here after making payments.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Support Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-headset me-2 text-info"></i>Need Help?
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <i class="fas fa-phone-alt text-success me-3 fs-5"></i>
                        <div>
                            <h6 class="mb-1">Call Us</h6>
                            <p class="mb-0 text-muted">0799 692 055</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-3">
                        <i class="fas fa-envelope text-primary me-3 fs-5"></i>
                        <div>
                            <h6 class="mb-1">Email Us</h6>
                            <p class="mb-0 text-muted">support@speedywheels.com</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-3">
                        <i class="fab fa-whatsapp text-success me-3 fs-5"></i>
                        <div>
                            <h6 class="mb-1">WhatsApp</h6>
                            <p class="mb-0 text-muted">0799 692 055</p>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="https://wa.me/254799692055" target="_blank" class="btn btn-success btn-sm w-100">
                            <i class="fab fa-whatsapp me-1"></i> Chat Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-credit-card me-2"></i>Complete Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="bookingId" name="booking_id">
                    <input type="hidden" id="amount" name="amount">
                    
                    <div class="mb-3">
                        <label class="form-label">Booking ID</label>
                        <input type="text" class="form-control" id="displayBookingId" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount to Pay</label>
                        <div class="input-group">
                            <span class="input-group-text">Ksh</span>
                            <input type="text" class="form-control" id="displayAmount" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">M-PESA Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text">+254</span>
                            <input type="tel" class="form-control" name="phone" 
                                   placeholder="7XXXXXXXX" maxlength="9" required
                                   value="799692055">
                        </div>
                        <small class="text-muted">Enter your M-PESA registered phone number</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You will receive a prompt on your phone to confirm the payment.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmPaymentBtn">
                    <i class="fas fa-check me-1"></i> Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    
    // Make payment buttons
    document.querySelectorAll('.make-payment').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            const amount = this.getAttribute('data-amount');
            
            document.getElementById('bookingId').value = bookingId;
            document.getElementById('amount').value = amount;
            document.getElementById('displayBookingId').value = '#' + bookingId;
            document.getElementById('displayAmount').value = amount;
            
            paymentModal.show();
        });
    });
    
    // Quick M-PESA button
    document.getElementById('quickMpesaBtn').addEventListener('click', function() {
        const select = document.getElementById('quickBookingSelect');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption.value) {
            const bookingId = selectedOption.value;
            const amount = selectedOption.getAttribute('data-amount');
            
            document.getElementById('bookingId').value = bookingId;
            document.getElementById('amount').value = amount;
            document.getElementById('displayBookingId').value = '#' + bookingId;
            document.getElementById('displayAmount').value = amount;
            
            paymentModal.show();
        } else {
            alert('Please select a booking to pay.');
        }
    });
    
    // Confirm payment
    document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
        const form = document.getElementById('paymentForm');
        const formData = new FormData(form);
        
        // Validate phone number
        const phone = formData.get('phone');
        if (!phone || phone.length !== 9 || !phone.startsWith('7')) {
            alert('Please enter a valid M-PESA phone number (7XXXXXXXX)');
            return;
        }
        
        // Show loading
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
        this.disabled = true;
        
        // Send payment request
        fetch('process_payment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Payment successful! Transaction ID: ' + data.transaction_id);
                paymentModal.hide();
                location.reload(); // Refresh page to update status
            } else {
                alert('✗ Payment failed: ' + data.message);
                this.innerHTML = originalText;
                this.disabled = false;
            }
        })
        .catch(error => {
            alert('✗ Error: ' + error);
            this.innerHTML = originalText;
            this.disabled = false;
        });
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

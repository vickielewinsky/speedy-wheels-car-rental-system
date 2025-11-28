<?php
// src/modules/payments/payment_form.php - USER PAYMENT FORM
require_once "../../config/database.php";
require_once "mpesa_processor.php";

$page_title = "MPESA Payment - Speedy Wheels";
require_once "../../includes/header.php";

// Get booking details from URL parameters
$booking_id = $_GET['booking_id'] ?? 1;
$amount = $_GET['amount'] ?? 6500;
$vehicle_info = $_GET['vehicle'] ?? "Toyota RAV4";

// Use the fixed MPESA processor
$mpesa = new MpesaProcessor();
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-mobile-alt"></i> MPESA Payment</h4>
                </div>
                <div class="card-body">
                    <!-- Success Alert -->
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> MPESA Payment System</h5>
                        <p class="mb-0">Your payment will be processed through our MPESA integration</p>
                    </div>

                    <!-- Booking Summary -->
                    <div class="alert alert-info">
                        <h5>Booking Summary</h5>
                        <div class="row">
                            <div class="col-6"><strong>Vehicle:</strong></div>
                            <div class="col-6"><?php echo htmlspecialchars($vehicle_info); ?></div>
                            
                            <div class="col-6"><strong>Amount:</strong></div>
                            <div class="col-6">KES <?php echo number_format($amount, 2); ?></div>
                            
                            <div class="col-6"><strong>Booking ID:</strong></div>
                            <div class="col-6">#<?php echo $booking_id; ?></div>
                        </div>
                    </div>
                    
                    <!-- Payment Form -->
                    <form method="POST" action="process_payment.php">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-phone"></i> MPESA Phone Number</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="254712345678" required 
                                   placeholder="2547XXXXXXXX"
                                   pattern="2547[0-9]{8}"
                                   title="Format: 2547XXXXXXXX">
                            <div class="form-text">Enter your MPESA registered phone number</div>
                        </div>
                        
                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                        <input type="hidden" name="vehicle_info" value="<?php echo htmlspecialchars($vehicle_info); ?>">
                        
                        <button type="submit" class="btn btn-success w-100 py-3">
                            <i class="fas fa-bolt"></i> PAY KES <?php echo number_format($amount, 2); ?> VIA MPESA
                        </button>
                    </form>
                    
                    <!-- Demo Instructions -->
                    <div class="mt-4 alert alert-warning">
                        <h6><i class="fas fa-info-circle"></i> Demo Instructions</h6>
                        <p class="mb-0 small">
                            <strong>Test Phone:</strong> Use 254712345678 for testing<br>
                            <strong>Simulation:</strong> This demo simulates MPESA STK Push<br>
                            <strong>Real Integration:</strong> Ready for Safaricom Daraja API
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>
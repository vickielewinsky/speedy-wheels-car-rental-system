<?php
// src/modules/payments/payment_status.php
require_once "../../config/database.php";
require_once "mpesa_processor.php";

$transaction_code = $_GET['transaction_code'] ?? '';

$page_title = "Payment Status - Speedy Wheels";
require_once "../../includes/header.php";

$mpesa = new MpesaProcessor();
$transaction = $mpesa->getTransactionStatus($transaction_code);
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-receipt"></i> Payment Status</h4>
                </div>
                <div class="card-body">
                    <?php if ($transaction && $transaction['status'] !== 'not_found'): ?>
                        <!-- Transaction Found -->
                        <div class="text-center mb-4">
                            <i class="fas fa-file-invoice-dollar fa-4x text-info mb-3"></i>
                            <h3>Transaction Details</h3>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Transaction Info</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Transaction Code:</strong> <?php echo htmlspecialchars($transaction['transaction_code']); ?></p>
                                        <p><strong>Status:</strong> 
                                            <span class="badge bg-<?php echo $transaction['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($transaction['status']); ?>
                                            </span>
                                        </p>
                                        <p><strong>Amount:</strong> KES <?php echo number_format($transaction['amount'], 2); ?></p>
                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($transaction['phone']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Timestamps</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Created:</strong> <?php echo date('M j, Y H:i:s', strtotime($transaction['created_at'])); ?></p>
                                        <?php if ($transaction['updated_at']): ?>
                                            <p><strong>Updated:</strong> <?php echo date('M j, Y H:i:s', strtotime($transaction['updated_at'])); ?></p>
                                        <?php endif; ?>
                                        <p><strong>Booking ID:</strong> #<?php echo htmlspecialchars($transaction['booking_id']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Transaction Not Found -->
                        <div class="text-center mb-4">
                            <i class="fas fa-search fa-4x text-warning mb-3"></i>
                            <h3 class="text-warning">Transaction Not Found</h3>
                            <p>The transaction code "<?php echo htmlspecialchars($transaction_code); ?>" was not found in our system.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="mt-4 text-center">
                        <a href="payment.php" class="btn btn-primary">
                            <i class="fas fa-credit-card"></i> Make Another Payment
                        </a>
                        <a href="<?php echo base_url('index.php'); ?>" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>
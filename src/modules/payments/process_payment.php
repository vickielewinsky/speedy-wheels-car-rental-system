<?php
require_once "../../config/database.php";
require_once "mpesa_processor.php";

if ($_POST) {
    $phone = $_POST['phone'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $booking_id = $_POST['booking_id'] ?? '';
    $vehicle_info = $_POST['vehicle_info'] ?? '';

    $mpesa = new MpesaProcessor();
    $result = $mpesa->initiateSTKPush($phone, $amount, $booking_id);

    $page_title = "Payment Processing - Speedy Wheels";
    require_once "../../includes/header.php";
    ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header <?php echo $result['success'] ? 'bg-success' : 'bg-danger'; ?> text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-<?php echo $result['success'] ? 'check-circle' : 'times-circle'; ?>"></i>
                            Payment Processing Result
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($result['success']): ?>
                            <!-- SUCCESS -->
                            <div class="text-center mb-4">
                                <i class="fas fa-mobile-alt fa-4x text-success mb-3"></i>
                                <h3 class="text-success">Payment Request Sent!</h3>
                            </div>

                            <div class="alert alert-success">
                                <h5><i class="fas fa-info-circle"></i> What Happens Next:</h5>
                                <ol>
                                    <li>You will receive an MPESA prompt on your phone</li>
                                    <li>Enter your MPESA PIN to complete payment</li>
                                    <li>Payment will be confirmed automatically</li>
                                </ol>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Transaction Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Amount:</strong> KES <?php echo number_format($amount, 2); ?></p>
                                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
                                            <p><strong>Booking ID:</strong> #<?php echo htmlspecialchars($booking_id); ?></p>
                                            <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($vehicle_info); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Transaction Info</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Transaction Code:</strong> <?php echo htmlspecialchars($result['transaction_code'] ?? 'N/A'); ?></p>
                                            <p><strong>Status:</strong> <span class="badge bg-warning">Pending Confirmation</span></p>
                                            <p><strong>Time:</strong> <?php echo date('H:i:s'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- FAILED -->
                            <div class="text-center mb-4">
                                <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                                <h3 class="text-danger">Payment Request Failed</h3>
                            </div>

                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle"></i> Error Details:</h5>
                                <p><?php echo htmlspecialchars($result['error'] ?? 'Unknown error occurred'); ?></p>
                            </div>

                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="mt-4 text-center">
                            <?php if ($result['success']): ?>
                                <a href="payment_status.php?transaction_code=<?php echo urlencode($result['transaction_code'] ?? ''); ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-receipt"></i> View Payment Status
                                </a>
                            <?php else: ?>
                                <a href="payment.php?booking_id=<?php echo urlencode($booking_id); ?>&amount=<?php echo urlencode($amount); ?>&vehicle=<?php echo urlencode($vehicle_info); ?>" 
                                   class="btn btn-warning">
                                    <i class="fas fa-redo"></i> Try Again
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo base_url('index.php'); ?>" class="btn btn-secondary">
                                <i class="fas fa-home"></i> Back to Home
                            </a>
                        </div>

                        <!-- MPESA Notice -->
                        <div class="mt-4 alert alert-info">
                            <h6><i class="fas fa-shield-alt"></i> MPESA Payment System</h6>
                            <p class="mb-0 small">
                                This system simulates MPESA STK Push payments. In production, this would integrate 
                                with Safaricom's official Daraja API for real financial transactions.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Transactions</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            // Get recent transactions for this booking
                            $pdo = new PDO(
                                "mysql:host=localhost;dbname=speedy_wheels;charset=utf8mb4",
                                "root",
                                "",
                                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                            );

                            $stmt = $pdo->prepare("
                                SELECT * FROM mpesa_transactions 
                                WHERE booking_id = ? OR phone = ?
                                ORDER BY created_at DESC 
                                LIMIT 5
                            ");
                            $stmt->execute([$booking_id, $phone]);
                            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (count($transactions) > 0) {
                                echo '<div class="table-responsive">';
                                echo '<table class="table table-sm table-striped">';
                                echo '<thead class="table-light"><tr><th>Transaction Code</th><th>Phone</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>';
                                echo '<tbody>';
                                foreach ($transactions as $transaction) {
                                    $status_badge = $transaction['status'] == 'completed' ? 
                                        '<span class="badge bg-success">Completed</span>' : 
                                        '<span class="badge bg-warning">Pending</span>';

                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($transaction['transaction_code']) . '</td>';
                                    echo '<td>' . htmlspecialchars($transaction['phone']) . '</td>';
                                    echo '<td>KES ' . number_format($transaction['amount'], 2) . '</td>';
                                    echo '<td>' . date('M j, H:i', strtotime($transaction['created_at'])) . '</td>';
                                    echo '<td>' . $status_badge . '</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody></table></div>';
                            } else {
                                echo '<p class="text-muted text-center">No transaction history found.</p>';
                            }
                        } catch (Exception $e) {
                            echo '<p class="text-muted text-center">Unable to load transaction history.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once "../../includes/footer.php";
} else {
    // If someone accesses this page directly without POST data
    header("Location: payment.php");
    exit();
}
?>
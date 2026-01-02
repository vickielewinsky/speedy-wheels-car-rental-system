<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once "../../config/database.php";
require_once "../../helpers/url_helper.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_POST) {
    $page_title = "Booking Confirmation - Speedy Wheels";
    require_once "../../includes/header.php";

    try {
        // Get database connection
        $pdo = getDatabaseConnection();

        if (!$pdo) {
            throw new Exception("Could not connect to database.");
        }

        // Get user ID from session
        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'] ?? 'customer';

        // Get form data based on NEW structure
        $vehicle_id = $_POST['vehicle_id'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $pickup_location = $_POST['pickup_location'] ?? 'Mombasa Main Branch';
        $dropoff_location = $_POST['dropoff_location'] ?? 'Mombasa Main Branch';
        $insurance_option = $_POST['insurance_option'] ?? 'basic';
        $payment_method = $_POST['payment_method'] ?? 'mpesa';
        $special_requests = $_POST['special_requests'] ?? '';
        $total_amount = $_POST['total_amount'] ?? 0;

        // Check if we need to create/update customer profile
        $create_customer = isset($_POST['create_customer']) && $_POST['create_customer'] == '1';
        $id_number = $_POST['id_number'] ?? '';
        $dl_number = $_POST['dl_number'] ?? '';
        $address = $_POST['address'] ?? 'Not specified';

        // Validate required fields
        if (empty($vehicle_id) || empty($start_date) || empty($end_date)) {
            throw new Exception("Vehicle selection and rental dates are required.");
        }

        // Validate rental period
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $rental_days = $start->diff($end)->days;

        if ($rental_days < 1) {
            throw new Exception("Rental period must be at least 1 day.");
        }

        // Check vehicle availability
        $vehicle_query = "SELECT * FROM vehicles WHERE vehicle_id = ? AND status = 'available'";
        $vehicle_stmt = $pdo->prepare($vehicle_query);
        $vehicle_stmt->execute([$vehicle_id]);
        $vehicle = $vehicle_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicle) {
            throw new Exception("Selected vehicle is not available.");
        }

        $daily_rate = $vehicle['daily_rate'];
        $base_cost = $daily_rate * $rental_days;
        $insurance_cost = $insurance_option === 'premium' ? 500 * $rental_days : 0;
        $calculated_total = $base_cost + $insurance_cost;

        // Use calculated total if not provided
        if (empty($total_amount) || $total_amount == 0) {
            $total_amount = $calculated_total;
        }

        // Check for booking conflicts
        $conflict_query = "SELECT COUNT(*) as conflict_count FROM bookings 
                          WHERE vehicle_id = ? AND status IN ('confirmed', 'active') 
                          AND ((start_date BETWEEN ? AND ?) OR (end_date BETWEEN ? AND ?) OR (? BETWEEN start_date AND end_date) OR (? BETWEEN start_date AND end_date))";
        $conflict_stmt = $pdo->prepare($conflict_query);
        $conflict_stmt->execute([$vehicle_id, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);
        $conflict = $conflict_stmt->fetch(PDO::FETCH_ASSOC);

        if ($conflict['conflict_count'] > 0) {
            throw new Exception("Sorry, this vehicle is already booked for the selected dates. Please choose different dates.");
        }

        // Start transaction
        $pdo->beginTransaction();

        // 1. Get or create customer record
        $customer_id = null;
        
        // Check if customer already exists for this user
        $customer_query = "SELECT * FROM customers WHERE user_id = ?";
        $customer_stmt = $pdo->prepare($customer_query);
        $customer_stmt->execute([$user_id]);
        $existing_customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_customer) {
            $customer_id = $existing_customer['customer_id'];
            
            // Update customer details if provided
            if ($create_customer && (!empty($id_number) || !empty($dl_number))) {
                $update_query = "UPDATE customers SET 
                                id_number = COALESCE(?, id_number),
                                dl_number = COALESCE(?, dl_number),
                                address = COALESCE(?, address),
                                updated_at = NOW()
                                WHERE customer_id = ?";
                $update_stmt = $pdo->prepare($update_query);
                $update_stmt->execute([
                    !empty($id_number) ? $id_number : null,
                    !empty($dl_number) ? $dl_number : null,
                    !empty($address) ? $address : null,
                    $customer_id
                ]);
            }
        } else {
            // Create new customer record
            // First get user details
            $user_query = "SELECT * FROM users WHERE id = ?";
            $user_stmt = $pdo->prepare($user_query);
            $user_stmt->execute([$user_id]);
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception("User account not found.");
            }

            // Validate required customer info for new customers
            if ($create_customer && (empty($id_number) || empty($dl_number))) {
                throw new Exception("ID Number and Driving License Number are required to create your customer profile.");
            }

            // Use default values if not provided
            $customer_name = $user['first_name'] . ' ' . $user['last_name'];
            $customer_email = $user['email'];
            $customer_phone = $user['phone'] ?? 'Not provided';
            
            // For new customers, require ID and DL numbers
            $customer_id_number = !empty($id_number) ? $id_number : 'Pending';
            $customer_dl_number = !empty($dl_number) ? $dl_number : 'Pending';

            $insert_customer = "INSERT INTO customers 
                               (user_id, name, email, phone, id_number, dl_number, address, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $insert_stmt = $pdo->prepare($insert_customer);
            $insert_stmt->execute([
                $user_id,
                $customer_name,
                $customer_email,
                $customer_phone,
                $customer_id_number,
                $customer_dl_number,
                $address
            ]);
            $customer_id = $pdo->lastInsertId();
        }

        if (!$customer_id) {
            throw new Exception("Could not create or retrieve customer profile.");
        }

        // 2. Create booking
        $booking_query = "INSERT INTO bookings 
                         (customer_id, vehicle_id, start_date, end_date, total_amount, status) 
                         VALUES (?, ?, ?, ?, ?, 'confirmed')";
        $booking_stmt = $pdo->prepare($booking_query);
        $booking_stmt->execute([
            $customer_id, 
            $vehicle_id, 
            $start_date, 
            $end_date, 
            $total_amount
        ]);
        $booking_id = $pdo->lastInsertId();

        // 3. Update vehicle status
        $update_vehicle = "UPDATE vehicles SET status = 'booked' WHERE vehicle_id = ?";
        $update_vehicle_stmt = $pdo->prepare($update_vehicle);
        $update_vehicle_stmt->execute([$vehicle_id]);

        // 4. Get customer details for display/email
        $final_customer_query = "SELECT * FROM customers WHERE customer_id = ?";
        $final_customer_stmt = $pdo->prepare($final_customer_query);
        $final_customer_stmt->execute([$customer_id]);
        $customer = $final_customer_stmt->fetch(PDO::FETCH_ASSOC);

        // Commit transaction
        $pdo->commit();

        // Try to send email confirmation
        $emailSent = false;
        $emailMessage = '';

        if (!empty($customer['email']) && filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) {
            try {
                $root_dir = dirname(__DIR__, 2);
                $emailServicePath = $root_dir . '/src/services/EmailService.php';
                
                if (file_exists($emailServicePath)) {
                    require_once $emailServicePath;

                    $autoloadPath = $root_dir . '/vendor/autoload.php';
                    if (file_exists($autoloadPath)) {
                        require_once $autoloadPath;
                    }

                    $emailService = new EmailService();

                    $bookingData = [
                        'booking_id' => $booking_id,
                        'vehicle' => $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['plate_no'] . ')',
                        'start_date' => date('M j, Y', strtotime($start_date)),
                        'end_date' => date('M j, Y', strtotime($end_date)),
                        'pickup_location' => $pickup_location,
                        'total_amount' => number_format($total_amount, 2),
                        'rental_days' => $rental_days
                    ];

                    $emailSent = $emailService->sendBookingConfirmation(
                        $bookingData, 
                        $customer['email'], 
                        $customer['name']
                    );

                    if ($emailSent) {
                        $emailMessage = '<div class="alert alert-success mt-3">
                                            <i class="fas fa-envelope me-2"></i>
                                            <strong>Booking confirmation email sent to:</strong> ' . htmlspecialchars($customer['email']) . '
                                         </div>';
                        error_log("Booking confirmation email sent to: " . $customer['email']);
                    } else {
                        $emailMessage = '<div class="alert alert-warning mt-3">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Email notification failed to send, but booking was created successfully.</strong>
                                         </div>';
                        error_log("Failed to send booking confirmation email to: " . $customer['email']);
                    }
                } else {
                    $emailMessage = '<div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Email service not configured.</strong> Booking confirmation email was not sent.
                                     </div>';
                }
            } catch (Exception $e) {
                $emailMessage = '<div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Email service temporarily unavailable.</strong> Booking was created successfully.
                                 </div>';
                error_log("Email service error: " . $e->getMessage());
            }
        } else {
            $emailMessage = '<div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>No valid email provided.</strong> Booking confirmation email was not sent.
                             </div>';
        }

        // Display success message
        ?>
        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card shadow-lg">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0"><i class="fas fa-check-circle"></i> Booking Confirmed!</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle me-2"></i> Booking Successfully Created!</h5>
                                <p class="mb-0">Your booking has been confirmed. Proceed to payment to secure your vehicle.</p>
                            </div>

                            <?php echo $emailMessage; ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Booking Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Booking ID:</strong> #<?php echo $booking_id; ?></p>
                                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($customer['name']); ?></p>
                                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
                                            <p><strong>Email:</strong> <?php echo !empty($customer['email']) ? htmlspecialchars($customer['email']) : '<span class="text-muted">Not provided</span>'; ?></p>
                                            <p><strong>ID Number:</strong> <?php echo htmlspecialchars($customer['id_number']); ?></p>
                                            <p><strong>DL Number:</strong> <?php echo htmlspecialchars($customer['dl_number']); ?></p>
                                            <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['plate_no'] . ')'); ?></p>
                                            <p><strong>Rental Period:</strong> <?php echo $rental_days; ?> days (<?php echo $start_date; ?> to <?php echo $end_date; ?>)</p>
                                            <p><strong>Pickup:</strong> <?php echo htmlspecialchars($pickup_location); ?></p>
                                            <p><strong>Drop-off:</strong> <?php echo htmlspecialchars($dropoff_location); ?></p>
                                            <p><strong>Insurance:</strong> <?php echo ucfirst($insurance_option); ?></p>
                                            <p><strong>Payment Method:</strong> <?php echo $payment_method == 'mpesa' ? 'MPESA (Pay Now)' : 'Pay at Pickup'; ?></p>
                                            <?php if (!empty($special_requests)): ?>
                                                <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($special_requests); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-receipt"></i> Pricing Summary</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Daily Rate:</span>
                                                <span>KES <?php echo number_format($daily_rate, 2); ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Rental Days:</span>
                                                <span><?php echo $rental_days; ?> days</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Base Cost:</span>
                                                <span>KES <?php echo number_format($base_cost, 2); ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Insurance (<?php echo ucfirst($insurance_option); ?>):</span>
                                                <span>KES <?php echo number_format($insurance_cost, 2); ?></span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between fw-bold fs-5">
                                                <span>Total Amount:</span>
                                                <span class="text-success">KES <?php echo number_format($total_amount, 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Next Steps based on payment method -->
                            <div class="mt-4 text-center">
                                <h5><i class="fas fa-arrow-right"></i> Next Steps</h5>
                                
                                <?php if ($payment_method == 'mpesa'): ?>
                                    <!-- MPESA Payment -->
                                    <p>Secure your booking by completing payment via MPESA</p>
                                    <div class="d-grid gap-2 col-md-8 mx-auto">
                                        <a href="../payments/payment.php?booking_id=<?php echo $booking_id; ?>&amount=<?php echo $total_amount; ?>&vehicle=<?php echo urlencode($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['plate_no'] . ')'); ?>" 
                                           class="btn btn-success btn-lg">
                                            <i class="fas fa-mobile-alt"></i> Pay via MPESA - KES <?php echo number_format($total_amount, 2); ?>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <!-- Pay at Pickup -->
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-clock me-2"></i>Pay at Pickup Selected</h6>
                                        <p>Your booking is confirmed. Please bring your ID, driver's license, and payment to:</p>
                                        <p><strong><?php echo htmlspecialchars($pickup_location); ?></strong></p>
                                        <p class="mb-0"><strong>Pickup Time:</strong> <?php echo date('h:i A', strtotime($start_date)); ?></p>
                                    </div>
                                <?php endif; ?>

                                <div class="btn-group mt-3" role="group">
                                    <a href="index.php" class="btn btn-outline-primary">
                                        <i class="fas fa-list"></i> View My Bookings
                                    </a>
                                    <a href="create_booking.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-plus"></i> Create Another Booking
                                    </a>
                                    <a href="../../index.php" class="btn btn-outline-success">
                                        <i class="fas fa-home"></i> Back to Home
                                    </a>
                                </div>
                            </div>

                            <!-- Important Notes -->
                            <div class="mt-4 alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Important Information</h6>
                                <ul class="mb-0">
                                    <?php if ($payment_method == 'mpesa'): ?>
                                        <li>Your booking will be held for <strong>1 hour</strong> pending MPESA payment</li>
                                    <?php else: ?>
                                        <li>Your booking is confirmed. Please arrive 15 minutes before pickup time</li>
                                    <?php endif; ?>
                                    <li>Vehicle pickup requires <strong>original ID</strong> and <strong>driver's license</strong></li>
                                    <li>Fuel is provided full-to-full</li>
                                    <li>A security deposit may be required upon vehicle pickup</li>
                                    <li>Contact us for any changes: <strong>254712345678</strong></li>
                                    <?php if ($emailSent): ?>
                                        <li><strong>Booking confirmation email has been sent to your email address</strong></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php

    } catch (Exception $e) {
        // Rollback transaction on error
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        echo '<div class="container mt-4">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header bg-danger text-white">
                                <h4 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Booking Failed</h4>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-times-circle fa-4x text-danger"></i>
                                </div>
                                <h4 class="text-danger">Booking Could Not Be Processed</h4>
                                <p class="lead">' . htmlspecialchars($e->getMessage()) . '</p>
                                <div class="mt-4">
                                    <a href="create_booking.php" class="btn btn-warning me-2">
                                        <i class="fas fa-redo"></i> Try Again
                                    </a>
                                    <a href="index.php" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-list"></i> View Bookings
                                    </a>
                                    <a href="../../index.php" class="btn btn-secondary">
                                        <i class="fas fa-home"></i> Back to Home
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              </div>';
    }

    require_once "../../includes/footer.php";
} else {
    // If accessed directly without POST data
    header("Location: create_booking.php");
    exit();
}
?>

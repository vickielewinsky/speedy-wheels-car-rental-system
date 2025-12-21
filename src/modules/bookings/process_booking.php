<?php
require_once "../../config/database.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_POST) {
    $page_title = "Booking Confirmation - Speedy Wheels";
    require_once "../../includes/header.php";

    // Use the same database connection method as create_booking.php
    $root_dir = dirname(__DIR__, 2);
    $db_config_path = $root_dir . '/config/database.php';

    if (file_exists($db_config_path)) {
        require_once $db_config_path;
    }

    try {
        // Get database connection using the same method as create_booking.php
        $pdo = getDatabaseConnection();

        if (!$pdo) {
            throw new Exception("Could not connect to database.");
        }

        // Handle both existing customer and new customer scenarios
        $customer_id = $_POST['customer_id'] ?? '';
        $customer_name = $_POST['customer_name'] ?? '';
        $customer_phone = $_POST['customer_phone'] ?? '';
        $customer_email = $_POST['customer_email'] ?? '';
        $customer_id_number = $_POST['customer_id_number'] ?? '';
        $customer_dl_number = $_POST['customer_dl_number'] ?? '';
        $customer_address = $_POST['customer_address'] ?? 'Not specified';

        $vehicle_id = $_POST['vehicle_id'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $pickup_location = $_POST['pickup_location'] ?? 'Mombasa Main Branch';
        $dropoff_location = $_POST['dropoff_location'] ?? 'Mombasa Main Branch';
        $insurance_option = $_POST['insurance_option'] ?? 'basic';
        $special_requests = $_POST['special_requests'] ?? '';

        // Validate required fields based on scenario
        if (!empty($customer_id)) {
            // Existing customer selected - verify customer exists
            $customer_query = "SELECT * FROM customers WHERE customer_id = ?";
            $customer_stmt = $pdo->prepare($customer_query);
            $customer_stmt->execute([$customer_id]);
            $existing_customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$existing_customer) {
                throw new Exception("Selected customer not found.");
            }

            $customer_name = $existing_customer['name'];
            $customer_phone = $existing_customer['phone'];
            $customer_id_number = $existing_customer['id_number'];
            $customer_dl_number = $existing_customer['dl_number'];
            $customer_email = $existing_customer['email'] ?? $customer_email;
            $customer_address = $existing_customer['address'] ?? $customer_address;
        } else {
            // New customer - validate all required fields
            if (empty($customer_name) || empty($customer_phone) || empty($customer_id_number) || empty($customer_dl_number)) {
                throw new Exception("All customer details are required when creating a new customer.");
            }
        }

        // Validate other required fields
        if (empty($vehicle_id) || empty($start_date) || empty($end_date)) {
            throw new Exception("Vehicle selection and rental dates are required.");
        }

        // Calculate rental details
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $rental_days = $start->diff($end)->days;

        if ($rental_days < 1) {
            throw new Exception("Rental period must be at least 1 day.");
        }

        // Get vehicle details and calculate pricing
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
        $total_amount = $base_cost + $insurance_cost;

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

        // 1. Create or get customer
        if (empty($customer_id)) {
            // Check if customer already exists with same phone or ID number
            $existing_customer_query = "SELECT customer_id FROM customers WHERE phone = ? OR id_number = ?";
            $existing_customer_stmt = $pdo->prepare($existing_customer_query);
            $existing_customer_stmt->execute([$customer_phone, $customer_id_number]);
            $existing_customer = $existing_customer_stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_customer) {
                $customer_id = $existing_customer['customer_id'];
                // Update customer details
                $update_customer = "UPDATE customers SET name = ?, email = ?, address = ? WHERE customer_id = ?";
                $update_stmt = $pdo->prepare($update_customer);
                $update_stmt->execute([$customer_name, $customer_email, $customer_address, $customer_id]);
            } else {
                // Create new customer
                $insert_customer = "INSERT INTO customers (name, email, phone, id_number, dl_number, address) 
                                   VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $pdo->prepare($insert_customer);
                $insert_stmt->execute([$customer_name, $customer_email, $customer_phone, $customer_id_number, $customer_dl_number, $customer_address]);
                $customer_id = $pdo->lastInsertId();
            }
        }

        // 2. Create booking (matches your database schema)
        $booking_query = "INSERT INTO bookings 
                         (customer_id, vehicle_id, start_date, end_date, total_amount, status) 
                         VALUES (?, ?, ?, ?, ?, 'confirmed')";
        $booking_stmt = $pdo->prepare($booking_query);
        $booking_stmt->execute([
            $customer_id, $vehicle_id, $start_date, $end_date, $total_amount
        ]);
        $booking_id = $pdo->lastInsertId();

        // 3. Update vehicle status
        $update_vehicle = "UPDATE vehicles SET status = 'booked' WHERE vehicle_id = ?";
        $update_vehicle_stmt = $pdo->prepare($update_vehicle);
        $update_vehicle_stmt->execute([$vehicle_id]);

        // Commit transaction
        $pdo->commit();

        // EMAIL NOTIFICATION - BOOKING CONFIRMATION
        $emailSent = false;
        $emailMessage = '';

        if (!empty($customer_email) && filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
            try {
                // Include the email service
                $emailServicePath = $root_dir . '/src/services/EmailService.php';
                if (file_exists($emailServicePath)) {
                    require_once $emailServicePath;

                    // Load PHPMailer autoloader
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

                    $emailSent = $emailService->sendBookingConfirmation($bookingData, $customer_email, $customer_name);

                    if ($emailSent) {
                        $emailMessage = '<div class="alert alert-success mt-3">
                                            <i class="fas fa-envelope me-2"></i>
                                            <strong>Booking confirmation email sent to:</strong> ' . htmlspecialchars($customer_email) . '
                                         </div>';
                        error_log("Booking confirmation email sent to: " . $customer_email);
                    } else {
                        $emailMessage = '<div class="alert alert-warning mt-3">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Email notification failed to send, but booking was created successfully.</strong>
                                         </div>';
                        error_log("Failed to send booking confirmation email to: " . $customer_email);
                    }
                } else {
                    $emailMessage = '<div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Email service not configured.</strong> Booking confirmation email was not sent.
                                     </div>';
                }
            } catch (Exception $e) {
                // Don't break the booking process if email fails
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

        // Display success message with payment option
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
                                <h5> Booking Successfully Created!</h5>
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
                                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
                                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer_phone); ?></p>
                                            <p><strong>Email:</strong> <?php echo !empty($customer_email) ? htmlspecialchars($customer_email) : '<span class="text-muted">Not provided</span>'; ?></p>
                                            <p><strong>ID Number:</strong> <?php echo htmlspecialchars($customer_id_number); ?></p>
                                            <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['plate_no'] . ')'); ?></p>
                                            <p><strong>Rental Period:</strong> <?php echo $rental_days; ?> days (<?php echo $start_date; ?> to <?php echo $end_date; ?>)</p>
                                            <p><strong>Pickup:</strong> <?php echo htmlspecialchars($pickup_location); ?></p>
                                            <p><strong>Drop-off:</strong> <?php echo htmlspecialchars($dropoff_location); ?></p>
                                            <p><strong>Insurance:</strong> <?php echo ucfirst($insurance_option); ?></p>
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

                            <!-- Payment Options -->
                            <div class="mt-4 text-center">
                                <h5><i class="fas fa-credit-card"></i> Payment Options</h5>
                                <p>Secure your booking by completing payment via MPESA</p>

                                <div class="d-grid gap-2 col-md-8 mx-auto">
                                    <a href="../payments/payment.php?booking_id=<?php echo $booking_id; ?>&amount=<?php echo $total_amount; ?>&vehicle=<?php echo urlencode($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['plate_no'] . ')'); ?>" 
                                       class="btn btn-success btn-lg">
                                        <i class="fas fa-mobile-alt"></i> Pay via MPESA - KES <?php echo number_format($total_amount, 2); ?>
                                    </a>
                                    <div class="btn-group" role="group">
                                        <a href="index.php" class="btn btn-outline-primary">
                                            <i class="fas fa-list"></i> View All Bookings
                                        </a>
                                        <a href="create_booking.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-plus"></i> Create Another Booking
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Booking Notes -->
                            <div class="mt-4 alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Important Notes</h6>
                                <ul class="mb-0">
                                    <li>Your booking will be held for 1 hour pending payment</li>
                                    <li>Vehicle pickup requires original ID and driver's license</li>
                                    <li>Fuel is provided full-to-full</li>
                                    <li>A security deposit may be required upon vehicle pickup</li>
                                    <li>Contact us for any changes: 254712345678</li>
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

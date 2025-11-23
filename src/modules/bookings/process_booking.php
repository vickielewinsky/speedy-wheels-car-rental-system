<?php
require_once "../../config/database.php";
require_once "../payments/mpesa_processor.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_POST) {
    $page_title = "Booking Confirmation - Speedy Wheels";
    require_once "../../includes/header.php";
    
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Extract and validate form data
        $customer_name = $_POST['customer_name'] ?? '';
        $customer_phone = $_POST['customer_phone'] ?? '';
        $customer_email = $_POST['customer_email'] ?? '';
        $customer_id_number = $_POST['customer_id_number'] ?? '';
        $customer_dl_number = $_POST['customer_dl_number'] ?? '';
        $customer_address = $_POST['customer_address'] ?? '';
        $vehicle_id = $_POST['vehicle_id'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $pickup_location = $_POST['pickup_location'] ?? 'Mombasa Main Branch';
        $dropoff_location = $_POST['dropoff_location'] ?? 'Mombasa Main Branch';
        $insurance_option = $_POST['insurance_option'] ?? 'basic';
        $special_requests = $_POST['special_requests'] ?? '';

        // Validate required fields
        if (empty($customer_name) || empty($customer_phone) || empty($customer_id_number) || 
            empty($customer_dl_number) || empty($vehicle_id) || empty($start_date) || empty($end_date)) {
            throw new Exception("All required fields must be filled.");
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
        $vehicle_stmt = $db->prepare($vehicle_query);
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
                          AND ((start_date BETWEEN ? AND ?) OR (end_date BETWEEN ? AND ?))";
        $conflict_stmt = $db->prepare($conflict_query);
        $conflict_stmt->execute([$vehicle_id, $start_date, $end_date, $start_date, $end_date]);
        $conflict = $conflict_stmt->fetch(PDO::FETCH_ASSOC);

        if ($conflict['conflict_count'] > 0) {
            throw new Exception("Sorry, this vehicle is already booked for the selected dates. Please choose different dates.");
        }

        // Start transaction
        $db->beginTransaction();

        // 1. Create or get customer
        $customer_query = "SELECT customer_id FROM customers WHERE phone = ? OR id_number = ?";
        $customer_stmt = $db->prepare($customer_query);
        $customer_stmt->execute([$customer_phone, $customer_id_number]);
        $existing_customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_customer) {
            $customer_id = $existing_customer['customer_id'];
            // Update customer details
            $update_customer = "UPDATE customers SET name = ?, email = ?, address = ? WHERE customer_id = ?";
            $update_stmt = $db->prepare($update_customer);
            $update_stmt->execute([$customer_name, $customer_email, $customer_address, $customer_id]);
        } else {
            // Create new customer
            $insert_customer = "INSERT INTO customers (name, email, phone, id_number, dl_number, address) 
                               VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = $db->prepare($insert_customer);
            $insert_stmt->execute([$customer_name, $customer_email, $customer_phone, $customer_id_number, $customer_dl_number, $customer_address]);
            $customer_id = $db->lastInsertId();
        }

        // 2. Create booking
        $booking_query = "INSERT INTO bookings 
                         (customer_id, vehicle_id, start_date, end_date, pickup_location, dropoff_location, 
                          rental_days, insurance_option, special_requests, total_amount, status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";
        $booking_stmt = $db->prepare($booking_query);
        $booking_stmt->execute([
            $customer_id, $vehicle_id, $start_date, $end_date, $pickup_location, $dropoff_location,
            $rental_days, $insurance_option, $special_requests, $total_amount
        ]);
        $booking_id = $db->lastInsertId();

        // 3. Update vehicle status
        $update_vehicle = "UPDATE vehicles SET status = 'booked' WHERE vehicle_id = ?";
        $update_vehicle_stmt = $db->prepare($update_vehicle);
        $update_vehicle_stmt->execute([$vehicle_id]);

        // Commit transaction
        $db->commit();

        // Display success message with payment option
        ?>
        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-lg">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0"><i class="fas fa-check-circle"></i> Booking Confirmed!</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                <h5>🎉 Booking Successfully Created!</h5>
                                <p class="mb-0">Your booking has been confirmed. Proceed to payment to secure your vehicle.</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Booking Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Booking ID:</strong> #<?php echo $booking_id; ?></p>
                                            <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($vehicle['model'] . ' (' . $vehicle['plate_no'] . ')'); ?></p>
                                            <p><strong>Rental Period:</strong> <?php echo $rental_days; ?> days (<?php echo $start_date; ?> to <?php echo $end_date; ?>)</p>
                                            <p><strong>Pickup:</strong> <?php echo htmlspecialchars($pickup_location); ?></p>
                                            <p><strong>Insurance:</strong> <?php echo ucfirst($insurance_option); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Pricing Summary</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Daily Rate:</strong> KES <?php echo number_format($daily_rate, 2); ?></p>
                                            <p><strong>Rental Days:</strong> <?php echo $rental_days; ?> days</p>
                                            <p><strong>Base Cost:</strong> KES <?php echo number_format($base_cost, 2); ?></p>
                                            <p><strong>Insurance:</strong> KES <?php echo number_format($insurance_cost, 2); ?></p>
                                            <hr>
                                            <p class="h5 text-success"><strong>Total Amount:</strong> KES <?php echo number_format($total_amount, 2); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Options -->
                            <div class="mt-4 text-center">
                                <h5>💳 Payment Options</h5>
                                <p>Secure your booking by completing payment via MPESA</p>
                                
                                <div class="d-grid gap-2 col-md-8 mx-auto">
                                    <a href="../payments/payment.php?booking_id=<?php echo $booking_id; ?>&amount=<?php echo $total_amount; ?>&vehicle=<?php echo urlencode($vehicle['model'] . ' (' . $vehicle['plate_no'] . ')'); ?>" 
                                       class="btn btn-success btn-lg">
                                        <i class="fas fa-mobile-alt"></i> Pay via MPESA - KES <?php echo number_format($total_amount, 2); ?>
                                    </a>
                                    <a href="index.php" class="btn btn-outline-primary">
                                        <i class="fas fa-list"></i> View All Bookings
                                    </a>
                                    <a href="create_booking.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-plus"></i> Create Another Booking
                                    </a>
                                </div>
                            </div>

                            <!-- Booking Notes -->
                            <div class="mt-4 alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Important Notes</h6>
                                <ul class="mb-0">
                                    <li>Your booking will be held for 1 hour pending payment</li>
                                    <li>Vehicle pickup requires original ID and driver's license</li>
                                    <li>Fuel is provided full-to-full</li>
                                    <li>Contact us for any changes: 254712345678</li>
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
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        
        echo '<div class="container mt-4">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header bg-danger text-white">
                                <h4 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Booking Failed</h4>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-times-circle fa-4x text-danger"></i>
                                </div>
                                <h4 class="text-danger">Booking Could Not Be Processed</h4>
                                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                                <div class="mt-4">
                                    <a href="create_booking.php" class="btn btn-warning">
                                        <i class="fas fa-redo"></i> Try Again
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
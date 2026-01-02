<?php
// src/modules/bookings/create_booking.php
$page_title = "Create New Booking - Speedy Wheels";

// Check if user is logged in (without starting session)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    // Include config first to get base_url function
    $root_dir = dirname(__DIR__, 2);
    $config_path = $root_dir . '/config/database.php';
    if (file_exists($config_path)) {
        require_once $config_path;
    }
    
    // Include url helper for base_url function
    $helper_path = $root_dir . '/helpers/url_helper.php';
    if (file_exists($helper_path)) {
        require_once $helper_path;
    }
    
    header('Location: ' . base_url('src/modules/auth/login.php'));
    exit();
}

// Include the shared header AFTER session check
require_once dirname(__DIR__, 2) . '/includes/header.php';

// Load database configuration
$root_dir = dirname(__DIR__, 2);
$db_config_path = $root_dir . '/config/database.php';
if (file_exists($db_config_path)) {
    require_once $db_config_path;
}

// Get database connection
try {
    $pdo = getDatabaseConnection();

    // Get current user information
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];
    
    // Fetch user details
    $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $current_user = $user_stmt->fetch();
    
    if (!$current_user) {
        // User not found in database
        session_destroy();
        header('Location: ' . base_url('src/modules/auth/login.php'));
        exit();
    }
    
    // Fetch customer details if user is linked to a customer
    $customer_stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ?");
    $customer_stmt->execute([$user_id]);
    $current_customer = $customer_stmt->fetch();
    
    // If user doesn't have a customer record, we'll handle it in the form
    // but we won't auto-create it here to avoid errors

    // Fetch available vehicles
    $vehicles_stmt = $pdo->query("SELECT 
        vehicle_id, 
        plate_no,
        model, 
        make, 
        year,
        color,
        daily_rate,
        status 
        FROM vehicles WHERE status = 'available'");
    $vehicles = $vehicles_stmt->fetchAll();

} catch (PDOException $e) {
    $vehicles = [];
    $current_user = [];
    $current_customer = [];
    error_log("Database error in create_booking.php: " . $e->getMessage());
}
?>

<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="fas fa-plus-circle me-2 text-primary"></i>Create New Booking
                </h1>
                <p class="text-muted mb-0">Book a vehicle for rental</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Bookings
                </a>
            </div>
        </div>

        <!-- Welcome Message -->
        <div class="alert alert-info mb-4">
            <i class="fas fa-user-circle me-2"></i>
            <strong>Welcome, <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>!</strong>
            You are booking as a registered user.
        </div>

        <!-- Booking Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-plus me-2"></i>Booking Information
                </h5>
            </div>
            <div class="card-body booking-form-container">
                <form action="process_booking.php" method="POST" id="bookingForm">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    
                    <div class="row">
                        <!-- Customer Information (Auto-filled for logged-in user) -->
                        <div class="col-md-6">
                            <h6 class="form-section-header">
                                <i class="fas fa-user me-2"></i>Your Information
                            </h6>

                            <div class="customer-details-panel p-3 rounded mb-3 bg-light">
                                <h6>Account Information:</h6>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($current_user['email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($current_user['phone'] ?? 'Not provided'); ?></p>
                                
                                <?php if ($current_customer): ?>
                                    <hr>
                                    <h6>Customer Profile:</h6>
                                    <p><strong>Customer ID:</strong> <?php echo htmlspecialchars($current_customer['customer_id']); ?></p>
                                    <p><strong>ID Number:</strong> <?php echo htmlspecialchars($current_customer['id_number']); ?></p>
                                    <p><strong>DL Number:</strong> <?php echo htmlspecialchars($current_customer['dl_number']); ?></p>
                                    <input type="hidden" name="customer_id" value="<?php echo $current_customer['customer_id']; ?>">
                                <?php endif; ?>
                            </div>

                            <!-- Profile completion form (only show if missing customer record) -->
                            <?php if (!$current_customer): ?>
                            <div class="mt-3">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <strong>Profile Incomplete</strong><br>
                                    Please provide the following details to complete your customer profile:
                                </div>
                                
                                <h6 class="form-section-header">
                                    <i class="fas fa-id-card me-2"></i>Complete Your Profile
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">ID Number *</label>
                                        <input type="text" class="form-control" name="id_number" 
                                               placeholder="Enter your ID number" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Driving License Number *</label>
                                        <input type="text" class="form-control" name="dl_number" 
                                               placeholder="Enter your DL number" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" name="address" 
                                               placeholder="Your address">
                                    </div>
                                </div>
                                <input type="hidden" name="create_customer" value="1">
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Vehicle Selection -->
                        <div class="col-md-6">
                            <h6 class="form-section-header">
                                <i class="fas fa-car me-2"></i>Vehicle Selection
                            </h6>

                            <div class="mb-3">
                                <label for="vehicle_id" class="form-label">Select Vehicle *</label>
                                <select class="form-select" id="vehicle_id" name="vehicle_id" required>
                                    <option value="">Choose a vehicle...</option>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                        <option value="<?php echo $vehicle['vehicle_id']; ?>" 
                                                data-daily-rate="<?php echo $vehicle['daily_rate']; ?>">
                                            <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ')'); ?> 
                                            - <?php echo htmlspecialchars($vehicle['plate_no']); ?>
                                            (KSh <?php echo number_format($vehicle['daily_rate'], 2); ?>/day)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($vehicles)): ?>
                                    <div class="alert alert-warning mt-2">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        No vehicles available at the moment. Please check back later.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vehicle Details</label>
                                <div id="vehicleDetails" class="vehicle-details-panel text-muted small">
                                    Select a vehicle to see details
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Rental Period -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="form-section-header">
                                <i class="fas fa-calendar-alt me-2"></i>Rental Period
                            </h6>

                            <div class="row date-picker-group">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date *</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date *</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Rental Duration</label>
                                <div id="rentalDuration" class="rental-duration-display text-muted">
                                    Select dates to calculate duration
                                </div>
                            </div>

                            <!-- Additional Options -->
                            <div class="mb-3">
                                <label for="pickup_location" class="form-label">Pickup Location</label>
                                <select class="form-select" id="pickup_location" name="pickup_location">
                                    <option value="Mombasa Main Branch">Mombasa Main Branch</option>
                                    <option value="Moi International Airport">Moi International Airport</option>
                                    <option value="Nyali Branch">Nyali Branch</option>
                                    <option value="Bamburi Branch">Bamburi Branch</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="dropoff_location" class="form-label">Drop-off Location</label>
                                <select class="form-select" id="dropoff_location" name="dropoff_location">
                                    <option value="Mombasa Main Branch">Mombasa Main Branch</option>
                                    <option value="Moi International Airport">Moi International Airport</option>
                                    <option value="Nyali Branch">Nyali Branch</option>
                                    <option value="Bamburi Branch">Bamburi Branch</option>
                                </select>
                            </div>
                        </div>

                        <!-- Pricing & Insurance -->
                        <div class="col-md-6">
                            <h6 class="form-section-header">
                                <i class="fas fa-money-bill-wave me-2"></i>Pricing Summary
                            </h6>

                            <div class="pricing-summary p-3 rounded mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Daily Rate:</span>
                                    <span id="dailyRate">KSh 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Number of Days:</span>
                                    <span id="numberOfDays">0</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold fs-5">
                                    <span>Total Amount:</span>
                                    <span id="totalAmount" class="total-amount">KSh 0.00</span>
                                </div>
                                <input type="hidden" name="total_amount" id="totalAmountHidden" value="0">
                            </div>

                            <!-- Insurance Options -->
                            <div class="mb-3">
                                <label class="form-label">Insurance Option</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="insurance_option" 
                                           id="basic_insurance" value="basic" checked>
                                    <label class="form-check-label" for="basic_insurance">
                                        Basic Insurance (Included)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="insurance_option" 
                                           id="premium_insurance" value="premium">
                                    <label class="form-check-label" for="premium_insurance">
                                        Premium Insurance (+KSh 500/day) - Comprehensive coverage
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Payment Method -->
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="pay_now" value="mpesa" checked>
                                    <label class="form-check-label" for="pay_now">
                                        <i class="fas fa-mobile-alt me-1"></i> Pay Now via MPESA
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="pay_later" value="later">
                                    <label class="form-check-label" for="pay_later">
                                        <i class="fas fa-clock me-1"></i> Pay at Pickup
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="form-section-header">
                                <i class="fas fa-info-circle me-2"></i>Additional Information
                            </h6>

                            <div class="mb-3">
                                <label for="special_requests" class="form-label">Special Requests or Notes</label>
                                <textarea class="form-control" id="special_requests" name="special_requests" rows="3" 
                                          placeholder="Any special requirements, notes, or additional driver information..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> *
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-outline-secondary me-md-2" onclick="resetForm()">
                                    <i class="fas fa-redo me-1"></i> Reset Form
                                </button>
                                <button type="submit" class="btn btn-primary" id="submitBtn" <?php echo empty($vehicles) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-check me-1"></i> Create Booking
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Speedy Wheels Car Rental Terms</h6>
                <p>1. The renter must be at least 21 years old and possess a valid driving license.</p>
                <p>2. A security deposit may be required.</p>
                <p>3. The vehicle must be returned in the same condition as rented.</p>
                <p>4. Fuel is not included in the rental price.</p>
                <p>5. Late returns will incur additional charges.</p>
                <p>6. The renter is responsible for any traffic violations during the rental period.</p>
                <p>7. Insurance coverage is as per the selected option.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleSelect = document.getElementById('vehicle_id');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const dailyRateSpan = document.getElementById('dailyRate');
    const numberOfDaysSpan = document.getElementById('numberOfDays');
    const totalAmountSpan = document.getElementById('totalAmount');
    const totalAmountHidden = document.getElementById('totalAmountHidden');
    const vehicleDetailsDiv = document.getElementById('vehicleDetails');
    const rentalDurationDiv = document.getElementById('rentalDuration');
    const insuranceRadios = document.querySelectorAll('input[name="insurance_option"]');
    const submitBtn = document.getElementById('submitBtn');

    // Vehicle selection handler
    vehicleSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const dailyRate = selectedOption.getAttribute('data-daily-rate');

        if (dailyRate) {
            dailyRateSpan.textContent = 'KSh ' + parseFloat(dailyRate).toLocaleString('en-KE', {minimumFractionDigits: 2});
            vehicleDetailsDiv.innerHTML = `
                <strong>Selected:</strong> ${selectedOption.textContent.split('(')[0]}<br>
                <strong>Daily Rate:</strong> KSh ${parseFloat(dailyRate).toLocaleString('en-KE', {minimumFractionDigits: 2})}
            `;
        } else {
            dailyRateSpan.textContent = 'KSh 0.00';
            vehicleDetailsDiv.textContent = 'Select a vehicle to see details';
        }
        calculateTotal();
    });

    // Calculate rental duration and total when dates change
    function updateDates() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDate && endDate && endDate > startDate) {
            const timeDiff = endDate - startDate;
            const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

            numberOfDaysSpan.textContent = daysDiff;
            rentalDurationDiv.innerHTML = `
                <strong>${daysDiff} day${daysDiff !== 1 ? 's' : ''}</strong><br>
                <small>${startDateInput.value} to ${endDateInput.value}</small>
            `;
        } else {
            numberOfDaysSpan.textContent = '0';
            rentalDurationDiv.textContent = 'Select dates to calculate duration';
        }
        calculateTotal();
    }

    startDateInput.addEventListener('change', updateDates);
    endDateInput.addEventListener('change', updateDates);

    // Update total when insurance option changes
    insuranceRadios.forEach(radio => {
        radio.addEventListener('change', calculateTotal);
    });

    // Calculate total amount
    function calculateTotal() {
        const dailyRate = parseFloat(vehicleSelect.options[vehicleSelect.selectedIndex]?.getAttribute('data-daily-rate') || 0);
        const days = parseInt(numberOfDaysSpan.textContent) || 0;
        const insuranceOption = document.querySelector('input[name="insurance_option"]:checked').value;
        const insuranceCost = insuranceOption === 'premium' ? 500 * days : 0;
        const total = (dailyRate * days) + insuranceCost;

        totalAmountSpan.textContent = 'KSh ' + total.toLocaleString('en-KE', {minimumFractionDigits: 2});
        totalAmountHidden.value = total;
    }

    // Form validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        // Validate rental dates
        if (!startDateInput.value || !endDateInput.value) {
            e.preventDefault();
            alert('Please select both start and end dates.');
            return false;
        }

        if (endDate <= startDate) {
            e.preventDefault();
            alert('End date must be after start date.');
            return false;
        }

        // Validate vehicle selection
        if (!vehicleSelect.value) {
            e.preventDefault();
            alert('Please select a vehicle.');
            return false;
        }

        // Check if user needs to complete profile
        const idNumberInput = document.querySelector('input[name="id_number"]');
        const dlNumberInput = document.querySelector('input[name="dl_number"]');
        
        if (idNumberInput && dlNumberInput) {
            if (!idNumberInput.value.trim() || !dlNumberInput.value.trim()) {
                e.preventDefault();
                alert('Please provide both ID Number and Driving License Number to complete your profile.');
                return false;
            }
        }

        // Check terms and conditions
        if (!document.getElementById('terms').checked) {
            e.preventDefault();
            alert('You must agree to the Terms and Conditions.');
            return false;
        }
    });

    // Set minimum end date based on start date
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
            updateDates();
        }
    });

    // Reset form function
    window.resetForm = function() {
        vehicleSelect.value = '';
        startDateInput.value = '';
        endDateInput.value = '';
        document.getElementById('vehicleDetails').textContent = 'Select a vehicle to see details';
        document.getElementById('rentalDuration').textContent = 'Select dates to calculate duration';
        dailyRateSpan.textContent = 'KSh 0.00';
        numberOfDaysSpan.textContent = '0';
        totalAmountSpan.textContent = 'KSh 0.00';
        totalAmountHidden.value = '0';
        document.getElementById('basic_insurance').checked = true;
        document.getElementById('pay_now').checked = true;
        document.getElementById('special_requests').value = '';
        document.getElementById('terms').checked = false;
        
        // Reset profile completion fields if they exist
        const idNumberInput = document.querySelector('input[name="id_number"]');
        const dlNumberInput = document.querySelector('input[name="dl_number"]');
        const addressInput = document.querySelector('input[name="address"]');
        
        if (idNumberInput) idNumberInput.value = '';
        if (dlNumberInput) dlNumberInput.value = '';
        if (addressInput) addressInput.value = '';
    };
});
</script>

<?php
// Include the shared footer
require_once dirname(__DIR__, 2) . '/includes/footer.php';
?>

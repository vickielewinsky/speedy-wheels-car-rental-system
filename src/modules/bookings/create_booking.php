<?php
// src/modules/bookings/create_booking.php
$page_title = "Create New Booking - Speedy Wheels";

// Include the shared header
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
    
    // Fetch available vehicles - using correct column names from your schema
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
    
    // Fetch customers - using correct column names from your schema
    $customers_stmt = $pdo->query("SELECT 
        customer_id,
        name,
        email,
        phone,
        id_number,
        dl_number,
        address
        FROM customers");
    $customers = $customers_stmt->fetchAll();
    
} catch (PDOException $e) {
    $vehicles = [];
    $customers = [];
    // Log error for debugging
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

        <!-- Booking Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-plus me-2"></i>Booking Information
                </h5>
            </div>
            <div class="card-body">
                <form action="process_booking.php" method="POST" id="bookingForm">
                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary">
                                <i class="fas fa-user me-2"></i>Customer Information
                            </h6>
                            
                            <!-- Customer Selection Tabs -->
                            <ul class="nav nav-pills mb-3" id="customerTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="existing-customer-tab" data-bs-toggle="pill" 
                                            data-bs-target="#existing-customer" type="button" role="tab">
                                        <i class="fas fa-users me-1"></i>Select Existing Customer
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="new-customer-tab" data-bs-toggle="pill" 
                                            data-bs-target="#new-customer" type="button" role="tab">
                                        <i class="fas fa-user-plus me-1"></i>Create New Customer
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="customerTabContent">
                                <!-- Existing Customer Tab -->
                                <div class="tab-pane fade show active" id="existing-customer" role="tabpanel">
                                    <div class="mb-3">
                                        <label for="customer_id" class="form-label">Select Customer *</label>
                                        <select class="form-select" id="customer_id" name="customer_id">
                                            <option value="">Choose a customer...</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?php echo $customer['customer_id']; ?>" 
                                                        data-name="<?php echo htmlspecialchars($customer['name']); ?>"
                                                        data-phone="<?php echo htmlspecialchars($customer['phone']); ?>"
                                                        data-email="<?php echo htmlspecialchars($customer['email']); ?>"
                                                        data-id-number="<?php echo htmlspecialchars($customer['id_number']); ?>"
                                                        data-dl-number="<?php echo htmlspecialchars($customer['dl_number']); ?>"
                                                        data-address="<?php echo htmlspecialchars($customer['address']); ?>">
                                                    <?php echo htmlspecialchars($customer['name']); ?> 
                                                    (<?php echo htmlspecialchars($customer['phone']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div id="existingCustomerDetails" class="bg-light p-3 rounded small" style="display: none;">
                                        <h6>Customer Details:</h6>
                                        <div id="customerDetailsContent"></div>
                                    </div>
                                </div>
                                
                                <!-- New Customer Tab -->
                                <div class="tab-pane fade" id="new-customer" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label">New Customer Details *</label>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control new-customer-field" name="customer_name" 
                                                       placeholder="Full Name">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="tel" class="form-control new-customer-field" name="customer_phone" 
                                                       placeholder="Phone Number">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control new-customer-field" name="customer_id_number" 
                                                       placeholder="ID Number">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control new-customer-field" name="customer_dl_number" 
                                                       placeholder="Driving License Number">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <input type="email" class="form-control new-customer-field" name="customer_email" 
                                                       placeholder="Email (Optional)">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control new-customer-field" name="customer_address" 
                                                       placeholder="Address (Optional)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Selection -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary">
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
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Vehicle Details</label>
                                <div id="vehicleDetails" class="text-muted small">
                                    Select a vehicle to see details
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Rental Period -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary">
                                <i class="fas fa-calendar-alt me-2"></i>Rental Period
                            </h6>
                            
                            <div class="row">
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
                                <div id="rentalDuration" class="text-muted">
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
                            <h6 class="mb-3 text-primary">
                                <i class="fas fa-money-bill-wave me-2"></i>Pricing Summary
                            </h6>
                            
                            <div class="bg-light p-3 rounded mb-3">
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
                                    <span id="totalAmount" class="text-primary">KSh 0.00</span>
                                </div>
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
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="mb-3 text-primary">
                                <i class="fas fa-info-circle me-2"></i>Additional Information
                            </h6>
                            
                            <div class="mb-3">
                                <label for="special_requests" class="form-label">Special Requests or Notes</label>
                                <textarea class="form-control" id="special_requests" name="special_requests" rows="3" 
                                          placeholder="Any special requirements, notes, or additional driver information..."></textarea>
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
                                <button type="submit" class="btn btn-primary">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleSelect = document.getElementById('vehicle_id');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const dailyRateSpan = document.getElementById('dailyRate');
    const numberOfDaysSpan = document.getElementById('numberOfDays');
    const totalAmountSpan = document.getElementById('totalAmount');
    const vehicleDetailsDiv = document.getElementById('vehicleDetails');
    const rentalDurationDiv = document.getElementById('rentalDuration');
    const insuranceRadios = document.querySelectorAll('input[name="insurance_option"]');
    const customerSelect = document.getElementById('customer_id');
    const existingCustomerDetails = document.getElementById('existingCustomerDetails');
    const customerDetailsContent = document.getElementById('customerDetailsContent');
    const newCustomerFields = document.querySelectorAll('.new-customer-field');

    // Customer tab functionality
    const customerTab = new bootstrap.Tab(document.getElementById('existing-customer-tab'));
    
    // Show customer details when existing customer is selected
    customerSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            const name = selectedOption.getAttribute('data-name');
            const phone = selectedOption.getAttribute('data-phone');
            const email = selectedOption.getAttribute('data-email');
            const idNumber = selectedOption.getAttribute('data-id-number');
            const dlNumber = selectedOption.getAttribute('data-dl-number');
            const address = selectedOption.getAttribute('data-address');
            
            customerDetailsContent.innerHTML = `
                <p><strong>Name:</strong> ${name}</p>
                <p><strong>Phone:</strong> ${phone}</p>
                <p><strong>Email:</strong> ${email || 'Not provided'}</p>
                <p><strong>ID Number:</strong> ${idNumber}</p>
                <p><strong>DL Number:</strong> ${dlNumber}</p>
                <p><strong>Address:</strong> ${address || 'Not provided'}</p>
            `;
            existingCustomerDetails.style.display = 'block';
            
            // Switch to existing customer tab
            document.getElementById('existing-customer-tab').click();
            
            // Clear new customer fields
            newCustomerFields.forEach(field => field.value = '');
        } else {
            existingCustomerDetails.style.display = 'none';
        }
    });

    // Clear existing customer selection when new customer tab is active
    document.getElementById('new-customer-tab').addEventListener('click', function() {
        customerSelect.value = '';
        existingCustomerDetails.style.display = 'none';
    });

    // Clear new customer fields when existing customer tab is active
    document.getElementById('existing-customer-tab').addEventListener('click', function() {
        newCustomerFields.forEach(field => field.value = '');
    });

    // Update vehicle details when vehicle is selected
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
    }

    // Form validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        const customerId = document.getElementById('customer_id').value;
        const customerName = document.querySelector('input[name="customer_name"]').value;
        const activeTab = document.querySelector('#customerTab .nav-link.active').id;
        
        // Validate rental dates
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

        // Validate customer information based on active tab
        if (activeTab === 'existing-customer-tab') {
            if (!customerId) {
                e.preventDefault();
                alert('Please select an existing customer.');
                return false;
            }
        } else {
            // New customer tab
            if (!customerName || !document.querySelector('input[name="customer_phone"]').value || 
                !document.querySelector('input[name="customer_id_number"]').value || 
                !document.querySelector('input[name="customer_dl_number"]').value) {
                e.preventDefault();
                alert('Please fill all required customer details (Name, Phone, ID Number, and Driving License).');
                return false;
            }
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
        customerSelect.value = '';
        existingCustomerDetails.style.display = 'none';
        newCustomerFields.forEach(field => field.value = '');
        document.getElementById('existing-customer-tab').click();
    };
});
</script>

<?php
// Include the shared footer
require_once dirname(__DIR__, 2) . '/includes/footer.php';
?>
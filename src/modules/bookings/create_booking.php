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
    
    // Fetch available vehicles
    $vehicles_stmt = $pdo->query("SELECT * FROM vehicles WHERE status = 'available'");
    $vehicles = $vehicles_stmt->fetchAll();
    
    // Fetch customers
    $customers_stmt = $pdo->query("SELECT * FROM customers");
    $customers = $customers_stmt->fetchAll();
    
} catch (PDOException $e) {
    $vehicles = [];
    $customers = [];
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
                            
                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Select Customer *</label>
                                <select class="form-select" id="customer_id" name="customer_id" required>
                                    <option value="">Choose a customer...</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['customer_id'] ?? $customer['id']; ?>">
                                            <?php echo htmlspecialchars($customer['full_name']); ?> 
                                            (<?php echo htmlspecialchars($customer['phone']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Or Create New Customer</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="new_customer_name" 
                                               placeholder="Full Name">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="tel" class="form-control" name="new_customer_phone" 
                                               placeholder="Phone Number">
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
                                        <option value="<?php echo $vehicle['vehicle_id'] ?? $vehicle['id']; ?>" 
                                                data-daily-rate="<?php echo $vehicle['daily_rate']; ?>">
                                            <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?> 
                                            - <?php echo htmlspecialchars($vehicle['plate_number']); ?>
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
                        </div>

                        <!-- Pricing -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary">
                                <i class="fas fa-money-bill-wave me-2"></i>Pricing Summary
                            </h6>
                            
                            <div class="bg-light p-3 rounded">
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
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="mb-3 text-primary">
                                <i class="fas fa-info-circle me-2"></i>Additional Information
                            </h6>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Special Requests or Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Any special requirements or notes..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-outline-secondary me-md-2">
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

    // Calculate total amount
    function calculateTotal() {
        const dailyRate = parseFloat(vehicleSelect.options[vehicleSelect.selectedIndex]?.getAttribute('data-daily-rate') || 0);
        const days = parseInt(numberOfDaysSpan.textContent) || 0;
        const total = dailyRate * days;
        
        totalAmountSpan.textContent = 'KSh ' + total.toLocaleString('en-KE', {minimumFractionDigits: 2});
    }

    // Form validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (endDate <= startDate) {
            e.preventDefault();
            alert('End date must be after start date.');
            return false;
        }
        
        if (!vehicleSelect.value) {
            e.preventDefault();
            alert('Please select a vehicle.');
            return false;
        }
    });
});
</script>

<?php
// Include the shared footer
require_once dirname(__DIR__, 2) . '/includes/footer.php';
?>
<?php
// chauffeur.php - Professional Chauffeur Service Page
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/config/database.php';

$page_title = "Professional Chauffeur Service - Speedy Wheels";

// Ensure base_url function exists
if (!function_exists('base_url')) {
    function base_url($path = '') {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $project_folder = '';
        $base = $protocol . '://' . $host . '/' . $project_folder;
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

session_start();
include __DIR__ . '/src/includes/header.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$username = $_SESSION['username'] ?? '';

// Process form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $customer_id = $user_id;
        $customer_name = $_POST['customer_name'];
        $customer_email = $_POST['customer_email'];
        $customer_phone = $_POST['customer_phone'];
        $pickup_location = $_POST['pickup_location'];
        $destination = $_POST['destination'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $duration_hours = $_POST['duration_hours'];
        $vehicle_preference = $_POST['vehicle_preference'];
        $special_requests = $_POST['special_requests'] ?? '';
        
        // Calculate cost (simplified - in real app, use proper pricing logic)
        $base_rate = 1500; // Base rate per hour
        $total_cost = $base_rate * $duration_hours;
        
        // Apply surcharges
        $time_hour = date('H', strtotime($time));
        if ($time_hour >= 20 || $time_hour < 6) {
            $total_cost *= 1.2; // 20% night surcharge
        }
        
        if (date('N', strtotime($date)) >= 6) {
            $total_cost *= 1.15; // 15% weekend surcharge
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO chauffeur_bookings 
            (customer_id, customer_name, customer_email, customer_phone, pickup_location, 
             destination, date, time, duration_hours, vehicle_preference, special_requests, total_cost)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $customer_id, $customer_name, $customer_email, $customer_phone,
            $pickup_location, $destination, $date, $time, $duration_hours,
            $vehicle_preference, $special_requests, $total_cost
        ]);
        
        $booking_id = $pdo->lastInsertId();
        $success_message = "Chauffeur booking request submitted successfully! Your booking ID is CH" . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
        
        // Send email notification (you can implement this using your EmailService)
        
    } catch (Exception $e) {
        $error_message = "Error submitting booking: " . $e->getMessage();
        error_log("Chauffeur booking error: " . $e->getMessage());
    }
}

// Get available drivers
try {
    $driver_stmt = $pdo->prepare("SELECT * FROM chauffeur_drivers WHERE status = 'available' ORDER BY rating DESC LIMIT 4");
    $driver_stmt->execute();
    $drivers = $driver_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $drivers = [];
}

// Get user's existing bookings for autofill
$user_bookings = [];
if ($user_id) {
    try {
        $booking_stmt = $pdo->prepare("
            SELECT b.booking_id, b.pickup_location, b.dropoff_location, v.make, v.model 
            FROM bookings b 
            JOIN vehicles v ON b.vehicle_id = v.vehicle_id 
            WHERE b.customer_id = ? 
            ORDER BY b.created_at DESC 
            LIMIT 5
        ");
        $booking_stmt->execute([$user_id]);
        $user_bookings = $booking_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Silent fail
    }
}
?>

<!-- Premium Chauffeur Service Hero Section -->
<section class="chauffeur-hero" style="
    background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), 
                url('https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
    background-size: cover;
    background-position: center;
    min-height: 70vh;
    display: flex;
    align-items: center;
    color: white;
    padding-top: 100px;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="badge badge-chauffeur mb-3 p-2" style="font-size: 1rem;">
                    <i class="fas fa-crown me-2"></i>Premium Service
                </div>
                <h1 class="display-3 fw-bold mb-4">Professional Chauffeur Service</h1>
                <p class="lead mb-4" style="font-size: 1.3rem;">
                    Experience luxury, safety, and convenience with our professional chauffeurs. 
                    Perfect for corporate events, weddings, airport transfers, and special occasions.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-shield-alt fa-2x text-warning me-2"></i>
                        <span>Fully Licensed & Insured</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-star fa-2x text-warning me-2"></i>
                        <span>4.9/5 Rating</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock fa-2x text-warning me-2"></i>
                        <span>24/7 Availability</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Our Chauffeur Service -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-4 mb-3">Why Choose Our Chauffeur Service</h2>
                <p class="lead text-muted">Experience the difference with our premium service</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 chauffeur-feature-card">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-tie fa-3x" style="color: #8B4513;"></i>
                        </div>
                        <h4 class="mb-3">Professional Drivers</h4>
                        <p class="text-muted">Our chauffeurs are handpicked professionals with extensive training and excellent customer service skills.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 chauffeur-feature-card">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="fas fa-car fa-3x" style="color: #8B4513;"></i>
                        </div>
                        <h4 class="mb-3">Luxury Fleet</h4>
                        <p class="text-muted">Choose from our premium fleet of luxury vehicles, all meticulously maintained and fully insured.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 chauffeur-feature-card">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="fas fa-headset fa-3x" style="color: #8B4513;"></i>
                        </div>
                        <h4 class="mb-3">24/7 Support</h4>
                        <p class="text-muted">Round-the-clock customer support to assist with any changes or emergencies during your journey.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Booking Form Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-dark text-white py-4">
                        <h3 class="mb-0 text-center">
                            <i class="fas fa-user-tie me-2"></i>Book Your Professional Chauffeur
                        </h3>
                    </div>
                    
                    <div class="card-body p-5">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($success_message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($error_message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="chauffeurForm">
                            <div class="row g-4">
                                <!-- Personal Information -->
                                <div class="col-12">
                                    <h5 class="mb-3" style="color: #8B4513;">
                                        <i class="fas fa-user me-2"></i>Personal Information
                                    </h5>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Full Name *</label>
                                    <input type="text" class="form-control form-control-lg" name="customer_name" 
                                           value="<?= $is_logged_in ? htmlspecialchars($username) : '' ?>" 
                                           required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email Address *</label>
                                    <input type="email" class="form-control form-control-lg" name="customer_email" 
                                           value="<?= $is_logged_in ? htmlspecialchars($_SESSION['email'] ?? '') : '' ?>" 
                                           required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number *</label>
                                    <input type="tel" class="form-control form-control-lg" name="customer_phone" 
                                           pattern="[0-9+]{10,15}" placeholder="+254700000000" required>
                                </div>
                                
                                <!-- Journey Details -->
                                <div class="col-12 mt-4">
                                    <h5 class="mb-3" style="color: #8B4513;">
                                        <i class="fas fa-route me-2"></i>Journey Details
                                    </h5>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Pickup Location *</label>
                                    <input type="text" class="form-control form-control-lg" name="pickup_location" 
                                           placeholder="e.g., Jomo Kenyatta International Airport" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Destination *</label>
                                    <input type="text" class="form-control form-control-lg" name="destination" 
                                           placeholder="e.g., Westlands, Nairobi" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Date *</label>
                                    <input type="date" class="form-control form-control-lg" name="date" 
                                           min="<?= date('Y-m-d') ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Time *</label>
                                    <input type="time" class="form-control form-control-lg" name="time" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Duration (Hours) *</label>
                                    <select class="form-select form-select-lg" name="duration_hours" required>
                                        <option value="">Select hours</option>
                                        <?php for ($i = 3; $i <= 24; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?> hour<?= $i > 1 ? 's' : '' ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <small class="text-muted">Minimum 3 hours booking</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Vehicle Preference</label>
                                    <select class="form-select form-select-lg" name="vehicle_preference">
                                        <option value="">Any Available</option>
                                        <option value="Luxury Sedan">Luxury Sedan</option>
                                        <option value="SUV">SUV</option>
                                        <option value="Executive Van">Executive Van</option>
                                        <option value="Mercedes-Benz">Mercedes-Benz</option>
                                        <option value="BMW">BMW</option>
                                    </select>
                                </div>
                                
                                <!-- Special Requests -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">Special Requests</label>
                                    <textarea class="form-control" name="special_requests" rows="3" 
                                              placeholder="e.g., Child seat required, Multiple stops, Meeting sign..."></textarea>
                                </div>
                                
                                <!-- Price Estimate -->
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><i class="fas fa-calculator me-2"></i>Estimated Cost</h6>
                                                <small>Final price will be confirmed after booking</small>
                                            </div>
                                            <div>
                                                <h4 class="text-success mb-0" id="costEstimate">Ksh 0.00</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Terms -->
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</a> 
                                            and understand that a 30% deposit may be required for bookings over 8 hours.
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="col-12 text-center mt-4">
                                    <?php if ($is_logged_in): ?>
                                        <button type="submit" class="btn btn-gradient-chauffeur btn-lg px-5 py-3">
                                            <i class="fas fa-paper-plane me-2"></i>Submit Booking Request
                                        </button>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Sign In Required</h5>
                                            <p>Please <a href="<?= base_url('src/modules/auth/login.php') ?>" class="fw-bold">sign in</a> 
                                               or <a href="<?= base_url('src/modules/auth/register.php') ?>" class="fw-bold">register</a> 
                                               to book a chauffeur service.</p>
                                            <a href="<?= base_url('src/modules/auth/login.php') ?>" class="btn btn-primary">
                                                <i class="fas fa-sign-in-alt me-2"></i>Sign In Now
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Professional Drivers -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-4 mb-3">Meet Our Professional Drivers</h2>
                <p class="lead text-muted">Highly trained and experienced chauffeurs</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($drivers)): ?>
                <?php foreach ($drivers as $driver): ?>
                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4 text-center">
                                <div class="mb-3">
                                    <div style="width: 100px; height: 100px; margin: 0 auto; border-radius: 50%; 
                                                background: linear-gradient(45deg, #8B4513, #D2691E); 
                                                display: flex; align-items: center; justify-content: center;">
                                        <span style="font-size: 2.5rem; color: white;">
                                            <?= strtoupper(substr($driver['driver_name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                </div>
                                <h5 class="mb-2"><?= htmlspecialchars($driver['driver_name']) ?></h5>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-center mb-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $driver['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted"><?= $driver['experience_years'] ?>+ years experience</small>
                                </div>
                                <p class="small text-muted mb-3">
                                    <i class="fas fa-car me-1"></i>
                                    <?= htmlspecialchars($driver['vehicle_types'] ?? 'All Vehicle Types') ?>
                                </p>
                                <div class="badge bg-success">Available</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Driver information will be available soon.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-4 mb-3">Frequently Asked Questions</h2>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="chauffeurFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                What is included in the chauffeur service?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#chauffeurFAQ">
                            <div class="accordion-body">
                                Our chauffeur service includes: professional licensed driver, fuel costs, toll fees, parking fees (where applicable), 
                                bottled water, and comprehensive insurance coverage.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What is the cancellation policy?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#chauffeurFAQ">
                            <div class="accordion-body">
                                Cancellations made 24+ hours before service: Full refund. 
                                Cancellations made 6-24 hours before: 50% refund. 
                                Cancellations made less than 6 hours before: No refund.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I request a specific driver?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#chauffeurFAQ">
                            <div class="accordion-body">
                                Yes, you can request a specific driver in the "Special Requests" section. 
                                We will do our best to accommodate your preference based on availability.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Is there a minimum booking duration?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#chauffeurFAQ">
                            <div class="accordion-body">
                                Yes, we have a minimum booking duration of 3 hours for chauffeur services. 
                                This ensures we can provide the highest quality service.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #8B4513, #D2691E);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-5 mb-3">Ready for a Premium Experience?</h2>
                <p class="lead mb-0">Book your professional chauffeur now and travel in style, comfort, and safety.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="#chauffeurForm" class="btn btn-light btn-lg px-5">
                    <i class="fas fa-user-tie me-2"></i>Book Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Chauffeur Service Terms & Conditions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Service Agreement</h6>
                <p>By booking our chauffeur service, you agree to:</p>
                <ul>
                    <li>Provide accurate pickup and destination information</li>
                    <li>Be ready at the scheduled pickup time</li>
                    <li>Respect our vehicles and drivers</li>
                    <li>Pay any additional charges incurred during the journey</li>
                    <li>Comply with all traffic and safety regulations</li>
                </ul>
                
                <h6 class="mt-4">Pricing & Payment</h6>
                <ul>
                    <li>Prices are quoted in Kenyan Shillings</li>
                    <li>A 30% deposit is required for bookings over 8 hours</li>
                    <li>Final payment is due at the end of the service</li>
                    <li>Additional waiting time beyond 15 minutes will be charged</li>
                </ul>
                
                <h6 class="mt-4">Cancellation Policy</h6>
                <p>See FAQ section for detailed cancellation policy.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.chauffeur-hero {
    position: relative;
    overflow: hidden;
}

.chauffeur-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(139, 69, 19, 0.1), rgba(210, 105, 30, 0.1));
}

.accordion-button:not(.collapsed) {
    background-color: rgba(139, 69, 19, 0.1);
    color: #8B4513;
    font-weight: bold;
}

.accordion-button:focus {
    border-color: #D2691E;
    box-shadow: 0 0 0 0.25rem rgba(210, 105, 30, 0.25);
}

.form-control:focus, .form-select:focus {
    border-color: #D2691E;
    box-shadow: 0 0 0 0.25rem rgba(210, 105, 30, 0.25);
}
</style>

<script>
// Cost estimation
document.addEventListener('DOMContentLoaded', function() {
    const durationSelect = document.querySelector('select[name="duration_hours"]');
    const dateInput = document.querySelector('input[name="date"]');
    const timeInput = document.querySelector('input[name="time"]');
    const costEstimate = document.getElementById('costEstimate');
    
    function calculateCost() {
        const hours = parseInt(durationSelect.value) || 3;
        const date = dateInput.value;
        const time = timeInput.value;
        
        let baseCost = hours * 1500; // Base rate Ksh 1500 per hour
        
        // Apply surcharges
        if (time) {
            const hour = parseInt(time.split(':')[0]);
            if (hour >= 20 || hour < 6) {
                baseCost *= 1.2; // Night surcharge
            }
        }
        
        if (date) {
            const day = new Date(date).getDay();
            if (day === 0 || day === 6) { // Weekend
                baseCost *= 1.15;
            }
        }
        
        if (costEstimate) {
            costEstimate.textContent = 'Ksh ' + baseCost.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }
    }
    
    if (durationSelect) durationSelect.addEventListener('change', calculateCost);
    if (dateInput) dateInput.addEventListener('change', calculateCost);
    if (timeInput) timeInput.addEventListener('change', calculateCost);
    
    // Set min time to current time if today
    if (dateInput && timeInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.addEventListener('change', function() {
            if (this.value === today) {
                const now = new Date();
                const currentTime = now.getHours().toString().padStart(2, '0') + ':' + 
                                  now.getMinutes().toString().padStart(2, '0');
                timeInput.min = currentTime;
                if (timeInput.value < currentTime) {
                    timeInput.value = currentTime;
                }
            } else {
                timeInput.min = '00:00';
            }
            calculateCost();
        });
    }
    
    // Form validation
    const form = document.getElementById('chauffeurForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const date = new Date(dateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (date < today) {
                e.preventDefault();
                alert('Please select a future date.');
                dateInput.focus();
            }
        });
    }
    
    // Initialize cost
    calculateCost();
});
</script>

<?php include __DIR__ . '/src/includes/footer.php'; ?>
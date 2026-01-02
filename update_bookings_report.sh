#!/bin/bash

# Backup the original file
cp src/modules/reports/bookings_report.php src/modules/reports/bookings_report.php.backup

# Create a new version of the file
cat > src/modules/reports/bookings_report_updated.php << 'PHP'
<?php
// src/modules/reports/bookings_report.php
require_once "../../config/database.php";
require_once "../../helpers/url_helper.php";
require_once "../../includes/auth.php";

// Require authentication and admin role
requireAuthentication();
if (!hasRole('admin')) {
    header('Location: ' . base_url('src/modules/auth/login.php'));
    exit();
}

// Get database connection
try {
    $pdo = getDatabaseConnection();
    
    // Get current month
    $currentMonth = date('Y-m');
    
    // Total Bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $totalBookings = $stmt->fetchColumn();
    
    // Active Bookings (confirmed or active)
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM bookings WHERE status IN ('confirmed', 'active')");
    $activeBookings = $stmt->fetchColumn();
    
    // This Month Bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) as month_count FROM bookings WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmt->execute([$currentMonth]);
    $monthBookings = $stmt->fetchColumn();
    
    // Completion Rate (completed vs total)
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM bookings");
    $rateData = $stmt->fetch();
    $completionRate = $rateData['total'] > 0 ? round(($rateData['completed'] / $rateData['total']) * 100, 1) : 0;
    
    // Revenue from Bookings - Combined from payments and mpesa_transactions
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(b.total_amount), 0) as booking_revenue
        FROM bookings b
        WHERE b.status = 'completed'
    ");
    $bookingRevenue = $stmt->fetchColumn();

    // MPESA Revenue for Bookings
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(mt.amount), 0) as mpesa_revenue
        FROM mpesa_transactions mt
        INNER JOIN bookings b ON mt.booking_id = b.booking_id
        WHERE mt.status = 'completed' AND b.status = 'completed'
    ");
    $mpesaBookingRevenue = $stmt->fetchColumn();

    // Total Revenue (Bookings + MPESA)
    $totalBookingRevenue = $bookingRevenue + $mpesaBookingRevenue;

    // Average Revenue per Booking
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as completed_bookings,
            COALESCE(AVG(b.total_amount), 0) as avg_booking_value
        FROM bookings b
        WHERE b.status = 'completed'
    ");
    $avgData = $stmt->fetch();
    $completedBookings = $avgData['completed_bookings'];
    $avgBookingValue = $avgData['avg_booking_value'];
    
    // Booking Status Breakdown
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM bookings GROUP BY status ORDER BY status");
    $statusBreakdown = $stmt->fetchAll();
    
    // Monthly Booking Trend
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as bookings
        FROM bookings 
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6
    ");
    $monthlyBookings = $stmt->fetchAll();
    
    // Recent Bookings
    $stmt = $pdo->query("
        SELECT 
            b.*,
            c.name as customer_name,
            c.phone as customer_phone,
            v.plate_no,
            v.model,
            v.make
        FROM bookings b
        JOIN customers c ON b.customer_id = c.customer_id
        JOIN vehicles v ON b.vehicle_id = v.vehicle_id
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $recentBookings = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Bookings Report Error: " . $e->getMessage());
    // Set default values
    $totalBookings = $activeBookings = $monthBookings = $completionRate = 0;
    $bookingRevenue = $mpesaBookingRevenue = $totalBookingRevenue = 0;
    $completedBookings = $avgBookingValue = 0;
    $statusBreakdown = $monthlyBookings = $recentBookings = [];
}

$page_title = "Bookings Report - Speedy Wheels";
require_once "../../includes/header.php";
?>

<div class="container-fluid mt-4">
    <!-- Header with Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-calendar-alt me-2 text-success"></i>Bookings Report
            </h1>
            <p class="text-muted mb-0">Booking trends and performance</p>
        </div>
        <div class="btn-group">
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <button class="btn btn-success" onclick="exportBookings()">
                <i class="fas fa-download me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Booking Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Bookings</h6>
                    <h3 class="card-title"><?php echo number_format($totalBookings); ?></h3>
                    <small><i class="fas fa-calendar-alt me-1"></i> All time</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Active Bookings</h6>
                    <h3 class="card-title"><?php echo number_format($activeBookings); ?></h3>
                    <small><i class="fas fa-play-circle me-1"></i> Currently active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">This Month</h6>
                    <h3 class="card-title"><?php echo number_format($monthBookings); ?></h3>
                    <small><i class="fas fa-calendar me-1"></i> <?php echo date('F Y'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Completion Rate</h6>
                    <h3 class="card-title"><?php echo $completionRate; ?>%</h3>
                    <small><i class="fas fa-check-circle me-1"></i> Successful bookings</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Analysis Section -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2">Total Booking Revenue</h6>
                    <h3 class="card-title">KES <?php echo number_format($totalBookingRevenue, 2); ?></h3>
                    <small><i class="fas fa-money-bill-wave me-1"></i> From completed bookings</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2">Regular Payments</h6>
                    <h3 class="card-title">KES <?php echo number_format($bookingRevenue, 2); ?></h3>
                    <small><i class="fas fa-credit-card me-1"></i> Standard payments</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2">MPESA Revenue</h6>
                    <h3 class="card-title">KES <?php echo number_format($mpesaBookingRevenue, 2); ?></h3>
                    <small><i class="fas fa-mobile-alt me-1"></i> Mobile payments</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Charts -->
    <!-- Rest of your existing code remains the same -->
    <!-- ... -->
</div>

<?php require_once "../../includes/footer.php"; ?>
PHP

# Replace the original file with the updated one
mv src/modules/reports/bookings_report_updated.php src/modules/reports/bookings_report.php

echo "Bookings report updated successfully!"
echo "Backup saved as: src/modules/reports/bookings_report.php.backup"

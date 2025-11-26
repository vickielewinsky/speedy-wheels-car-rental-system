<?php
// src/modules/reports/index.php

// Start session and check authentication FIRST
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Check if user is admin - do this BEFORE any output
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Now include header and display content
require_once '../../includes/header.php';

try {
    $pdo = getDatabaseConnection();
    
    // FINANCIAL REPORTS DATA
    $financial_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_bookings,
            COALESCE(SUM(total_amount), 0) as total_revenue,
            COALESCE(AVG(total_amount), 0) as avg_booking_value,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
            COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_bookings,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END), 0) as completed_revenue
        FROM bookings
    ");
    $financial_data = $financial_stmt->fetch(PDO::FETCH_ASSOC);

    // BOOKING ANALYTICS DATA
    $monthly_stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as bookings_count,
            COALESCE(SUM(total_amount), 0) as revenue,
            AVG(total_amount) as avg_revenue
        FROM bookings 
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6
    ");
    $monthly_data = $monthly_stmt->fetchAll(PDO::FETCH_ASSOC);

    // REVENUE TRACKING - M-PESA Transactions
    $mpesa_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_transactions,
            COALESCE(SUM(amount), 0) as total_mpesa_revenue,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_transactions,
            COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_transactions,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_transactions
        FROM mpesa_transactions
    ");
    $mpesa_data = $mpesa_stmt->fetch(PDO::FETCH_ASSOC);

    // VEHICLE PERFORMANCE
    $vehicle_stmt = $pdo->query("
        SELECT 
            v.make,
            v.model,
            v.plate_no,
            v.daily_rate,
            COUNT(b.booking_id) as booking_count,
            COALESCE(SUM(b.total_amount), 0) as total_revenue,
            COALESCE(AVG(b.total_amount), 0) as avg_revenue_per_booking
        FROM vehicles v
        LEFT JOIN bookings b ON v.vehicle_id = b.vehicle_id 
        GROUP BY v.vehicle_id, v.make, v.model, v.plate_no, v.daily_rate
        ORDER BY total_revenue DESC
    ");
    $vehicle_data = $vehicle_stmt->fetchAll(PDO::FETCH_ASSOC);

    // CUSTOMER ANALYTICS
    $customer_stmt = $pdo->query("
        SELECT 
            c.name,
            c.email,
            c.phone,
            COUNT(b.booking_id) as booking_count,
            COALESCE(SUM(b.total_amount), 0) as total_spent,
            MAX(b.created_at) as last_booking_date
        FROM customers c
        LEFT JOIN bookings b ON c.customer_id = b.customer_id 
        GROUP BY c.customer_id, c.name, c.email, c.phone
        ORDER BY total_spent DESC
        LIMIT 10
    ");
    $customer_data = $customer_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h1><i class="fas fa-chart-line"></i> Speedy Wheels Reporting System</h1>
            <p class="lead">Comprehensive Financial, Booking & Revenue Analytics</p>
        </div>
    </div>

    <!-- FINANCIAL REPORTS SECTION -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Financial Reports</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Revenue</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">Ksh <?= number_format($financial_data['total_revenue'], 2) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Bookings</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $financial_data['total_bookings'] ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Booking Value</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">Ksh <?= number_format($financial_data['avg_booking_value'], 2) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Completed Revenue</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">Ksh <?= number_format($financial_data['completed_revenue'], 2) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Pending Bookings</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $financial_data['pending_bookings'] ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="card border-left-secondary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Cancelled</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $financial_data['cancelled_bookings'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOOKING ANALYTICS & REVENUE TRACKING -->
    <div class="row">
        <!-- Booking Analytics -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Booking Analytics - Monthly Trend</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($monthly_data)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Month</th>
                                        <th>Bookings</th>
                                        <th>Revenue</th>
                                        <th>Average</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthly_data as $month): ?>
                                        <tr>
                                            <td><strong><?= $month['month'] ?></strong></td>
                                            <td><?= $month['bookings_count'] ?></td>
                                            <td>Ksh <?= number_format($month['revenue'], 2) ?></td>
                                            <td>Ksh <?= number_format($month['avg_revenue'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No booking data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Revenue Tracking -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-money-bill-trend-up"></i> Revenue Tracking - M-PESA</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-3">
                                    <h6>Total Transactions</h6>
                                    <h4><?= $mpesa_data['total_transactions'] ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body py-3">
                                    <h6>M-PESA Revenue</h6>
                                    <h4>Ksh <?= number_format($mpesa_data['total_mpesa_revenue'], 2) ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body py-3">
                                    <h6>Success Rate</h6>
                                    <h4>
                                        <?= $mpesa_data['total_transactions'] > 0 ? 
                                            number_format(($mpesa_data['successful_transactions'] / $mpesa_data['total_transactions']) * 100, 1) : 0 ?>%
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            Successful: <?= $mpesa_data['successful_transactions'] ?> | 
                            Failed: <?= $mpesa_data['failed_transactions'] ?> | 
                            Pending: <?= $mpesa_data['pending_transactions'] ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VEHICLE PERFORMANCE -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-car"></i> Vehicle Performance Analytics</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($vehicle_data)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Vehicle</th>
                                        <th>Plate No</th>
                                        <th>Daily Rate</th>
                                        <th>Bookings</th>
                                        <th>Total Revenue</th>
                                        <th>Avg/Booking</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehicle_data as $vehicle): ?>
                                        <tr>
                                            <td><strong><?= $vehicle['make'] ?> <?= $vehicle['model'] ?></strong></td>
                                            <td><?= $vehicle['plate_no'] ?></td>
                                            <td>Ksh <?= number_format($vehicle['daily_rate'], 2) ?></td>
                                            <td><?= $vehicle['booking_count'] ?></td>
                                            <td>Ksh <?= number_format($vehicle['total_revenue'], 2) ?></td>
                                            <td>Ksh <?= number_format($vehicle['avg_revenue_per_booking'], 2) ?></td>
                                            <td>
                                                <?php if ($vehicle['booking_count'] > 2): ?>
                                                    <span class="badge bg-success">High</span>
                                                <?php elseif ($vehicle['booking_count'] == 0): ?>
                                                    <span class="badge bg-secondary">No Data</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Medium</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No vehicle performance data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- CUSTOMER ANALYTICS -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Customer Analytics - Top Customers</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($customer_data)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Customer Name</th>
                                        <th>Contact</th>
                                        <th>Total Bookings</th>
                                        <th>Total Spent</th>
                                        <th>Last Booking</th>
                                        <th>Customer Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customer_data as $customer): ?>
                                        <tr>
                                            <td><strong><?= $customer['name'] ?></strong></td>
                                            <td>
                                                <div><?= $customer['email'] ?></div>
                                                <small class="text-muted"><?= $customer['phone'] ?></small>
                                            </td>
                                            <td><?= $customer['booking_count'] ?></td>
                                            <td>Ksh <?= number_format($customer['total_spent'], 2) ?></td>
                                            <td><?= $customer['last_booking_date'] ? date('M j, Y', strtotime($customer['last_booking_date'])) : 'Never' ?></td>
                                            <td>
                                                <?php if ($customer['total_spent'] > 50000): ?>
                                                    <span class="badge bg-success">VIP</span>
                                                <?php elseif ($customer['total_spent'] > 20000): ?>
                                                    <span class="badge bg-primary">Regular</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">New</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No customer data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
<?php
require_once __DIR__ . '/../../helpers/url_helper.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/Auth.php';

requireAuthentication();

$page_title = "Dashboard - Speedy Wheels";
require_once __DIR__ . '/../../includes/header.php';

$auth = new Auth();
$current_user = $auth->getCurrentUser();

if (!$current_user) {
    die("User not logged in. Please <a href='" . login.php . "'>login</a>.");
}

// Get user role
$user_role = strtolower($current_user['user_role'] ?? 'user');
$is_admin = ($user_role === 'admin' || $user_role === 'superadmin');

// Common data for all users
$bookings = $auth->getUserBookings($current_user['id']);
$activeCount = $auth->countActiveBookings($current_user['id']);
$completedCount = $auth->countCompletedBookings($current_user['id']);

// Admin-specific data
$admin_data = [];
if ($is_admin) {
    // Get all registered users (for admin only)
    $admin_data['all_users'] = $auth->getAllUsers();
    
    // Get M-Pesa transactions (for admin only)
    $admin_data['mpesa_transactions'] = $auth->getMpesaTransactions();
    
    // Get system stats (for admin only)
    $admin_data['system_stats'] = $auth->getSystemStats();
    
    // Get recent bookings (for admin only)
    $admin_data['recent_bookings'] = $auth->getRecentBookings(10);
    
    // Get revenue stats (for admin only)
    $admin_data['revenue_stats'] = $auth->getRevenueStats();
}
?>

<div class="container-fluid mt-4">

    <!-- Top Right Back Button -->
    <div class="d-flex justify-content-end mb-3">
        <a href="<?php echo base_url('index.php'); ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-home me-1"></i> Back to Home
        </a>
    </div>

    <!-- Welcome Card -->
    <div class="card dashboard-welcome-card text-white p-4 shadow-lg rounded mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3>Welcome back, <?php echo htmlspecialchars($current_user['first_name']); ?>! 👋</h3>
                <p>
                    Logged in as 
                    <span class="badge 
                        <?php 
                        if ($is_admin) echo 'bg-warning';
                        else echo 'bg-info';
                        ?>">
                        <?php echo ucfirst($user_role); ?>
                    </span>
                </p>
                <?php if ($is_admin): ?>
                    <small class="opacity-75">
                        <i class="fas fa-shield-alt me-1"></i> Administrator Panel Active
                    </small>
                <?php endif; ?>
            </div>
            <div class="text-center mt-3 mt-md-0">
                <i class="fas fa-user-circle fa-6x opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- ADMIN DASHBOARD SECTION -->
    <?php if ($is_admin): ?>
        <!-- Admin Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-danger shadow-lg rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Total Users</h6>
                            <h3><?php echo $admin_data['system_stats']['total_users'] ?? 0; ?></h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success shadow-lg rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Total Bookings</h6>
                            <h3><?php echo $admin_data['system_stats']['total_bookings'] ?? 0; ?></h3>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-primary shadow-lg rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Total Revenue</h6>
                            <h3>KES <?php echo number_format($admin_data['revenue_stats']['total_revenue'] ?? 0, 2); ?></h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info shadow-lg rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>M-Pesa Transactions</h6>
                            <h3><?php echo $admin_data['system_stats']['mpesa_transactions'] ?? 0; ?></h3>
                        </div>
                        <i class="fas fa-mobile-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Tabs Section -->
        <div class="card shadow-lg rounded mb-4">
            <div class="card-header bg-dark text-white">
                <ul class="nav nav-tabs card-header-tabs" id="adminTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button">All Users</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="mpesa-tab" data-bs-toggle="tab" data-bs-target="#mpesa" type="button">M-Pesa Payments</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button">Recent Bookings</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="revenue-tab" data-bs-toggle="tab" data-bs-target="#revenue" type="button">Revenue</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="adminTabsContent">
                    
                    <!-- All Users Tab -->
                    <div class="tab-pane fade show active" id="users" role="tabpanel">
                        <h5><i class="fas fa-users me-2"></i> Registered Users</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admin_data['all_users'] as $user): ?>
                                        <tr>
                                            <td>#<?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                    if ($user['user_role'] === 'admin') echo 'bg-warning';
                                                    else echo 'bg-info';
                                                    ?>">
                                                    <?php echo ucfirst($user['user_role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-user" data-id="<?php echo $user['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- M-Pesa Payments Tab -->
                    <div class="tab-pane fade" id="mpesa" role="tabpanel">
                        <h5><i class="fas fa-mobile-alt me-2"></i> M-Pesa Transactions</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Phone</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admin_data['mpesa_transactions'] as $transaction): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($transaction['transaction_id'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($transaction['phone']); ?></td>
                                            <td>KES <?php echo number_format($transaction['amount'], 2); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                    if ($transaction['status'] === 'completed') echo 'bg-success';
                                                    elseif ($transaction['status'] === 'pending') echo 'bg-warning';
                                                    else echo 'bg-secondary';
                                                    ?>">
                                                    <?php echo ucfirst($transaction['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($transaction['booking_reference']); ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($transaction['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info view-mpesa" data-id="<?php echo $transaction['id']; ?>">
                                                    <i class="fas fa-receipt"></i> Receipt
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Recent Bookings Tab -->
                    <div class="tab-pane fade" id="bookings" role="tabpanel">
                        <h5><i class="fas fa-calendar-alt me-2"></i> Recent Bookings</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Customer</th>
                                        <th>Vehicle</th>
                                        <th>Period</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admin_data['recent_bookings'] as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['vehicle_name']); ?></td>
                                            <td><?php echo date('d M', strtotime($booking['booking_date'])) . ' - ' . date('d M', strtotime($booking['return_date'])); ?></td>
                                            <td>KES <?php echo number_format($booking['total_price'], 2); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                    if ($booking['status'] === 'active') echo 'bg-primary';
                                                    elseif ($booking['status'] === 'completed') echo 'bg-success';
                                                    else echo 'bg-secondary';
                                                    ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Revenue Tab -->
                    <div class="tab-pane fade" id="revenue" role="tabpanel">
                        <h5><i class="fas fa-chart-line me-2"></i> Revenue Analytics</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Monthly Revenue</h6>
                                        <h3>KES <?php echo number_format($admin_data['revenue_stats']['monthly_revenue'] ?? 0, 2); ?></h3>
                                        <p class="text-muted">This month</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Daily Average</h6>
                                        <h3>KES <?php echo number_format($admin_data['revenue_stats']['daily_average'] ?? 0, 2); ?></h3>
                                        <p class="text-muted">Last 30 days</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h6>Payment Methods</h6>
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-success" style="width: 80%">M-Pesa (80%)</div>
                                <div class="progress-bar bg-info" style="width: 15%">Cash (15%)</div>
                                <div class="progress-bar bg-warning" style="width: 5%">Other (5%)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- USER DASHBOARD SECTION -->
    <?php if (!$is_admin): ?>
        <!-- Stats Cards for Regular Users -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary shadow-lg rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Active Bookings</h5>
                            <h3><?php echo $activeCount; ?></h3>
                        </div>
                        <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success shadow-lg rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Completed Rentals</h5>
                            <h3><?php echo $completedCount; ?></h3>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info shadow-lg rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Total Spent</h5>
                            <h3>
                                <?php
                                $total_spent = 0;
                                foreach ($bookings as $b) {
                                    if (!empty($b['price'])) {
                                        $total_spent += $b['price'];
                                    }
                                }
                                echo 'KES ' . number_format($total_spent, 2);
                                ?>
                            </h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- CREDENTIALS CARD (Visible to all) -->
    <div class="card shadow-lg rounded p-3 mb-4">
        <h4><i class="fas fa-id-card me-2"></i> 
            <?php echo $is_admin ? 'Admin Credentials' : 'My Credentials'; ?>
        </h4>
        <table class="table table-bordered align-middle">
            <tr>
                <th>Username</th>
                <td><?php echo htmlspecialchars($current_user['username']); ?></td>
            </tr>
            <tr>
                <th>Full Name</th>
                <td><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($current_user['email']); ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo htmlspecialchars($current_user['phone']); ?></td>
            </tr>
            <tr>
                <th>Password</th>
                <td>
                    <input type="password" id="password-field" class="form-control form-control-sm d-inline-block w-auto" value="********" readonly>
                    <i class="fas fa-eye ms-2" id="togglePassword" style="cursor:pointer;"></i>
                </td>
            </tr>
            <tr>
                <th>Registered At</th>
                <td><?php echo $current_user['created_at'] ? date('F j, Y', strtotime($current_user['created_at'])) : 'N/A'; ?></td>
            </tr>
            <?php if ($is_admin): ?>
                <tr>
                    <th>Admin Privileges</th>
                    <td>
                        <span class="badge bg-success me-2"><i class="fas fa-users-cog"></i> Manage Users</span>
                        <span class="badge bg-info me-2"><i class="fas fa-money-check-alt"></i> View Payments</span>
                        <span class="badge bg-warning"><i class="fas fa-chart-bar"></i> View Reports</span>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
        
        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mt-3">
            <div>
                <a href="#" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> Edit Profile
                </a>
                <a href="#" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-key me-1"></i> Change Password
                </a>
            </div>
            <?php if ($is_admin): ?>
                <div>
                    <a href="<?php echo base_url('src/modules/reports/index.php'); ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-chart-bar me-1"></i> Full Reports
                    </a>
                    <a href="<?php echo base_url('src/modules/customers/index.php'); ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-users me-1"></i> Manage Customers
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BOOKINGS SECTION (Enhanced for both) -->
    <div class="card shadow-lg rounded p-3">
        <h4><i class="fas fa-car-side me-2"></i> 
            <?php echo $is_admin ? 'All Bookings' : 'My Bookings'; ?>
        </h4>
        
        <?php if(!empty($bookings)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <?php if ($is_admin): ?>
                                <th>Customer</th>
                            <?php endif; ?>
                            <th>Vehicle</th>
                            <th>Booking Date</th>
                            <th>Return Date</th>
                            <th>Days</th>
                            <th>Rate/Day</th>
                            <th>Total Amount</th>
                            <th>Payment Status</th>
                            <th>Booking Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bookings as $b): ?>
                            <tr>
                                <?php if ($is_admin): ?>
                                    <td><?php echo htmlspecialchars($b['customer_name'] ?? 'Customer'); ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="vehicle-icon me-2">
                                            <i class="fas fa-car fa-2x text-muted"></i>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($b['vehicle_name'] ?? $b['vehicle_make'] . ' ' . $b['vehicle_model']); ?></strong><br>
                                            <small class="text-muted">ID: <?php echo $b['vehicle_id'] ?? 'N/A'; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('d M Y', strtotime($b['booking_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($b['return_date'])); ?></td>
                                <td>
                                    <?php 
                                    $days = (strtotime($b['return_date']) - strtotime($b['booking_date'])) / (60 * 60 * 24);
                                    echo ceil($days);
                                    ?>
                                </td>
                                <td>KES <?php echo number_format($b['vehicle_rate'] ?? 0, 2); ?></td>
                                <td><strong>KES <?php echo number_format($b['price'] ?? 0, 2); ?></strong></td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        if ($b['payment_status'] === 'paid') echo 'bg-success';
                                        elseif ($b['payment_status'] === 'partial') echo 'bg-warning';
                                        else echo 'bg-danger';
                                        ?>">
                                        <?php echo ucfirst($b['payment_status'] ?? 'pending'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        if ($b['status'] === 'active') echo 'bg-primary';
                                        elseif ($b['status'] === 'completed') echo 'bg-success';
                                        elseif ($b['status'] === 'cancelled') echo 'bg-danger';
                                        else echo 'bg-secondary';
                                        ?>">
                                        <?php echo ucfirst($b['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary view-booking" data-id="<?php echo $b['booking_id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success receipt-btn" data-id="<?php echo $b['booking_id']; ?>">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                        <?php if ($is_admin): ?>
                                            <button class="btn btn-sm btn-outline-warning edit-booking" data-id="<?php echo $b['booking_id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                <p class="text-muted">
                    <?php echo $is_admin ? 'No bookings found in the system.' : 'You haven\'t booked any vehicles yet.'; ?>
                </p>
                <a href="<?php echo base_url('src/modules/vehicles/index.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-car me-1"></i> Browse Vehicles
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- RECEIPTS SECTION (For users only) -->
    <?php if (!$is_admin && !empty($bookings)): ?>
        <div class="card shadow-lg rounded p-3 mt-4">
            <h4><i class="fas fa-receipt me-2"></i> My Receipts</h4>
            <div class="row g-3">
                <?php foreach($bookings as $index => $b): 
                    if ($b['payment_status'] === 'paid'): ?>
                    <div class="col-md-4">
                        <div class="card receipt-card h-100">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-file-invoice-dollar me-1"></i> Receipt #<?php echo $index + 1; ?></h6>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($b['vehicle_name'] ?? 'Vehicle'); ?></h5>
                                <div class="receipt-details">
                                    <p><strong>Booking ID:</strong> #<?php echo $b['booking_id']; ?></p>
                                    <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($b['booking_date'])); ?></p>
                                    <p><strong>Amount:</strong> KES <?php echo number_format($b['price'], 2); ?></p>
                                    <p><strong>Payment Method:</strong> M-Pesa</p>
                                    <p><strong>Transaction ID:</strong> <?php echo $b['transaction_id'] ?? 'N/A'; ?></p>
                                </div>
                                <div class="text-center mt-3">
                                    <button class="btn btn-sm btn-outline-success download-receipt" data-id="<?php echo $b['booking_id']; ?>">
                                        <i class="fas fa-download me-1"></i> Download PDF
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary print-receipt" data-id="<?php echo $b['booking_id']; ?>">
                                        <i class="fas fa-print me-1"></i> Print
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- QUICK ACTIONS SECTION -->
    <div class="card shadow-lg rounded p-3 mt-4">
        <h4><i class="fas fa-bolt me-2"></i> Quick Actions</h4>
        <div class="row g-3">
            <?php if ($is_admin): ?>
                <!-- Admin Quick Actions -->
                <div class="col-md-3">
                    <a href="<?php echo base_url('src/modules/customers/index.php'); ?>" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <span>Manage Users</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?php echo base_url('src/modules/vehicles/index.php'); ?>" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-car fa-2x mb-2"></i>
                        <span>Manage Vehicles</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?php echo base_url('src/modules/bookings/index.php'); ?>" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                        <span>All Bookings</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?php echo base_url('src/modules/reports/index.php'); ?>" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-chart-bar fa-2x mb-2"></i>
                        <span>Generate Reports</span>
                    </a>
                </div>
            <?php else: ?>
                <!-- User Quick Actions -->
                <div class="col-md-4">
                    <a href="<?php echo base_url('src/modules/vehicles/index.php'); ?>" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-car fa-2x mb-2"></i>
                        <span>Book New Vehicle</span>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="#" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-history fa-2x mb-2"></i>
                        <span>Booking History</span>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="#" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-question-circle fa-2x mb-2"></i>
                        <span>Get Help</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Modal for User Details -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetails">
                Loading...
            </div>
        </div>
    </div>
</div>

<!-- Modal for Receipt -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="receiptDetails">
                Loading...
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" onclick="printReceipt()">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
const togglePassword = document.querySelector('#togglePassword');
const passwordField = document.querySelector('#password-field');
togglePassword.addEventListener('click', () => {
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);
    togglePassword.classList.toggle('fa-eye');
    togglePassword.classList.toggle('fa-eye-slash');
});

// Initialize Bootstrap tabs
var tabEl = document.querySelectorAll('button[data-bs-toggle="tab"]')
tabEl.forEach(function(tab) {
    new bootstrap.Tab(tab)
});

// View User Details
document.querySelectorAll('.view-user').forEach(button => {
    button.addEventListener('click', function() {
        const userId = this.getAttribute('data-id');
        fetch(`get_user_details.php?id=${userId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('userDetails').innerHTML = data;
                new bootstrap.Modal(document.getElementById('userModal')).show();
            });
    });
});

// View Receipt
document.querySelectorAll('.receipt-btn, .view-mpesa').forEach(button => {
    button.addEventListener('click', function() {
        const bookingId = this.getAttribute('data-id');
        fetch(`get_receipt.php?booking_id=${bookingId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('receiptDetails').innerHTML = data;
                new bootstrap.Modal(document.getElementById('receiptModal')).show();
            });
    });
});

// Print Receipt
function printReceipt() {
    const receiptContent = document.getElementById('receiptDetails').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Print Receipt</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                @media print {
                    body { margin: 0; padding: 20px; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            ${receiptContent}
            <script>
                window.onload = function() { window.print(); }
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// Download Receipt
document.querySelectorAll('.download-receipt').forEach(button => {
    button.addEventListener('click', function() {
        const bookingId = this.getAttribute('data-id');
        window.location.href = `download_receipt.php?booking_id=${bookingId}`;
    });
});
</script>

<style>
.dashboard-welcome-card {
    background: linear-gradient(135deg, <?php echo $is_admin ? '#f39c12, #e74c3c' : '#667eea, #764ba2'; ?>);
    color: #fff;
}
.card h4 {
    margin-bottom: 1rem;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 10px;
}
.card h5 {
    font-weight: 600;
}
.receipt-card {
    border: 2px solid #28a745;
    transition: transform 0.3s;
}
.receipt-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2);
}
.vehicle-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 8px;
}
.nav-tabs .nav-link.active {
    background: #343a40;
    color: white;
    border-color: #343a40;
}
.progress-bar {
    font-size: 12px;
    font-weight: bold;
}
.btn-group .btn {
    padding: 0.25rem 0.5rem;
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
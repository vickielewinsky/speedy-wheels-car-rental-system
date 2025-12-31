<?php
// src/modules/auth/dashboard.php - FIXED VERSION

// Start session
session_start();

// Include helpers - url_helper.php already has base_url()
require_once __DIR__ . '/../../helpers/url_helper.php';
require_once __DIR__ . '/../../config/database.php';

// Simple Auth class for basic operations
class SimpleAuth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function getUserBookings($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.*, v.make, v.model, v.daily_rate 
                FROM bookings b
                LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                WHERE b.user_id = ?
                ORDER BY b.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Admin functions
    public function getAllUsers() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM users ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function getMpesaTransactions() {
        try {
            $stmt = $this->pdo->query("
                SELECT * FROM mpesa_transactions 
                ORDER BY created_at DESC 
                LIMIT 50
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function getSystemStats() {
        try {
            $stats = [];
            
            // Total users
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users");
            $stats['total_users'] = $stmt->fetchColumn();
            
            // Total bookings
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM bookings");
            $stats['total_bookings'] = $stmt->fetchColumn();
            
            // M-Pesa transactions
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM mpesa_transactions");
            $stats['mpesa_transactions'] = $stmt->fetchColumn();
            
            return $stats;
        } catch (Exception $e) {
            return ['total_users' => 0, 'total_bookings' => 0, 'mpesa_transactions' => 0];
        }
    }
    
    public function getRevenueStats() {
        try {
            // Get total revenue from payments table
            $stmt = $this->pdo->query("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed'");
            $total = $stmt->fetchColumn();
            
            // Get monthly revenue
            $stmt = $this->pdo->query("
                SELECT SUM(amount) as monthly 
                FROM payments 
                WHERE payment_status = 'completed' 
                AND MONTH(payment_date) = MONTH(CURRENT_DATE())
                AND YEAR(payment_date) = YEAR(CURRENT_DATE())
            ");
            $monthly = $stmt->fetchColumn();
            
            return [
                'total_revenue' => $total ?: 0,
                'monthly_revenue' => $monthly ?: 0,
                'daily_average' => $total ? $total / 30 : 0 // Simple average
            ];
        } catch (Exception $e) {
            return ['total_revenue' => 0, 'monthly_revenue' => 0, 'daily_average' => 0];
        }
    }
    
    public function getRecentBookings($limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.*, u.first_name, u.last_name, v.make, v.model,
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       CONCAT(v.make, ' ', v.model) as vehicle_name
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.id
                LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                ORDER BY b.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function countActiveBookings($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count FROM bookings 
                WHERE user_id = ? AND status = 'active'
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn() ?: 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    public function countCompletedBookings($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count FROM bookings 
                WHERE user_id = ? AND status = 'completed'
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn() ?: 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=speedy_wheels;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Create auth instance
    $auth = new SimpleAuth($pdo);
    $current_user = $auth->getCurrentUser();
    
    if (!$current_user) {
        header("Location: " . base_url('src/modules/auth/login.php'));
        exit();
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
        $admin_data['all_users'] = $auth->getAllUsers();
        $admin_data['mpesa_transactions'] = $auth->getMpesaTransactions();
        $admin_data['system_stats'] = $auth->getSystemStats();
        $admin_data['recent_bookings'] = $auth->getRecentBookings(10);
        $admin_data['revenue_stats'] = $auth->getRevenueStats();
    }
    
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

$page_title = "Dashboard - Speedy Wheels";
include __DIR__ . '/../../includes/header.php';
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
                    <span class="badge <?php echo $is_admin ? 'bg-warning' : 'bg-info'; ?>">
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
                                        <th>Booking ID</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admin_data['mpesa_transactions'] as $transaction): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($transaction['transaction_code'] ?? 'N/A'); ?></td>
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
                                            <td>#<?php echo $transaction['booking_id']; ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($transaction['created_at'])); ?></td>
                                            <td>
                                                <a href="<?php echo base_url('src/modules/payments/payment_status.php?transaction_code=' . urlencode($transaction['transaction_code'])); ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-receipt"></i> View
                                                </a>
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
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admin_data['recent_bookings'] as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['booking_id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($booking['start_date'])); ?></td>
                                            <td><?php echo date('d M Y', strtotime($booking['end_date'])); ?></td>
                                            <td>KES <?php echo number_format($booking['total_amount'] ?? 0, 2); ?></td>
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
                                    $days = ceil((strtotime($b['end_date']) - strtotime($b['start_date'])) / (60 * 60 * 24));
                                    $total_spent += ($b['daily_rate'] ?? 0) * $days;
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
                        <a href="<?php echo base_url('src/modules/customers/index.php'); ?>" class="badge bg-success me-2 text-decoration-none">
                            <i class="fas fa-users-cog"></i> Manage Users
                        </a>
                        <a href="<?php echo base_url('src/modules/payments/payment.php'); ?>" class="badge bg-info me-2 text-decoration-none">
                            <i class="fas fa-money-check-alt"></i> View Payments
                        </a>
                        <a href="<?php echo base_url('src/modules/reports/index.php'); ?>" class="badge bg-warning text-decoration-none">
                            <i class="fas fa-chart-bar"></i> View Reports
                        </a>
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
                <a href="<?php echo base_url('src/modules/auth/reset_passwords.php'); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-key me-1"></i> Change Password
                </a>
            </div>
            <?php if ($is_admin): ?>
                <div>
                    <a href="<?php echo base_url('src/modules/reports/index.php'); ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-chart-bar me-1"></i> Full Reports
                    </a>
                    <a href="<?php echo base_url('src/modules/vehicles/index.php'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-car me-1"></i> Manage Vehicles
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
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Days</th>
                            <th>Rate/Day</th>
                            <th>Total Amount</th>
                            <th>Payment Status</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bookings as $b): 
                            $days = ceil((strtotime($b['end_date']) - strtotime($b['start_date'])) / (60 * 60 * 24));
                            $total_amount = ($b['daily_rate'] ?? 0) * $days;
                        ?>
                            <tr>
                                <?php if ($is_admin): ?>
                                    <td><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="vehicle-icon me-2">
                                            <i class="fas fa-car fa-2x text-muted"></i>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($b['make'] . ' ' . $b['model']); ?></strong><br>
                                            <small class="text-muted">Rate: KES <?php echo number_format($b['daily_rate'] ?? 0, 2); ?>/day</small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('d M Y', strtotime($b['start_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($b['end_date'])); ?></td>
                                <td><?php echo $days; ?></td>
                                <td>KES <?php echo number_format($b['daily_rate'] ?? 0, 2); ?></td>
                                <td><strong>KES <?php echo number_format($total_amount, 2); ?></strong></td>
                                <td>
                                    <span class="badge bg-success">
                                        Paid
                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        if ($b['status'] === 'active') echo 'bg-primary';
                                        elseif ($b['status'] === 'completed') echo 'bg-success';
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
                                        <a href="/'src/modules/auth/get_receipt.php?booking_id=' . $b['booking_id']" class="btn btn-sm btn-outline-success" target="_blank">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <?php if ($is_admin): ?>
                                            <a href="/'src/modules/bookings/index.php?edit=' . $b['booking_id']" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
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
                <a href="<?php echo base_url('src/modules/bookings/create_booking.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-car me-1"></i> Book a Vehicle
                </a>
            </div>
        <?php endif; ?>
    </div>

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
                    <a href="<?php echo base_url('src/modules/payments/payment.php'); ?>" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-file-invoice-dollar fa-2x mb-2"></i>
                        <span>Payment History</span>
                    </a>
                </div>
            <?php else: ?>
                <!-- User Quick Actions -->
                <div class="col-md-4">
                    <a href="<?php echo base_url('src/modules/bookings/create_booking.php'); ?>" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-car fa-2x mb-2"></i>
                        <span>Book New Vehicle</span>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?php echo base_url('src/modules/vehicles/index.php'); ?>" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <span>Browse Vehicles</span>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?php echo base_url('contact.php'); ?>" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
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
var tabEl = document.querySelectorAll('button[data-bs-toggle="tab"]');
tabEl.forEach(function(tab) {
    new bootstrap.Tab(tab);
});

// View booking details
document.querySelectorAll('.view-booking').forEach(button => {
    button.addEventListener('click', function() {
        const bookingId = this.getAttribute('data-id');
        alert('Viewing booking #' + bookingId + '\nThis would show booking details in a modal.');
    });
});

// View user details
document.querySelectorAll('.view-user').forEach(button => {
    button.addEventListener('click', function() {
        const userId = this.getAttribute('data-id');
        fetch('/"src/modules/auth/get_user_details.php"?id=' + userId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('userDetails').innerHTML = data;
                new bootstrap.Modal(document.getElementById('userModal')).show();
            })
            .catch(error => {
                document.getElementById('userDetails').innerHTML = 
                    '<div class="alert alert-danger">Error loading user details: ' + error + '</div>';
                new bootstrap.Modal(document.getElementById('userModal')).show();
            });
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<?php
// src/modules/reports/revenue_report.php
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
    
    // Get current month and year
    $currentMonth = date('Y-m');
    $currentYear = date('Y');
    
    // Total Revenue (from payments table + mpesa_transactions)
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM payments 
        WHERE status = 'completed'
        
        UNION ALL
        
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM mpesa_transactions 
        WHERE status = 'completed'
    ");
    
    $totalRevenue = 0;
    while ($row = $stmt->fetch()) {
        $totalRevenue += $row['total'];
    }
    
    // This Month Revenue
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as revenue 
        FROM payments 
        WHERE status = 'completed' AND DATE_FORMAT(created_at, '%Y-%m') = ?
        
        UNION ALL
        
        SELECT COALESCE(SUM(amount), 0) as revenue 
        FROM mpesa_transactions 
        WHERE status = 'completed' AND DATE_FORMAT(created_at, '%Y-%m') = ?
    ");
    $stmt->execute([$currentMonth, $currentMonth]);
    
    $monthRevenue = 0;
    while ($row = $stmt->fetch()) {
        $monthRevenue += $row['revenue'];
    }
    
    // This Week Revenue
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $weekEnd = date('Y-m-d', strtotime('sunday this week'));
    
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as revenue 
        FROM payments 
        WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?
        
        UNION ALL
        
        SELECT COALESCE(SUM(amount), 0) as revenue 
        FROM mpesa_transactions 
        WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$weekStart, $weekEnd, $weekStart, $weekEnd]);
    
    $weekRevenue = 0;
    while ($row = $stmt->fetch()) {
        $weekRevenue += $row['revenue'];
    }
    
    // Average Revenue per Transaction
    $stmt = $pdo->query("
        SELECT COALESCE(AVG(amount), 0) as avg_revenue 
        FROM payments 
        WHERE status = 'completed'
        
        UNION ALL
        
        SELECT COALESCE(AVG(amount), 0) as avg_revenue 
        FROM mpesa_transactions 
        WHERE status = 'completed'
    ");
    
    $avgRevenue = 0;
    $count = 0;
    while ($row = $stmt->fetch()) {
        $avgRevenue += $row['avg_revenue'];
        $count++;
    }
    $avgRevenue = $count > 0 ? ($avgRevenue / $count) : 0;
    
    // Monthly Revenue Breakdown (for chart) - Combined from both tables
    $stmt = $pdo->query("
        SELECT 
            month,
            COALESCE(SUM(revenue), 0) as revenue
        FROM (
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                amount as revenue
            FROM payments 
            WHERE status = 'completed'
            
            UNION ALL
            
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                amount as revenue
            FROM mpesa_transactions 
            WHERE status = 'completed'
        ) as combined_revenue
        GROUP BY month
        ORDER BY month DESC
        LIMIT 6
    ");
    $monthlyRevenue = $stmt->fetchAll();
    
    // Recent Transactions (from both tables)
    $stmt = $pdo->query("
        SELECT 
            'payment' as type,
            p.payment_id as id,
            p.amount,
            p.status,
            p.created_at,
            c.name as customer_name,
            v.model as vehicle_model,
            b.booking_id
        FROM payments p
        LEFT JOIN bookings b ON p.booking_id = b.booking_id
        LEFT JOIN customers c ON b.customer_id = c.customer_id
        LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
        
        UNION ALL
        
        SELECT 
            'mpesa' as type,
            m.id,
            m.amount,
            m.status,
            m.created_at,
            c.name as customer_name,
            v.model as vehicle_model,
            m.booking_id
        FROM mpesa_transactions m
        LEFT JOIN bookings b ON m.booking_id = b.booking_id
        LEFT JOIN customers c ON b.customer_id = c.customer_id
        LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
        
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $recentPayments = $stmt->fetchAll();
    
    // Transaction Statistics
    $stmt = $pdo->query("
        SELECT 'payments' as source, status, COUNT(*) as count FROM payments GROUP BY status
        UNION ALL
        SELECT 'mpesa' as source, status, COUNT(*) as count FROM mpesa_transactions GROUP BY status
    ");
    $transactionStats = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Revenue Report Error: " . $e->getMessage());
    // Set default values
    $totalRevenue = $monthRevenue = $weekRevenue = $avgRevenue = 0;
    $monthlyRevenue = [];
    $recentPayments = [];
    $transactionStats = [];
}

$page_title = "Revenue Report - Speedy Wheels";
require_once "../../includes/header.php";
?>

<div class="container-fluid mt-4">
    <!-- Header with Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-chart-bar me-2 text-primary"></i>Revenue Report
            </h1>
            <p class="text-muted mb-0">View income and payment analytics</p>
        </div>
        <div class="btn-group">
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Reports
            </a>
            <button class="btn btn-success" onclick="exportRevenue()">
                <i class="fas fa-download me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Revenue Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Revenue</h6>
                    <h3 class="card-title">KES <?php echo number_format($totalRevenue, 2); ?></h3>
                    <small><i class="fas fa-calendar-alt me-1"></i> All time</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">This Month</h6>
                    <h3 class="card-title">KES <?php echo number_format($monthRevenue, 2); ?></h3>
                    <small><i class="fas fa-calendar me-1"></i> <?php echo date('F Y'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">This Week</h6>
                    <h3 class="card-title">KES <?php echo number_format($weekRevenue, 2); ?></h3>
                    <small><i class="fas fa-calendar-week me-1"></i> Week <?php echo date('W'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Avg. per Transaction</h6>
                    <h3 class="card-title">KES <?php echo number_format($avgRevenue, 2); ?></h3>
                    <small><i class="fas fa-calculator me-1"></i> Average amount</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart and Stats -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Revenue Trend</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($monthlyRevenue)): ?>
                        <div style="height: 300px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No revenue data available for chart</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Transaction Statistics</h5>
                </div>
                <div class="card-body">
                    <?php 
                    // Calculate stats
                    $completed = $pending = $failed = 0;
                    $paymentCount = $mpesaCount = 0;
                    
                    foreach ($transactionStats as $stat) {
                        if ($stat['status'] == 'completed') {
                            $completed += $stat['count'];
                        } elseif ($stat['status'] == 'pending') {
                            $pending += $stat['count'];
                        } elseif ($stat['status'] == 'failed') {
                            $failed += $stat['count'];
                        }
                        
                        if ($stat['source'] == 'payments') {
                            $paymentCount += $stat['count'];
                        } elseif ($stat['source'] == 'mpesa') {
                            $mpesaCount += $stat['count'];
                        }
                    }
                    
                    $totalTransactions = $completed + $pending + $failed;
                    $successRate = $totalTransactions > 0 ? round(($completed / $totalTransactions) * 100, 1) : 0;
                    ?>
                    
                    <div class="mb-3">
                        <small class="text-muted">Success Rate</small>
                        <div class="d-flex justify-content-between">
                            <span>Completion</span>
                            <strong class="text-success"><?php echo $successRate; ?>%</strong>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $successRate; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Transaction Status</small>
                        <div class="d-flex justify-content-between">
                            <span>Completed</span>
                            <strong class="text-success"><?php echo $completed; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Pending</span>
                            <strong class="text-warning"><?php echo $pending; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Failed</span>
                            <strong class="text-danger"><?php echo $failed; ?></strong>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div>
                        <small class="text-muted">Payment Sources</small>
                        <div class="mt-2">
                            <span class="badge bg-primary me-2">Payments: <?php echo $paymentCount; ?></span>
                            <span class="badge bg-success">MPESA: <?php echo $mpesaCount; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Transactions</h5>
            <a href="<?php echo base_url('src/modules/payments/index.php'); ?>" class="btn btn-sm btn-outline-primary">
                View All Payments
            </a>
        </div>
        <div class="card-body">
            <?php if (!empty($recentPayments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Transaction ID</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPayments as $payment): ?>
                                <tr>
                                    <td>
                                        <?php if ($payment['type'] == 'mpesa'): ?>
                                            <span class="badge bg-success">MPESA</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Payment</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($payment['type'] == 'mpesa'): ?>
                                            MPESA-<?php echo htmlspecialchars($payment['id']); ?>
                                        <?php else: ?>
                                            PAY-<?php echo htmlspecialchars($payment['id']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($payment['customer_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($payment['vehicle_model'] ?? 'N/A'); ?></td>
                                    <td class="fw-bold">KES <?php echo number_format($payment['amount'], 2); ?></td>
                                    <td>
                                        <?php if ($payment['status'] == 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php elseif ($payment['status'] == 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No transactions found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($monthlyRevenue)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php echo implode(',', array_map(function($item) { 
            return "'" . date('M Y', strtotime($item['month'] . '-01')) . "'"; 
        }, array_reverse($monthlyRevenue))); ?>],
        datasets: [{
            label: 'Revenue (KES)',
            data: [<?php echo implode(',', array_map(function($item) { 
                return $item['revenue']; 
            }, array_reverse($monthlyRevenue))); ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgb(54, 162, 235)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'KES ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'KES ' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Export function
function exportRevenue() {
    const data = [
        ['Month', 'Revenue (KES)'],
        <?php foreach ($monthlyRevenue as $item): ?>
        ['<?php echo date('M Y', strtotime($item['month'] . '-01')); ?>', <?php echo $item['revenue']; ?>],
        <?php endforeach; ?>
    ];
    
    let csvContent = "data:text/csv;charset=utf-8,";
    data.forEach(row => {
        csvContent += row.join(",") + "\r\n";
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "revenue_report_<?php echo date('Y-m-d'); ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
<?php endif; ?>

<?php require_once "../../includes/footer.php"; ?>
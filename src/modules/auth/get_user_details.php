<?php
// src/modules/auth/get_user_details.php
session_start();
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['id'])) {
    die("User ID required");
}

$auth = new Auth();
$user_id = $_GET['id'];
$current_user = $auth->getCurrentUser();

// Check if current user is admin
if (!$auth->isAdmin()) {
    die("Access denied. Admin only.");
}

$user = $auth->getUserDetails($user_id);

if (!$user) {
    echo "<div class='alert alert-danger'>User not found!</div>";
    exit;
}
?>

<div class="user-details">
    <div class="row">
        <div class="col-md-4 text-center mb-3">
            <div class="mb-3">
                <i class="fas fa-user-circle fa-5x text-primary"></i>
            </div>
            <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
            <span class="badge <?php echo $user['user_role'] === 'admin' ? 'bg-warning' : 'bg-info'; ?>">
                <?php echo ucfirst($user['user_role']); ?>
            </span>
        </div>
        <div class="col-md-8">
            <table class="table table-sm">
                <tr>
                    <th>Username:</th>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Registered:</th>
                    <td><?php echo date('F j, Y, g:i a', strtotime($user['created_at'])); ?></td>
                </tr>
                <tr>
                    <th>Total Bookings:</th>
                    <td><?php echo $user['total_bookings'] ?? 0; ?></td>
                </tr>
                <tr>
                    <th>Total Spent:</th>
                    <td>KES <?php echo number_format($user['total_spent'] ?? 0, 2); ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="mt-3">
        <h5>Recent Bookings</h5>
        <?php
        // Get recent bookings for this user
        $stmt = $auth->pdo->prepare("
            SELECT b.reference, b.booking_date, b.return_date, b.total_price, b.status,
                   v.name as vehicle_name
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.vehicle_id
            WHERE b.user_id = :user_id
            ORDER BY b.created_at DESC
            LIMIT 3
        ");
        $stmt->execute(['user_id' => $user_id]);
        $recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($recentBookings)) {
            echo "<div class='list-group'>";
            foreach ($recentBookings as $booking) {
                $badge_class = $booking['status'] === 'active' ? 'bg-primary' : 
                              ($booking['status'] === 'completed' ? 'bg-success' : 'bg-secondary');
                echo "<div class='list-group-item'>
                    <div class='d-flex justify-content-between'>
                        <div>
                            <strong>" . htmlspecialchars($booking['vehicle_name']) . "</strong><br>
                            <small>" . htmlspecialchars($booking['reference']) . "</small>
                        </div>
                        <div class='text-end'>
                            <div>KES " . number_format($booking['total_price'], 2) . "</div>
                            <small class='badge $badge_class'>
                                " . ucfirst($booking['status']) . "
                            </small>
                        </div>
                    </div>
                </div>";
            }
            echo "</div>";
        } else {
            echo "<p class='text-muted'>No recent bookings</p>";
        }
        ?>
    </div>
</div>
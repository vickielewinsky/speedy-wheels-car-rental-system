<?php
// src/modules/customers/index.php - ADD BACK TO DASHBOARD BUTTON
require_once "../../config/database.php";
require_once "../../includes/auth.php";

// Require authentication and admin role
requireAuthentication();
if (!hasRole('admin')) {
    header('Location: ' . base_url('src/modules/auth/login.php'));
    exit();
}

$page_title = "Manage Customers - Speedy Wheels";
require_once "../../includes/header.php";

// Get database connection
try {
    $pdo = getDatabaseConnection();

    // Fetch customers
    $customers_stmt = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC");
    $customers = $customers_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $customers = [];
    error_log("Database error in customers index: " . $e->getMessage());
}
?>

<div class="container-fluid mt-4">
    <!-- Header with Back to Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="fas fa-users me-2 text-info"></i>Manage Customers
            </h1>
            <p class="text-muted mb-0">View and manage customer records</p>
        </div>
        <div class="btn-group">
            <a href="<?php echo base_url('src/modules/auth/dashboard.php'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <a href="add_customer.php" class="btn btn-info">
                <i class="fas fa-user-plus me-1"></i> Add Customer
            </a>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>All Customers
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($customers)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Customer ID</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>ID Number</th>
                                <th>DL Number</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td>#<?php echo $customer['customer_id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($customer['name']); ?></strong>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($customer['phone']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($customer['email'] ?? 'No email'); ?></small>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($customer['id_number']); ?></code>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($customer['dl_number']); ?></code>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($customer['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view_customer.php?id=<?php echo $customer['customer_id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Customers Found</h4>
                    <p class="text-muted">Add your first customer to get started.</p>
                    <a href="add_customer.php" class="btn btn-info">
                        <i class="fas fa-user-plus me-1"></i> Add First Customer
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>
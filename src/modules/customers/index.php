<?php
require_once __DIR__ . '/../../config/config.php';
$page_title = "Customer Management";
include __DIR__ . '/../../includes/header.php';

try {
    $total_customers = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    $customers_with_bookings = (int)$pdo->query("SELECT COUNT(DISTINCT customer_id) FROM bookings")->fetchColumn();

    $stmt = $pdo->query("SELECT c.*, COUNT(b.booking_id) AS total_bookings, MAX(b.created_at) AS last_booking_date FROM customers c LEFT JOIN bookings b ON c.customer_id=b.customer_id GROUP BY c.customer_id ORDER BY c.created_at DESC");
    $customers = $stmt->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3><i class="fas fa-users text-info"></i> Customer Management</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="fas fa-plus"></i> Add</button>
</div>

<?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="row mb-4">
  <div class="col-md-4">
    <div class="card text-white bg-info mb-3"><div class="card-body"><h4 class="mb-0"><?php echo $total_customers; ?></h4><small>Total Customers</small></div></div>
  </div>
  <div class="col-md-4">
    <div class="card text-white bg-success mb-3"><div class="card-body"><h4 class="mb-0"><?php echo $customers_with_bookings; ?></h4><small>Active Customers</small></div></div>
  </div>
  <div class="col-md-4">
    <div class="card text-white bg-primary mb-3"><div class="card-body"><h4 class="mb-0"><?php echo (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(); ?></h4><small>Total Bookings</small></div></div>
  </div>
</div>

<div class="card">
  <div class="card-header"><strong>Customer Directory</strong></div>
  <div class="card-body">
    <?php if (empty($customers)): ?>
      <div class="text-center py-4"><p class="text-muted">No customers found.</p></div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>Name</th><th>Contact</th><th>ID</th><th>DL</th><th>Bookings</th><th>Last Booking</th></tr></thead>
          <tbody>
            <?php foreach ($customers as $c): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($c['email']); ?><br><small class="text-muted"><?php echo htmlspecialchars($c['phone']); ?></small></td>
                <td><?php echo htmlspecialchars($c['id_number']); ?></td>
                <td><?php echo htmlspecialchars($c['dl_number']); ?></td>
                <td><span class="badge bg-primary"><?php echo (int)$c['total_bookings']; ?></span></td>
                <td><?php echo $c['last_booking_date'] ? date('M j, Y',strtotime($c['last_booking_date'])) : '<span class="text-muted">No bookings</span>'; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Customer Modal (frontend only - you can implement backend) -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="addCustomerForm" method="post" action="<?php echo url('src/modules/customers/create.php'); ?>">
      <div class="modal-header"><h5 class="modal-title">Add Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Full name</label><input name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" required></div>
        <div class="row">
          <div class="col"><div class="mb-3"><label class="form-label">ID Number</label><input name="id_number" class="form-control" required></div></div>
          <div class="col"><div class="mb-3"><label class="form-label">DL Number</label><input name="dl_number" class="form-control" required></div></div>
        </div>
        <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Add</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

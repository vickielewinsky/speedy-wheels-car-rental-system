<?php
require_once __DIR__ . '/../../config/config.php';
$page_title = "Booking Management";
include __DIR__ . '/../../includes/header.php';

try {
    $total_bookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $confirmed_bookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status='confirmed'")->fetchColumn();
    $active_bookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status='active'")->fetchColumn();

    $stmt = $pdo->query("SELECT b.*, c.name AS customer_name, v.plate_no, v.model, v.make FROM bookings b LEFT JOIN customers c ON b.customer_id=c.customer_id LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id ORDER BY b.created_at DESC LIMIT 10");
    $recent_bookings = $stmt->fetchAll();
    $total_revenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE status IN ('completed','active')")->fetchColumn();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3><i class="fas fa-calendar-check text-success"></i> Booking Management</h3>
  <a href="<?php echo url('src/modules/bookings/create.php'); ?>" class="btn btn-success"><i class="fas fa-plus"></i> New Booking</a>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="row mb-4">
  <div class="col-md-3">
    <div class="card text-white bg-primary mb-3">
      <div class="card-body">
        <h4 class="mb-0"><?php echo $total_bookings; ?></h4>
        <small>Total Bookings</small>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-white bg-success mb-3">
      <div class="card-body">
        <h4 class="mb-0"><?php echo $confirmed_bookings; ?></h4>
        <small>Confirmed</small>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-white bg-warning mb-3">
      <div class="card-body">
        <h4 class="mb-0"><?php echo $active_bookings; ?></h4>
        <small>Active</small>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-white bg-info mb-3">
      <div class="card-body">
        <h4 class="mb-0">KES <?php echo number_format($total_revenue,0); ?></h4>
        <small>Revenue</small>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><strong>Recent Bookings</strong></div>
  <div class="card-body">
    <?php if (empty($recent_bookings)): ?>
      <div class="text-center py-4"><p class="text-muted">No recent bookings.</p></div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr><th>#</th><th>Customer</th><th>Vehicle</th><th>Period</th><th>Total</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recent_bookings as $b): ?>
            <tr>
              <td><?php echo (int)$b['booking_id']; ?></td>
              <td><?php echo htmlspecialchars($b['customer_name']); ?></td>
              <td><?php echo htmlspecialchars(($b['make'] ?? '') . ' ' . ($b['model'] ?? '')); ?><br><small class="text-muted"><?php echo htmlspecialchars($b['plate_no'] ?? ''); ?></small></td>
              <td><?php echo date('M j',strtotime($b['start_date'])); ?> - <?php echo date('M j, Y',strtotime($b['end_date'])); ?></td>
              <td>KES <?php echo number_format($b['total_amount'],2); ?></td>
              <td><span class="badge bg-<?php echo ($b['status']=='confirmed'?'success':($b['status']=='pending'?'warning':($b['status']=='active'?'primary':($b['status']=='completed'?'info':'danger'))))); ?>"><?php echo htmlspecialchars(ucfirst($b['status'])); ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

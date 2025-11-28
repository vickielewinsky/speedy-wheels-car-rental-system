<?php
// vehicles/index.php
require_once __DIR__ . '/../../config/config.php';
$page_title = "Vehicle Management";
include __DIR__ . '/../../includes/header.php';

// Handle POST: add vehicle
$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plate_no = trim($_POST['plate_no'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $make = trim($_POST['make'] ?? '');
    $daily_rate = trim($_POST['daily_rate'] ?? '');

    // Basic validation
    if ($plate_no === '' || $model === '' || $make === '' || $daily_rate === '') {
        $messages[] = ['type'=>'danger','text'=>'Please fill all required fields.'];
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO vehicles (plate_no, model, make, year, color, daily_rate, status, created_at) VALUES (:plate, :model, :make, NULL, NULL, :rate, 'available', NOW())");
            $stmt->execute([
                ':plate' => $plate_no,
                ':model' => $model,
                ':make' => $make,
                ':rate' => (float)$daily_rate
            ]);
            $messages[] = ['type'=>'success','text'=>'Vehicle added successfully.'];
        } catch (PDOException $e) {
            // unique constraint?
            $messages[] = ['type'=>'danger','text'=>'Error adding vehicle: ' . $e->getMessage()];
        }
    }
}

// Fetch vehicles
$vehicles = $pdo->query("SELECT * FROM vehicles ORDER BY vehicle_id DESC")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Vehicle Management</h3>
</div>

<?php foreach ($messages as $m): ?>
    <div class="alert alert-<?php echo $m['type']; ?>"><?php echo htmlspecialchars($m['text']); ?></div>
<?php endforeach; ?>

<div class="row">
  <div class="col-md-4">
    <div class="card mb-3">
      <div class="card-header bg-primary text-white"><strong>Add New Vehicle</strong></div>
      <div class="card-body">
        <form method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label">Plate Number</label>
            <input class="form-control" name="plate_no" required placeholder="KCA 123A">
          </div>
          <div class="mb-3">
            <label class="form-label">Model</label>
            <input class="form-control" name="model" required placeholder="Toyota RAV4">
          </div>
          <div class="mb-3">
            <label class="form-label">Make</label>
            <input class="form-control" name="make" required placeholder="Toyota">
          </div>
          <div class="mb-3">
            <label class="form-label">Daily Rate (KES)</label>
            <input type="number" step="0.01" class="form-control" name="daily_rate" required placeholder="6000.00">
          </div>
          <button class="btn btn-primary w-100">Add Vehicle</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="card">
      <div class="card-header bg-primary text-white"><strong>Fleet</strong></div>
      <div class="card-body">
        <?php if (empty($vehicles)): ?>
            <p class="text-muted">No vehicles found. Use the form to add one.</p>
        <?php else: ?>
            <div class="table-responsive">
              <table class="table vehicle-management-table">
                <thead>
                  <tr>
                    <th>Plate</th>
                    <th>Model</th>
                    <th>Make</th>
                    <th>Daily Rate</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($vehicles as $v): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($v['plate_no']); ?></td>
                      <td><?php echo htmlspecialchars($v['model']); ?></td>
                      <td><?php echo htmlspecialchars($v['make']); ?></td>
                      <td>KES <?php echo number_format($v['daily_rate'],2); ?></td>
                      <td>
                        <?php
                          $s = $v['status'] ?? 'available';
                          $badgeClass = 'status-badge-' . $s;
                        ?>
                        <span class="<?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($s)); ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
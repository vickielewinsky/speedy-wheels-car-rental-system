<?php
require_once __DIR__ . '/src/config/config.php';
$page_title = "System - Database Test";
include __DIR__ . '/src/includes/header.php';
?>

<div class="card">
  <div class="card-body">
    <h4>Database Connection Test</h4>
    <?php
    try {
        $stmt = $pdo->query("SELECT DATABASE()")->fetchColumn();
        echo "<div class='alert alert-success'>✅ Connected to database: <strong>" . htmlspecialchars($stmt) . "</strong></div>";

        $tables = ['vehicles','customers','bookings','users'];
        echo "<ul class='list-group'>";
        foreach ($tables as $t) {
            $exists = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t))->rowCount() > 0;
            $badge = $exists ? "<span class='badge bg-success'>exists</span>" : "<span class='badge bg-danger'>missing</span>";
            echo "<li class='list-group-item d-flex justify-content-between align-items-center'>" . htmlspecialchars($t) . " " . $badge . "</li>";
        }
        echo "</ul>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>
    <a href="<?php echo url('index.php'); ?>" class="btn btn-primary mt-3"><i class="fas fa-home"></i> Back to Home</a>
  </div>
</div>

<?php include __DIR__ . '/src/includes/footer.php'; ?>

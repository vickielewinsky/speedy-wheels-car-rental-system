<?php
require_once "../../config/database.php";

$database = new Database();
$db = $database->getConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Management - Speedy Wheels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">
                <i class="fas fa-car"></i> Speedy Wheels
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../index.php">Home</a>
                <a class="nav-link" href="index.php">Vehicles</a>
                <a class="nav-link" href="../bookings/index.php">Bookings</a>
                <a class="nav-link" href="../customers/index.php">Customers</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Vehicle Management</h2>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Add New Vehicle</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Plate Number</label>
                                <input type="text" name="plate_no" class="form-control" required placeholder="KCA 123A">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control" required placeholder="Toyota RAV4">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Make</label>
                                <input type="text" name="make" class="form-control" required placeholder="Toyota">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Daily Rate (KES)</label>
                                <input type="number" name="daily_rate" class="form-control" required step="0.01" placeholder="6000.00">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Add Vehicle</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Vehicle Fleet</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $query = "SELECT * FROM vehicles ORDER BY vehicle_id DESC";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            
                            if ($stmt->rowCount() > 0) {
                                echo '<table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Plate No</th>
                                            <th>Model</th>
                                            <th>Make</th>
                                            <th>Daily Rate</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                                
                                while ($vehicle = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<tr>
                                        <td>' . htmlspecialchars($vehicle['plate_no']) . '</td>
                                        <td>' . htmlspecialchars($vehicle['model']) . '</td>
                                        <td>' . htmlspecialchars($vehicle['make']) . '</td>
                                        <td>KES ' . number_format($vehicle['daily_rate'], 2) . '</td>
                                        <td><span class="badge bg-success">Available</span></td>
                                    </tr>';
                                }
                                
                                echo '</tbody></table>';
                            } else {
                                echo '<p class="text-muted">No vehicles found. Import the database schema first.</p>';
                            }
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Error loading vehicles: ' . $e->getMessage() . '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

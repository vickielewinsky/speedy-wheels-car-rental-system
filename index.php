<?php
/**
 * Speedy Wheels Car Rental System - Main Entry Point
 * TUM Diploma Project - DCS/365J/2023
 */

// Include database configuration
require_once 'src/config/database.php';

// Show homepage
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Speedy Wheels Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        .card { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> Speedy Wheels
            </a>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h1 class="display-4 text-primary">
                    <i class="fas fa-car"></i> Speedy Wheels Car Rental
                </h1>
                <p class="lead">Technical University of Mombasa - Final Year Project</p>
                <p class="text-muted">Student: Lewinsky Victoria Wesonga (DCS/365J/2023)</p>
                <a href="test-db.php" class="btn btn-outline-primary">Test Database Connection</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-car-side fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Vehicle Management</h5>
                        <p class="card-text">Manage your fleet of rental vehicles</p>
                        <a href="src/modules/vehicles/index.php" class="btn btn-primary">Manage Vehicles</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Booking System</h5>
                        <p class="card-text">Handle customer reservations and bookings</p>
                        <a href="src/modules/bookings/index.php" class="btn btn-success">Manage Bookings</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Customer Portal</h5>
                        <p class="card-text">Manage customer information and records</p>
                        <a href="src/modules/customers/index.php" class="btn btn-info">Manage Customers</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-chart-bar fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">System Info</h5>
                        <p class="card-text">Database connection and system status</p>
                        <a href="test-db.php" class="btn btn-warning">System Test</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white mt-5 py-3">
        <div class="container text-center">
            <p>Speedy Wheels Car Rental System &copy; 2024 | Technical University of Mombasa - DCS/365J/2023</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

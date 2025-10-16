<?php
if (!isset($page_title)) $page_title = APP_NAME;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo htmlspecialchars($page_title); ?></title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body { background:#f7f8fb; }
    .brand-gradient {
      background: linear-gradient(135deg,#667eea,#764ba2);
      color:#fff;
    }
    .hero {
      border-radius: 12px;
      padding: 48px 24px;
      color: #fff;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark brand-gradient">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?php echo url('index.php'); ?>">
      <i class="fas fa-car me-2"></i><strong>Speedy Wheels</strong>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo url('index.php'); ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo url('src/modules/vehicles/'); ?>">Vehicles</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo url('src/modules/bookings/'); ?>">Bookings</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo url('src/modules/customers/'); ?>">Customers</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo url('test-db.php'); ?>">System Test</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="container mt-4">

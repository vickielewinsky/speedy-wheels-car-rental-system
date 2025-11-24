<?php
// src/modules/auth/login.php

// Start output buffering at the VERY BEGINNING
ob_start();

// Start session at the VERY BEGINNING
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "Auth.php";
require_once "../../includes/auth.php";

$page_title = "Login - Speedy Wheels";
require_once "../../includes/header.php";

// Redirect if already logged in
if (isAuthenticated()) {
    header("Location: " . base_url('index.php'));
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $auth = new Auth();
    $result = $auth->login($username, $password);
    
    if ($result['success']) {
        $_SESSION['success_message'] = 'Login successful!';
        header("Location: " . base_url('index.php'));
        exit();
    } else {
        $error = $result['error'];
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-sign-in-alt"></i> Login to Speedy Wheels</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                   required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                    
                    <!-- Demo Accounts -->
                    <div class="mt-4 alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Demo Accounts</h6>
                        <p class="mb-0 small">
                            <strong>Admin:</strong> admin / admin123<br>
                            <strong>Customer:</strong> john_doe / customer123
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "../../includes/footer.php"; ?>

<?php
// End output buffering and send all output
ob_end_flush();
?>
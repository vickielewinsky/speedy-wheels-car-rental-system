<?php
// src/modules/auth/logout.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy all session data to log out user
$_SESSION = [];
session_destroy();

// Redirect to main index.php in the root directory
header("Location: /speedy-wheels-car-rental-system/index.php");
exit();
?>

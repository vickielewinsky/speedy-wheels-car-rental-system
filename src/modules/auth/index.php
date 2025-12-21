<?php
require_once __DIR__ . '/../../helpers/url_helper.php';

// If user is logged in → dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Otherwise → login
header("Location: login.php");
exit;

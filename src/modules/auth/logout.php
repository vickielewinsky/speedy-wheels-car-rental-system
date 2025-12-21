<?php
// src/modules/auth/logout.php
require_once __DIR__ . "/../../helpers/url_helper.php";
session_start();
session_destroy();
header("Location: " . base_url("index.php"));
exit();
?>

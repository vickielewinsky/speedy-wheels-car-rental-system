<?php
require_once 'src/config/config.php';
require_once 'src/helpers/url_helper.php';

$url = base_url('src/modules/bookings/index.php');
echo "Bookings URL: <a href='$url' target='_blank'>$url</a><br>";

// Also test without clicking
echo "<br>Try typing this directly in browser:<br>";
echo "http://localhost/speedy-wheels-car-rental-system/src/modules/bookings/index.php";
?>

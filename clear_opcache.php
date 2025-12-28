<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPCache cleared!<br>";
}
session_start();
session_regenerate_id(true);
echo "Session regenerated.<br>";
echo "Now test your bookings page:<br>";
echo '<a href="src/modules/bookings/index.php">Bookings Page</a>';
?>

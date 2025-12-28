<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared!<br>";
} else {
    echo "OPcache not enabled.<br>";
}

// Also clear session if needed
session_start();
session_regenerate_id(true);
echo "Session regenerated.";
?>

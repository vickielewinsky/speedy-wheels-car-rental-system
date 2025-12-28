<?php
$content = file_get_contents('src/helpers/url_helper.php');
echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "</pre>";
?>

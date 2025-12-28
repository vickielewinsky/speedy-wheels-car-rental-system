<?php
// Read the file
$content = file_get_contents('src/modules/bookings/index.php');

// Fix the match expression
$pattern = "/\\\$status_badge = match\\(\\\$booking\\['status'\\]\\) \\{(\s*)'confirmed', 'active' => 'bg-success',/";
$replacement = '\$status_badge = match(\$booking[\'status\']) {$1\'confirmed\'|\'active\' => \'bg-success\',';

$content = preg_replace($pattern, $replacement, $content);

// Write back
file_put_contents('src/modules/bookings/index.php', $content);
echo "Fixed match expression\n";
?>

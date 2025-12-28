<?php
$file = 'src/modules/bookings/index.php';
$content = file_get_contents($file);

// Replace the match() block with switch-case
$new_code = <<<'NEW'
<?php
                            switch($booking['status']) {
                                case 'confirmed':
                                case 'active':
                                    $status_badge = 'bg-success';
                                    break;
                                case 'pending':
                                    $status_badge = 'bg-warning';
                                    break;
                                case 'cancelled':
                                    $status_badge = 'bg-danger';
                                    break;
                                case 'completed':
                                    $status_badge = 'bg-info';
                                    break;
                                default:
                                    $status_badge = 'bg-secondary';
                            }
NEW;

// Replace the match() block
$content = str_replace(
    "<?php\n                            \$status_badge = match(\$booking['status']) {\n                                'confirmed'|'active' => 'bg-success',\n                                'pending' => 'bg-warning',\n                                'cancelled' => 'bg-danger',\n                                'completed' => 'bg-info',\n                                default => 'bg-secondary'\n                            };",
    $new_code,
    $content
);

file_put_contents($file, $content);
echo "Replaced match() with switch()\n";
?>

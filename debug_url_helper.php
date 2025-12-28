<?php
// Copy of url_helper.php with debug
function debug_base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https://'
        : 'http://';

    $host = $_SERVER['HTTP_HOST'];
    $project_path = '/speedy-wheels-car-rental-system/';

    echo "Debug info:<br>";
    echo "Protocol: $protocol<br>";
    echo "Host: $host<br>";
    echo "Project path: $project_path<br>";
    echo "Path requested: $path<br>";
    
    $result = $protocol . $host . $project_path . '/' . ltrim($path, '/');
    echo "Result: $result<br>";
    
    return $result;
}

echo "Debug test:<br>";
echo debug_base_url('src/modules/auth/logout.php');
?>

<?php
// config/paths.php

function base_url($path = '') {
    // Detect if we're using PHP built-in server
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the script directory
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    
    // For PHP built-in server, we need to adjust
    if ($script_dir === '/') {
        $base = $protocol . $host;
    } else {
        $base = $protocol . $host . $script_dir;
    }
    
    // Remove trailing slash if present
    $base = rtrim($base, '/');
    
    // Add path if provided
    if ($path) {
        $path = ltrim($path, '/');
        return $base . '/' . $path;
    }
    
    return $base;
}

function asset_url($path = '') {
    return base_url($path);
}

// Use this in your HTML:
// <link href="<?php echo asset_url('css/style.css'); ?>" rel="stylesheet">
// <img src="<?php echo asset_url('images/hero-car.png'); ?>">
?>
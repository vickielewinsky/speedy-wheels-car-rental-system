<?php


/**
 * Generate absolute URL
 */
function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
        ? 'https://' 
        : 'http://';

    $host = $_SERVER['HTTP_HOST'];
    $project_path = '/speedy-wheels-car-rental-system/';

    // Remove trailing slash from project path if present
    $project_path = rtrim($project_path, '/');

    return $protocol . $host . $project_path . '/' . ltrim($path, '/');
}

/**
 * Redirect with message
 */
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        if ($type === 'success') {
            $_SESSION['success_message'] = $message;
        } else {
            $_SESSION['error_message'] = $message;
        }
    }

    header("Location: " . base_url($url));
    exit;
}

/**
 * Get current URL
 */
function current_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
        ? 'https://' 
        : 'http://';

    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

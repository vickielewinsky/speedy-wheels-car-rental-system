<?php
// src/core/BaseController.php

class BaseController
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Send JSON response and terminate execution
     */
    protected function jsonResponse(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Safe redirect helper
     *
     * Accepts:
     *  - Absolute URLs (https://...)
     *  - Project-root paths (index.php, src/modules/...)
     *  - Module-relative paths (login.php, dashboard.php)
     *
     * Prevents duplicate paths like:
     *  src/modules/auth/src/modules/auth/login.php
     */
    protected function redirect(string $url): void
    {
        // Absolute URL → redirect as-is
        if (preg_match('#^https?://#i', $url)) {
            header("Location: $url");
            exit;
        }

        // Normalize slashes
        $url = ltrim($url, '/');

        // Project base path
        $projectBase = '/speedy-wheels-car-rental-system/';

        // Prevent double-prefixing project base
        if (!str_starts_with($url, 'speedy-wheels-car-rental-system/')) {
            $url = $projectBase . $url;
        }

        header("Location: $url");
        exit;
    }
}

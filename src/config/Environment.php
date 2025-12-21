<?php
class Environment {
    const DEVELOPMENT = 'development';
    const PRODUCTION = 'production';
    
    public static function get() {
        return $_SERVER['APP_ENV'] ?? self::DEVELOPMENT;
    }
    
    public static function isDevelopment() {
        return self::get() === self::DEVELOPMENT;
    }

    // Add this method to fix the error
    public static function isDebug() {
        // Usually debug is true in development, false in production
        return self::isDevelopment();
    }
}

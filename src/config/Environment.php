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
}
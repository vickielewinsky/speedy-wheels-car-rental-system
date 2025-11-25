<?php
// src/config/email_config.php

class EmailConfig {
    // SMTP Configuration - UPDATED WITH YOUR CREDENTIALS
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_USERNAME = 'your-email@gmail.com';
    const SMTP_PASSWORD = 'your-16-character-app-password';  // Your app password (spaces removed)
    const SMTP_SECURE = 'tls';
    
    // System Email Details
    const FROM_EMAIL = 'bookings@speedywheels.com';
    const FROM_NAME = 'Speedy Wheels Car Rental';
    
    // Email Templates
    const TEMPLATES_DIR = __DIR__ . '/../templates/emails/';
    
    public static function getSMTPConfig() {
        return [
            'host' => self::SMTP_HOST,
            'port' => self::SMTP_PORT,
            'username' => self::SMTP_USERNAME,
            'password' => self::SMTP_PASSWORD,
            'secure' => self::SMTP_SECURE
        ];
    }
    
    public static function isConfigured() {
        $config = self::getSMTPConfig();
        return !empty($config['username']) && !empty($config['password']);
    }
}
?>

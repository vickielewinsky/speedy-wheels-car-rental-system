<?php
// Speedy Wheels Car Rental System
// Developer: Lewinsky Victoria Wesonga
// Student ID: DCS/365J/2023
// Technical University of Mombasa
// Supervisor: Mr. Mbugua

class EmailConfig {
    // SMTP Configuration - GMAIL SETTINGS
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_USERNAME = 'vickielewinsky@gmail.com';
    const SMTP_PASSWORD = 'pvhwubqspjwcggoz';  // App password
    const SMTP_SECURE = 'tls';

    const FROM_EMAIL = 'vickielewinsky@gmail.com';
    const FROM_NAME = 'Speedy Wheels Car Rental';

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
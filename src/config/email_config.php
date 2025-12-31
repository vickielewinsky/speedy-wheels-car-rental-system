<?php require_once __DIR__ . "/../helpers/url_helper.php"; ?>
<?php require_once __DIR__ . "/../helpers/url_helper.php"; ?>
<?php

// Speedy Wheels Car Rental System
// Developer: Lewinsky Victoria Wesonga
// Student ID: DCS/365J/2023
// Technical University of Mombasa
// Supervisor: Mr. Mbugua


class EmailConfig {
    // SMTP Configuration - UPDATED WITH YOUR CREDENTIALS
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_USERNAME = 'lewinskyvictoria45@gmail.com';
    const SMTP_PASSWORD = 'ibqxoqregcyvrpkj';  // Your app password (spaces removed)
    const SMTP_SECURE = 'tls';

    const FROM_EMAIL = 'lewinskyvictoria45@gmail.com';
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

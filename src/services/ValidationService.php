// src/services/ValidationService.php
class ValidationService {
    public static function sanitizeInput($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validatePhone($phone) {
        return preg_match('/^[0-9]{10,15}$/', $phone);
    }
}
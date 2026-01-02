<?php
// Test email sending directly
echo "Testing email sending...\n\n";

// Include required files
require_once 'vendor/autoload.php';
require_once 'src/config/email_config.php';

// Test if EmailConfig is loaded
if (!class_exists('EmailConfig')) {
    die("âŒ EmailConfig class not found\n");
}

echo "âœ… EmailConfig loaded\n";
echo "Username: " . EmailConfig::SMTP_USERNAME . "\n";
echo "Host: " . EmailConfig::SMTP_HOST . "\n\n";

// Try to send a test email
try {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = EmailConfig::SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = EmailConfig::SMTP_USERNAME;
    $mail->Password   = EmailConfig::SMTP_PASSWORD;
    $mail->SMTPSecure = EmailConfig::SMTP_SECURE;
    $mail->Port       = EmailConfig::SMTP_PORT;
    
    // Enable debugging
    $mail->SMTPDebug = 2; // Show detailed debugging
    
    // Recipients
    $mail->setFrom(EmailConfig::FROM_EMAIL, EmailConfig::FROM_NAME);
    $mail->addAddress('lewinskyvictoria45@gmail.com', 'Test User');
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Speedy Wheels';
    $mail->Body    = '<h1>Test Email</h1><p>This is a test email from Speedy Wheels.</p>';
    $mail->AltBody = 'Test Email - This is a test email from Speedy Wheels.';
    
    echo "Attempting to send email...\n";
    
    if ($mail->send()) {
        echo "\nâœ… Test email sent successfully!\n";
    } else {
        echo "\nâŒ Failed to send test email\n";
        echo "Error: " . $mail->ErrorInfo . "\n";
    }
    
} catch (Exception $e) {
    echo "\ní²¥ Exception: " . $e->getMessage() . "\n";
}
?>

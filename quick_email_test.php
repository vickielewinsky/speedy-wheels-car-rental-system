<?php
// quick_email_test.php
require 'vendor/autoload.php';
require 'src/config/email_config.php';
require 'src/services/EmailService.php';

echo "=== Direct Email Test ===\n";
echo "Username: " . EmailConfig::SMTP_USERNAME . "\n";
echo "Password set: " . (empty(EmailConfig::SMTP_PASSWORD) ? 'NO' : 'YES') . "\n";

// Instantiate without namespace
$emailService = new EmailService();

// Create test booking data for sendBookingConfirmation method
$testBookingData = [
    'booking_reference' => 'TEST123',
    'vehicle_model' => 'Toyota Corolla',
    'pickup_date' => date('Y-m-d'),
    'return_date' => date('Y-m-d', strtotime('+3 days')),
    'pickup_location' => 'Nairobi CBD',
    'return_location' => 'Nairobi CBD',
    'total_amount' => 15000,
    'booking_date' => date('Y-m-d H:i:s')
];

$customerEmail = 'lewinskyvictoria45@gmail.com';
$customerName = 'Test Customer';

echo "Sending test booking confirmation to: $customerEmail\n";

$result = $emailService->sendBookingConfirmation($testBookingData, $customerEmail, $customerName);

if ($result) {
    echo "✅ Booking confirmation email sent successfully!\n";
    echo "Please check your email inbox (and spam folder)\n";
} else {
    echo "❌ Failed to send booking confirmation email.\n";
    echo "This could be due to:\n";
    echo "1. Incorrect Gmail app password\n";
    echo "2. Gmail blocking 'less secure apps'\n";
    echo "3. SMTP connection issues\n";
    echo "4. Network/firewall restrictions\n";
    
    // Let's check if we can get more error info
    echo "\n=== Checking SMTP Connection ===\n";
    
    // Simple SMTP test
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = EmailConfig::SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EmailConfig::SMTP_USERNAME;
        $mail->Password = EmailConfig::SMTP_PASSWORD;
        $mail->SMTPSecure = EmailConfig::SMTP_SECURE;
        $mail->Port = EmailConfig::SMTP_PORT;
        
        $mail->setFrom(EmailConfig::FROM_EMAIL, EmailConfig::FROM_NAME);
        $mail->addAddress($customerEmail);
        $mail->Subject = 'SMTP Connection Test';
        $mail->Body = 'This is a test email body.';
        
        if ($mail->smtpConnect()) {
            echo "✓ SMTP connection successful\n";
            $mail->smtpClose();
        }
    } catch (Exception $e) {
        echo "✗ SMTP connection failed: " . $e->getMessage() . "\n";
    }
}
?>
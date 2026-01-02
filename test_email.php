<?php
// test_email.php - Place in your root directory
session_start();
require_once 'vendor/autoload.php';

echo "<h2>Speedy Wheels Email Diagnostic</h2>";

// Check if email_config.php exists
$config_path = __DIR__ . '/src/config/email_config.php';
if (file_exists($config_path)) {
    echo "✅ Email config found at: " . $config_path . "<br>";
    
    // Load and display config
    require_once $config_path;
    
    echo "<h3>Email Configuration:</h3>";
    echo "SMTP Host: " . EmailConfig::SMTP_HOST . "<br>";
    echo "SMTP Port: " . EmailConfig::SMTP_PORT . "<br>";
    echo "SMTP Username: " . EmailConfig::SMTP_USERNAME . "<br>";
    echo "SMTP Password: " . (EmailConfig::SMTP_PASSWORD ? "***SET***" : "NOT SET") . "<br>";
    echo "From Email: " . EmailConfig::FROM_EMAIL . "<br>";
    
    // Test PHPMailer
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✅ PHPMailer loaded successfully<br>";
    } else {
        echo "❌ PHPMailer NOT loaded<br>";
    }
    
} else {
    echo "❌ Email config NOT found at: " . $config_path . "<br>";
}

// Test email sending function
if (isset($_GET['test'])) {
    echo "<hr><h3>Sending Test Email...</h3>";
    
    // Simulate your registration email function
    function testSendEmail($toEmail = "vickielewinsky@gmail.com", $name = "Test User", $username = "testuser") {
        try {
            $root_dir = __DIR__;
            require_once $root_dir . '/src/config/email_config.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Enable verbose debug output
            $mail->SMTPDebug = 3;
            $mail->Debugoutput = function($str, $level) {
                echo "Debug [$level]: $str<br>";
            };
            
            // Server settings
            $mail->isSMTP();
            $mail->Host       = EmailConfig::SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = EmailConfig::SMTP_USERNAME;
            $mail->Password   = EmailConfig::SMTP_PASSWORD;
            $mail->SMTPSecure = EmailConfig::SMTP_SECURE;
            $mail->Port       = EmailConfig::SMTP_PORT;
            
            // Sender
            $mail->setFrom(EmailConfig::FROM_EMAIL, EmailConfig::FROM_NAME);
            
            // Recipient
            $mail->addAddress($toEmail, $name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Test Email from Speedy Wheels';
            $mail->Body    = '<h1>Test Successful!</h1><p>This is a test email from Speedy Wheels.</p>';
            $mail->AltBody = 'Test Successful! This is a test email from Speedy Wheels.';
            
            if ($mail->send()) {
                echo "<div style='color: green;'>✅ Test email sent successfully!</div>";
                return true;
            } else {
                echo "<div style='color: red;'>❌ Failed to send test email: " . $mail->ErrorInfo . "</div>";
                return false;
            }
            
        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Exception: " . $e->getMessage() . "</div>";
            return false;
        }
    }
    
    testSendEmail();
}

echo "<hr>";
echo "<p><a href='?test=1'>Click here to send a test email</a></p>";
echo "<p><a href='src/modules/auth/register.php'>Go to Registration Page</a></p>";
?>
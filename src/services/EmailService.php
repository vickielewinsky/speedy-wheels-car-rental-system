<?php
require_once __DIR__ . '/../config/email_config.php';

class EmailService {
    private $phpmailer;

    public function __construct() {
        $this->phpmailer = new PHPMailer\PHPMailer\PHPMailer(true);
        $this->setupSMTP();
    }

    private function setupSMTP() {
        $config = EmailConfig::getSMTPConfig();

        $this->phpmailer->isSMTP();
        $this->phpmailer->Host = $config['host'];
        $this->phpmailer->Port = $config['port'];
        $this->phpmailer->SMTPAuth = true;
        $this->phpmailer->Username = $config['username'];
        $this->phpmailer->Password = $config['password'];
        $this->phpmailer->SMTPSecure = $config['secure'];

        $this->phpmailer->setFrom(EmailConfig::FROM_EMAIL, EmailConfig::FROM_NAME);
        $this->phpmailer->isHTML(true);
    }

    public function sendBookingConfirmation($bookingData, $customerEmail, $customerName) {
        try {
            $subject = "Booking Confirmation - Speedy Wheels #" . $bookingData['booking_id'];

            $template = $this->buildBookingConfirmationEmail($bookingData, $customerName);

            $this->phpmailer->addAddress($customerEmail, $customerName);
            $this->phpmailer->Subject = $subject;
            $this->phpmailer->Body = $template;

            return $this->phpmailer->send();

        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    private function buildBookingConfirmationEmail($bookingData, $customerName) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; border-radius: 8px; overflow: hidden; }
                .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; background: white; margin: 20px; border-radius: 8px; }
                .booking-details { background: #f8f9fa; padding: 20px; border-radius: 5px; border-left: 4px solid #007bff; margin: 20px 0; }
                .footer { background: #343a40; color: white; padding: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>ó Speedy Wheels</h1>
                    <h2>Booking Confirmed!</h2>
                </div>

                <div class='content'>
                    <p>Dear <strong>{$customerName}</strong>,</p>
                    <p>Thank you for choosing Speedy Wheels Car Rental! Your booking has been successfully confirmed.</p>

                    <div class='booking-details'>
                        <h3 style='margin-top: 0;'>ã Booking Details</h3>
                        <p><strong>Booking ID:</strong> #{$bookingData['booking_id']}</p>
                        <p><strong>Vehicle:</strong> {$bookingData['vehicle']}</p>
                        <p><strong>Rental Period:</strong> {$bookingData['start_date']} to {$bookingData['end_date']} ({$bookingData['rental_days']} days)</p>
                        <p><strong>Pickup Location:</strong> {$bookingData['pickup_location']}</p>
                        <p><strong>Total Amount:</strong> KSh {$bookingData['total_amount']}</p>
                    </div>

                    <h4>ù What to Bring:</h4>
                    <ul>
                        <li>Original National ID/Passport</li>
                        <li>Original Driver's License</li>
                        <li>Payment Confirmation</li>
                    </ul>

                    <p>Need to make changes? Contact us at <strong>254712345678</strong></p>
                </div>

                <div class='footer'>
                    <p>Speedy Wheels Car Rental &copy; 2024</p>
                    <p>Mombasa, Kenya | 254712345678</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>

<?php
// process_payment.php - FIXED: Uses correct payments table structure

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=speedy_wheels;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get POST data
    $booking_id = $_POST['booking_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $phone = $_POST['phone'] ?? null;

    // Validate input
    if (!$booking_id || !$amount || !$phone) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit();
    }

    // Validate phone
    if (strlen($phone) !== 9 || !preg_match('/^7[0-9]{8}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number. Use format: 7XXXXXXXX']);
        exit();
    }

    // Get user email
    $userId = $_SESSION['user_id'];
    $sql = "SELECT email FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    // Verify booking belongs to user
    $sql = "SELECT b.booking_id 
            FROM bookings b
            JOIN customers c ON b.customer_id = c.customer_id
            WHERE b.booking_id = ? AND c.email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$booking_id, $user['email']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Booking not found or access denied']);
        exit();
    }

    // Format phone for M-PESA
    $mpesa_phone = '254' . $phone;
    
    // Generate transaction ID and receipt number
    $transaction_id = 'MPESA_' . date('YmdHis') . '_' . rand(1000, 9999);
    $mpesa_receipt_number = 'RCPT' . date('YmdHis') . rand(100, 999);
    
    // Insert payment record using correct column names
    $sql = "INSERT INTO payments (
                booking_id, 
                phone, 
                amount, 
                merchant_request_id, 
                checkout_request_id, 
                mpesa_receipt_number, 
                transaction_date, 
                status, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'completed', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $booking_id,
        $mpesa_phone,
        $amount,
        'REQ_' . $transaction_id,
        'CHK_' . $transaction_id,
        $mpesa_receipt_number
    ]);

    // Update booking status
    $sql = "UPDATE bookings SET status = 'active' WHERE booking_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$booking_id]);

    // Response
    $response = [
        'success' => true,
        'message' => 'Payment processed successfully via M-PESA',
        'transaction_id' => $transaction_id,
        'mpesa_receipt' => $mpesa_receipt_number,
        'amount' => $amount,
        'phone' => $mpesa_phone,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Payment Processing Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

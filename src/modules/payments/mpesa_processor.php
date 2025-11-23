<?php
// src/modules/payments/mpesa_processor.php - FIXED VERSION

class MpesaProcessor {
    private $pdo;
    
    public function __construct() {
        // Use your existing database connection instead of Database class
        try {
            $this->pdo = new PDO(
                "mysql:host=localhost;dbname=speedy_wheels;charset=utf8mb4",
                "root",
                "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function initiateSTKPush($phone, $amount, $bookingId) {
        try {
            // Simulate MPESA STK push (replace with actual MPESA API)
            $transactionCode = "MPE" . date('YmdHis') . rand(100, 999);
            
            // Save transaction to database
            $stmt = $this->pdo->prepare("
                INSERT INTO mpesa_transactions 
                (transaction_code, phone, amount, booking_id, status, created_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$transactionCode, $phone, $amount, $bookingId]);
            
            return [
                'success' => true,
                'transaction_code' => $transactionCode,
                'message' => 'Payment initiated successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Payment failed: ' . $e->getMessage()
            ];
        }
    }

    public function confirmTransaction($transactionCode) {
        try {
            // Simulate transaction confirmation
            $stmt = $this->pdo->prepare("
                UPDATE mpesa_transactions 
                SET status = 'completed', updated_at = NOW() 
                WHERE transaction_code = ?
            ");
            $stmt->execute([$transactionCode]);
            
            return [
                'success' => true,
                'message' => 'Payment confirmed successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Confirmation failed: ' . $e->getMessage()
            ];
        }
    }

    public function getTransactionStatus($transactionCode) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM mpesa_transactions 
                WHERE transaction_code = ?
            ");
            $stmt->execute([$transactionCode]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $transaction ?: ['status' => 'not_found'];
            
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

// Simple function to get MPESA processor instance
function getMpesaProcessor() {
    return new MpesaProcessor();
}
?>
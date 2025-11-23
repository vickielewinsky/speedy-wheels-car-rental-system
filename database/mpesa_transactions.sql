-- Create mpesa_transactions table
CREATE TABLE IF NOT EXISTS mpesa_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_code VARCHAR(50) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    booking_id INT,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    INDEX (transaction_code),
    INDEX (phone),
    INDEX (booking_id)
);
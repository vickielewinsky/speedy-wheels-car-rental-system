-- =============================================
-- Speedy Wheels Car Rental System Database Schema
-- Technical University of Mombasa - DCS/365J/2023
-- =============================================

CREATE DATABASE IF NOT EXISTS speedy_wheels;
USE speedy_wheels;

-- Customers table
CREATE TABLE customers (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15) NOT NULL,
    id_number VARCHAR(20) UNIQUE NOT NULL,
    dl_number VARCHAR(20) UNIQUE NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_dl_number (dl_number)
);

-- Vehicles table
CREATE TABLE vehicles (
    vehicle_id INT PRIMARY KEY AUTO_INCREMENT,
    plate_no VARCHAR(15) UNIQUE NOT NULL,
    model VARCHAR(50) NOT NULL,
    make VARCHAR(50) NOT NULL,
    year YEAR NOT NULL,
    color VARCHAR(20),
    daily_rate DECIMAL(10,2) NOT NULL,
    status ENUM('available', 'booked', 'maintenance') DEFAULT 'available',
    current_mileage INT DEFAULT 0,
    last_service DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_plate (plate_no)
);

-- Bookings table
CREATE TABLE bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    vehicle_id INT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_amount DECIMAL(10,2),
    status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE,
    INDEX idx_dates (start_date, end_date),
    INDEX idx_status (status)
);

-- NEW: Enhanced Users table for authentication system
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    user_role ENUM('customer', 'admin') DEFAULT 'customer',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (user_role)
);

-- NEW: Link customers to users for authentication
ALTER TABLE customers 
ADD COLUMN user_id INT NULL AFTER customer_id,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
ADD INDEX idx_user_id (user_id);

-- Payments table for MPESA transactions
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    phone VARCHAR(15) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    merchant_request_id VARCHAR(50),
    checkout_request_id VARCHAR(50),
    mpesa_receipt_number VARCHAR(50),
    transaction_date TIMESTAMP NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_merchant (merchant_request_id),
    INDEX idx_checkout (checkout_request_id)
);

-- NEW: MPESA transactions table (for the payment processor we built)
CREATE TABLE mpesa_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_code VARCHAR(50) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    booking_id INT,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_transaction_code (transaction_code),
    INDEX idx_phone (phone),
    INDEX idx_status (status),
    INDEX idx_booking (booking_id)
);

-- =============================================
-- SAMPLE DATA
-- =============================================

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name, phone, user_role) VALUES 
(
    'admin', 
    'admin@speedywheels.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'System', 
    'Administrator', 
    '254700000000', 
    'admin'
);

-- Insert sample customer users
INSERT INTO users (username, email, password_hash, first_name, last_name, phone, user_role) VALUES 
(
    'john_doe', 
    'john@email.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'John', 
    'Doe', 
    '254712345678', 
    'customer'
),
(
    'mary_achieng', 
    'mary@email.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Mary', 
    'Achieng', 
    '254723456789', 
    'customer'
);

-- Update customers table to link with users
INSERT INTO customers (user_id, name, email, phone, id_number, dl_number, address) VALUES 
(
    (SELECT id FROM users WHERE username = 'john_doe'),
    'John Kamau', 
    'john@email.com', 
    '254712345678', 
    '12345678', 
    'DL123456', 
    'Mombasa Town'
),
(
    (SELECT id FROM users WHERE username = 'mary_achieng'),
    'Mary Achieng', 
    'mary@email.com', 
    '254723456789', 
    '23456789', 
    'DL234567', 
    'Nyali'
);

-- Sample vehicles
INSERT INTO vehicles (plate_no, model, make, year, color, daily_rate, status) VALUES 
('KCA 123A', 'Toyota RAV4', 'Toyota', 2022, 'White', 6000.00, 'available'),
('KBM 456B', 'Honda CR-V', 'Honda', 2021, 'Black', 5500.00, 'available'),
('KDA 789C', 'Nissan X-Trail', 'Nissan', 2023, 'Silver', 6500.00, 'available'),
('KCB 234D', 'Mazda CX-5', 'Mazda', 2022, 'Red', 5800.00, 'available'),
('KNA 567E', 'Subaru Forester', 'Subaru', 2023, 'Blue', 6200.00, 'maintenance');

-- Sample bookings
INSERT INTO bookings (customer_id, vehicle_id, start_date, end_date, total_amount, status) VALUES 
(1, 1, '2024-01-15', '2024-01-18', 18000.00, 'completed'),
(2, 2, '2024-01-20', '2024-01-22', 11000.00, 'confirmed'),
(1, 3, '2024-02-01', '2024-02-05', 26000.00, 'pending');

-- Sample MPESA transactions
INSERT INTO mpesa_transactions (transaction_code, phone, amount, booking_id, status) VALUES 
('MPE20240115123045987', '254712345678', 18000.00, 1, 'completed'),
('MPE20240120144512345', '254723456789', 11000.00, 2, 'pending');

-- =============================================
-- STORED PROCEDURES (Optional - for advanced features)
-- =============================================

DELIMITER //

-- Procedure to calculate booking total
CREATE PROCEDURE CalculateBookingTotal(
    IN p_vehicle_id INT,
    IN p_start_date DATE,
    IN p_end_date DATE,
    OUT p_total_amount DECIMAL(10,2)
)
BEGIN
    DECLARE daily_rate DECIMAL(10,2);
    DECLARE days_count INT;
    
    -- Get vehicle daily rate
    SELECT daily_rate INTO daily_rate FROM vehicles WHERE vehicle_id = p_vehicle_id;
    
    -- Calculate number of days
    SET days_count = DATEDIFF(p_end_date, p_start_date) + 1;
    
    -- Calculate total amount
    SET p_total_amount = daily_rate * days_count;
END//

DELIMITER ;

-- =============================================
-- VIEWS (Optional - for reporting)
-- =============================================

-- View for active bookings with customer and vehicle info
CREATE VIEW active_bookings AS
SELECT 
    b.booking_id,
    c.name as customer_name,
    c.phone as customer_phone,
    v.plate_no,
    v.model,
    v.make,
    b.start_date,
    b.end_date,
    b.total_amount,
    b.status
FROM bookings b
JOIN customers c ON b.customer_id = c.customer_id
JOIN vehicles v ON b.vehicle_id = v.vehicle_id
WHERE b.status IN ('confirmed', 'active');

-- View for payment reports
CREATE VIEW payment_reports AS
SELECT 
    p.payment_id,
    b.booking_id,
    c.name as customer_name,
    v.model as vehicle_model,
    p.amount,
    p.status as payment_status,
    p.created_at as payment_date
FROM payments p
LEFT JOIN bookings b ON p.booking_id = b.booking_id
LEFT JOIN customers c ON b.customer_id = c.customer_id
LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id;
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

-- Users table for admin/staff
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff');

-- Sample data for testing
INSERT INTO customers (name, email, phone, id_number, dl_number, address) VALUES 
('John Kamau', 'john@email.com', '254712345678', '12345678', 'DL123456', 'Mombasa Town'),
('Mary Achieng', 'mary@email.com', '254723456789', '23456789', 'DL234567', 'Nyali');

INSERT INTO vehicles (plate_no, model, make, year, color, daily_rate, status) VALUES 
('KCA 123A', 'Toyota RAV4', 'Toyota', 2022, 'White', 6000.00, 'available'),
('KBM 456B', 'Honda CR-V', 'Honda', 2021, 'Black', 5500.00, 'available'),
('KDA 789C', 'Nissan X-Trail', 'Nissan', 2023, 'Silver', 6500.00, 'available');
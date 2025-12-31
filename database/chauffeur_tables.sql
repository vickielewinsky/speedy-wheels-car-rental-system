-- Chauffeur Service Tables for Speedy Wheels Car Rental System
-- Run this SQL in your database before using the chauffeur feature

-- Table for chauffeur bookings
CREATE TABLE IF NOT EXISTS chauffeur_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    duration_hours INT NOT NULL,
    vehicle_preference VARCHAR(50),
    special_requests TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    total_cost DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Table for chauffeur drivers
CREATE TABLE IF NOT EXISTS chauffeur_drivers (
    driver_id INT PRIMARY KEY AUTO_INCREMENT,
    driver_name VARCHAR(100) NOT NULL,
    license_number VARCHAR(50) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    experience_years INT DEFAULT 0,
    languages TEXT,
    vehicle_types TEXT,
    rating DECIMAL(3,2) DEFAULT 5.0,
    status ENUM('available', 'busy', 'on_leave') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample drivers
INSERT INTO chauffeur_drivers (driver_name, license_number, phone, email, experience_years, languages, vehicle_types, rating) VALUES
('John Mwangi', 'DL00123456', '+254712345678', 'john.mwangi@speedywheels.com', 8, 'English, Swahili, French', 'SUV, Sedan, Luxury', 4.8),
('Sarah Akinyi', 'DL00123457', '+254723456789', 'sarah.akinyi@speedywheels.com', 5, 'English, Swahili', 'Sedan, Compact', 4.9),
('David Omondi', 'DL00123458', '+254734567890', 'david.omondi@speedywheels.com', 12, 'English, Swahili, German', 'SUV, Luxury, Van', 4.7),
('Grace Wambui', 'DL00123459', '+254745678901', 'grace.wambui@speedy-wheels.com', 6, 'English, Swahili, Spanish', 'Sedan, Compact, SUV', 4.6);

-- Pricing configuration table
CREATE TABLE IF NOT EXISTS chauffeur_pricing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_type VARCHAR(50) NOT NULL,
    base_rate_per_hour DECIMAL(10,2) NOT NULL,
    min_hours INT DEFAULT 1,
    extra_charges TEXT,
    is_active BOOLEAN DEFAULT TRUE
);

-- Insert sample pricing
INSERT INTO chauffeur_pricing (service_type, base_rate_per_hour, min_hours, extra_charges) VALUES
('Standard Sedan', 1500.00, 3, 'After Hours: +20%, Weekend: +15%'),
('SUV/Luxury', 2500.00, 3, 'After Hours: +25%, Weekend: +20%'),
('Airport Transfer', 2000.00, 2, 'Waiting Time: Ksh 500/hr, Night: +Ksh 1000'),
('Corporate', 3500.00, 4, 'After Hours: +30%, Weekend: +25%');

-- Create a view for active chauffeur bookings
CREATE OR REPLACE VIEW active_chauffeur_bookings AS
SELECT 
    cb.id,
    cb.customer_name,
    cb.pickup_location,
    cb.destination,
    cb.date,
    cb.time,
    cb.duration_hours,
    cb.total_cost,
    cb.status,
    d.driver_name as assigned_driver
FROM chauffeur_bookings cb
LEFT JOIN chauffeur_drivers d ON cb.driver_assigned = d.driver_id
WHERE cb.status IN ('pending', 'confirmed')
ORDER BY cb.date, cb.time;

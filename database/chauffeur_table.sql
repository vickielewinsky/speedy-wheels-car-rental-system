-- chauffeur_table.sql
CREATE TABLE IF NOT EXISTS chauffeur_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    booking_id INT NULL,
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
    driver_assigned VARCHAR(100),
    driver_contact VARCHAR(20),
    total_cost DECIMAL(10,2),
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE SET NULL
);

-- Add sample chauffeur drivers table
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
('Grace Wambui', 'DL00123459', '+254745678901', 'grace.wambui@speedywheels.com', 6, 'English, Swahili, Spanish', 'Sedan, Compact, SUV', 4.6);

-- Price configuration table
CREATE TABLE IF NOT EXISTS chauffeur_pricing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_type VARCHAR(50) NOT NULL,
    base_rate_per_hour DECIMAL(10,2) NOT NULL,
    min_hours INT DEFAULT 1,
    extra_charges JSON,
    is_active BOOLEAN DEFAULT TRUE
);

-- Insert pricing
INSERT INTO chauffeur_pricing (service_type, base_rate_per_hour, min_hours, extra_charges) VALUES
('Standard Sedan', 1500.00, 3, '{"after_hours": 2000, "weekend": 1800, "holiday": 2200}'),
('SUV/Luxury', 2500.00, 3, '{"after_hours": 3000, "weekend": 2800, "holiday": 3200}'),
('Airport Transfer', 2000.00, 2, '{"waiting_time": 500, "night_surcharge": 1000}'),
('Corporate', 3500.00, 4, '{"after_hours": 4000, "weekend": 3800}');
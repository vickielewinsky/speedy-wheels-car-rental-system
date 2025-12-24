#!/bin/bash

# setup_auto_cleanup.sh
# Run this script once to set up the automatic cleanup system

echo "🚀 Setting up Automatic Cleanup System for Speedy Wheels..."
echo "========================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Navigate to project directory
PROJECT_ROOT="/c/xampp/htdocs/speedy-wheels-car-rental-system"
cd "$PROJECT_ROOT"
echo -e "${BLUE}Project root: $PROJECT_ROOT${NC}"

# Step 1: Create necessary directories
echo -e "\n${BLUE}Step 1: Creating directories...${NC}"
mkdir -p storage/logs storage/cache src/services src/modules/cleanup
echo -e "${GREEN}✓ Directories created${NC}"

# Step 2: Update database schema
echo -e "\n${BLUE}Step 2: Creating database schema update...${NC}"
cat > update_schema.sql << 'SQL_EOF'
-- Add automatic cleanup fields to database
-- Run this in MySQL/phpMyAdmin

-- Add to vehicles table
ALTER TABLE vehicles 
ADD COLUMN IF NOT EXISTS last_cleanup_check DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS auto_cleanup_enabled BOOLEAN DEFAULT TRUE;

-- Add to bookings table
ALTER TABLE bookings
ADD COLUMN IF NOT EXISTS auto_completion_date DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS is_test_booking BOOLEAN DEFAULT FALSE;

-- Add cleanup log table
CREATE TABLE IF NOT EXISTS cleanup_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action_type VARCHAR(50) NOT NULL,
    bookings_completed INT DEFAULT 0,
    vehicles_freed INT DEFAULT 0,
    test_data_cleaned INT DEFAULT 0,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for faster cleanup queries
CREATE INDEX IF NOT EXISTS idx_bookings_status_return ON bookings(status, return_date);
CREATE INDEX IF NOT EXISTS idx_vehicles_status ON vehicles(status);
SQL_EOF

echo -e "${GREEN}✓ Schema update file created: update_schema.sql${NC}"
echo -e "${YELLOW}Run this SQL in phpMyAdmin or via: mysql -u root -p < update_schema.sql${NC}"

# Step 3: Create AutoCleanupService
echo -e "\n${BLUE}Step 3: Creating AutoCleanupService...${NC}"
cat > src/services/AutoCleanupService.php << 'SERVICE_EOF'
<?php
// src/services/AutoCleanupService.php

require_once __DIR__ . '/DatabaseService.php';

class AutoCleanupService {
    private $db;
    private $pdo;
    
    public function __construct() {
        $this->db = new DatabaseService();
        $this->pdo = $this->db->getConnection();
    }
    
    public function runAutoCleanup($force = false) {
        try {
            // Check last run time
            $lastRunFile = __DIR__ . '/../../storage/last_cleanup.txt';
            if (!$force && file_exists($lastRunFile)) {
                $lastRun = file_get_contents($lastRunFile);
                if ($lastRun && time() - strtotime($lastRun) < 3600) {
                    return ['success' => true, 'skipped' => true];
                }
            }
            
            $this->pdo->beginTransaction();
            $results = ['completed_bookings' => 0, 'freed_vehicles' => 0, 'cleaned_test_data' => 0];
            
            // Complete expired bookings
            $stmt = $this->pdo->prepare("
                UPDATE bookings b
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                SET b.status = 'completed',
                    b.payment_status = 'paid',
                    b.updated_at = NOW(),
                    v.status = 'available',
                    v.updated_at = NOW()
                WHERE b.status IN ('active', 'confirmed')
                  AND b.return_date < CURDATE()
                  AND v.status != 'available'
            ");
            $stmt->execute();
            $results['completed_bookings'] = $stmt->rowCount();
            
            // Free vehicles
            $stmt = $this->pdo->prepare("
                UPDATE vehicles v
                SET v.status = 'available',
                    v.updated_at = NOW()
                WHERE v.status != 'available'
                  AND NOT EXISTS (
                    SELECT 1 FROM bookings b 
                    WHERE b.vehicle_id = v.vehicle_id 
                      AND b.status IN ('active', 'confirmed', 'pending')
                      AND b.return_date >= CURDATE()
                  )
            ");
            $stmt->execute();
            $results['freed_vehicles'] = $stmt->rowCount();
            
            $this->pdo->commit();
            
            // Update last run time
            file_put_contents($lastRunFile, date('Y-m-d H:i:s'));
            
            // Log results
            $logMessage = date('Y-m-d H:i:s') . " - Cleanup completed: " . 
                         $results['completed_bookings'] . " bookings, " . 
                         $results['freed_vehicles'] . " vehicles\n";
            file_put_contents(__DIR__ . '/../../storage/logs/cleanup.log', $logMessage, FILE_APPEND);
            
            return [
                'success' => true,
                'message' => "Cleaned " . $results['completed_bookings'] . " bookings, freed " . $results['freed_vehicles'] . " vehicles",
                'results' => $results
            ];
            
        } catch (Exception $e) {
            if (isset($this->pdo) && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("AutoCleanup Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function forceCleanupAll() {
        try {
            $this->pdo->beginTransaction();
            
            // Complete ALL expired bookings
            $stmt = $this->pdo->prepare("
                UPDATE bookings 
                SET status = 'completed',
                    payment_status = 'paid',
                    updated_at = NOW()
                WHERE status IN ('active', 'confirmed', 'pending')
                  AND return_date < CURDATE()
            ");
            $stmt->execute();
            $completed = $stmt->rowCount();
            
            // Make ALL vehicles available
            $stmt = $this->pdo->prepare("
                UPDATE vehicles 
                SET status = 'available',
                    updated_at = NOW()
                WHERE status != 'available'
            ");
            $stmt->execute();
            $freed = $stmt->rowCount();
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'completed_bookings' => $completed,
                'freed_vehicles' => $freed
            ];
            
        } catch (Exception $e) {
            if (isset($this->pdo) && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function getCleanupStats() {
        try {
            $stats = [];
            
            // Expired bookings
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count 
                FROM bookings 
                WHERE status IN ('active', 'confirmed', 'pending')
                  AND return_date < CURDATE()
            ");
            $stmt->execute();
            $stats['expired_bookings'] = $stmt->fetchColumn();
            
            // Unavailable vehicles
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count 
                FROM vehicles 
                WHERE status != 'available'
            ");
            $stmt->execute();
            $stats['unavailable_vehicles'] = $stmt->fetchColumn();
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Stats Error: " . $e->getMessage());
            return [];
        }
    }
}
SERVICE_EOF
echo -e "${GREEN}✓ AutoCleanupService created${NC}"

# Step 4: Create cleanup CLI tool
echo -e "\n${BLUE}Step 4: Creating cleanup CLI tool...${NC}"
cat > cleanup.php << 'CLI_EOF'
<?php
// cleanup.php - Command line cleanup tool

if (php_sapi_name() !== 'cli') {
    die("CLI only\n");
}

require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/services/AutoCleanupService.php';

$service = new AutoCleanupService();
$options = getopt("fhs", ["force", "help", "stats"]);

if (isset($options['h']) || isset($options['help'])) {
    echo "Usage: php cleanup.php [options]\n";
    echo "  -f, --force   Force cleanup ALL\n";
    echo "  -s, --stats   Show statistics\n";
    echo "  -h, --help    Show help\n";
    exit(0);
}

if (isset($options['s']) || isset($options['stats'])) {
    $stats = $service->getCleanupStats();
    echo "Expired Bookings: " . $stats['expired_bookings'] . "\n";
    echo "Unavailable Vehicles: " . $stats['unavailable_vehicles'] . "\n";
    exit(0);
}

if (isset($options['f']) || isset($options['force'])) {
    echo "Force cleaning ALL expired bookings...\n";
    $result = $service->forceCleanupAll();
    
    if ($result['success']) {
        echo "✅ Force cleanup completed!\n";
        echo "   Completed: " . $result['completed_bookings'] . " bookings\n";
        echo "   Freed: " . $result['freed_vehicles'] . " vehicles\n";
    } else {
        echo "❌ Error: " . $result['error'] . "\n";
        exit(1);
    }
    exit(0);
}

// Normal cleanup
echo "Running cleanup...\n";
$result = $service->runAutoCleanup(true);

if ($result['success']) {
    if (isset($result['skipped'])) {
        echo "⏭️  Cleanup skipped (ran recently)\n";
    } else {
        echo "✅ Cleanup completed!\n";
        echo "   " . $result['message'] . "\n";
    }
} else {
    echo "❌ Error: " . $result['error'] . "\n";
    exit(1);
}
CLI_EOF
echo -e "${GREEN}✓ CLI tool created: cleanup.php${NC}"

# Step 5: Create immediate cleanup script
echo -e "\n${BLUE}Step 5: Creating immediate cleanup script...${NC}"
cat > cleanup_now.bat << 'BAT_EOF'
@echo off
echo Cleaning up expired bookings...
php cleanup.php --force
echo.
echo Current status:
php cleanup.php --stats
pause
BAT_EOF
echo -e "${GREEN}✓ Windows cleanup script created: cleanup_now.bat${NC}"

# Step 6: Create simple SQL for immediate cleanup
echo -e "\n${BLUE}Step 6: Creating SQL cleanup script...${NC}"
cat > cleanup_immediate.sql << 'SQL2_EOF'
-- Run this in phpMyAdmin to clean everything immediately

-- Complete expired bookings
UPDATE bookings 
SET status = 'completed', 
    payment_status = 'paid',
    updated_at = NOW()
WHERE status IN ('active', 'confirmed', 'pending')
  AND return_date < CURDATE();

-- Make all vehicles available
UPDATE vehicles 
SET status = 'available',
    updated_at = NOW()
WHERE status != 'available';

-- Show results
SELECT 
    '✅ Cleanup Complete!' as message,
    (SELECT COUNT(*) FROM bookings WHERE status IN ('active','confirmed','pending') AND return_date < CURDATE()) as remaining_expired,
    (SELECT COUNT(*) FROM vehicles WHERE status != 'available') as remaining_unavailable;
SQL2_EOF
echo -e "${GREEN}✓ SQL script created: cleanup_immediate.sql${NC}"

echo -e "\n${GREEN}✅ Setup Complete!${NC}"
echo -e "\n${YELLOW}Next steps:${NC}"
echo "1. Run the SQL in update_schema.sql"
echo "2. Test cleanup: php cleanup.php"
echo "3. Force cleanup: php cleanup.php --force"
echo "4. Or run: cleanup_now.bat"

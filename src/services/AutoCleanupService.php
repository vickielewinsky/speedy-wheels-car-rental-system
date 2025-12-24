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

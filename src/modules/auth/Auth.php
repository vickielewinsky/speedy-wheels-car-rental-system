<?php
// src/modules/auth/Auth.php

require_once __DIR__ . '/../../helpers/url_helper.php';
require_once __DIR__ . '/../../config/database.php';

class Auth {
    private $pdo;

    public function __construct() {
        $this->pdo = $GLOBALS['pdo'] ?? null;
    }

    /**
     * Login a user with username/email and password
     */
    public function login($username, $password) {
        try {
            // Fetch user by username or email
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, password_hash, user_role 
                FROM users 
                WHERE (username = :username OR email = :email) 
                AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([
                'username' => $username,
                'email' => $username
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'error' => 'Invalid username or password'];
            }

            // Verify password against password_hash column
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'error' => 'Invalid username or password'];
            }

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['user_role'];
            $_SESSION['is_logged_in'] = true;

            return ['success' => true];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) return null;

        $stmt = $this->pdo->prepare("
            SELECT id, username, email, first_name, last_name, phone, user_role, created_at 
            FROM users WHERE id = :id
        ");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    // Fetch all bookings for the logged-in user - FIXED WITH CORRECT COLUMN NAMES
    public function getUserBookings($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.*, 
                       v.model AS vehicle_model,
                       v.make AS vehicle_make,
                       v.daily_rate AS vehicle_rate,
                       v.name AS vehicle_name,
                       CONCAT(v.make, ' ', v.model) AS vehicle_full_name
                FROM bookings b
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                WHERE b.user_id = :user_id
                ORDER BY b.booking_date DESC
            ");
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (PDOException $e) {
            error_log("Error in getUserBookings: " . $e->getMessage());
            return [];
        }
    }

    // Count active bookings
    public function countActiveBookings($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM bookings 
                WHERE user_id = :user_id AND status = 'active'
            ");
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting active bookings: " . $e->getMessage());
            return 0;
        }
    }

    // Count completed bookings
    public function countCompletedBookings($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM bookings 
                WHERE user_id = :user_id AND status = 'completed'
            ");
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting completed bookings: " . $e->getMessage());
            return 0;
        }
    }

    // NEW METHODS FOR ADMIN DASHBOARD

    /**
     * Get all users (admin only)
     */
    public function getAllUsers() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, first_name, last_name, phone, user_role, created_at 
                FROM users 
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error in getAllUsers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get M-Pesa transactions (admin only)
     */
    public function getMpesaTransactions() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT mp.*, 
                       u.email, 
                       u.phone as user_phone,
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       b.reference as booking_reference
                FROM mpesa_transactions mp
                LEFT JOIN bookings b ON mp.booking_id = b.id
                LEFT JOIN users u ON b.user_id = u.id
                ORDER BY mp.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error in getMpesaTransactions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system statistics (admin only)
     */
    public function getSystemStats() {
        $stats = [];

        try {
            // Total users
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM users");
            $stmt->execute();
            $stats['total_users'] = $stmt->fetchColumn() ?: 0;

            // Total bookings
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM bookings");
            $stmt->execute();
            $stats['total_bookings'] = $stmt->fetchColumn() ?: 0;

            // M-Pesa transactions
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM mpesa_transactions WHERE status = 'completed'");
            $stmt->execute();
            $stats['mpesa_transactions'] = $stmt->fetchColumn() ?: 0;

            // Available vehicles
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM vehicles WHERE status = 'available'");
            $stmt->execute();
            $stats['available_vehicles'] = $stmt->fetchColumn() ?: 0;

        } catch (Exception $e) {
            error_log("Error in getSystemStats: " . $e->getMessage());
            // Set defaults
            $stats = [
                'total_users' => 0,
                'total_bookings' => 0,
                'mpesa_transactions' => 0,
                'available_vehicles' => 0
            ];
        }

        return $stats;
    }

    /**
     * Get recent bookings (admin only)
     */
    public function getRecentBookings($limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       v.name as vehicle_name,
                       v.daily_rate
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                ORDER BY b.created_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error in getRecentBookings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get revenue statistics (admin only)
     */
    public function getRevenueStats() {
        $stats = [];

        try {
            // Total revenue from bookings
            $stmt = $this->pdo->prepare("SELECT SUM(total_price) as total FROM bookings WHERE payment_status = 'paid'");
            $stmt->execute();
            $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

            // Monthly revenue
            $stmt = $this->pdo->prepare("
                SELECT SUM(total_price) as total FROM bookings 
                WHERE payment_status = 'paid' 
                AND MONTH(created_at) = MONTH(CURRENT_DATE())
                AND YEAR(created_at) = YEAR(CURRENT_DATE())
            ");
            $stmt->execute();
            $stats['monthly_revenue'] = $stmt->fetchColumn() ?: 0;

            // Weekly revenue
            $stmt = $this->pdo->prepare("
                SELECT SUM(total_price) as total FROM bookings 
                WHERE payment_status = 'paid' 
                AND YEARWEEK(created_at) = YEARWEEK(CURRENT_DATE())
            ");
            $stmt->execute();
            $stats['weekly_revenue'] = $stmt->fetchColumn() ?: 0;

            // Calculate daily average
            if ($stats['total_revenue'] > 0) {
                $stmt = $this->pdo->prepare("
                    SELECT DATEDIFF(MAX(created_at), MIN(created_at)) as days FROM bookings
                ");
                $stmt->execute();
                $days = $stmt->fetchColumn();
                $stats['daily_average'] = $days > 0 ? round($stats['total_revenue'] / $days, 2) : $stats['total_revenue'];
            } else {
                $stats['daily_average'] = 0;
            }

        } catch (Exception $e) {
            error_log("Error in getRevenueStats: " . $e->getMessage());
            // Set defaults
            $stats = [
                'total_revenue' => 0,
                'monthly_revenue' => 0,
                'weekly_revenue' => 0,
                'daily_average' => 0
            ];
        }

        return $stats;
    }

    /**
     * Get all bookings for admin view
     */
    public function getAllBookings() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email as customer_email,
                       u.phone as customer_phone,
                       v.name as vehicle_name,
                       v.make,
                       v.model,
                       v.daily_rate,
                       mp.transaction_id,
                       mp.phone as mpesa_phone,
                       mp.amount as mpesa_amount
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                LEFT JOIN mpesa_transactions mp ON b.id = mp.booking_id
                ORDER BY b.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error in getAllBookings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payment statistics for admin
     */
    public function getPaymentStats() {
        $stats = [];

        try {
            // Get total bookings count
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM bookings");
            $stmt->execute();
            $totalBookings = $stmt->fetchColumn();

            if ($totalBookings > 0) {
                // Count by payment status
                $stmt = $this->pdo->prepare("
                    SELECT 
                        SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                        SUM(CASE WHEN payment_status = 'failed' THEN 1 ELSE 0 END) as failed_count
                    FROM bookings
                ");
                $stmt->execute();
                $paymentCounts = $stmt->fetch(PDO::FETCH_ASSOC);

                $stats['paid_percent'] = round(($paymentCounts['paid_count'] / $totalBookings) * 100, 2);
                $stats['pending_percent'] = round(($paymentCounts['pending_count'] / $totalBookings) * 100, 2);
                $stats['failed_percent'] = round(($paymentCounts['failed_count'] / $totalBookings) * 100, 2);
            } else {
                $stats['paid_percent'] = 0;
                $stats['pending_percent'] = 0;
                $stats['failed_percent'] = 0;
            }

        } catch (Exception $e) {
            error_log("Error in getPaymentStats: " . $e->getMessage());
            $stats = [
                'paid_percent' => 0,
                'pending_percent' => 0,
                'failed_percent' => 0
            ];
        }

        return $stats;
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin() {
        $user = $this->getCurrentUser();
        if (!$user) return false;

        $role = strtolower($user['user_role'] ?? '');
        return in_array($role, ['admin', 'superadmin']);
    }

    /**
     * Get user details by ID (for admin)
     */
    public function getUserDetails($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.*,
                       COUNT(b.id) as total_bookings,
                       SUM(b.total_price) as total_spent
                FROM users u
                LEFT JOIN bookings b ON u.id = b.user_id
                WHERE u.id = :user_id
                GROUP BY u.id
            ");
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Error in getUserDetails: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get receipt details by booking ID
     */
    public function getReceiptDetails($booking_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.*,
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                       u.email,
                       u.phone,
                       v.name as vehicle_name,
                       v.make,
                       v.model,
                       v.year,
                       v.daily_rate,
                       mp.transaction_id,
                       mp.phone as mpesa_phone,
                       mp.amount,
                       mp.created_at as payment_date
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                LEFT JOIN mpesa_transactions mp ON b.id = mp.booking_id
                WHERE b.id = :booking_id
            ");
            $stmt->execute(['booking_id' => $booking_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Error in getReceiptDetails: " . $e->getMessage());
            return null;
        }
    }
}
?>
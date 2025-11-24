<?php
// src/modules/auth/Auth.php
require_once "../../config/database.php";

class Auth {
    private $pdo;
    
    public function __construct() {
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

    public function login($username, $password) {
        try {
            // SIMPLE query - just find by username
            $stmt = $this->pdo->prepare("
                SELECT * FROM users WHERE username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'error' => 'Invalid username'];
            }

            // Debug: Check what we're comparing
            $input_password = $password;
            $stored_hash = $user['password_hash'];
            
            // Test the password
            $password_match = password_verify($input_password, $stored_hash);
            
            if (!$password_match) {
                return ['success' => false, 'error' => 'Invalid password'];
            }

            // Ensure session is started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Set ALL session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['user_role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['login_time'] = time();

            return [
                'success' => true, 
                'message' => 'Login successful'
            ];

        } catch (Exception $e) {
            return [
                'success' => false, 
                'error' => 'System error: ' . $e->getMessage()
            ];
        }
    }

    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function register($user_data) {
        try {
            $required = ['username', 'email', 'password', 'first_name', 'last_name'];
            foreach ($required as $field) {
                if (empty($user_data[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }

            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$user_data['username'], $user_data['email']]);
            if ($stmt->fetch()) {
                throw new Exception("Username or email already exists");
            }

            $password_hash = password_hash($user_data['password'], PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password_hash, first_name, last_name, phone, user_role) 
                VALUES (?, ?, ?, ?, ?, ?, 'customer')
            ");
            
            $stmt->execute([
                $user_data['username'],
                $user_data['email'],
                $password_hash,
                $user_data['first_name'],
                $user_data['last_name'],
                $user_data['phone'] ?? null
            ]);

            $user_id = $this->pdo->lastInsertId();
            
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['user_role'] = 'customer';
            $_SESSION['first_name'] = $user_data['first_name'];
            $_SESSION['last_name'] = $user_data['last_name'];
            $_SESSION['login_time'] = time();

            return [
                'success' => true,
                'message' => 'Registration successful'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
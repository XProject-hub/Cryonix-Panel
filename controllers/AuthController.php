<?php
/**
 * Cryonix Panel - Auth Controller
 * Copyright 2026 XProject-Hub
 */

namespace CryonixPanel\Controllers;

require_once dirname(__DIR__) . '/core/Database.php';

use CryonixPanel\Core\Database;

class AuthController {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login(): void {
        $adminPath = $_ENV['ADMIN_PATH'] ?? 'admin';
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Please enter username and password';
            header("Location: /{$adminPath}/login");
            exit;
        }
        
        try {
            $user = $this->db->fetch(
                "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'",
                [$username, $username]
            );
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in_at'] = time();
                
                // Update last login
                try {
                    $this->db->update('users', [
                        'last_login_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$user['id']]);
                } catch (\Exception $e) {}
                
                header("Location: /{$adminPath}/dashboard");
                exit;
            }
            
            $_SESSION['error'] = 'Invalid username or password';
            header("Location: /{$adminPath}/login");
            exit;
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
            header("Location: /{$adminPath}/login");
            exit;
        }
    }
    
    public function logout(): void {
        $adminPath = $_ENV['ADMIN_PATH'] ?? 'admin';
        
        session_destroy();
        $_SESSION = [];
        
        header("Location: /{$adminPath}/login");
        exit;
    }
}


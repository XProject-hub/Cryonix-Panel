<?php
/**
 * Cryonix Panel - Database Seeder
 * Creates default admin user
 * Copyright 2026 XProject-Hub
 */

// Load environment first
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

require_once __DIR__ . '/../core/Database.php';

use CryonixPanel\Core\Database;

echo "=== Cryonix Panel Database Seeder ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::getInstance();
    $config = require __DIR__ . '/../config/app.php';
    
    // Check if admin exists
    $existingAdmin = $db->fetch("SELECT id FROM users WHERE username = 'admin'");
    
    if ($existingAdmin) {
        echo "Admin user already exists. Skipping.\n";
    } else {
        // Create admin
        $db->insert('users', [
            'username' => $config['default_admin']['username'],
            'email' => $config['default_admin']['email'],
            'password' => password_hash($config['default_admin']['password'], PASSWORD_ARGON2ID),
            'full_name' => 'Administrator',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "✓ Created admin user:\n";
        echo "  Username: {$config['default_admin']['username']}\n";
        echo "  Password: {$config['default_admin']['password']}\n";
        echo "  Email: {$config['default_admin']['email']}\n\n";
    }
    
    echo "=== Seeding Complete ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

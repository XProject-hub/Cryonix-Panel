<?php
/**
 * Cryonix Panel - License Checker
 * Validates license with Cryonix Cloud
 * Copyright 2026 XProject-Hub
 */

namespace CryonixPanel\Core;

class License {
    private Database $db;
    private string $licenseServer;
    private string $licenseFile = '/etc/cryonix/license.json';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $config = require __DIR__ . '/../config/app.php';
        $this->licenseServer = $config['license_server'];
    }
    
    public function isValid(): bool {
        $license = $this->getLicenseInfo();
        if (!$license) return false;
        
        if ($license['status'] !== 'active') return false;
        
        if ($license['expires_at'] && strtotime($license['expires_at']) < time()) {
            return false;
        }
        
        return true;
    }
    
    public function getLicenseInfo(): ?array {
        return $this->db->fetch("SELECT * FROM license_info ORDER BY id DESC LIMIT 1");
    }
    
    public function activate(string $licenseKey): array {
        $fingerprint = $this->getFingerprint();
        $serverIp = $this->getServerIp();
        
        $response = $this->apiRequest('/api/v1/license/activate', [
            'license_key' => $licenseKey,
            'server_ip' => $serverIp,
            'fingerprint' => $fingerprint,
            'hostname' => gethostname(),
            'panel_version' => '1.0.0',
            'os_info' => php_uname()
        ]);
        
        if ($response && $response['success']) {
            // Save to database
            $this->db->query("DELETE FROM license_info");
            $this->db->insert('license_info', [
                'license_key' => $licenseKey,
                'activation_id' => $response['activation_id'] ?? null,
                'status' => 'active',
                'max_connections' => $response['limits']['max_connections'] ?? 100,
                'max_channels' => $response['limits']['max_channels'] ?? 500,
                'expires_at' => $response['expires_at'],
                'last_check_at' => date('Y-m-d H:i:s')
            ]);
            
            // Save to file for backup
            $this->saveLicenseFile([
                'license_key' => $licenseKey,
                'fingerprint' => $fingerprint,
                'activated_at' => date('Y-m-d H:i:s')
            ]);
            
            return ['success' => true, 'message' => 'License activated successfully'];
        }
        
        return ['success' => false, 'error' => $response['error'] ?? 'Activation failed'];
    }
    
    public function heartbeat(): bool {
        $license = $this->getLicenseInfo();
        if (!$license) return false;
        
        $response = $this->apiRequest('/api/v1/license/heartbeat', [
            'license_key' => $license['license_key'],
            'fingerprint' => $this->getFingerprint(),
            'stats' => $this->getStats()
        ]);
        
        if ($response && $response['success']) {
            $status = $response['locked'] ? 'locked' : 'active';
            $this->db->update('license_info', [
                'status' => $status,
                'last_check_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$license['id']]);
            
            return !$response['locked'];
        }
        
        // If can't reach server, check grace period
        if ($license['last_check_at']) {
            $config = require __DIR__ . '/../config/app.php';
            $graceHours = $config['grace_period_hours'];
            $lastCheck = strtotime($license['last_check_at']);
            
            if ((time() - $lastCheck) < ($graceHours * 3600)) {
                return true; // Still within grace period
            }
        }
        
        return false;
    }
    
    public function getLimits(): array {
        $license = $this->getLicenseInfo();
        return [
            'max_connections' => $license['max_connections'] ?? 0,
            'max_channels' => $license['max_channels'] ?? 0
        ];
    }
    
    private function getFingerprint(): string {
        $parts = [];
        
        // Machine ID
        if (file_exists('/etc/machine-id')) {
            $parts[] = trim(file_get_contents('/etc/machine-id'));
        }
        
        // Server IP
        $parts[] = $this->getServerIp();
        
        // Hostname
        $parts[] = gethostname();
        
        return hash('sha256', implode('|', $parts));
    }
    
    private function getServerIp(): string {
        // Try to get real IP
        $ip = shell_exec("hostname -I 2>/dev/null | awk '{print $1}'");
        return trim($ip) ?: '127.0.0.1';
    }
    
    private function getStats(): array {
        return [
            'users_count' => $this->db->count('lines'),
            'channels_count' => $this->db->count('streams'),
            'movies_count' => $this->db->count('movies'),
            'active_connections' => $this->db->count('user_activity', 'ended_at IS NULL')
        ];
    }
    
    private function apiRequest(string $endpoint, array $data): ?array {
        $url = $this->licenseServer . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: CryonixPanel/1.0.0'
            ]
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return null;
        }
        
        return json_decode($response, true);
    }
    
    private function saveLicenseFile(array $data): void {
        $dir = dirname($this->licenseFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        file_put_contents($this->licenseFile, json_encode($data, JSON_PRETTY_PRINT));
        @chmod($this->licenseFile, 0600);
    }
}


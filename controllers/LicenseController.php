<?php
/**
 * Cryonix Panel - License Controller
 * Copyright 2026 XProject-Hub
 */

namespace CryonixPanel\Controllers;

require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/core/License.php';

use CryonixPanel\Core\Database;
use CryonixPanel\Core\License;

class LicenseController {
    private Database $db;
    private License $license;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->license = new License();
    }
    
    public function activate(): void {
        $adminPath = $_ENV['ADMIN_PATH'] ?? 'admin';
        
        $licenseKey = trim($_POST['license_key'] ?? '');
        
        if (empty($licenseKey)) {
            $_SESSION['error'] = 'Please enter a license key';
            header("Location: /{$adminPath}/license");
            exit;
        }
        
        // Validate format
        if (!preg_match('/^CRYX-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/i', $licenseKey)) {
            $_SESSION['error'] = 'Invalid license key format. Expected: CRYX-XXXX-XXXX-XXXX-XXXX';
            header("Location: /{$adminPath}/license");
            exit;
        }
        
        try {
            $result = $this->license->activate($licenseKey);
            
            if ($result['success']) {
                $_SESSION['success'] = 'License activated successfully!';
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Activation failed. Please contact support.';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        
        header("Location: /{$adminPath}/license");
        exit;
    }
}


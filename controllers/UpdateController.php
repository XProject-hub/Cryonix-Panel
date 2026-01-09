<?php
/**
 * Cryonix Panel - Update Controller
 * Copyright 2026 XProject-Hub
 */

namespace CryonixPanel\Controllers;

require_once dirname(__DIR__) . '/core/Updater.php';

use CryonixPanel\Core\Updater;

class UpdateController {
    
    public function check(): void {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $updater = new Updater();
        echo json_encode($updater->checkForUpdates());
    }
    
    public function apply(): void {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }
        
        $updater = new Updater();
        echo json_encode($updater->applyUpdate());
    }
}


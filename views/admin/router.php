<?php
/**
 * Cryonix Panel - Admin Router
 * Routes admin panel pages
 * Copyright 2026 XProject-Hub
 */

$adminRoute = $_SERVER['ADMIN_ROUTE'] ?? '/dashboard';
$adminPath = $_SERVER['ADMIN_PATH'] ?? '/admin';

// Remove trailing slash and clean up
$adminRoute = rtrim($adminRoute, '/');
if (empty($adminRoute)) $adminRoute = '/dashboard';

// Parse route parts
$routeParts = explode('/', ltrim($adminRoute, '/'));
$section = $routeParts[0] ?? 'dashboard';
$action = $routeParts[1] ?? null;
$id = $routeParts[2] ?? null;

// Set action/id in GET
if ($action) $_GET['action'] = $action;
if ($id) $_GET['id'] = $id;

switch ($section) {
    case 'dashboard':
        require CRYONIX_ROOT . '/views/admin/dashboard.php';
        break;
        
    // Users / Lines
    case 'users':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/users-add.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/users.php';
        }
        break;
        
    // Live Channels / Streams
    case 'channels':
    case 'streams':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/streams-add.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/streams.php';
        }
        break;
        
    // Movies (VOD)
    case 'movies':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/movies-add.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/movies.php';
        }
        break;
        
    // Series
    case 'series':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/series-add.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/series.php';
        }
        break;
        
    // Categories
    case 'categories':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/categories-add.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/categories.php';
        }
        break;
        
    // Bouquets
    case 'bouquets':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/bouquets-add.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/bouquets.php';
        }
        break;
        
    // Servers
    case 'servers':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/servers-add.php';
        } elseif ($action === 'delete' && $id) {
            // Handle server deletion
            require_once CRYONIX_ROOT . '/core/Database.php';
            $db = \CryonixPanel\Core\Database::getInstance();
            $db->delete('servers', 'id = ? AND is_main = 0', [$id]);
            header('Location: ' . ADMIN_PATH . '/servers');
            exit;
        } else {
            require CRYONIX_ROOT . '/views/admin/servers.php';
        }
        break;
        
    // Settings
    case 'settings':
        if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle settings save
            require_once CRYONIX_ROOT . '/core/Database.php';
            $db = \CryonixPanel\Core\Database::getInstance();
            foreach ($_POST as $key => $value) {
                $db->query("UPDATE settings SET `value` = ? WHERE `key` = ?", [$value, $key]);
            }
            $_SESSION['success'] = 'Settings saved!';
            header('Location: ' . ADMIN_PATH . '/settings');
            exit;
        }
        require CRYONIX_ROOT . '/views/admin/settings.php';
        break;
        
    // License
    case 'license':
        require CRYONIX_ROOT . '/views/admin/license.php';
        break;
        
    // Activity log
    case 'activity':
        require CRYONIX_ROOT . '/views/admin/activity.php';
        break;
        
    // System logs
    case 'logs':
        require CRYONIX_ROOT . '/views/admin/logs.php';
        break;
        
    default:
        // Default to dashboard
        require CRYONIX_ROOT . '/views/admin/dashboard.php';
}

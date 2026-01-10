<?php
/**
 * Cryonix Panel - Admin Router
 * Routes admin panel pages
 * Copyright 2026 XProject-Hub
 */

$adminRoute = $_SERVER['ADMIN_ROUTE'] ?? '/dashboard';
$adminPath = $_SERVER['ADMIN_PATH'] ?? '/admin';

// Remove trailing slash
$adminRoute = rtrim($adminRoute, '/');
if (empty($adminRoute)) $adminRoute = '/dashboard';

switch ($adminRoute) {
    case '/dashboard':
        require CRYONIX_ROOT . '/views/admin/dashboard.php';
        break;
        
    case '/users':
        require CRYONIX_ROOT . '/views/admin/users.php';
        break;
        
    case '/channels':
    case '/streams':
        require CRYONIX_ROOT . '/views/admin/streams.php';
        break;
        
    case '/movies':
        require CRYONIX_ROOT . '/views/admin/movies.php';
        break;
        
    case '/series':
        require CRYONIX_ROOT . '/views/admin/series.php';
        break;
        
    case '/categories':
        require CRYONIX_ROOT . '/views/admin/categories.php';
        break;
        
    case '/bouquets':
        require CRYONIX_ROOT . '/views/admin/bouquets.php';
        break;
        
    case '/servers':
        require CRYONIX_ROOT . '/views/admin/servers.php';
        break;
        
    case '/settings':
        require CRYONIX_ROOT . '/views/admin/settings.php';
        break;
        
    case '/license':
        require CRYONIX_ROOT . '/views/admin/license.php';
        break;
        
    case '/activity':
        require CRYONIX_ROOT . '/views/admin/activity.php';
        break;
        
    case '/logs':
        require CRYONIX_ROOT . '/views/admin/logs.php';
        break;
        
    default:
        // Check if it's a sub-route like /users/add
        if (preg_match('#^/users/(\w+)(?:/(\d+))?$#', $adminRoute, $m)) {
            $_GET['action'] = $m[1];
            $_GET['id'] = $m[2] ?? null;
            require CRYONIX_ROOT . '/views/admin/users.php';
        } elseif (preg_match('#^/streams/(\w+)(?:/(\d+))?$#', $adminRoute, $m)) {
            $_GET['action'] = $m[1];
            $_GET['id'] = $m[2] ?? null;
            require CRYONIX_ROOT . '/views/admin/streams.php';
        } else {
            // Default to dashboard for unknown routes
            require CRYONIX_ROOT . '/views/admin/dashboard.php';
        }
}


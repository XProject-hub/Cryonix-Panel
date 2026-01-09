<?php
/**
 * Cryonix Panel - IPTV Management Panel
 * Main Entry Point
 * Copyright 2026 XProject-Hub
 */

define('CRYONIX_ROOT', dirname(__DIR__));
define('CRYONIX_START', microtime(true));

// Load environment
$envFile = CRYONIX_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Get admin path from env or default
$ADMIN_PATH = $_ENV['ADMIN_PATH'] ?? 'admin';
define('ADMIN_PATH', '/' . $ADMIN_PATH);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'CryonixPanel\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    $file = CRYONIX_ROOT . '/' . strtolower(str_replace('\\', '/', substr($class, strlen($prefix)))) . '.php';
    if (file_exists($file)) require $file;
});

require_once CRYONIX_ROOT . '/core/Database.php';
require_once CRYONIX_ROOT . '/core/License.php';

use CryonixPanel\Core\Database;
use CryonixPanel\Core\License;

session_start();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Check if accessing admin panel
$isAdminRoute = str_starts_with($uri, ADMIN_PATH);

// Check license on admin routes
if ($isAdminRoute || $uri === '/dashboard') {
    try {
        $license = new License();
        if (!$license->isValid()) {
            // Redirect to license activation
            if ($uri !== ADMIN_PATH . '/license') {
                header('Location: ' . ADMIN_PATH . '/license');
                exit;
            }
        }
    } catch (\Exception $e) {
        // Database not ready yet, allow access to license page
    }
}

// Simple routing
switch (true) {
    // Streaming endpoints (Xtream API compatible)
    case $uri === '/player_api.php' || $uri === '/panel_api.php':
        require CRYONIX_ROOT . '/api/xtream.php';
        break;
        
    case preg_match('#^/live/([^/]+)/([^/]+)/(\d+)\.(ts|m3u8)$#', $uri, $m):
        require CRYONIX_ROOT . '/api/stream.php';
        handleLiveStream($m[1], $m[2], $m[3], $m[4]);
        break;
        
    case preg_match('#^/movie/([^/]+)/([^/]+)/(\d+)\.(mp4|mkv|m3u8)$#', $uri, $m):
        require CRYONIX_ROOT . '/api/stream.php';
        handleMovieStream($m[1], $m[2], $m[3], $m[4]);
        break;
        
    case preg_match('#^/series/([^/]+)/([^/]+)/(\d+)\.(mp4|mkv|m3u8)$#', $uri, $m):
        require CRYONIX_ROOT . '/api/stream.php';
        handleSeriesStream($m[1], $m[2], $m[3], $m[4]);
        break;
        
    // Get M3U playlist
    case $uri === '/get.php':
        require CRYONIX_ROOT . '/api/playlist.php';
        break;
        
    // EPG
    case $uri === '/xmltv.php':
        require CRYONIX_ROOT . '/api/epg.php';
        break;
        
    // Admin panel - dynamic path
    case $uri === ADMIN_PATH || $uri === ADMIN_PATH . '/':
        header('Location: ' . ADMIN_PATH . '/dashboard');
        exit;
        
    case $uri === ADMIN_PATH . '/login' && $method === 'GET':
        require CRYONIX_ROOT . '/views/admin/login.php';
        break;
        
    case $uri === ADMIN_PATH . '/login' && $method === 'POST':
        require CRYONIX_ROOT . '/controllers/AuthController.php';
        (new \CryonixPanel\Controllers\AuthController())->login();
        break;
        
    case $uri === ADMIN_PATH . '/logout':
        require CRYONIX_ROOT . '/controllers/AuthController.php';
        (new \CryonixPanel\Controllers\AuthController())->logout();
        break;
        
    case $uri === ADMIN_PATH . '/license':
        require CRYONIX_ROOT . '/views/admin/license.php';
        break;
        
    case $uri === ADMIN_PATH . '/license/activate' && $method === 'POST':
        require CRYONIX_ROOT . '/controllers/LicenseController.php';
        (new \CryonixPanel\Controllers\LicenseController())->activate();
        break;
        
    // Update system routes
    case $uri === ADMIN_PATH . '/update/check':
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        require CRYONIX_ROOT . '/controllers/UpdateController.php';
        (new \CryonixPanel\Controllers\UpdateController())->check();
        break;
        
    case $uri === ADMIN_PATH . '/update/apply' && $method === 'POST':
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        require CRYONIX_ROOT . '/controllers/UpdateController.php';
        (new \CryonixPanel\Controllers\UpdateController())->apply();
        break;
        
    case str_starts_with($uri, ADMIN_PATH):
        // Check auth for admin
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . ADMIN_PATH . '/login');
            exit;
        }
        // Pass the relative path within admin to router
        $_SERVER['ADMIN_PATH'] = ADMIN_PATH;
        $_SERVER['ADMIN_ROUTE'] = substr($uri, strlen(ADMIN_PATH));
        require CRYONIX_ROOT . '/views/admin/router.php';
        break;
        
    // Default - show login or redirect
    case $uri === '/' || $uri === '':
        header('Location: ' . ADMIN_PATH . '/login');
        exit;
        
    // Old /admin route - redirect to new path
    case str_starts_with($uri, '/admin'):
        // Redirect old /admin/* to new path for backwards compatibility
        $newPath = ADMIN_PATH . substr($uri, 6);
        header('Location: ' . $newPath);
        exit;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
}

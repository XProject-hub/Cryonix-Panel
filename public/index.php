<?php
/**
 * Cryonix Panel - IPTV Management Panel
 * Main Entry Point
 * Copyright 2026 XProject-Hub
 */

define('CRYONIX_ROOT', dirname(__DIR__));
define('CRYONIX_START', microtime(true));

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

// Check license on admin routes
if (str_starts_with($uri, '/admin') || $uri === '/dashboard') {
    $license = new License();
    if (!$license->isValid()) {
        // Redirect to license activation
        if ($uri !== '/admin/license') {
            header('Location: /admin/license');
            exit;
        }
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
        
    // Admin panel
    case $uri === '/admin' || $uri === '/admin/':
        header('Location: /admin/dashboard');
        exit;
        
    case $uri === '/admin/login' && $method === 'GET':
        require CRYONIX_ROOT . '/views/admin/login.php';
        break;
        
    case $uri === '/admin/login' && $method === 'POST':
        require CRYONIX_ROOT . '/controllers/AuthController.php';
        (new \CryonixPanel\Controllers\AuthController())->login();
        break;
        
    case $uri === '/admin/logout':
        require CRYONIX_ROOT . '/controllers/AuthController.php';
        (new \CryonixPanel\Controllers\AuthController())->logout();
        break;
        
    case $uri === '/admin/license':
        require CRYONIX_ROOT . '/views/admin/license.php';
        break;
        
    case $uri === '/admin/license/activate' && $method === 'POST':
        require CRYONIX_ROOT . '/controllers/LicenseController.php';
        (new \CryonixPanel\Controllers\LicenseController())->activate();
        break;
        
    // Update system routes
    case $uri === '/admin/update/check':
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        require CRYONIX_ROOT . '/controllers/UpdateController.php';
        (new \CryonixPanel\Controllers\UpdateController())->check();
        break;
        
    case $uri === '/admin/update/apply' && $method === 'POST':
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        require CRYONIX_ROOT . '/controllers/UpdateController.php';
        (new \CryonixPanel\Controllers\UpdateController())->apply();
        break;
        
    case str_starts_with($uri, '/admin'):
        // Check auth for admin
        if (!isset($_SESSION['user_id'])) {
            header('Location: /admin/login');
            exit;
        }
        require CRYONIX_ROOT . '/views/admin/router.php';
        break;
        
    // Default - show login or redirect
    case $uri === '/' || $uri === '':
        header('Location: /admin/login');
        exit;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
}


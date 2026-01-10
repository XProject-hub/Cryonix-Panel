<?php
/**
 * Cryonix Panel - Live Stream Handler
 * Validates user and proxies live streams
 * Copyright 2026 XProject-Hub
 */

error_reporting(0);
ini_set('display_errors', 0);

// Parse URL: /live/{username}/{password}/{stream_id}.{ext}
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Remove /live/ prefix
$path = preg_replace('#^/live/#', '', $path);
$parts = explode('/', $path);

if (count($parts) < 3) {
    header('HTTP/1.1 400 Bad Request');
    die('Invalid stream URL');
}

$username = $parts[0];
$password = $parts[1];
$streamFile = $parts[2];

// Parse stream ID and extension
preg_match('/^(\d+)\.(ts|m3u8|mpegts)$/', $streamFile, $matches);
if (!$matches) {
    header('HTTP/1.1 400 Bad Request');
    die('Invalid stream format');
}

$streamId = (int)$matches[1];
$ext = $matches[2];

// Load environment
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

try {
    // Database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'cryonix_panel';
    $dbuser = $_ENV['DB_USER'] ?? 'cryonix_panel';
    $dbpass = $_ENV['DB_PASS'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Validate user
    $stmt = $pdo->prepare("SELECT * FROM `lines` WHERE username = ? AND password = ? AND status = 'active'");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied');
    }
    
    // Check expiry
    if ($user['exp_date'] && strtotime($user['exp_date']) < time()) {
        header('HTTP/1.1 403 Forbidden');
        die('Account expired');
    }
    
    // Check max connections
    $maxConn = (int)($user['max_connections'] ?? 1);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM connections WHERE line_id = ? AND is_active = 1");
    $stmt->execute([$user['id']]);
    $currentConn = (int)$stmt->fetchColumn();
    
    if ($currentConn >= $maxConn) {
        header('HTTP/1.1 429 Too Many Requests');
        die('Maximum connections reached');
    }
    
    // Get stream
    $stmt = $pdo->prepare("SELECT * FROM streams WHERE id = ? AND status = 'active'");
    $stmt->execute([$streamId]);
    $stream = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stream) {
        header('HTTP/1.1 404 Not Found');
        die('Stream not found');
    }
    
    $sourceUrl = $stream['stream_source'];
    
    if (empty($sourceUrl)) {
        header('HTTP/1.1 503 Service Unavailable');
        die('Stream source not configured');
    }
    
    // Log connection
    $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $stmt = $pdo->prepare("INSERT INTO connections (line_id, stream_id, ip_address, user_agent, is_active, started_at) VALUES (?, ?, ?, ?, 1, NOW())");
    $stmt->execute([$user['id'], $streamId, $clientIp, $userAgent]);
    $connectionId = $pdo->lastInsertId();
    
    // Register shutdown to mark connection inactive
    register_shutdown_function(function() use ($pdo, $connectionId) {
        try {
            $stmt = $pdo->prepare("UPDATE connections SET is_active = 0, ended_at = NOW() WHERE id = ?");
            $stmt->execute([$connectionId]);
        } catch (Exception $e) {}
    });
    
    // Proxy the stream
    header('Content-Type: video/mp2t');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('X-Accel-Buffering: no');
    
    // Use cURL to stream content
    $ch = curl_init($sourceUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'CryonixPanel/1.0',
        CURLOPT_HTTPHEADER => [
            'Accept: */*',
            'Connection: keep-alive'
        ],
        CURLOPT_WRITEFUNCTION => function($ch, $data) {
            echo $data;
            flush();
            return strlen($data);
        }
    ]);
    
    curl_exec($ch);
    curl_close($ch);
    
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    die('Database error');
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    die('Server error');
}


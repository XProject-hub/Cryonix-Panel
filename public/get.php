<?php
/**
 * Cryonix Panel - Playlist Generator
 * Generates M3U playlists for users
 * Copyright 2026 XProject-Hub
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

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

$username = $_GET['username'] ?? '';
$password = $_GET['password'] ?? '';
$type = $_GET['type'] ?? 'm3u_plus';
$output = $_GET['output'] ?? 'ts';

if (empty($username) || empty($password)) {
    header('HTTP/1.1 401 Unauthorized');
    die('Invalid credentials');
}

try {
    // Direct database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'cryonix_panel';
    $dbuser = $_ENV['DB_USER'] ?? 'cryonix_panel';
    $dbpass = $_ENV['DB_PASS'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Validate user
    $stmt = $pdo->prepare("SELECT * FROM `lines` WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('HTTP/1.1 401 Unauthorized');
        die('Invalid credentials');
    }
    
    // Check status
    if ($user['status'] !== 'active') {
        header('HTTP/1.1 403 Forbidden');
        die('Account disabled');
    }
    
    // Check expiry
    if ($user['exp_date'] && strtotime($user['exp_date']) < time()) {
        header('HTTP/1.1 403 Forbidden');
        die('Account expired');
    }
    
    // Get all streams
    $stmt = $pdo->query("
        SELECT s.*, c.category_name 
        FROM streams s 
        LEFT JOIN stream_categories c ON s.category_id = c.id
        WHERE s.status = 'active'
        ORDER BY c.category_name, s.sort_order
    ");
    $streams = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    $serverUrl = 'http://' . $_SERVER['HTTP_HOST'];
    
    // Generate playlist based on type
    switch ($type) {
        case 'm3u_plus':
        case 'm3u':
            header('Content-Type: application/x-mpegurl');
            header('Content-Disposition: attachment; filename="playlist.m3u"');
            
            echo "#EXTM3U\n";
            
            foreach ($streams as $stream) {
                $ext = ($output === 'hls') ? 'm3u8' : 'ts';
                $streamUrl = "{$serverUrl}/live/{$username}/{$password}/{$stream['id']}.{$ext}";
                $epgId = $stream['epg_channel_id'] ?? '';
                $name = $stream['stream_display_name'] ?? 'Channel';
                $logo = $stream['stream_icon'] ?? '';
                $group = $stream['category_name'] ?? 'Uncategorized';
                
                echo "#EXTINF:-1 tvg-id=\"{$epgId}\" tvg-name=\"{$name}\" tvg-logo=\"{$logo}\" group-title=\"{$group}\",{$name}\n";
                echo "{$streamUrl}\n";
            }
            break;
            
        case 'enigma2_script':
        case 'enigma216_script':
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="userbouquet.cryonix.tv"');
            
            echo "#NAME Cryonix IPTV\n";
            
            foreach ($streams as $stream) {
                $ext = ($output === 'hls') ? 'm3u8' : 'ts';
                $streamUrl = "{$serverUrl}/live/{$username}/{$password}/{$stream['id']}.{$ext}";
                $serviceName = str_replace([':', ' '], ['%3a', '%20'], $stream['stream_display_name'] ?? 'Channel');
                
                echo "#SERVICE 4097:0:1:0:0:0:0:0:0:0:{$streamUrl}:{$serviceName}\n";
                echo "#DESCRIPTION " . ($stream['stream_display_name'] ?? 'Channel') . "\n";
            }
            break;
            
        default:
            header('Content-Type: application/json');
            echo json_encode([
                'user_info' => [
                    'username' => $user['username'],
                    'status' => $user['status'],
                    'exp_date' => $user['exp_date'],
                    'max_connections' => $user['max_connections']
                ],
                'available_channels' => count($streams)
            ]);
    }
    
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    die('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    die('Error: ' . $e->getMessage());
}

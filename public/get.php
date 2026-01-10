<?php
/**
 * Cryonix Panel - Playlist Generator
 * Generates M3U playlists for users
 * Copyright 2026 XProject-Hub
 */

require_once __DIR__ . '/../vendor/autoload.php';
if (file_exists(__DIR__ . '/../.env')) {
    \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();
}

define('CRYONIX_ROOT', dirname(__DIR__));
require_once CRYONIX_ROOT . '/core/Database.php';

use CryonixPanel\Core\Database;

$username = $_GET['username'] ?? '';
$password = $_GET['password'] ?? '';
$type = $_GET['type'] ?? 'm3u_plus';
$output = $_GET['output'] ?? 'ts';

if (empty($username) || empty($password)) {
    header('HTTP/1.1 401 Unauthorized');
    die('Invalid credentials');
}

try {
    $db = Database::getInstance();
    
    // Validate user
    $user = $db->fetch("SELECT * FROM `lines` WHERE username = ? AND password = ? AND status = 'active'", [$username, $password]);
    
    if (!$user) {
        header('HTTP/1.1 401 Unauthorized');
        die('Invalid credentials or account expired');
    }
    
    // Check expiry
    if ($user['exp_date'] && strtotime($user['exp_date']) < time()) {
        header('HTTP/1.1 403 Forbidden');
        die('Account expired');
    }
    
    // Get user's bouquets
    $bouquetIds = json_decode($user['bouquet'] ?? '[]', true) ?: [];
    
    // Get all streams (or filtered by bouquet)
    if (!empty($bouquetIds)) {
        $placeholders = implode(',', array_fill(0, count($bouquetIds), '?'));
        $streams = $db->fetchAll("
            SELECT s.*, c.category_name 
            FROM streams s 
            LEFT JOIN stream_categories c ON s.category_id = c.id
            LEFT JOIN bouquet_streams bs ON s.id = bs.stream_id
            WHERE bs.bouquet_id IN ($placeholders) AND s.status = 'active'
            ORDER BY c.category_name, s.sort_order
        ", $bouquetIds) ?: [];
    } else {
        $streams = $db->fetchAll("
            SELECT s.*, c.category_name 
            FROM streams s 
            LEFT JOIN stream_categories c ON s.category_id = c.id
            WHERE s.status = 'active'
            ORDER BY c.category_name, s.sort_order
        ") ?: [];
    }
    
    $serverUrl = 'http://' . $_SERVER['HTTP_HOST'];
    $serverPort = $_SERVER['SERVER_PORT'] ?? 8080;
    
    // Generate playlist based on type
    switch ($type) {
        case 'm3u_plus':
        case 'm3u':
            header('Content-Type: application/x-mpegurl');
            header('Content-Disposition: attachment; filename="playlist.m3u"');
            
            echo "#EXTM3U\n";
            
            foreach ($streams as $stream) {
                $ext = ($output === 'hls') ? 'm3u8' : 'ts';
                $streamUrl = "{$serverUrl}:{$serverPort}/live/{$username}/{$password}/{$stream['id']}.{$ext}";
                
                echo "#EXTINF:-1 tvg-id=\"{$stream['epg_channel_id']}\" tvg-name=\"{$stream['stream_display_name']}\" tvg-logo=\"{$stream['stream_icon']}\" group-title=\"{$stream['category_name']}\",{$stream['stream_display_name']}\n";
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
                $streamUrl = "{$serverUrl}:{$serverPort}/live/{$username}/{$password}/{$stream['id']}.{$ext}";
                $serviceName = str_replace([':', ' '], ['%3a', '%20'], $stream['stream_display_name']);
                
                echo "#SERVICE 4097:0:1:0:0:0:0:0:0:0:{$streamUrl}:{$serviceName}\n";
                echo "#DESCRIPTION {$stream['stream_display_name']}\n";
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
    
} catch (\Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    die('Error: ' . $e->getMessage());
}


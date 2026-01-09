<?php
/**
 * Cryonix Panel - IPTV Management Panel
 * Application Configuration
 * Copyright 2026 XProject-Hub
 */

return [
    'name' => 'Cryonix Panel',
    'version' => '1.0.0',
    'url' => getenv('APP_URL') ?: 'http://localhost',
    'timezone' => 'UTC',
    'debug' => getenv('APP_DEBUG') === 'true',
    'secret_key' => getenv('APP_SECRET') ?: 'change-this-secret',
    
    // Cloud licensing server
    'license_server' => 'https://cloud.cryonix.io',
    'license_check_interval' => 3600, // 1 hour
    'grace_period_hours' => 72,
    
    // Session
    'session_lifetime' => 7200,
    
    // Default admin (created on first install)
    'default_admin' => [
        'username' => 'admin',
        'password' => 'Cryonix',
        'email' => 'admin@localhost'
    ],
    
    // Stream settings
    'stream_path' => '/streams',
    'hls_segment_time' => 10,
    'hls_list_size' => 5,
];


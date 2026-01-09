<?php
/**
 * Cryonix Panel - Database Configuration
 * Copyright 2026 XProject-Hub
 */

return [
    'driver' => 'mysql',
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: 3306,
    'database' => getenv('DB_NAME') ?: 'cryonix_panel',
    'username' => getenv('DB_USER') ?: 'cryonix',
    'password' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];


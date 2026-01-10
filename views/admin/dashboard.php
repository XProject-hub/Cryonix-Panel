<?php
/**
 * Cryonix Panel - Admin Dashboard
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Dashboard';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$stats = [
    'users' => 0,
    'channels' => 0,
    'movies' => 0,
    'series' => 0,
    'active' => 0
];

try {
    $db = Database::getInstance();
    $stats['users'] = $db->count('lines');
    $stats['channels'] = $db->count('streams');
    $stats['movies'] = $db->count('movies');
    $stats['series'] = $db->count('series');
    $stats['active'] = $db->count('user_activity', 'ended_at IS NULL');
} catch (\Exception $e) {
    // Database not ready
}

ob_start();
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-white">Dashboard</h1>
    <p class="text-gray-400">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
    <div class="glass rounded-xl p-6 border border-gray-800/50">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-white"><?= number_format($stats['users']) ?></div>
                <div class="text-sm text-gray-400">Total Users</div>
            </div>
        </div>
    </div>
    
    <div class="glass rounded-xl p-6 border border-gray-800/50">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-white"><?= number_format($stats['channels']) ?></div>
                <div class="text-sm text-gray-400">Live Channels</div>
            </div>
        </div>
    </div>
    
    <div class="glass rounded-xl p-6 border border-gray-800/50">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-white"><?= number_format($stats['movies']) ?></div>
                <div class="text-sm text-gray-400">Movies</div>
            </div>
        </div>
    </div>
    
    <div class="glass rounded-xl p-6 border border-gray-800/50">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-white"><?= number_format($stats['series']) ?></div>
                <div class="text-sm text-gray-400">Series</div>
            </div>
        </div>
    </div>
    
    <div class="glass rounded-xl p-6 border border-cyan-500/30 bg-gradient-to-br from-cyan-500/5 to-transparent">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-cyan-400"><?= number_format($stats['active']) ?></div>
                <div class="text-sm text-gray-400">Active Now</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="glass rounded-xl p-6 border border-gray-800/50">
        <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 gap-3">
            <a href="<?= ADMIN_PATH ?>/users/add" class="flex items-center gap-3 p-3 rounded-lg bg-dark-800 hover:bg-dark-900 transition">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <span class="text-sm text-gray-300">Add User</span>
            </a>
            <a href="<?= ADMIN_PATH ?>/streams/add" class="flex items-center gap-3 p-3 rounded-lg bg-dark-800 hover:bg-dark-900 transition">
                <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <span class="text-sm text-gray-300">Add Channel</span>
            </a>
            <a href="<?= ADMIN_PATH ?>/movies/add" class="flex items-center gap-3 p-3 rounded-lg bg-dark-800 hover:bg-dark-900 transition">
                <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <span class="text-sm text-gray-300">Add Movie</span>
            </a>
            <a href="<?= ADMIN_PATH ?>/settings" class="flex items-center gap-3 p-3 rounded-lg bg-dark-800 hover:bg-dark-900 transition">
                <div class="w-10 h-10 rounded-lg bg-gray-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                <span class="text-sm text-gray-300">Settings</span>
            </a>
        </div>
    </div>
    
    <div class="glass rounded-xl p-6 border border-gray-800/50">
        <h2 class="text-lg font-semibold text-white mb-4">System Info</h2>
        <div class="space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Panel Version</span>
                <span class="text-white font-mono"><?= file_exists(CRYONIX_ROOT . '/VERSION') ? trim(file_get_contents(CRYONIX_ROOT . '/VERSION')) : '1.0.0' ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">PHP Version</span>
                <span class="text-white font-mono"><?= phpversion() ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Server</span>
                <span class="text-white font-mono"><?= php_uname('n') ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Server Time</span>
                <span class="text-white font-mono"><?= date('Y-m-d H:i:s') ?></span>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


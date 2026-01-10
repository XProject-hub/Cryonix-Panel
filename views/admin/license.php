<?php
/**
 * Cryonix Panel - License Management
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'License';
$adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/admin';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$license = null;
$error = '';
$success = '';

try {
    $db = Database::getInstance();
    $license = $db->fetch("SELECT * FROM license_info ORDER BY id DESC LIMIT 1");
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Get license key from .env if not in DB
if (!$license) {
    $envKey = $_ENV['LICENSE_KEY'] ?? '';
}

ob_start();
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-white">License</h1>
    <p class="text-gray-400">Manage your Cryonix Panel license</p>
</div>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400">
    <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<?php if ($license && $license['status'] === 'active'): ?>
<!-- Active License -->
<div class="glass rounded-xl p-6 border border-green-500/30 bg-gradient-to-br from-green-500/5 to-transparent mb-6">
    <div class="flex items-center gap-4 mb-4">
        <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <div>
            <div class="text-lg font-bold text-green-400">License Active</div>
            <div class="text-sm text-gray-400">Your panel is fully licensed</div>
        </div>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <div class="text-xs text-gray-500 uppercase">License Key</div>
            <div class="text-sm text-white font-mono"><?= htmlspecialchars(substr($license['license_key'], 0, 15)) ?>...</div>
        </div>
        <div>
            <div class="text-xs text-gray-500 uppercase">Max Connections</div>
            <div class="text-sm text-white"><?= number_format($license['max_connections']) ?></div>
        </div>
        <div>
            <div class="text-xs text-gray-500 uppercase">Max Channels</div>
            <div class="text-sm text-white"><?= number_format($license['max_channels']) ?></div>
        </div>
        <div>
            <div class="text-xs text-gray-500 uppercase">Expires</div>
            <div class="text-sm text-white"><?= date('M d, Y', strtotime($license['expires_at'])) ?></div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- No License - Activation Form -->
<div class="glass rounded-xl p-6 border border-amber-500/30 bg-gradient-to-br from-amber-500/5 to-transparent mb-6">
    <div class="flex items-center gap-4 mb-4">
        <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center">
            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <div class="text-lg font-bold text-amber-400">License Required</div>
            <div class="text-sm text-gray-400">Activate your license to use all features</div>
        </div>
    </div>
    
    <form action="<?= $adminPath ?>/license/activate" method="POST" class="mt-4">
        <div class="flex gap-4">
            <input type="text" name="license_key" placeholder="CRYX-XXXX-XXXX-XXXX-XXXX" value="<?= htmlspecialchars($envKey ?? '') ?>" required 
                class="flex-1 px-4 py-3 rounded-lg bg-dark-900 border border-gray-800 text-white font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/50">
            <button type="submit" class="px-6 py-3 rounded-lg bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">
                Activate License
            </button>
        </div>
    </form>
    
    <p class="mt-4 text-sm text-gray-500">
        Don't have a license? <a href="https://cryonix.io/pricing" target="_blank" class="text-cyan-400 hover:text-cyan-300">Purchase one here</a>
    </p>
</div>
<?php endif; ?>

<!-- Info Card -->
<div class="glass rounded-xl p-6 border border-gray-800/50">
    <h2 class="text-lg font-semibold text-white mb-4">License Information</h2>
    <div class="space-y-3 text-sm">
        <p class="text-gray-400">Your license is tied to this server's IP address and machine ID. If you need to transfer your license to a new server, please contact support.</p>
        <p class="text-gray-400">
            <strong class="text-white">Server IP:</strong> <?= htmlspecialchars($_SERVER['SERVER_ADDR'] ?? shell_exec("hostname -I | awk '{print $1}'")) ?>
        </p>
        <p class="text-gray-400">
            <strong class="text-white">Support:</strong> <a href="mailto:support@cryonix.io" class="text-cyan-400">support@cryonix.io</a>
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


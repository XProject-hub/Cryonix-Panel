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

$envKey = $_ENV['LICENSE_KEY'] ?? '';
$isActive = $license && $license['status'] === 'active';
$expiresAt = $license ? strtotime($license['expires_at']) : 0;
$daysLeft = $expiresAt > time() ? ceil(($expiresAt - time()) / 86400) : 0;

ob_start();
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-white">License</h1>
    <p class="text-gray-400">Your Cryonix Panel license status</p>
</div>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if ($isActive): ?>
<!-- Active License -->
<div class="glass rounded-2xl p-8 border border-green-500/30 bg-gradient-to-br from-green-500/5 to-transparent mb-6">
    <div class="flex items-center gap-6">
        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-green-500/20">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <div class="flex-1">
            <div class="text-2xl font-bold text-green-400 mb-1">License Active</div>
            <div class="text-gray-400">Your panel is fully licensed and operational</div>
        </div>
    </div>
    
    <div class="mt-8 p-6 rounded-xl bg-dark-900/50 border border-gray-800/50">
        <div class="grid grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-gray-500 text-sm mb-1">Status</div>
                <div class="text-green-400 font-bold text-lg">ACTIVE</div>
            </div>
            <div class="text-center border-x border-gray-800">
                <div class="text-gray-500 text-sm mb-1">Expires</div>
                <div class="text-white font-bold text-lg"><?= date('M d, Y', $expiresAt) ?></div>
            </div>
            <div class="text-center">
                <div class="text-gray-500 text-sm mb-1">Days Remaining</div>
                <div class="font-bold text-lg <?= $daysLeft < 30 ? 'text-amber-400' : 'text-white' ?>"><?= $daysLeft ?> days</div>
            </div>
        </div>
    </div>
    
    <?php if ($daysLeft < 30): ?>
    <div class="mt-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="text-amber-300">Your license expires soon! <a href="https://cryonix.io/pricing" target="_blank" class="underline hover:text-amber-200">Renew now</a></span>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- No License -->
<div class="glass rounded-2xl p-8 border border-amber-500/30 bg-gradient-to-br from-amber-500/5 to-transparent mb-6">
    <div class="flex items-center gap-6 mb-6">
        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/20">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-amber-400 mb-1">License Required</div>
            <div class="text-gray-400">Activate your license to unlock all features</div>
        </div>
    </div>
    
    <form action="<?= $adminPath ?>/license/activate" method="POST">
        <div class="flex gap-4">
            <input type="text" name="license_key" placeholder="CRYX-XXXX-XXXX-XXXX-XXXX" value="<?= htmlspecialchars($envKey) ?>" required 
                class="flex-1 px-5 py-4 rounded-xl bg-dark-900 border border-gray-800 text-white text-lg font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/50 placeholder-gray-600">
            <button type="submit" class="px-8 py-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold hover:from-amber-600 hover:to-orange-600 transition shadow-lg shadow-amber-500/20">
                Activate
            </button>
        </div>
    </form>
    
    <p class="mt-6 text-gray-500 text-center">
        Don't have a license? <a href="https://cryonix.io/pricing" target="_blank" class="text-cyan-400 hover:text-cyan-300">Purchase one here →</a>
    </p>
</div>
<?php endif; ?>

<!-- Support Info -->
<div class="glass rounded-xl p-6 border border-gray-800/50">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center">
            <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div>
            <div class="text-white font-semibold">Need Help?</div>
            <div class="text-gray-400 text-sm">Contact us at <a href="mailto:support@cryonix.io" class="text-cyan-400">support@cryonix.io</a></div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>

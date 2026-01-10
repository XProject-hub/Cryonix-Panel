<?php
/**
 * Cryonix Panel - Settings
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Settings';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$settings = [];
$error = '';
$success = '';

try {
    $db = Database::getInstance();
    $rows = $db->fetchAll("SELECT * FROM settings") ?: [];
    foreach ($rows as $row) {
        $settings[$row['key']] = $row['value'];
    }
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

ob_start();
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-white">Settings</h1>
    <p class="text-gray-400">Configure your Cryonix Panel</p>
</div>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="space-y-6">
    <!-- General Settings -->
    <div class="glass rounded-xl p-6 border border-gray-800/50">
        <h2 class="text-lg font-semibold text-white mb-4">General Settings</h2>
        <form method="POST" action="<?= ADMIN_PATH ?>/settings/save" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Site Name</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'Cryonix IPTV') ?>" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Timezone</label>
                    <input type="text" name="timezone" value="<?= htmlspecialchars($settings['timezone'] ?? 'UTC') ?>" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
                </div>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-cyan-500 text-white font-semibold hover:bg-cyan-600 transition">
                Save Settings
            </button>
        </form>
    </div>
    
    <!-- Stream Settings -->
    <div class="glass rounded-xl p-6 border border-gray-800/50">
        <h2 class="text-lg font-semibold text-white mb-4">Stream Settings</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Buffer Size</label>
                <input type="text" value="<?= htmlspecialchars($settings['stream_buffer_size'] ?? '8192') ?>" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Max Connections Per User</label>
                <input type="text" value="<?= htmlspecialchars($settings['max_connections_per_user'] ?? '1') ?>" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50" readonly>
            </div>
        </div>
    </div>
    
    <!-- Update System -->
    <div class="glass rounded-xl p-6 border border-amber-500/30 bg-gradient-to-r from-amber-500/5 to-orange-500/5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white mb-1">System Updates</h2>
                <p class="text-gray-400 text-sm">Current version: <?= file_exists(CRYONIX_ROOT . '/VERSION') ? trim(file_get_contents(CRYONIX_ROOT . '/VERSION')) : '1.0.0' ?></p>
            </div>
            <button onclick="checkForUpdates()" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold hover:from-amber-600 hover:to-orange-600 transition">
                Check for Updates
            </button>
        </div>
    </div>
</div>

<script>
function checkForUpdates() {
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Checking...';
    
    fetch('<?= ADMIN_PATH ?>/update/check')
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Check for Updates';
            
            if (data.available) {
                if (confirm('Update available! v' + data.current + ' → v' + data.latest + '\n\nUpdate now?')) {
                    applyUpdate();
                }
            } else {
                alert('You are on the latest version!');
            }
        })
        .catch(e => {
            btn.disabled = false;
            btn.textContent = 'Check for Updates';
            alert('Error: ' + e.message);
        });
}

function applyUpdate() {
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-black/80 flex items-center justify-center z-50';
    overlay.innerHTML = '<div class="bg-gray-900 rounded-xl p-8 text-center"><div class="w-12 h-12 animate-spin border-4 border-amber-500 border-t-transparent rounded-full mx-auto mb-4"></div><p class="text-white text-lg">Updating...</p></div>';
    document.body.appendChild(overlay);
    
    fetch('<?= ADMIN_PATH ?>/update/apply', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                overlay.innerHTML = '<div class="bg-gray-900 rounded-xl p-8 text-center"><p class="text-green-400 text-lg">✓ Update Complete!</p><p class="text-gray-400 mt-2">Refreshing...</p></div>';
                setTimeout(() => location.reload(), 1500);
            } else {
                overlay.remove();
                alert('Update failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(e => {
            overlay.remove();
            alert('Update failed: ' + e.message);
        });
}
</script>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


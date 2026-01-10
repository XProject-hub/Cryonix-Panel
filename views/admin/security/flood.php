<?php
$pageTitle = 'Logins Flood Protection';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$floodLogs = [];
try { $db = Database::getInstance(); $floodLogs = $db->fetchAll("SELECT * FROM flood_logs ORDER BY created_at DESC LIMIT 100") ?: []; } catch (\Exception $e) {}
ob_start();
?>
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">Logins Flood Protection</h1>
    <button class="px-3 py-1.5 rounded-lg bg-red-500 text-white text-xs">Clear All</button>
</div>
<div class="grid grid-cols-4 gap-4 mb-4">
    <div class="glass rounded-lg p-4 border border-gray-800/50"><div class="text-gray-500 text-xs">Max Attempts</div><div class="text-2xl font-bold text-white">5</div></div>
    <div class="glass rounded-lg p-4 border border-gray-800/50"><div class="text-gray-500 text-xs">Block Duration</div><div class="text-2xl font-bold text-white">30 min</div></div>
    <div class="glass rounded-lg p-4 border border-gray-800/50"><div class="text-gray-500 text-xs">Blocked Today</div><div class="text-2xl font-bold text-red-400"><?= count($floodLogs) ?></div></div>
    <div class="glass rounded-lg p-4 border border-gray-800/50"><div class="text-gray-500 text-xs">Status</div><div class="text-lg font-bold text-green-400">Active</div></div>
</div>
<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-dark-800/50 text-gray-500 text-xs uppercase"><tr><th class="px-4 py-3 text-left">IP Address</th><th class="px-4 py-3 text-left">Attempts</th><th class="px-4 py-3 text-left">Last Attempt</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php if (empty($floodLogs)): ?><tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No flood attempts detected</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


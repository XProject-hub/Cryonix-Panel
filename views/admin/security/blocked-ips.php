<?php
$pageTitle = 'Blocked IPs';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$blockedIps = [];
try { $db = Database::getInstance(); $blockedIps = $db->fetchAll("SELECT * FROM blocked_ips ORDER BY created_at DESC") ?: []; } catch (\Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ip'])) {
    $ip = $_POST['ip'];
    $reason = $_POST['reason'] ?? 'Manual block';
    try { $db->insert('blocked_ips', ['ip' => $ip, 'reason' => $reason, 'created_at' => date('Y-m-d H:i:s')]); header('Location: ' . ADMIN_PATH . '/security/blocked-ips'); exit; } catch (\Exception $e) {}
}
ob_start();
?>
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">Blocked IP's</h1>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-3 py-1.5 rounded-lg bg-red-500 text-white text-xs">+ Block IP</button>
</div>
<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-dark-800/50 text-gray-500 text-xs uppercase"><tr><th class="px-4 py-3 text-left">IP Address</th><th class="px-4 py-3 text-left">Reason</th><th class="px-4 py-3 text-left">Blocked At</th><th class="px-4 py-3 text-left">Actions</th></tr></thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php if (empty($blockedIps)): ?><tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No blocked IPs</td></tr><?php else: foreach ($blockedIps as $b): ?>
            <tr class="hover:bg-dark-800/30">
                <td class="px-4 py-3"><code class="text-red-400"><?= htmlspecialchars($b['ip']) ?></code></td>
                <td class="px-4 py-3 text-gray-400"><?= htmlspecialchars($b['reason']) ?></td>
                <td class="px-4 py-3 text-gray-500 text-xs"><?= $b['created_at'] ?></td>
                <td class="px-4 py-3"><a href="#" class="text-green-400 text-xs">Unblock</a></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<div id="addModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <form method="POST" class="glass rounded-xl p-6 border border-gray-800 w-96">
        <h2 class="text-lg font-bold text-white mb-4">Block IP Address</h2>
        <div class="space-y-3">
            <div><label class="block text-xs text-gray-400 mb-1">IP Address</label><input type="text" name="ip" required class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm" placeholder="192.168.1.1"></div>
            <div><label class="block text-xs text-gray-400 mb-1">Reason</label><input type="text" name="reason" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm" placeholder="Suspicious activity"></div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
            <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 rounded bg-gray-800 text-gray-400 text-xs">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded bg-red-500 text-white text-xs">Block</button>
        </div>
    </form>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


<?php
$pageTitle = 'Login Logs';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$logs = [];
try { $db = Database::getInstance(); $logs = $db->fetchAll("SELECT * FROM login_logs ORDER BY created_at DESC LIMIT 100") ?: []; } catch (\Exception $e) {}
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Login Logs</h1></div>
<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full text-xs"><thead class="bg-dark-800/50 text-gray-500 uppercase"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Username</th><th class="px-4 py-3 text-left">IP</th><th class="px-4 py-3 text-left">User Agent</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
    <tbody class="divide-y divide-gray-800/50"><?php if (empty($logs)): ?><tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No login logs</td></tr><?php else: foreach ($logs as $l): ?>
    <tr class="hover:bg-dark-800/30"><td class="px-4 py-2 text-gray-500"><?= $l['created_at'] ?></td><td class="px-4 py-2 text-white"><?= htmlspecialchars($l['username'] ?? '-') ?></td><td class="px-4 py-2 text-cryo-400 font-mono"><?= htmlspecialchars($l['ip'] ?? '-') ?></td><td class="px-4 py-2 text-gray-400 text-[10px] max-w-xs truncate"><?= htmlspecialchars($l['user_agent'] ?? '-') ?></td><td class="px-4 py-2"><span class="px-1.5 py-0.5 rounded text-[10px] <?= ($l['status'] ?? '') === 'success' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' ?>"><?= $l['status'] ?? '-' ?></span></td></tr>
    <?php endforeach; endif; ?></tbody></table>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


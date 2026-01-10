<?php
$pageTitle = 'Stream Logs';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$logs = [];
try { $db = Database::getInstance(); $logs = $db->fetchAll("SELECT * FROM stream_logs ORDER BY created_at DESC LIMIT 100") ?: []; } catch (\Exception $e) {}
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Stream Logs</h1></div>
<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full text-xs"><thead class="bg-dark-800/50 text-gray-500 uppercase"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Stream</th><th class="px-4 py-3 text-left">Action</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
    <tbody class="divide-y divide-gray-800/50"><?php if (empty($logs)): ?><tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No stream logs</td></tr><?php else: foreach ($logs as $l): ?>
    <tr class="hover:bg-dark-800/30"><td class="px-4 py-2 text-gray-500"><?= $l['created_at'] ?></td><td class="px-4 py-2 text-white"><?= htmlspecialchars($l['stream_name'] ?? '-') ?></td><td class="px-4 py-2 text-cryo-400"><?= htmlspecialchars($l['action'] ?? '-') ?></td><td class="px-4 py-2"><span class="px-1.5 py-0.5 rounded text-[10px] <?= ($l['status'] ?? '') === 'success' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' ?>"><?= $l['status'] ?? '-' ?></span></td></tr>
    <?php endforeach; endif; ?></tbody></table>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


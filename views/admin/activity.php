<?php
/**
 * Cryonix Panel - Activity Logs
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Activity Logs';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$logs = [];

try {
    $db = Database::getInstance();
    $logs = $db->fetchAll("
        SELECT ua.*, l.username, s.stream_display_name
        FROM user_activity ua
        LEFT JOIN `lines` l ON ua.user_id = l.id
        LEFT JOIN streams s ON ua.stream_id = s.id
        ORDER BY ua.started_at DESC
        LIMIT 500
    ") ?: [];
} catch (\Exception $e) {
    // Table might not exist
}

ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-white">Activity Logs</h1>
        <p class="text-gray-500 text-sm">User streaming activity history</p>
    </div>
    <div class="flex items-center gap-2">
        <input type="text" placeholder="Search user..." class="px-3 py-1.5 rounded-lg bg-dark-800 border border-gray-800 text-white text-xs w-48 focus:outline-none focus:border-cryo-500">
        <button class="px-3 py-1.5 rounded-lg bg-dark-800 text-gray-400 hover:text-white text-xs">Filter</button>
    </div>
</div>

<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-dark-800/50">
            <tr class="text-left text-gray-500 text-xs uppercase">
                <th class="px-4 py-3">User</th>
                <th class="px-4 py-3">Stream</th>
                <th class="px-4 py-3">IP Address</th>
                <th class="px-4 py-3">Started</th>
                <th class="px-4 py-3">Ended</th>
                <th class="px-4 py-3">Duration</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php if (empty($logs)): ?>
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No activity logs yet</td>
            </tr>
            <?php else: ?>
            <?php foreach ($logs as $log): 
                $isActive = empty($log['ended_at']);
                $duration = $isActive 
                    ? gmdate('H:i:s', time() - strtotime($log['started_at']))
                    : gmdate('H:i:s', strtotime($log['ended_at']) - strtotime($log['started_at']));
            ?>
            <tr class="hover:bg-dark-800/30 transition">
                <td class="px-4 py-3">
                    <span class="text-white font-medium"><?= htmlspecialchars($log['username'] ?? 'Unknown') ?></span>
                </td>
                <td class="px-4 py-3 text-gray-400"><?= htmlspecialchars($log['stream_display_name'] ?? 'N/A') ?></td>
                <td class="px-4 py-3">
                    <code class="text-xs bg-dark-900 px-2 py-0.5 rounded text-gray-400"><?= htmlspecialchars($log['ip_address'] ?? '') ?></code>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs"><?= date('M d, H:i', strtotime($log['started_at'])) ?></td>
                <td class="px-4 py-3 text-gray-500 text-xs"><?= $isActive ? '-' : date('H:i:s', strtotime($log['ended_at'])) ?></td>
                <td class="px-4 py-3 text-gray-400 text-xs font-mono"><?= $duration ?></td>
                <td class="px-4 py-3">
                    <?php if ($isActive): ?>
                    <span class="px-2 py-0.5 rounded bg-green-500/10 text-green-400 text-xs">Active</span>
                    <?php else: ?>
                    <span class="px-2 py-0.5 rounded bg-gray-800 text-gray-500 text-xs">Ended</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


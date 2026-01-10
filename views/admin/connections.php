<?php
/**
 * Cryonix Panel - Live Connections
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Live Connections';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$connections = [];
$totalConnections = 0;

try {
    $db = Database::getInstance();
    $connections = $db->fetchAll("
        SELECT ua.*, l.username, l.max_connections, s.stream_display_name, srv.server_name
        FROM user_activity ua
        LEFT JOIN `lines` l ON ua.user_id = l.id
        LEFT JOIN streams s ON ua.stream_id = s.id
        LEFT JOIN servers srv ON ua.server_id = srv.id
        WHERE ua.ended_at IS NULL
        ORDER BY ua.started_at DESC
        LIMIT 500
    ") ?: [];
    $totalConnections = count($connections);
} catch (\Exception $e) {
    // Table might not exist yet
}

ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-white">Live Connections</h1>
        <p class="text-gray-500 text-sm">Real-time active connections</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="px-3 py-1.5 rounded-lg bg-green-500/10 text-green-400 text-sm font-semibold">
            <?= $totalConnections ?> Active
        </span>
        <button onclick="location.reload()" class="px-3 py-1.5 rounded-lg bg-dark-800 text-gray-400 hover:text-white text-xs transition">
            ↻ Refresh
        </button>
    </div>
</div>

<?php if (empty($connections)): ?>
<div class="glass rounded-xl p-12 border border-gray-800/50 text-center">
    <div class="text-4xl mb-3">📡</div>
    <p class="text-gray-400">No active connections</p>
    <p class="text-gray-600 text-sm mt-1">Connections will appear here when users start streaming</p>
</div>
<?php else: ?>
<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-dark-800/50">
            <tr class="text-left text-gray-500 text-xs uppercase">
                <th class="px-4 py-3">User</th>
                <th class="px-4 py-3">Stream</th>
                <th class="px-4 py-3">Server</th>
                <th class="px-4 py-3">IP Address</th>
                <th class="px-4 py-3">User Agent</th>
                <th class="px-4 py-3">Started</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php foreach ($connections as $conn): ?>
            <tr class="hover:bg-dark-800/30 transition">
                <td class="px-4 py-3">
                    <span class="text-white font-medium"><?= htmlspecialchars($conn['username'] ?? 'Unknown') ?></span>
                </td>
                <td class="px-4 py-3 text-gray-400"><?= htmlspecialchars($conn['stream_display_name'] ?? 'N/A') ?></td>
                <td class="px-4 py-3 text-gray-400"><?= htmlspecialchars($conn['server_name'] ?? 'Main') ?></td>
                <td class="px-4 py-3">
                    <code class="text-xs bg-dark-900 px-2 py-0.5 rounded text-gray-400"><?= htmlspecialchars($conn['ip_address'] ?? '') ?></code>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate"><?= htmlspecialchars(substr($conn['user_agent'] ?? '', 0, 40)) ?>...</td>
                <td class="px-4 py-3 text-gray-500 text-xs"><?= date('H:i:s', strtotime($conn['started_at'])) ?></td>
                <td class="px-4 py-3">
                    <button onclick="killConnection(<?= $conn['id'] ?>)" class="text-red-400 hover:text-red-300 text-xs">Kill</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function killConnection(id) {
    if (confirm('Kill this connection?')) {
        fetch('<?= ADMIN_PATH ?>/api/kill-connection', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        }).then(() => location.reload());
    }
}
// Auto-refresh every 10 seconds
setTimeout(() => location.reload(), 10000);
</script>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


<?php
/**
 * Cryonix Panel - Servers Management
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Servers';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$servers = [];
$error = '';

try {
    $db = Database::getInstance();
    $servers = $db->fetchAll("SELECT * FROM servers ORDER BY is_main DESC, server_name") ?: [];
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-white">Servers</h1>
        <p class="text-gray-400">Manage your streaming servers and load balancers</p>
    </div>
    <a href="<?= ADMIN_PATH ?>/servers/add" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold hover:from-cyan-600 hover:to-blue-600 transition">
        + Add Load Balancer
    </a>
</div>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-dark-800">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Server</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">IP Address</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Connections</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Bandwidth</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php foreach ($servers as $server): ?>
            <tr class="hover:bg-dark-800/50 transition">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full <?= $server['status'] === 'online' ? 'bg-green-400' : 'bg-red-400' ?>"></span>
                        <span class="text-white font-medium"><?= htmlspecialchars($server['server_name']) ?></span>
                        <?php if ($server['is_main']): ?>
                        <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 text-xs">MAIN</span>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="px-4 py-3 font-mono text-gray-400"><?= htmlspecialchars($server['server_ip']) ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full <?= $server['status'] === 'online' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' ?> text-xs">
                        <?= ucfirst($server['status']) ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-400"><?= number_format($server['current_connections'] ?? 0) ?></td>
                <td class="px-4 py-3 text-gray-400">
                    <span class="text-cyan-400">↓<?= number_format($server['bandwidth_in'] ?? 0) ?></span> / 
                    <span class="text-orange-400">↑<?= number_format($server['bandwidth_out'] ?? 0) ?></span> Mbps
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="<?= ADMIN_PATH ?>/servers/edit/<?= $server['id'] ?>" class="text-cyan-400 hover:text-cyan-300 text-sm mr-3">Edit</a>
                    <?php if (!$server['is_main']): ?>
                    <a href="<?= ADMIN_PATH ?>/servers/delete/<?= $server['id'] ?>" onclick="return confirm('Delete this server?')" class="text-red-400 hover:text-red-300 text-sm">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($servers)): ?>
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    No servers configured. The main server will be created automatically when you visit the dashboard.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-6 glass rounded-xl p-6 border border-gray-800/50">
    <h3 class="text-white font-semibold mb-3">About Load Balancers</h3>
    <p class="text-gray-400 text-sm">
        Load balancers distribute streaming traffic across multiple servers. Add additional servers to scale your IPTV service and handle more concurrent connections.
        Each load balancer server needs the Cryonix Agent installed to report statistics back to this panel.
    </p>
</div>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


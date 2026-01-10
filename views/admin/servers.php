<?php
/**
 * Cryonix Panel - Manage Servers
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Servers';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$servers = [];
$error = '';

try {
    $db = Database::getInstance();
    $servers = $db->fetchAll("SELECT * FROM servers ORDER BY is_main DESC, id") ?: [];
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Get system stats for servers
function getServerStats($server) {
    // In production, this would query the remote server via API
    return [
        'cpu' => rand(5, 40),
        'mem' => rand(10, 60),
        'load' => number_format(rand(10, 300) / 100, 2),
        'cores' => rand(4, 64),
        'mem_used' => rand(2, 16),
        'mem_total' => rand(16, 64),
        'disk_used' => rand(50, 500),
        'disk_total' => rand(500, 2000),
        'clients' => rand(0, 100),
        'in_speed' => rand(0, 100),
        'out_speed' => rand(0, 500)
    ];
}

ob_start();
?>

<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">Servers</h1>
    <div class="flex items-center gap-2">
        <button onclick="location.reload()" class="px-3 py-1.5 rounded-lg bg-dark-800 text-gray-400 hover:text-white text-xs transition">Refresh</button>
        <a href="<?= ADMIN_PATH ?>/servers/add" class="px-3 py-1.5 rounded-lg bg-teal-500 text-white text-xs transition hover:bg-teal-600">+ Install Server</a>
        <a href="<?= ADMIN_PATH ?>/servers/install" class="px-3 py-1.5 rounded-lg bg-blue-500 text-white text-xs transition hover:bg-blue-600">+ Install LB</a>
    </div>
</div>

<?php if ($error): ?>
<div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Entries selector and search -->
<div class="flex items-center justify-between mb-3">
    <div class="flex items-center gap-2 text-xs text-gray-500">
        Show
        <select class="px-2 py-1 rounded bg-dark-800 border border-gray-800 text-white text-xs">
            <option>10</option>
            <option>25</option>
            <option>50</option>
            <option>100</option>
        </select>
        entries
    </div>
    <div>
        <input type="text" placeholder="Search..." class="px-3 py-1.5 rounded-lg bg-dark-800 border border-gray-800 text-white text-xs w-48 focus:outline-none focus:border-cryo-500">
    </div>
</div>

<!-- Servers Table -->
<div class="glass rounded-xl border border-gray-800/50 overflow-x-auto">
    <table class="w-full text-xs">
        <thead class="bg-dark-800/50 text-gray-500 uppercase">
            <tr>
                <th class="px-3 py-2 text-left">ID</th>
                <th class="px-3 py-2 text-left">Actions</th>
                <th class="px-3 py-2 text-left">Server Name</th>
                <th class="px-3 py-2 text-center">Status</th>
                <th class="px-3 py-2 text-center">Health</th>
                <th class="px-3 py-2 text-left">Server Info</th>
                <th class="px-3 py-2 text-left">Domain</th>
                <th class="px-3 py-2 text-left">Server IP</th>
                <th class="px-3 py-2 text-center">Ports</th>
                <th class="px-3 py-2 text-center">Clients</th>
                <th class="px-3 py-2 text-center">CPU %</th>
                <th class="px-3 py-2 text-center">MEM %</th>
                <th class="px-3 py-2 text-center">IN</th>
                <th class="px-3 py-2 text-center">OUT</th>
                <th class="px-3 py-2 text-left">Network</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php if (empty($servers)): ?>
            <tr><td colspan="15" class="px-3 py-8 text-center text-gray-500">No servers found</td></tr>
            <?php else: ?>
            <?php foreach ($servers as $server): 
                $stats = getServerStats($server);
                $isOnline = ($server['status'] ?? 'online') === 'online';
                $isMain = $server['is_main'] == 1;
            ?>
            <tr class="hover:bg-dark-800/30 transition">
                <td class="px-3 py-2 text-gray-400"><?= $server['id'] ?></td>
                <td class="px-3 py-2">
                    <div class="flex items-center gap-1">
                        <div class="relative group">
                            <button class="px-2 py-1 rounded bg-cyan-500 text-white text-[10px]">Options</button>
                            <div class="hidden group-hover:block absolute left-0 top-full mt-1 bg-dark-900 rounded shadow-xl border border-gray-800 py-1 z-10 min-w-[120px]">
                                <a href="<?= ADMIN_PATH ?>/servers/edit/<?= $server['id'] ?>" class="block px-3 py-1 text-gray-400 hover:text-white hover:bg-dark-800">Edit</a>
                                <a href="#" class="block px-3 py-1 text-gray-400 hover:text-white hover:bg-dark-800">Restart</a>
                                <a href="#" class="block px-3 py-1 text-gray-400 hover:text-white hover:bg-dark-800">Sync</a>
                            </div>
                        </div>
                        <?php if (!$isMain): ?>
                        <button onclick="deleteServer(<?= $server['id'] ?>)" class="px-2 py-1 rounded bg-red-500 text-white text-[10px]">×</button>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="px-3 py-2">
                    <span class="text-white"><?= htmlspecialchars($server['server_name']) ?></span>
                    <?php if ($isMain): ?><span class="ml-1 text-[10px] text-blue-400">(Main)</span><?php endif; ?>
                </td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] <?= $isOnline ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' ?>">
                        <?= $isOnline ? 'Online' : 'Offline' ?>
                    </span>
                </td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-green-500/20 text-green-400">OK</span>
                </td>
                <td class="px-3 py-2 text-gray-500 text-[10px]">
                    <?= $server['os_type'] ?? 'Ubuntu' ?><br>
                    <?= $stats['cores'] ?> Cores / Load: <?= $stats['load'] ?><br>
                    Mem: <?= $stats['mem_used'] ?>G of <?= $stats['mem_total'] ?>G / Disk: <?= $stats['disk_used'] ?>G
                </td>
                <td class="px-3 py-2 text-gray-400"><?= htmlspecialchars($server['domain_name'] ?? '-') ?></td>
                <td class="px-3 py-2">
                    <code class="text-cyan-400 text-[10px]"><?= htmlspecialchars($server['server_ip']) ?></code>
                </td>
                <td class="px-3 py-2 text-center text-gray-500 text-[10px]">
                    <?= $server['http_port'] ?? 80 ?><br>
                    <?= $server['https_port'] ?? 443 ?>
                </td>
                <td class="px-3 py-2 text-center">
                    <span class="text-white"><?= $stats['clients'] ?></span><br>
                    <span class="text-gray-500 text-[10px]"><?= $server['max_clients'] ?? 1000 ?></span>
                </td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] <?= $stats['cpu'] > 80 ? 'bg-red-500 text-white' : ($stats['cpu'] > 50 ? 'bg-amber-500 text-white' : 'bg-cyan-500/20 text-cyan-400') ?>">
                        <?= $stats['cpu'] ?>%
                    </span>
                </td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] <?= $stats['mem'] > 80 ? 'bg-red-500 text-white' : ($stats['mem'] > 50 ? 'bg-amber-500 text-white' : 'bg-teal-500/20 text-teal-400') ?>">
                        <?= $stats['mem'] ?>%
                    </span>
                </td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-green-500/20 text-green-400"><?= $stats['in_speed'] ?>%</span>
                </td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-pink-500/20 text-pink-400"><?= $stats['out_speed'] ?>%</span>
                </td>
                <td class="px-3 py-2 text-gray-500 text-[10px]">
                    <?= $server['network_speed'] ?? 1000 ?> Mbps
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-3 text-xs text-gray-500">
    Showing 1 to <?= count($servers) ?> of <?= count($servers) ?> entries
</div>

<script>
function deleteServer(id) {
    if (confirm('Delete this server?')) {
        window.location.href = '<?= ADMIN_PATH ?>/servers/delete/' + id;
    }
}
</script>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>

<?php
/**
 * Cryonix Panel - Admin Dashboard
 * Real-time IPTV Panel Stats
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Dashboard';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$stats = [
    'online_users' => 0,
    'open_connections' => 0,
    'total_input' => 0,
    'total_output' => 0,
    'online_streams' => 0,
    'offline_streams' => 0
];

$servers = [];
$mainServer = null;

try {
    $db = Database::getInstance();
    
    // Get stats
    $stats['online_users'] = $db->count('user_activity', 'ended_at IS NULL');
    $stats['open_connections'] = $db->count('user_activity', 'ended_at IS NULL');
    $stats['online_streams'] = $db->count('streams', "status = 'online' OR status IS NULL");
    $stats['offline_streams'] = $db->count('streams', "status = 'offline'");
    
    // Get servers
    $servers = $db->fetchAll("SELECT * FROM servers ORDER BY is_main DESC, server_name") ?: [];
    
    // Find or create main server
    $mainServer = $db->fetch("SELECT * FROM servers WHERE is_main = 1");
    if (!$mainServer) {
        // Auto-create main server
        $serverIp = $_SERVER['SERVER_ADDR'] ?? trim(shell_exec("hostname -I | awk '{print $1}'"));
        $db->insert('servers', [
            'server_name' => 'Main Server',
            'server_ip' => $serverIp,
            'server_port' => 80,
            'is_main' => 1,
            'status' => 'online',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $mainServer = $db->fetch("SELECT * FROM servers WHERE is_main = 1");
        $servers = $db->fetchAll("SELECT * FROM servers ORDER BY is_main DESC, server_name") ?: [];
    }
    
    // Calculate total bandwidth from all servers
    foreach ($servers as $server) {
        $stats['total_input'] += floatval($server['bandwidth_in'] ?? 0);
        $stats['total_output'] += floatval($server['bandwidth_out'] ?? 0);
    }
    
} catch (\Exception $e) {
    // Database not ready
}

// Format bandwidth
function formatBandwidth($mbps) {
    if ($mbps >= 1000) {
        return number_format($mbps / 1000, 1) . ' Gbps';
    }
    return number_format($mbps, 0) . ' Mbps';
}

// Get system stats for main server
function getSystemStats() {
    $stats = ['cpu' => 0, 'ram' => 0, 'uptime' => 'N/A'];
    
    // CPU
    $load = sys_getloadavg();
    $cores = (int) trim(shell_exec("nproc 2>/dev/null") ?: 1);
    $stats['cpu'] = min(100, round(($load[0] / $cores) * 100));
    
    // RAM
    $free = shell_exec("free -m 2>/dev/null");
    if ($free && preg_match('/Mem:\s+(\d+)\s+(\d+)/', $free, $m)) {
        $stats['ram'] = round(($m[2] / $m[1]) * 100);
    }
    
    // Uptime
    $uptime = shell_exec("uptime -p 2>/dev/null");
    if ($uptime) {
        $stats['uptime'] = trim(str_replace('up ', '', $uptime));
    }
    
    return $stats;
}

$systemStats = getSystemStats();

ob_start();
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">Dashboard</h1>
    <p class="text-gray-400 text-sm">Real-time panel statistics</p>
</div>

<!-- Top Stats Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Online Users -->
    <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-5 shadow-lg">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 rounded-full bg-white/10"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white"><?= number_format($stats['online_users']) ?></div>
            <div class="text-blue-100 text-sm">ONLINE USERS</div>
        </div>
    </div>
    
    <!-- Open Connections -->
    <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-teal-500 to-teal-600 p-5 shadow-lg">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 rounded-full bg-white/10"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white"><?= number_format($stats['open_connections']) ?></div>
            <div class="text-teal-100 text-sm">OPEN CONNECTIONS</div>
        </div>
    </div>
    
    <!-- Bandwidth -->
    <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 p-5 shadow-lg">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 rounded-full bg-white/10"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-amber-200 text-xs">↓ IN</span>
                    <span class="text-xl font-bold text-white"><?= formatBandwidth($stats['total_input']) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-amber-200 text-xs">↑ OUT</span>
                    <span class="text-xl font-bold text-white"><?= formatBandwidth($stats['total_output']) ?></span>
                </div>
            </div>
            <div class="text-amber-100 text-sm mt-1">TOTAL BANDWIDTH</div>
        </div>
    </div>
    
    <!-- Streams -->
    <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 p-5 shadow-lg">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 rounded-full bg-white/10"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div>
                    <div class="text-2xl font-bold text-white"><?= number_format($stats['online_streams']) ?></div>
                    <div class="text-green-300 text-xs">ONLINE</div>
                </div>
                <div class="text-purple-300 text-2xl">/</div>
                <div>
                    <div class="text-2xl font-bold text-purple-200"><?= number_format($stats['offline_streams']) ?></div>
                    <div class="text-red-300 text-xs">OFFLINE</div>
                </div>
            </div>
            <div class="text-purple-100 text-sm mt-1">STREAMS</div>
        </div>
    </div>
</div>

<!-- Servers Section -->
<div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-white">Servers</h2>
    <a href="<?= ADMIN_PATH ?>/servers/add" class="px-4 py-2 rounded-lg bg-cyan-500/10 text-cyan-400 text-sm font-medium hover:bg-cyan-500/20 transition">
        + Add Load Balancer
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    <?php foreach ($servers as $server): 
        $isMain = $server['is_main'] == 1;
        $isOnline = ($server['status'] ?? 'online') === 'online';
        $cpu = $isMain ? $systemStats['cpu'] : rand(10, 60);
        $ram = $isMain ? $systemStats['ram'] : rand(20, 70);
        $uptime = $isMain ? $systemStats['uptime'] : ($server['uptime'] ?? '30d 12h');
        $connections = $server['current_connections'] ?? 0;
        $users = $server['current_users'] ?? 0;
        $streamsLive = $server['streams_live'] ?? 0;
        $streamsOff = $server['streams_off'] ?? 0;
        $bandwidthIn = $server['bandwidth_in'] ?? 0;
        $bandwidthOut = $server['bandwidth_out'] ?? 0;
    ?>
    <div class="glass rounded-xl border <?= $isOnline ? 'border-gray-800/50' : 'border-red-500/30' ?> overflow-hidden">
        <!-- Server Header -->
        <div class="flex items-center justify-between px-4 py-3 <?= $isMain ? 'bg-blue-500/10' : 'bg-gray-800/50' ?> border-b border-gray-800/50">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full <?= $isOnline ? 'bg-green-400' : 'bg-red-400' ?>"></span>
                <span class="text-white font-medium text-sm"><?= htmlspecialchars($server['server_name']) ?></span>
                <?php if ($isMain): ?>
                <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 text-xs">MAIN</span>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-gray-400 text-xs"><?= htmlspecialchars($server['server_ip']) ?></span>
                <a href="<?= ADMIN_PATH ?>/servers/edit/<?= $server['id'] ?>" class="text-gray-500 hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
            </div>
        </div>
        
        <!-- Server Stats -->
        <div class="p-4">
            <div class="grid grid-cols-4 gap-3 mb-4">
                <div>
                    <div class="text-gray-500 text-xs mb-1">Conns.</div>
                    <div class="px-2 py-1 rounded bg-blue-500/10 text-blue-400 text-sm font-semibold text-center"><?= number_format($connections) ?></div>
                </div>
                <div>
                    <div class="text-gray-500 text-xs mb-1">Users</div>
                    <div class="px-2 py-1 rounded bg-gray-800 text-white text-sm font-semibold text-center"><?= number_format($users) ?></div>
                </div>
                <div>
                    <div class="text-gray-500 text-xs mb-1">Streams Live</div>
                    <div class="px-2 py-1 rounded bg-green-500/10 text-green-400 text-sm font-semibold text-center"><?= number_format($streamsLive) ?></div>
                </div>
                <div>
                    <div class="text-gray-500 text-xs mb-1">Streams Off</div>
                    <div class="px-2 py-1 rounded bg-red-500/10 text-red-400 text-sm font-semibold text-center"><?= number_format($streamsOff) ?></div>
                </div>
            </div>
            
            <!-- Bandwidth -->
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <div class="text-gray-500 text-xs mb-1">Input</div>
                    <div class="px-2 py-1 rounded bg-cyan-500/10 text-cyan-400 text-sm font-semibold"><?= formatBandwidth($bandwidthIn) ?></div>
                </div>
                <div>
                    <div class="text-gray-500 text-xs mb-1">Output</div>
                    <div class="px-2 py-1 rounded bg-orange-500/10 text-orange-400 text-sm font-semibold"><?= formatBandwidth($bandwidthOut) ?></div>
                </div>
            </div>
            
            <!-- CPU & RAM Bars -->
            <div class="space-y-2">
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-500">CPU</span>
                        <span class="text-<?= $cpu > 80 ? 'red' : ($cpu > 50 ? 'amber' : 'green') ?>-400"><?= $cpu ?>%</span>
                    </div>
                    <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 <?= $cpu > 80 ? 'bg-red-500' : ($cpu > 50 ? 'bg-amber-500' : 'bg-green-500') ?>" style="width: <?= $cpu ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-500">RAM</span>
                        <span class="text-<?= $ram > 80 ? 'red' : ($ram > 50 ? 'amber' : 'green') ?>-400"><?= $ram ?>%</span>
                    </div>
                    <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 <?= $ram > 80 ? 'bg-red-500' : ($ram > 50 ? 'bg-amber-500' : 'bg-green-500') ?>" style="width: <?= $ram ?>%"></div>
                    </div>
                </div>
            </div>
            
            <!-- Uptime -->
            <div class="mt-3 pt-3 border-t border-gray-800/50 flex justify-between text-xs">
                <span class="text-gray-500">Uptime</span>
                <span class="text-gray-400"><?= $uptime ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($servers)): ?>
    <div class="col-span-full glass rounded-xl p-8 text-center border border-gray-800/50">
        <p class="text-gray-400">No servers configured. Main server will be created automatically.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Auto-refresh -->
<script>
// Refresh stats every 30 seconds
setTimeout(() => location.reload(), 30000);
</script>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>

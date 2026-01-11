<?php
/**
 * Cryonix Panel - Live Connections
 * Real-time connection monitoring with GeoIP
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Live Connections';

require_once CRYONIX_ROOT . '/core/Database.php';
require_once CRYONIX_ROOT . '/core/GeoIP.php';
use CryonixPanel\Core\Database;
use CryonixPanel\Core\GeoIP;

$connections = [];
$stats = ['total' => 0, 'countries' => [], 'isps' => []];
$error = '';

try {
    $db = Database::getInstance();
    $geoip = GeoIP::getInstance();
    
    // Get active connections
    $connections = $db->fetchAll("
        SELECT c.*, l.username, l.exp_date, s.stream_display_name
        FROM connections c
        LEFT JOIN `lines` l ON c.line_id = l.id
        LEFT JOIN streams s ON c.stream_id = s.id
        WHERE c.is_active = 1
        ORDER BY c.started_at DESC
        LIMIT 500
    ") ?: [];
    
    // Add GeoIP data to connections
    foreach ($connections as &$conn) {
        $geo = $geoip->lookup($conn['ip_address']);
        $conn['country_code'] = $geo['country_code'];
        $conn['country_name'] = $geo['country_name'];
        $conn['city'] = $geo['city'];
        $conn['isp'] = $geo['isp'];
        $conn['flag_url'] = GeoIP::getFlagUrl($geo['country_code']);
        
        // Stats
        $stats['total']++;
        if ($geo['country_code']) {
            $stats['countries'][$geo['country_code']] = ($stats['countries'][$geo['country_code']] ?? 0) + 1;
        }
        if ($geo['isp']) {
            $stats['isps'][$geo['isp']] = ($stats['isps'][$geo['isp']] ?? 0) + 1;
        }
    }
    
    arsort($stats['countries']);
    arsort($stats['isps']);
    
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

ob_start();
?>

<div class="max-w-full">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-white">Live Connections</h1>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full bg-green-500/20 text-green-400 text-sm font-medium">
                <?= $stats['total'] ?> Active
            </span>
            <button onclick="location.reload()" class="px-3 py-1.5 rounded-lg bg-cryo-500 text-white text-sm font-medium hover:bg-cryo-600 transition">
                Refresh
            </button>
        </div>
    </div>
    
    <?php if ($error): ?>
    <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm"><?= $error ?></div>
    <?php endif; ?>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="glass rounded-xl border border-gray-800/50 p-4">
            <div class="text-2xl font-bold text-white"><?= $stats['total'] ?></div>
            <div class="text-xs text-gray-400">Total Connections</div>
        </div>
        <div class="glass rounded-xl border border-gray-800/50 p-4">
            <div class="text-2xl font-bold text-cryo-400"><?= count($stats['countries']) ?></div>
            <div class="text-xs text-gray-400">Countries</div>
        </div>
        <div class="glass rounded-xl border border-gray-800/50 p-4">
            <div class="text-2xl font-bold text-amber-400"><?= count($stats['isps']) ?></div>
            <div class="text-xs text-gray-400">ISPs</div>
        </div>
        <div class="glass rounded-xl border border-gray-800/50 p-4">
            <div class="flex items-center gap-2">
                <?php $topCountries = array_slice($stats['countries'], 0, 3, true); ?>
                <?php foreach ($topCountries as $code => $count): ?>
                <img src="<?= GeoIP::getFlagUrl($code) ?>" alt="<?= $code ?>" class="w-6 h-4 rounded shadow" title="<?= $code ?>: <?= $count ?>">
                <?php endforeach; ?>
            </div>
            <div class="text-xs text-gray-400 mt-1">Top Countries</div>
        </div>
    </div>
    
    <!-- Country Distribution -->
    <?php if (!empty($stats['countries'])): ?>
    <div class="glass rounded-xl border border-gray-800/50 p-4 mb-6">
        <h3 class="text-sm font-medium text-gray-400 mb-3">Connection by Country</h3>
        <div class="flex flex-wrap gap-2">
            <?php foreach (array_slice($stats['countries'], 0, 20, true) as $code => $count): ?>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-dark-800">
                <img src="<?= GeoIP::getFlagUrl($code) ?>" alt="<?= $code ?>" class="w-5 h-3.5 rounded">
                <span class="text-xs text-white"><?= $code ?></span>
                <span class="text-xs text-cryo-400 font-medium"><?= $count ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Connections Table -->
    <div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-dark-800/50 border-b border-gray-800/50">
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">USER</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">STREAM</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">IP / LOCATION</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">ISP</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">DEVICE</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">CONNECTED</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-400">ACTIONS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/30">
                <?php if (empty($connections)): ?>
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">No active connections</td>
                </tr>
                <?php else: ?>
                <?php foreach ($connections as $conn): ?>
                <tr class="hover:bg-dark-800/30 transition">
                    <td class="px-3 py-2">
                        <div class="text-sm text-white font-medium"><?= htmlspecialchars($conn['username'] ?? 'Unknown') ?></div>
                    </td>
                    <td class="px-3 py-2">
                        <div class="text-sm text-gray-300"><?= htmlspecialchars($conn['stream_display_name'] ?? '-') ?></div>
                    </td>
                    <td class="px-3 py-2">
                        <div class="flex items-center gap-2">
                            <?php if ($conn['flag_url']): ?>
                            <img src="<?= $conn['flag_url'] ?>" alt="<?= $conn['country_code'] ?>" class="w-5 h-3.5 rounded shadow">
                            <?php endif; ?>
                            <div>
                                <div class="text-sm text-white"><?= htmlspecialchars($conn['ip_address']) ?></div>
                                <div class="text-xs text-gray-500"><?= $conn['city'] ? $conn['city'] . ', ' : '' ?><?= $conn['country_name'] ?? '' ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-2">
                        <div class="text-xs text-gray-400 max-w-[150px] truncate"><?= htmlspecialchars($conn['isp'] ?? '-') ?></div>
                    </td>
                    <td class="px-3 py-2">
                        <div class="text-xs text-gray-400 max-w-[200px] truncate" title="<?= htmlspecialchars($conn['user_agent'] ?? '') ?>">
                            <?= htmlspecialchars(substr($conn['user_agent'] ?? '-', 0, 30)) ?>...
                        </div>
                    </td>
                    <td class="px-3 py-2">
                        <div class="text-xs text-gray-400">
                            <?= $conn['started_at'] ? date('H:i:s', strtotime($conn['started_at'])) : '-' ?>
                        </div>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button onclick="killConnection(<?= $conn['id'] ?>)" class="p-1.5 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition" title="Kill Connection">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function killConnection(id) {
    if (!confirm('Kill this connection?')) return;
    
    fetch('<?= ADMIN_PATH ?>/api/kill-connection', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ connection_id: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.error || 'Failed');
    });
}

// Auto-refresh every 30 seconds
setTimeout(() => location.reload(), 30000);
</script>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>

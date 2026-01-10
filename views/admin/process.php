<?php
/**
 * Cryonix Panel - Process Monitor
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Process Monitor';

// Get system processes
$processes = [];
$output = shell_exec("ps aux --sort=-%mem 2>/dev/null | head -20");
if ($output) {
    $lines = explode("\n", trim($output));
    $header = preg_split('/\s+/', array_shift($lines));
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        $parts = preg_split('/\s+/', $line, 11);
        if (count($parts) >= 11) {
            $processes[] = [
                'user' => $parts[0],
                'pid' => $parts[1],
                'cpu' => $parts[2],
                'mem' => $parts[3],
                'vsz' => $parts[4],
                'rss' => $parts[5],
                'command' => $parts[10] ?? ''
            ];
        }
    }
}

// Get FFmpeg processes
$ffmpegProcesses = [];
$ffmpegOutput = shell_exec("pgrep -a ffmpeg 2>/dev/null");
if ($ffmpegOutput) {
    foreach (explode("\n", trim($ffmpegOutput)) as $line) {
        if (empty($line)) continue;
        $parts = explode(' ', $line, 2);
        $ffmpegProcesses[] = [
            'pid' => $parts[0],
            'command' => substr($parts[1] ?? '', 0, 100)
        ];
    }
}

// System stats
$loadAvg = sys_getloadavg();
$memInfo = shell_exec("free -m 2>/dev/null");
$memUsed = $memTotal = 0;
if ($memInfo && preg_match('/Mem:\s+(\d+)\s+(\d+)/', $memInfo, $m)) {
    $memTotal = $m[1];
    $memUsed = $m[2];
}

ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-white">Process Monitor</h1>
        <p class="text-gray-500 text-sm">System processes and FFmpeg instances</p>
    </div>
    <button onclick="location.reload()" class="px-3 py-1.5 rounded-lg bg-dark-800 text-gray-400 hover:text-white text-xs transition">
        ↻ Refresh
    </button>
</div>

<!-- System Overview -->
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="glass rounded-lg p-4 border border-gray-800/50">
        <div class="text-gray-500 text-xs mb-1">Load Average (1m)</div>
        <div class="text-2xl font-bold text-white"><?= number_format($loadAvg[0], 2) ?></div>
    </div>
    <div class="glass rounded-lg p-4 border border-gray-800/50">
        <div class="text-gray-500 text-xs mb-1">Load Average (5m)</div>
        <div class="text-2xl font-bold text-white"><?= number_format($loadAvg[1], 2) ?></div>
    </div>
    <div class="glass rounded-lg p-4 border border-gray-800/50">
        <div class="text-gray-500 text-xs mb-1">Memory Used</div>
        <div class="text-2xl font-bold text-white"><?= number_format($memUsed) ?> MB</div>
        <div class="text-xs text-gray-500">of <?= number_format($memTotal) ?> MB</div>
    </div>
    <div class="glass rounded-lg p-4 border border-gray-800/50">
        <div class="text-gray-500 text-xs mb-1">FFmpeg Instances</div>
        <div class="text-2xl font-bold text-cyan-400"><?= count($ffmpegProcesses) ?></div>
    </div>
</div>

<!-- FFmpeg Processes -->
<div class="mb-6">
    <h2 class="text-sm font-semibold text-white mb-3">FFmpeg Processes</h2>
    <div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-dark-800/50">
                <tr class="text-left text-gray-500 text-xs uppercase">
                    <th class="px-4 py-2">PID</th>
                    <th class="px-4 py-2">Command</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/50">
                <?php if (empty($ffmpegProcesses)): ?>
                <tr><td colspan="3" class="px-4 py-4 text-center text-gray-500 text-xs">No FFmpeg processes running</td></tr>
                <?php else: ?>
                <?php foreach ($ffmpegProcesses as $p): ?>
                <tr class="hover:bg-dark-800/30">
                    <td class="px-4 py-2 font-mono text-cyan-400 text-xs"><?= $p['pid'] ?></td>
                    <td class="px-4 py-2 text-gray-400 text-xs truncate max-w-lg"><?= htmlspecialchars($p['command']) ?></td>
                    <td class="px-4 py-2">
                        <button onclick="killProcess(<?= $p['pid'] ?>)" class="text-red-400 hover:text-red-300 text-xs">Kill</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Top Processes -->
<div>
    <h2 class="text-sm font-semibold text-white mb-3">Top Processes (by Memory)</h2>
    <div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-dark-800/50">
                <tr class="text-left text-gray-500 text-xs uppercase">
                    <th class="px-4 py-2">User</th>
                    <th class="px-4 py-2">PID</th>
                    <th class="px-4 py-2">CPU%</th>
                    <th class="px-4 py-2">MEM%</th>
                    <th class="px-4 py-2">Command</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/50">
                <?php foreach ($processes as $p): ?>
                <tr class="hover:bg-dark-800/30">
                    <td class="px-4 py-2 text-gray-400 text-xs"><?= htmlspecialchars($p['user']) ?></td>
                    <td class="px-4 py-2 font-mono text-xs text-gray-500"><?= $p['pid'] ?></td>
                    <td class="px-4 py-2 text-xs <?= floatval($p['cpu']) > 50 ? 'text-red-400' : 'text-gray-400' ?>"><?= $p['cpu'] ?>%</td>
                    <td class="px-4 py-2 text-xs <?= floatval($p['mem']) > 20 ? 'text-amber-400' : 'text-gray-400' ?>"><?= $p['mem'] ?>%</td>
                    <td class="px-4 py-2 text-gray-500 text-xs truncate max-w-md"><?= htmlspecialchars(substr($p['command'], 0, 60)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function killProcess(pid) {
    if (confirm('Kill process ' + pid + '?')) {
        fetch('<?= ADMIN_PATH ?>/api/kill-process', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({pid: pid})
        }).then(() => location.reload());
    }
}
// Auto-refresh every 5 seconds
setTimeout(() => location.reload(), 5000);
</script>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


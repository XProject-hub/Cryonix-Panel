<?php
/**
 * Cryonix Panel - Manage Streams
 * Full Stream Management with Filters
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Streams';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$streams = [];
$categories = [];
$servers = [];
$error = '';
$totalStreams = 0;

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = (int)($_GET['limit'] ?? 10);
$offset = ($page - 1) * $limit;

// Filters
$search = $_GET['search'] ?? '';
$serverId = $_GET['server'] ?? '';
$categoryId = $_GET['category'] ?? '';
$filter = $_GET['filter'] ?? '';

try {
    $db = Database::getInstance();
    
    // Get categories
    $categories = $db->fetchAll("SELECT * FROM stream_categories WHERE category_type = 'live' ORDER BY category_name") ?: [];
    
    // Get servers
    $servers = $db->fetchAll("SELECT * FROM servers ORDER BY server_name") ?: [];
    
    // Build query
    $where = ["1=1"];
    $params = [];
    
    if ($search) {
        $where[] = "(stream_display_name LIKE ? OR stream_source LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($categoryId) {
        $where[] = "category_id = ?";
        $params[] = $categoryId;
    }
    
    if ($filter === 'online') {
        $where[] = "status = 'active'";
    } elseif ($filter === 'offline') {
        $where[] = "status = 'offline'";
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Count total
    $countResult = $db->fetch("SELECT COUNT(*) as cnt FROM streams WHERE $whereClause", $params);
    $totalStreams = (int)($countResult['cnt'] ?? 0);
    
    // Get streams with pagination
    $streams = $db->fetchAll("
        SELECT s.*, c.category_name
        FROM streams s
        LEFT JOIN stream_categories c ON s.category_id = c.id
        WHERE $whereClause
        ORDER BY s.id DESC
        LIMIT $limit OFFSET $offset
    ", $params) ?: [];
    
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

$totalPages = ceil($totalStreams / $limit);

ob_start();
?>

<div class="max-w-full">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-white">Streams</h1>
        <div class="flex items-center gap-2">
            <button onclick="toggleAutoRefresh()" id="autoRefreshBtn" class="px-3 py-1.5 rounded text-xs font-medium bg-gray-700 text-gray-300 hover:bg-gray-600 transition">
                Auto-Refresh
            </button>
            <a href="<?= ADMIN_PATH ?>/streams/add" class="px-3 py-1.5 rounded text-xs font-medium bg-amber-500 text-white hover:bg-amber-600 transition">
                Add Stream
            </a>
            <a href="<?= ADMIN_PATH ?>/channels/add" class="px-3 py-1.5 rounded text-xs font-medium bg-cryo-500 text-white hover:bg-cryo-600 transition">
                Create
            </a>
        </div>
    </div>
    
    <?php if ($error): ?>
    <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm"><?= $error ?></div>
    <?php endif; ?>
    
    <!-- Filters -->
    <form method="GET" class="glass rounded-xl border border-gray-800/50 p-4 mb-4">
        <div class="grid grid-cols-6 gap-4">
            <div class="col-span-1">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search Streams" 
                    class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm focus:border-cryo-500 focus:outline-none">
            </div>
            <div class="col-span-1">
                <select name="server" class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm focus:border-cryo-500 focus:outline-none">
                    <option value="">All Servers</option>
                    <?php foreach ($servers as $srv): ?>
                    <option value="<?= $srv['id'] ?>" <?= $serverId == $srv['id'] ? 'selected' : '' ?>><?= htmlspecialchars($srv['server_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-span-1">
                <select name="category" class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm focus:border-cryo-500 focus:outline-none">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-span-1">
                <select name="filter" class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm focus:border-cryo-500 focus:outline-none">
                    <option value="">No Filter</option>
                    <option value="online" <?= $filter === 'online' ? 'selected' : '' ?>>Online Only</option>
                    <option value="offline" <?= $filter === 'offline' ? 'selected' : '' ?>>Offline Only</option>
                </select>
            </div>
            <div class="col-span-1">
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-sm">Show</span>
                    <select name="limit" onchange="this.form.submit()" class="px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm focus:border-cryo-500 focus:outline-none">
                        <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
            </div>
            <div class="col-span-1">
                <button type="submit" class="w-full px-3 py-2 rounded-lg bg-cryo-500 text-white text-sm font-medium hover:bg-cryo-600 transition">
                    Search
                </button>
            </div>
        </div>
    </form>
    
    <!-- Table -->
    <div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-dark-800/50 border-b border-gray-800/50">
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">ID</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">ICON</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">NAME</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">SOURCE</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-400">CLIENTS</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-400">UPTIME</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-400">ACTIONS</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-400">PLAYER</th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-400">EPG</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">STREAM INFO</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/30">
                <?php if (empty($streams)): ?>
                <tr>
                    <td colspan="10" class="px-4 py-8 text-center text-gray-500">No streams found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($streams as $stream): ?>
                <?php 
                    $isOnline = $stream['status'] === 'active';
                    
                    // Calculate real uptime from started_at
                    $uptime = 0;
                    if ($isOnline && !empty($stream['started_at'])) {
                        $uptime = time() - strtotime($stream['started_at']);
                    }
                    
                    $hours = floor($uptime / 3600);
                    $mins = floor(($uptime % 3600) / 60);
                    $secs = $uptime % 60;
                    $uptimeStr = sprintf('%02dh %02dm %02ds', $hours, $mins, $secs);
                    
                    $clients = (int)($stream['current_clients'] ?? 0);
                ?>
                <tr class="hover:bg-dark-800/30 transition">
                    <td class="px-3 py-2 text-sm text-gray-300"><?= $stream['id'] ?></td>
                    <td class="px-3 py-2">
                        <?php if ($stream['stream_icon']): ?>
                        <img src="<?= htmlspecialchars($stream['stream_icon']) ?>" class="w-8 h-6 object-cover rounded" alt="">
                        <?php else: ?>
                        <div class="w-8 h-6 rounded bg-gray-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2">
                        <div class="text-sm text-white font-medium"><?= htmlspecialchars($stream['stream_display_name']) ?></div>
                        <div class="text-xs text-cryo-400"><?= htmlspecialchars($stream['category_name'] ?? 'Uncategorized') ?></div>
                    </td>
                    <td class="px-3 py-2">
                        <div class="text-xs text-gray-400 max-w-[200px] truncate" title="<?= htmlspecialchars($stream['stream_source']) ?>">
                            <?= htmlspecialchars(substr($stream['stream_source'], 0, 50)) ?>...
                        </div>
                        <div class="text-xs text-gray-500">Stream Source</div>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?= $clients > 0 ? 'bg-green-500/20 text-green-400' : 'bg-gray-700 text-gray-400' ?>">
                            <?= $clients ?>
                        </span>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?= $isOnline ? 'bg-teal-500/20 text-teal-400' : 'bg-red-500/20 text-red-400' ?>">
                            <?= $isOnline ? $uptimeStr : 'OFFLINE' ?>
                        </span>
                    </td>
                    <td class="px-3 py-2">
                        <div class="flex items-center justify-center gap-1">
                            <button onclick="streamAction(<?= $stream['id'] ?>, 'restart')" title="Restart" class="p-1 rounded hover:bg-gray-700 text-gray-400 hover:text-white transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                            <button onclick="streamAction(<?= $stream['id'] ?>, 'stop')" title="Stop" class="p-1 rounded hover:bg-gray-700 text-gray-400 hover:text-white transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                            </button>
                            <a href="<?= ADMIN_PATH ?>/streams/edit/<?= $stream['id'] ?>" title="Edit" class="p-1 rounded hover:bg-gray-700 text-gray-400 hover:text-white transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button onclick="streamAction(<?= $stream['id'] ?>, 'delete')" title="Delete" class="p-1 rounded hover:bg-red-500/20 text-gray-400 hover:text-red-400 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button onclick="playStream(<?= $stream['id'] ?>)" class="p-1.5 rounded-full bg-green-500 text-white hover:bg-green-600 transition">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </button>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <?php if ($stream['epg_channel_id']): ?>
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-cryo-500/20 text-cryo-400">+</span>
                        <?php else: ?>
                        <span class="text-gray-600">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2">
                        <?php if ($isOnline && !empty($stream['bitrate'])): ?>
                        <div class="text-xs text-gray-300">
                            <span class="text-green-400"><?= number_format($stream['bitrate']) ?> kbps</span> 
                            <span class="text-gray-500"><?= $stream['resolution'] ?? '-' ?></span>
                        </div>
                        <div class="text-xs text-gray-500">
                            <?= $stream['codec_video'] ?? 'N/A' ?> • <?= $stream['codec_audio'] ?? 'N/A' ?> @ <?= $stream['fps'] ?? '0' ?> FPS
                        </div>
                        <?php elseif ($isOnline): ?>
                        <button onclick="probeStream(<?= $stream['id'] ?>)" class="px-2 py-1 rounded text-xs bg-amber-500/20 text-amber-400 hover:bg-amber-500/30">
                            Probe Info
                        </button>
                        <?php else: ?>
                        <span class="text-xs text-gray-500 italic">Offline</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="flex items-center justify-between mt-4">
        <div class="text-sm text-gray-500">
            Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $totalStreams) ?> of <?= number_format($totalStreams) ?> entries
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="flex items-center gap-1">
            <?php if ($page > 1): ?>
            <a href="?page=1&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&server=<?= $serverId ?>&category=<?= $categoryId ?>&filter=<?= $filter ?>" class="px-3 py-1 rounded bg-dark-800 text-gray-400 hover:bg-gray-700 text-sm">«</a>
            <a href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&server=<?= $serverId ?>&category=<?= $categoryId ?>&filter=<?= $filter ?>" class="px-3 py-1 rounded bg-dark-800 text-gray-400 hover:bg-gray-700 text-sm">‹</a>
            <?php endif; ?>
            
            <?php 
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++): 
            ?>
            <a href="?page=<?= $i ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&server=<?= $serverId ?>&category=<?= $categoryId ?>&filter=<?= $filter ?>" 
               class="px-3 py-1 rounded text-sm <?= $i === $page ? 'bg-cryo-500 text-white' : 'bg-dark-800 text-gray-400 hover:bg-gray-700' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <span class="text-gray-500">...</span>
            <a href="?page=<?= $totalPages ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&server=<?= $serverId ?>&category=<?= $categoryId ?>&filter=<?= $filter ?>" class="px-3 py-1 rounded bg-dark-800 text-gray-400 hover:bg-gray-700 text-sm"><?= $totalPages ?></a>
            <a href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&server=<?= $serverId ?>&category=<?= $categoryId ?>&filter=<?= $filter ?>" class="px-3 py-1 rounded bg-dark-800 text-gray-400 hover:bg-gray-700 text-sm">›</a>
            <a href="?page=<?= $totalPages ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&server=<?= $serverId ?>&category=<?= $categoryId ?>&filter=<?= $filter ?>" class="px-3 py-1 rounded bg-dark-800 text-gray-400 hover:bg-gray-700 text-sm">»</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
let autoRefresh = false;
let refreshInterval;

function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    const btn = document.getElementById('autoRefreshBtn');
    if (autoRefresh) {
        btn.classList.remove('bg-gray-700');
        btn.classList.add('bg-green-500');
        refreshInterval = setInterval(() => location.reload(), 10000);
    } else {
        btn.classList.remove('bg-green-500');
        btn.classList.add('bg-gray-700');
        clearInterval(refreshInterval);
    }
}

function streamAction(id, action) {
    if (action === 'delete' && !confirm('Delete this stream?')) return;
    
    fetch('<?= ADMIN_PATH ?>/api/stream-action', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ stream_id: id, action: action })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Action failed');
        }
    });
}

function playStream(id) {
    window.open('<?= ADMIN_PATH ?>/player/' + id, 'player', 'width=800,height=600');
}

function probeStream(id) {
    const btn = event.target;
    btn.textContent = 'Probing...';
    btn.disabled = true;
    
    fetch('<?= ADMIN_PATH ?>/api/stream-action', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ stream_id: id, action: 'probe' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.online) {
            location.reload();
        } else {
            alert(data.message || 'Stream probe failed');
            btn.textContent = 'Probe Info';
            btn.disabled = false;
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
        btn.textContent = 'Probe Info';
        btn.disabled = false;
    });
}
</script>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>

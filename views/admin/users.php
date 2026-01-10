<?php
/**
 * Cryonix Panel - Users Management
 * Full Xtream UI Style with Actions
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Users';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$users = [];
$total = 0;
$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 10);
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$reseller = $_GET['reseller'] ?? '';
$filter = $_GET['filter'] ?? '';

try {
    $db = Database::getInstance();
    
    $where = "1=1";
    $params = [];
    
    if ($search) {
        $where .= " AND (username LIKE ? OR password LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($reseller) {
        $where .= " AND reseller = ?";
        $params[] = $reseller;
    }
    if ($filter === 'active') {
        $where .= " AND status = 'active'";
    } elseif ($filter === 'expired') {
        $where .= " AND exp_date < NOW()";
    } elseif ($filter === 'trial') {
        $where .= " AND is_trial = 1";
    } elseif ($filter === 'banned') {
        $where .= " AND is_banned = 1";
    }
    
    $countResult = $db->fetch("SELECT COUNT(*) as cnt FROM `lines` WHERE $where", $params);
    $total = $countResult['cnt'] ?? 0;
    
    $users = $db->fetchAll("SELECT * FROM `lines` WHERE $where ORDER BY id DESC LIMIT $limit OFFSET $offset", $params) ?: [];
    $resellers = $db->fetchAll("SELECT DISTINCT reseller FROM `lines` WHERE reseller IS NOT NULL") ?: [];
} catch (\Exception $e) {
    $error = $e->getMessage();
}

$totalPages = ceil($total / $limit);
$serverUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

ob_start();
?>

<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">Users</h1>
    <div class="flex items-center gap-2">
        <button onclick="location.reload()" class="px-3 py-1.5 rounded bg-green-500 text-white text-xs flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Auto-Refresh
        </button>
        <a href="<?= ADMIN_PATH ?>/users/add" class="px-3 py-1.5 rounded bg-cryo-500 text-white text-xs flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add User
        </a>
    </div>
</div>

<!-- Filters -->
<div class="glass rounded-xl border border-gray-800/50 p-4 mb-4">
    <form method="GET" class="flex items-center gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search Users..." class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm">
        </div>
        <div class="text-gray-500 text-xs">Filter Results</div>
        <select name="reseller" class="px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm min-w-[150px]">
            <option value="">All Resellers</option>
            <?php foreach ($resellers as $r): ?>
            <option value="<?= htmlspecialchars($r['reseller']) ?>" <?= $reseller === $r['reseller'] ? 'selected' : '' ?>><?= htmlspecialchars($r['reseller']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="filter" class="px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm min-w-[120px]">
            <option value="">No Filter</option>
            <option value="active" <?= $filter === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="expired" <?= $filter === 'expired' ? 'selected' : '' ?>>Expired</option>
            <option value="trial" <?= $filter === 'trial' ? 'selected' : '' ?>>Trial</option>
            <option value="banned" <?= $filter === 'banned' ? 'selected' : '' ?>>Banned</option>
        </select>
        <div class="text-gray-500 text-xs">Show</div>
        <select name="limit" onchange="this.form.submit()" class="px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm w-20">
            <option value="10" <?= $limit === 10 ? 'selected' : '' ?>>10</option>
            <option value="25" <?= $limit === 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $limit === 50 ? 'selected' : '' ?>>50</option>
            <option value="100" <?= $limit === 100 ? 'selected' : '' ?>>100</option>
        </select>
        <button type="submit" class="px-4 py-2 rounded bg-cryo-500 text-white text-xs">Filter</button>
    </form>
</div>

<!-- Users Table -->
<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-dark-800/50 text-gray-500 uppercase">
                <tr>
                    <th class="px-3 py-2.5 text-left">ID</th>
                    <th class="px-3 py-2.5 text-left">Username</th>
                    <th class="px-3 py-2.5 text-left">Password</th>
                    <th class="px-3 py-2.5 text-left">Reseller</th>
                    <th class="px-3 py-2.5 text-left">Status</th>
                    <th class="px-3 py-2.5 text-left">Trial</th>
                    <th class="px-3 py-2.5 text-left">Expiration</th>
                    <th class="px-3 py-2.5 text-left">Days</th>
                    <th class="px-3 py-2.5 text-left">Conns</th>
                    <th class="px-3 py-2.5 text-left">Info</th>
                    <th class="px-3 py-2.5 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/50">
                <?php if (empty($users)): ?>
                <tr><td colspan="11" class="px-4 py-8 text-center text-gray-500">No users found</td></tr>
                <?php else: foreach ($users as $u): 
                    $expDate = strtotime($u['exp_date'] ?? 'now');
                    $daysLeft = max(0, floor(($expDate - time()) / 86400));
                    $isExpired = $expDate < time();
                    $isActive = ($u['status'] ?? 'active') === 'active' && !$isExpired;
                    $isTrial = ($u['is_trial'] ?? 0) == 1;
                    $isBanned = ($u['is_banned'] ?? 0) == 1;
                    $currentConns = $u['current_connections'] ?? 0;
                    $maxConns = $u['max_connections'] ?? 1;
                ?>
                <tr class="hover:bg-dark-800/30">
                    <td class="px-3 py-2 text-gray-400"><?= $u['id'] ?></td>
                    <td class="px-3 py-2 text-white font-medium"><?= htmlspecialchars($u['username']) ?></td>
                    <td class="px-3 py-2 text-gray-400 font-mono"><?= htmlspecialchars($u['password']) ?></td>
                    <td class="px-3 py-2 text-gray-400"><?= htmlspecialchars($u['reseller'] ?? 'XTV') ?></td>
                    <td class="px-3 py-2">
                        <?php if ($isBanned): ?>
                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-red-500/20 text-red-400">BANNED</span>
                        <?php elseif ($isExpired): ?>
                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-orange-500/20 text-orange-400">EXPIRED</span>
                        <?php elseif ($isActive): ?>
                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-green-500/20 text-green-400">ACTIVE</span>
                        <?php else: ?>
                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-gray-500/20 text-gray-400">DISABLED</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2">
                        <?php if ($isTrial): ?>
                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-amber-500/20 text-amber-400">TRIAL</span>
                        <?php else: ?>
                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-cryo-500/20 text-cryo-400">OFFICIAL</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2">
                        <span class="<?= $isExpired ? 'text-red-400' : 'text-green-400' ?>"><?= date('d-m-Y H:i', $expDate) ?></span>
                    </td>
                    <td class="px-3 py-2 <?= $daysLeft < 7 ? 'text-red-400' : 'text-gray-400' ?>"><?= $daysLeft ?> Days</td>
                    <td class="px-3 py-2 text-gray-400"><?= $currentConns ?> / <?= $maxConns ?></td>
                    <td class="px-3 py-2 text-gray-500 text-[10px] max-w-[150px] truncate"><?= htmlspecialchars($u['admin_notes'] ?? '-') ?></td>
                    <td class="px-3 py-2">
                        <div class="flex items-center gap-0.5">
                            <!-- Reset ISP -->
                            <button onclick="userAction(<?= $u['id'] ?>, 'reset-isp')" class="p-1 rounded hover:bg-dark-700 text-gray-500 hover:text-purple-400" title="Reset ISP">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                            <!-- Lock ISP -->
                            <button onclick="userAction(<?= $u['id'] ?>, 'lock-isp')" class="p-1 rounded hover:bg-dark-700 text-gray-500 hover:text-amber-400" title="Lock ISP">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </button>
                            <!-- Extend -->
                            <button onclick="userAction(<?= $u['id'] ?>, 'extend')" class="p-1 rounded hover:bg-dark-700 text-gray-500 hover:text-green-400" title="Extend Line">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </button>
                            <!-- Edit -->
                            <a href="<?= ADMIN_PATH ?>/users/edit/<?= $u['id'] ?>" class="p-1 rounded hover:bg-dark-700 text-gray-500 hover:text-cryo-400" title="Edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <!-- Download Playlist -->
                            <button onclick="openPlaylistModal('<?= htmlspecialchars($u['username']) ?>', '<?= htmlspecialchars($u['password']) ?>')" class="p-1 rounded hover:bg-dark-700 text-gray-500 hover:text-blue-400" title="Download Playlist">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </button>
                            <!-- Kill Connections -->
                            <button onclick="userAction(<?= $u['id'] ?>, 'kill')" class="p-1 rounded hover:bg-dark-700 text-gray-500 hover:text-orange-400" title="Kill Connections">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            </button>
                            <!-- Ban -->
                            <button onclick="userAction(<?= $u['id'] ?>, 'ban')" class="p-1 rounded hover:bg-dark-700 text-gray-500 hover:text-red-400" title="Ban">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </button>
                            <!-- Disable -->
                            <button onclick="userAction(<?= $u['id'] ?>, 'disable')" class="p-1 rounded hover:bg-dark-700 text-gray-500 hover:text-gray-400" title="Disable">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </button>
                            <!-- Stats -->
                            <a href="<?= ADMIN_PATH ?>/users/stats/<?= $u['id'] ?>" class="p-1 rounded hover:bg-dark-700 text-gray-500 hover:text-cyan-400" title="User Stats">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </a>
                            <!-- Delete -->
                            <button onclick="if(confirm('Delete this user?')) userAction(<?= $u['id'] ?>, 'delete')" class="p-1 rounded hover:bg-dark-700 text-gray-500 hover:text-red-500" title="Delete">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="flex items-center justify-between mt-4">
    <div class="text-gray-500 text-xs">Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $total) ?> of <?= $total ?> entries</div>
    <div class="flex items-center gap-1">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&reseller=<?= urlencode($reseller) ?>&filter=<?= $filter ?>" class="px-2 py-1 rounded bg-dark-800 text-gray-400 text-xs hover:bg-dark-700">&lt;</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <a href="?page=<?= $i ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&reseller=<?= urlencode($reseller) ?>&filter=<?= $filter ?>" class="px-2 py-1 rounded text-xs <?= $i === $page ? 'bg-cryo-500 text-white' : 'bg-dark-800 text-gray-400 hover:bg-dark-700' ?>"><?= $i ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <span class="text-gray-600 px-1">...</span>
        <a href="?page=<?= $totalPages ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&reseller=<?= urlencode($reseller) ?>&filter=<?= $filter ?>" class="px-2 py-1 rounded bg-dark-800 text-gray-400 text-xs hover:bg-dark-700"><?= $totalPages ?></a>
        <a href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&reseller=<?= urlencode($reseller) ?>&filter=<?= $filter ?>" class="px-2 py-1 rounded bg-dark-800 text-gray-400 text-xs hover:bg-dark-700">&gt;</a>
        <?php endif; ?>
    </div>
</div>

<!-- Download Playlist Modal -->
<div id="playlistModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <div class="glass rounded-xl p-6 border border-gray-800 w-[450px]">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-white">Download Playlist</h2>
            <button onclick="closePlaylistModal()" class="text-gray-500 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="mb-4">
            <label class="block text-xs text-gray-400 mb-1">Select an output format:</label>
            <select id="playlistFormat" onchange="updatePlaylistUrl()" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm">
                <option value="">-- Select Format --</option>
                <option value="e2_16_hls">Enigma 2 OE 1.6 Auto Script - HLS</option>
                <option value="e2_16_ts">Enigma 2 OE 1.6 Auto Script - MPEGTS</option>
                <option value="e2_20_hls">Enigma 2 OE 2.0 Auto Script - HLS</option>
                <option value="e2_20_ts">Enigma 2 OE 2.0 Auto Script - MPEGTS</option>
                <option value="m3u">m3u With Options</option>
                <option value="m3u_hls">m3u With Options - HLS</option>
                <option value="m3u_ts">m3u With Options - MPEGTS</option>
            </select>
        </div>
        
        <div id="playlistUrlContainer" class="hidden">
            <label class="block text-xs text-gray-400 mb-1">Playlist URL:</label>
            <div class="flex gap-2">
                <input type="text" id="playlistUrl" readonly class="flex-1 px-3 py-2 rounded bg-dark-900 border border-gray-800 text-cryo-400 text-xs font-mono" onclick="this.select()">
                <button type="button" onclick="copyPlaylistUrl()" class="px-3 py-2 rounded bg-gray-700 text-white text-xs hover:bg-gray-600 flex items-center justify-center" title="Copy">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
                <a id="playlistDownloadLink" href="#" target="_blank" class="px-3 py-2 rounded bg-cryo-500 text-white text-xs hover:bg-cryo-600 flex items-center justify-center" title="Download">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </a>
            </div>
            <p id="copySuccess" class="hidden text-green-400 text-xs mt-2">Copied to clipboard!</p>
        </div>
    </div>
</div>

<script>
const serverUrl = '<?= $serverUrl ?>';
let currentUser = { username: '', password: '' };

function openPlaylistModal(username, password) {
    currentUser = { username, password };
    document.getElementById('playlistFormat').value = '';
    document.getElementById('playlistUrlContainer').classList.add('hidden');
    document.getElementById('playlistModal').classList.remove('hidden');
}

function closePlaylistModal() {
    document.getElementById('playlistModal').classList.add('hidden');
}

function updatePlaylistUrl() {
    const format = document.getElementById('playlistFormat').value;
    if (!format) {
        document.getElementById('playlistUrlContainer').classList.add('hidden');
        return;
    }
    
    document.getElementById('playlistUrlContainer').classList.remove('hidden');
    
    let url = serverUrl + ':8080/get.php?username=' + currentUser.username + '&password=' + currentUser.password;
    
    switch(format) {
        case 'e2_16_hls':
            url += '&type=enigma216_script&output=hls';
            break;
        case 'e2_16_ts':
            url += '&type=enigma216_script&output=mpegts';
            break;
        case 'e2_20_hls':
            url += '&type=enigma2_script&output=hls';
            break;
        case 'e2_20_ts':
            url += '&type=enigma2_script&output=mpegts';
            break;
        case 'm3u':
            url += '&type=m3u_plus';
            break;
        case 'm3u_hls':
            url += '&type=m3u_plus&output=hls';
            break;
        case 'm3u_ts':
            url += '&type=m3u_plus&output=mpegts';
            break;
    }
    
    document.getElementById('playlistUrl').value = url;
    document.getElementById('playlistDownloadLink').href = url;
}

function copyPlaylistUrl() {
    const urlInput = document.getElementById('playlistUrl');
    const url = urlInput.value;
    
    // Try modern clipboard API first
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            showCopySuccess();
        }).catch(() => {
            fallbackCopy(urlInput);
        });
    } else {
        fallbackCopy(urlInput);
    }
}

function fallbackCopy(input) {
    input.select();
    input.setSelectionRange(0, 99999);
    try {
        document.execCommand('copy');
        showCopySuccess();
    } catch (err) {
        alert('Copy failed. Please select and copy manually.');
    }
}

function showCopySuccess() {
    const msg = document.getElementById('copySuccess');
    msg.classList.remove('hidden');
    setTimeout(() => msg.classList.add('hidden'), 2000);
}

function userAction(userId, action) {
    fetch('<?= ADMIN_PATH ?>/api/user-action', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId, action: action })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Action failed');
        }
    })
    .catch(err => alert('Error: ' + err));
}
</script>

<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>

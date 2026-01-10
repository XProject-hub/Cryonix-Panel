<?php
/**
 * Cryonix Panel - Add/Edit User (Xtream UI Style)
 * Copyright 2026 XProject-Hub
 */
$pageTitle = isset($_GET['id']) ? 'Edit User' : 'Add User';
$isEdit = isset($_GET['id']);
$userId = $_GET['id'] ?? null;

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$user = null;
$error = '';
$success = '';
$bouquets = [];

try {
    $db = Database::getInstance();
    $bouquets = $db->fetchAll("SELECT * FROM bouquets ORDER BY bouquet_name") ?: [];
    
    if ($isEdit && $userId) {
        $user = $db->fetch("SELECT * FROM `lines` WHERE id = ?", [$userId]);
        if (!$user) {
            header('Location: ' . ADMIN_PATH . '/users');
            exit;
        }
    }
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username)) {
        $error = 'Username is required';
    } elseif (!$isEdit && empty($password)) {
        $error = 'Password is required';
    } else {
        try {
            $data = [
                'username' => $username,
                'max_connections' => (int)($_POST['max_connections'] ?? 1),
                'exp_date' => $_POST['exp_date'] ?? date('Y-m-d', strtotime('+1 month')),
                'is_mag' => isset($_POST['is_mag']) ? 1 : 0,
                'is_e2' => isset($_POST['is_e2']) ? 1 : 0,
                'is_isplock' => isset($_POST['is_isplock']) ? 1 : 0,
                'is_trial' => isset($_POST['is_trial']) ? 1 : 0,
                'is_restreamer' => isset($_POST['is_restreamer']) ? 1 : 0,
                'forced_country' => $_POST['forced_country'] ?? '',
                'admin_notes' => $_POST['admin_notes'] ?? '',
                'reseller_notes' => $_POST['reseller_notes'] ?? '',
                'allowed_ips' => $_POST['allowed_ips'] ?? '',
                'allowed_ua' => $_POST['allowed_ua'] ?? '',
                'allowed_outputs' => json_encode($_POST['outputs'] ?? ['hls', 'ts', 'rtmp']),
                'is_banned' => isset($_POST['is_banned']) ? 1 : 0
            ];
            
            if (!empty($password)) {
                $data['password'] = $password;
            }
            
            if ($isEdit) {
                $db->update('`lines`', $data, 'id = ?', [$userId]);
                $success = 'User updated successfully!';
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $newUserId = $db->insert('`lines`', $data);
                
                // Link bouquets
                if (!empty($_POST['bouquets'])) {
                    foreach ($_POST['bouquets'] as $bid) {
                        $db->insert('line_bouquets', ['line_id' => $newUserId, 'bouquet_id' => (int)$bid]);
                    }
                }
                
                $success = 'User created successfully!';
            }
            
            header('Location: ' . ADMIN_PATH . '/users');
            exit;
        } catch (\Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Generate random credentials
$randomUser = 'user_' . substr(md5(uniqid()), 0, 8);
$randomPass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 12);

ob_start();
?>

<style>
.tab-btn { transition: all 0.2s; }
.tab-btn.active { background: linear-gradient(90deg, #0ea5e9, #3b82f6); color: white; }
.tab-content { display: none; }
.tab-content.active { display: block; }
</style>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white"><?= $pageTitle ?></h1>
    <div class="flex gap-2">
        <a href="<?= ADMIN_PATH ?>/users" class="px-4 py-2 rounded-lg bg-gray-700 text-white text-sm hover:bg-gray-600 transition">← Back to Users</a>
    </div>
</div>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <!-- Tabs -->
    <div class="flex gap-1 mb-6">
        <button type="button" class="tab-btn active px-6 py-2.5 rounded-lg bg-gray-800 text-gray-300 font-medium" data-tab="details">
            📋 DETAILS
        </button>
        <button type="button" class="tab-btn px-6 py-2.5 rounded-lg bg-gray-800 text-gray-300 font-medium" data-tab="advanced">
            ⚙️ ADVANCED
        </button>
        <button type="button" class="tab-btn px-6 py-2.5 rounded-lg bg-gray-800 text-gray-300 font-medium" data-tab="restrictions">
            ⚠️ RESTRICTIONS
        </button>
        <button type="button" class="tab-btn px-6 py-2.5 rounded-lg bg-gray-800 text-gray-300 font-medium" data-tab="bouquets">
            📦 BOUQUETS
        </button>
    </div>
    
    <!-- Tab: Details -->
    <div id="tab-details" class="tab-content active glass rounded-xl p-6 border border-gray-800/50">
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm text-gray-400 mb-2">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? $randomUser) ?>" 
                    placeholder="auto-generate if blank"
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-700 text-white focus:border-cyan-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-2">Password</label>
                <input type="text" name="password" value="<?= $isEdit ? '' : $randomPass ?>" 
                    placeholder="<?= $isEdit ? 'leave blank to keep current' : 'auto-generate if blank' ?>"
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-700 text-white focus:border-cyan-500 focus:outline-none">
            </div>
        </div>
        
        <div class="grid grid-cols-4 gap-6 mt-6">
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-400">Enigma Device</span>
                <label class="relative inline-flex cursor-pointer">
                    <input type="checkbox" name="is_e2" <?= ($user['is_e2'] ?? 0) ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-cyan-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-400">MAG Device</span>
                <label class="relative inline-flex cursor-pointer">
                    <input type="checkbox" name="is_mag" <?= ($user['is_mag'] ?? 0) ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-cyan-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>
        </div>
        
        <div class="grid grid-cols-3 gap-6 mt-6">
            <div>
                <label class="block text-sm text-gray-400 mb-2">Max Connections</label>
                <input type="number" name="max_connections" value="<?= $user['max_connections'] ?? 1 ?>" min="1"
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-700 text-white focus:border-cyan-500 focus:outline-none">
            </div>
            <div class="flex items-center gap-3 pt-6">
                <span class="text-sm text-gray-400">ISP Lock</span>
                <label class="relative inline-flex cursor-pointer">
                    <input type="checkbox" name="is_isplock" <?= ($user['is_isplock'] ?? 0) ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-cyan-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>
        </div>
        
        <div class="grid grid-cols-3 gap-6 mt-6">
            <div>
                <label class="block text-sm text-gray-400 mb-2">Created</label>
                <input type="text" value="<?= $user['created_at'] ?? 'auto-generate' ?>" disabled
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-950 border border-gray-800 text-gray-500">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-2">Expiry</label>
                <input type="datetime-local" name="exp_date" value="<?= date('Y-m-d\TH:i', strtotime($user['exp_date'] ?? '+1 month')) ?>"
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-700 text-white focus:border-cyan-500 focus:outline-none">
            </div>
            <div class="flex items-center gap-3 pt-6">
                <input type="checkbox" name="never_expire" id="never" class="w-4 h-4 rounded bg-dark-900 border-gray-700">
                <label for="never" class="text-sm text-gray-400">Never</label>
            </div>
        </div>
        
        <div class="mt-6">
            <label class="block text-sm text-gray-400 mb-2">Admin Notes</label>
            <textarea name="admin_notes" rows="3" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-700 text-white focus:border-cyan-500 focus:outline-none"><?= htmlspecialchars($user['admin_notes'] ?? '') ?></textarea>
        </div>
        
        <div class="mt-6">
            <label class="block text-sm text-gray-400 mb-2">Reseller Notes</label>
            <textarea name="reseller_notes" rows="3" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-700 text-white focus:border-cyan-500 focus:outline-none"><?= htmlspecialchars($user['reseller_notes'] ?? '') ?></textarea>
        </div>
    </div>
    
    <!-- Tab: Advanced -->
    <div id="tab-advanced" class="tab-content glass rounded-xl p-6 border border-gray-800/50">
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm text-gray-400 mb-2">Forced Country</label>
                <select name="forced_country" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-700 text-white">
                    <option value="">Off</option>
                    <option value="US">United States</option>
                    <option value="UK">United Kingdom</option>
                    <option value="DE">Germany</option>
                    <option value="FR">France</option>
                    <option value="IT">Italy</option>
                    <option value="ES">Spain</option>
                    <option value="NL">Netherlands</option>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-4 gap-6 mt-6">
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-400">Trial Account</span>
                <label class="relative inline-flex cursor-pointer">
                    <input type="checkbox" name="is_trial" <?= ($user['is_trial'] ?? 0) ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-cyan-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-400">Restreamer</span>
                <label class="relative inline-flex cursor-pointer">
                    <input type="checkbox" name="is_restreamer" <?= ($user['is_restreamer'] ?? 0) ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-cyan-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>
        </div>
        
        <div class="mt-6">
            <label class="block text-sm text-gray-400 mb-2">Access Output</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2"><input type="checkbox" name="outputs[]" value="hls" checked class="w-4 h-4 rounded"><span class="text-gray-300">HLS</span></label>
                <label class="flex items-center gap-2"><input type="checkbox" name="outputs[]" value="ts" checked class="w-4 h-4 rounded"><span class="text-gray-300">MPEGTS</span></label>
                <label class="flex items-center gap-2"><input type="checkbox" name="outputs[]" value="rtmp" checked class="w-4 h-4 rounded"><span class="text-gray-300">RTMP</span></label>
            </div>
        </div>
    </div>
    
    <!-- Tab: Restrictions -->
    <div id="tab-restrictions" class="tab-content glass rounded-xl p-6 border border-gray-800/50">
        <div class="mb-6">
            <label class="block text-sm text-gray-400 mb-2">Allowed IP Addresses</label>
            <div class="flex gap-2 mb-2">
                <input type="text" id="new-ip" placeholder="192.168.1.1" class="flex-1 px-4 py-2 rounded-lg bg-dark-900 border border-gray-700 text-white">
                <button type="button" onclick="addIp()" class="px-4 py-2 rounded-lg bg-teal-500 text-white">+</button>
                <button type="button" onclick="clearIps()" class="px-4 py-2 rounded-lg bg-red-500 text-white">×</button>
            </div>
            <textarea name="allowed_ips" id="allowed-ips" rows="4" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-700 text-white font-mono text-sm"><?= htmlspecialchars($user['allowed_ips'] ?? '') ?></textarea>
        </div>
        
        <div>
            <label class="block text-sm text-gray-400 mb-2">Allowed User-Agents</label>
            <div class="flex gap-2 mb-2">
                <input type="text" id="new-ua" placeholder="VLC/3.0" class="flex-1 px-4 py-2 rounded-lg bg-dark-900 border border-gray-700 text-white">
                <button type="button" onclick="addUa()" class="px-4 py-2 rounded-lg bg-teal-500 text-white">+</button>
                <button type="button" onclick="clearUas()" class="px-4 py-2 rounded-lg bg-red-500 text-white">×</button>
            </div>
            <textarea name="allowed_ua" id="allowed-ua" rows="4" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-700 text-white font-mono text-sm"><?= htmlspecialchars($user['allowed_ua'] ?? '') ?></textarea>
        </div>
    </div>
    
    <!-- Tab: Bouquets -->
    <div id="tab-bouquets" class="tab-content glass rounded-xl p-6 border border-gray-800/50">
        <?php if (empty($bouquets)): ?>
        <div class="text-center py-8">
            <p class="text-gray-500 mb-4">No bouquets created yet.</p>
            <a href="<?= ADMIN_PATH ?>/bouquets/add" class="text-cyan-400 hover:text-cyan-300">Create your first bouquet →</a>
        </div>
        <?php else: ?>
        <div class="space-y-2 max-h-96 overflow-y-auto">
            <?php foreach ($bouquets as $b): ?>
            <label class="flex items-center gap-3 p-3 rounded-lg bg-dark-900 hover:bg-dark-800 cursor-pointer">
                <input type="checkbox" name="bouquets[]" value="<?= $b['id'] ?>" class="w-5 h-5 rounded bg-dark-950 border-gray-700 text-cyan-500">
                <span class="text-white"><?= htmlspecialchars($b['bouquet_name']) ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer Actions -->
    <div class="flex justify-between mt-6">
        <button type="button" id="prev-btn" class="px-6 py-2.5 rounded-lg bg-gray-700 text-white font-medium hover:bg-gray-600 transition hidden">
            ← Previous
        </button>
        <div class="flex gap-3 ml-auto">
            <button type="button" id="next-btn" class="px-6 py-2.5 rounded-lg bg-teal-600 text-white font-medium hover:bg-teal-500 transition">
                Next →
            </button>
            <button type="submit" id="submit-btn" class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium hover:from-cyan-600 hover:to-blue-600 transition hidden">
                <?= $isEdit ? 'Update User' : 'Add User' ?>
            </button>
        </div>
    </div>
</form>

<script>
const tabs = ['details', 'advanced', 'restrictions', 'bouquets'];
let currentTab = 0;

document.querySelectorAll('.tab-btn').forEach((btn, i) => {
    btn.addEventListener('click', () => switchTab(i));
});

document.getElementById('next-btn').addEventListener('click', () => {
    if (currentTab < tabs.length - 1) switchTab(currentTab + 1);
});

document.getElementById('prev-btn').addEventListener('click', () => {
    if (currentTab > 0) switchTab(currentTab - 1);
});

function switchTab(index) {
    currentTab = index;
    document.querySelectorAll('.tab-btn').forEach((btn, i) => btn.classList.toggle('active', i === index));
    document.querySelectorAll('.tab-content').forEach((content, i) => content.classList.toggle('active', i === index));
    
    document.getElementById('prev-btn').classList.toggle('hidden', index === 0);
    document.getElementById('next-btn').classList.toggle('hidden', index === tabs.length - 1);
    document.getElementById('submit-btn').classList.toggle('hidden', index !== tabs.length - 1);
}

function addIp() {
    const ip = document.getElementById('new-ip').value.trim();
    if (ip) {
        const ta = document.getElementById('allowed-ips');
        ta.value = (ta.value ? ta.value + '\n' : '') + ip;
        document.getElementById('new-ip').value = '';
    }
}

function clearIps() { document.getElementById('allowed-ips').value = ''; }

function addUa() {
    const ua = document.getElementById('new-ua').value.trim();
    if (ua) {
        const ta = document.getElementById('allowed-ua');
        ta.value = (ta.value ? ta.value + '\n' : '') + ua;
        document.getElementById('new-ua').value = '';
    }
}

function clearUas() { document.getElementById('allowed-ua').value = ''; }
</script>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>

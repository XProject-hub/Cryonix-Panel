<?php
/**
 * Cryonix Panel - Add/Edit Server
 * Copyright 2026 XProject-Hub
 */
$pageTitle = isset($_GET['id']) ? 'Edit Server' : 'Add Server';
$isEdit = isset($_GET['id']);
$serverId = $_GET['id'] ?? null;

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$server = null;
$error = '';

try {
    $db = Database::getInstance();
    
    if ($isEdit && $serverId) {
        $server = $db->fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
        if (!$server) {
            header('Location: ' . ADMIN_PATH . '/servers');
            exit;
        }
    }
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'server_name' => $_POST['server_name'] ?? '',
            'domain_name' => $_POST['domain_name'] ?? '',
            'server_ip' => $_POST['server_ip'] ?? '',
            'vpn_ip' => $_POST['vpn_ip'] ?? '',
            'ssh_password' => $_POST['ssh_password'] ?? '',
            'max_clients' => (int)($_POST['max_clients'] ?? 1000),
            'is_timeshift' => isset($_POST['is_timeshift']) ? 1 : 0,
            'is_duplex' => isset($_POST['is_duplex']) ? 1 : 0,
            'http_port' => (int)($_POST['http_port'] ?? 8080),
            'https_port' => (int)($_POST['https_port'] ?? 8443),
            'rtmp_port' => (int)($_POST['rtmp_port'] ?? 25462),
            'ssh_port' => (int)($_POST['ssh_port'] ?? 22),
            'time_diff' => (int)($_POST['time_diff'] ?? 0),
            'network_interface' => $_POST['network_interface'] ?? '',
            'network_speed' => (int)($_POST['network_speed'] ?? 1000),
            'os_type' => $_POST['os_type'] ?? 'Ubuntu 22',
            'geoip_enabled' => isset($_POST['geoip_enabled']) ? 1 : 0,
            'geoip_priority' => $_POST['geoip_priority'] ?? 'high',
            'geoip_countries' => $_POST['geoip_countries'] ?? '',
            'isp_enabled' => isset($_POST['isp_enabled']) ? 1 : 0,
            'isp_priority' => $_POST['isp_priority'] ?? 'high',
            'allowed_isps' => $_POST['allowed_isps'] ?? '',
            'status' => 'online'
        ];
        
        if ($isEdit) {
            $db->update('servers', $data, 'id = ?', [$serverId]);
        } else {
            $data['is_main'] = 0;
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->insert('servers', $data);
        }
        
        header('Location: ' . ADMIN_PATH . '/servers');
        exit;
    } catch (\Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

ob_start();
?>

<style>
.tab-btn { transition: all 0.2s; }
.tab-btn.active { background: linear-gradient(90deg, #0ea5e9, #3b82f6); color: white; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.form-label { display: block; font-size: 0.75rem; color: #9ca3af; margin-bottom: 0.5rem; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; border-radius: 0.5rem; background: #0f1115; border: 1px solid #374151; color: white; font-size: 0.875rem; }
.form-input:focus { outline: none; border-color: #0ea5e9; }
.toggle { position: relative; display: inline-flex; cursor: pointer; }
.toggle input { position: absolute; opacity: 0; }
.toggle-bg { width: 2.5rem; height: 1.25rem; background: #374151; border-radius: 9999px; transition: background 0.2s; }
.toggle input:checked + .toggle-bg { background: #0ea5e9; }
.toggle-dot { position: absolute; top: 0.125rem; left: 0.125rem; width: 1rem; height: 1rem; background: white; border-radius: 9999px; transition: transform 0.2s; }
.toggle input:checked ~ .toggle-dot { transform: translateX(1.25rem); }
</style>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-white"><?= $pageTitle ?></h1>
    <a href="<?= ADMIN_PATH ?>/servers" class="px-4 py-2 rounded-lg bg-dark-800 text-gray-400 hover:text-white text-xs transition">← Back to Servers</a>
</div>

<?php if ($error): ?>
<div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <!-- Tabs -->
    <div class="flex gap-1 mb-4">
        <button type="button" class="tab-btn active px-5 py-2 rounded-lg bg-dark-800 text-gray-400 text-xs font-medium" data-tab="details">DETAILS</button>
        <button type="button" class="tab-btn px-5 py-2 rounded-lg bg-dark-800 text-gray-400 text-xs font-medium" data-tab="advanced">ADVANCED</button>
        <button type="button" class="tab-btn px-5 py-2 rounded-lg bg-dark-800 text-gray-400 text-xs font-medium" data-tab="isp">ISP MANAGER</button>
    </div>
    
    <!-- Tab: Details -->
    <div id="tab-details" class="tab-content active glass rounded-xl p-5 border border-gray-800/50">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Server Name</label>
                <input type="text" name="server_name" value="<?= htmlspecialchars($server['server_name'] ?? '') ?>" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Domain Name</label>
                <input type="text" name="domain_name" value="<?= htmlspecialchars($server['domain_name'] ?? '') ?>" class="form-input" placeholder="cdn.example.com">
            </div>
            <div>
                <label class="form-label">Server IP</label>
                <input type="text" name="server_ip" value="<?= htmlspecialchars($server['server_ip'] ?? '') ?>" class="form-input" required>
            </div>
            <div>
                <label class="form-label">VPN IP</label>
                <input type="text" name="vpn_ip" value="<?= htmlspecialchars($server['vpn_ip'] ?? '') ?>" class="form-input">
            </div>
            <div>
                <label class="form-label">Root Password</label>
                <input type="password" name="ssh_password" value="<?= htmlspecialchars($server['ssh_password'] ?? '') ?>" class="form-input">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Max Clients</label>
                    <input type="number" name="max_clients" value="<?= $server['max_clients'] ?? 1000 ?>" class="form-input">
                </div>
                <div class="flex items-center gap-4 pt-6">
                    <label class="toggle">
                        <input type="checkbox" name="is_timeshift" <?= ($server['is_timeshift'] ?? 0) ? 'checked' : '' ?>>
                        <div class="toggle-bg"></div>
                        <div class="toggle-dot"></div>
                    </label>
                    <span class="text-xs text-gray-400">Timeshift Only</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs text-gray-400">Duplex</span>
                <label class="toggle">
                    <input type="checkbox" name="is_duplex" <?= ($server['is_duplex'] ?? 0) ? 'checked' : '' ?>>
                    <div class="toggle-bg"></div>
                    <div class="toggle-dot"></div>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Tab: Advanced -->
    <div id="tab-advanced" class="tab-content glass rounded-xl p-5 border border-gray-800/50">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">HTTP Port</label>
                <input type="number" name="http_port" value="<?= $server['http_port'] ?? 8080 ?>" class="form-input">
            </div>
            <div>
                <label class="form-label">HTTPS Port</label>
                <input type="number" name="https_port" value="<?= $server['https_port'] ?? 8443 ?>" class="form-input">
            </div>
            <div>
                <label class="form-label">RTMP Port</label>
                <input type="number" name="rtmp_port" value="<?= $server['rtmp_port'] ?? 25462 ?>" class="form-input">
            </div>
            <div>
                <label class="form-label">SSH Port</label>
                <input type="number" name="ssh_port" value="<?= $server['ssh_port'] ?? 22 ?>" class="form-input">
            </div>
            <div>
                <label class="form-label">Time Difference - Seconds</label>
                <input type="number" name="time_diff" value="<?= $server['time_diff'] ?? 0 ?>" class="form-input">
            </div>
            <div>
                <label class="form-label">Network Speed (Mbps)</label>
                <input type="number" name="network_speed" value="<?= $server['network_speed'] ?? 1000 ?>" class="form-input">
            </div>
            <div>
                <label class="form-label">Network Interface</label>
                <select name="network_interface" class="form-input">
                    <option value="">Auto Detect</option>
                    <option value="eth0" <?= ($server['network_interface'] ?? '') === 'eth0' ? 'selected' : '' ?>>eth0</option>
                    <option value="eth1" <?= ($server['network_interface'] ?? '') === 'eth1' ? 'selected' : '' ?>>eth1</option>
                    <option value="ens3" <?= ($server['network_interface'] ?? '') === 'ens3' ? 'selected' : '' ?>>ens3</option>
                </select>
            </div>
            <div>
                <label class="form-label">Operating System</label>
                <select name="os_type" class="form-input">
                    <option value="Ubuntu 22" <?= ($server['os_type'] ?? '') === 'Ubuntu 22' ? 'selected' : '' ?>>Ubuntu 22</option>
                    <option value="Ubuntu 20" <?= ($server['os_type'] ?? '') === 'Ubuntu 20' ? 'selected' : '' ?>>Ubuntu 20</option>
                    <option value="Ubuntu 18" <?= ($server['os_type'] ?? '') === 'Ubuntu 18' ? 'selected' : '' ?>>Ubuntu 18</option>
                    <option value="Debian 11" <?= ($server['os_type'] ?? '') === 'Debian 11' ? 'selected' : '' ?>>Debian 11</option>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div class="flex items-center gap-4">
                <span class="text-xs text-gray-400">GeoIP Load Balancing</span>
                <label class="toggle">
                    <input type="checkbox" name="geoip_enabled" <?= ($server['geoip_enabled'] ?? 0) ? 'checked' : '' ?>>
                    <div class="toggle-bg"></div>
                    <div class="toggle-dot"></div>
                </label>
                <select name="geoip_priority" class="form-input w-32">
                    <option value="high">High Priority</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
        </div>
        
        <div class="mt-4">
            <label class="form-label">GeoIP Countries (comma separated)</label>
            <input type="text" name="geoip_countries" value="<?= htmlspecialchars($server['geoip_countries'] ?? '') ?>" class="form-input" placeholder="US, UK, DE, FR">
        </div>
    </div>
    
    <!-- Tab: ISP Manager -->
    <div id="tab-isp" class="tab-content glass rounded-xl p-5 border border-gray-800/50">
        <div class="flex items-center gap-4 mb-4">
            <span class="text-xs text-gray-400">Enable ISP</span>
            <label class="toggle">
                <input type="checkbox" name="isp_enabled" <?= ($server['isp_enabled'] ?? 0) ? 'checked' : '' ?>>
                <div class="toggle-bg"></div>
                <div class="toggle-dot"></div>
            </label>
            <select name="isp_priority" class="form-input w-40">
                <option value="high">High Priority</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
        </div>
        
        <div>
            <label class="form-label">Allowed ISP Names</label>
            <div class="flex gap-2 mb-2">
                <input type="text" id="new-isp" placeholder="ISP Name" class="form-input flex-1">
                <button type="button" onclick="addIsp()" class="px-3 py-2 rounded-lg bg-teal-500 text-white text-xs">+</button>
                <button type="button" onclick="clearIsps()" class="px-3 py-2 rounded-lg bg-red-500 text-white text-xs">×</button>
            </div>
            <textarea name="allowed_isps" id="allowed-isps" rows="4" class="form-input font-mono text-xs"><?= htmlspecialchars($server['allowed_isps'] ?? '') ?></textarea>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="flex justify-between mt-4">
        <button type="button" id="prev-btn" class="px-5 py-2 rounded-lg bg-dark-800 text-gray-400 text-xs font-medium hover:text-white transition hidden">Previous</button>
        <div class="flex gap-2 ml-auto">
            <button type="button" id="next-btn" class="px-5 py-2 rounded-lg bg-teal-600 text-white text-xs font-medium hover:bg-teal-500 transition">Next</button>
            <button type="submit" id="submit-btn" class="px-5 py-2 rounded-lg bg-cryo-500 text-white text-xs font-medium hover:bg-cryo-600 transition hidden"><?= $isEdit ? 'Update' : 'Add' ?></button>
        </div>
    </div>
</form>

<script>
const tabs = ['details', 'advanced', 'isp'];
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

function addIsp() {
    const isp = document.getElementById('new-isp').value.trim();
    if (isp) {
        const ta = document.getElementById('allowed-isps');
        ta.value = (ta.value ? ta.value + '\n' : '') + isp;
        document.getElementById('new-isp').value = '';
    }
}

function clearIsps() { document.getElementById('allowed-isps').value = ''; }
</script>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>

<?php
/**
 * Cryonix Panel - Install Load Balancer
 * Remote server installation
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Load Balancer Installation';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serverName = trim($_POST['server_name'] ?? '');
    $serverIp = trim($_POST['server_ip'] ?? '');
    $sshPassword = trim($_POST['ssh_password'] ?? '');
    $sshPort = (int)($_POST['ssh_port'] ?? 22);
    $httpPort = (int)($_POST['http_port'] ?? 8080);
    $httpsPort = (int)($_POST['https_port'] ?? 8443);
    $rtmpPort = (int)($_POST['rtmp_port'] ?? 25462);
    $osType = $_POST['os_type'] ?? 'Ubuntu 22';
    
    if (empty($serverName) || empty($serverIp) || empty($sshPassword)) {
        $error = 'Server Name, IP and SSH Password are required';
    } else {
        try {
            $db = Database::getInstance();
            
            // Add server to database first
            $db->insert('servers', [
                'server_name' => $serverName,
                'server_ip' => $serverIp,
                'ssh_password' => $sshPassword,
                'ssh_port' => $sshPort,
                'http_port' => $httpPort,
                'https_port' => $httpsPort,
                'rtmp_port' => $rtmpPort,
                'os_type' => $osType,
                'is_main' => 0,
                'status' => 'installing',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $success = 'Server added! Installation will start in background. Check server status on Manage Servers page.';
            
            // TODO: Trigger background installation via SSH
            // This would connect to the server and run install script
            
        } catch (\Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

ob_start();
?>

<style>
.form-label { display: block; font-size: 0.75rem; color: #9ca3af; margin-bottom: 0.5rem; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; border-radius: 0.5rem; background: #0f1115; border: 1px solid #374151; color: white; font-size: 0.875rem; }
.form-input:focus { outline: none; border-color: #0ea5e9; }
</style>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-white">Load Balancer Installation</h1>
    <a href="<?= ADMIN_PATH ?>/servers" class="px-4 py-2 rounded-lg bg-dark-800 text-gray-400 hover:text-white text-xs transition">← Back to Servers</a>
</div>

<?php if ($error): ?>
<div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST" class="max-w-2xl">
    <div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
        <div class="bg-gradient-to-r from-cryo-500 to-blue-500 px-5 py-3">
            <span class="text-white text-sm font-medium">DETAILS</span>
        </div>
        
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Server Name</label>
                    <input type="text" name="server_name" class="form-input" required placeholder="Load Balancer 1">
                </div>
                <div>
                    <label class="form-label">Server IP</label>
                    <input type="text" name="server_ip" class="form-input" required placeholder="192.168.1.100">
                </div>
            </div>
            
            <div>
                <label class="form-label">SSH Password</label>
                <input type="password" name="ssh_password" class="form-input" required>
            </div>
            
            <div>
                <label class="form-label">System OS</label>
                <select name="os_type" class="form-input">
                    <option value="Ubuntu 22">Ubuntu 22</option>
                    <option value="Ubuntu 20">Ubuntu 20</option>
                    <option value="Ubuntu 18">Ubuntu 18</option>
                    <option value="Debian 11">Debian 11</option>
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">SSH Port</label>
                    <input type="number" name="ssh_port" value="22" class="form-input">
                </div>
                <div>
                    <label class="form-label">HTTP Port</label>
                    <input type="number" name="http_port" value="8080" class="form-input">
                </div>
                <div>
                    <label class="form-label">HTTPS Port</label>
                    <input type="number" name="https_port" value="8443" class="form-input">
                </div>
                <div>
                    <label class="form-label">RTMP Port</label>
                    <input type="number" name="rtmp_port" value="25462" class="form-input">
                </div>
            </div>
            
            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-blue-500 text-white text-sm font-medium hover:bg-blue-600 transition">
                    Install Server
                </button>
            </div>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


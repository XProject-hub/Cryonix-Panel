<?php
/**
 * Cryonix Panel - Add/Edit Server (Load Balancer)
 * Copyright 2026 XProject-Hub
 */
$pageTitle = isset($_GET['id']) ? 'Edit Server' : 'Add Load Balancer';
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
    $name = trim($_POST['server_name'] ?? '');
    $ip = trim($_POST['server_ip'] ?? '');
    $httpPort = (int)($_POST['http_port'] ?? 80);
    $status = $_POST['status'] ?? 'online';
    
    if (empty($name)) {
        $error = 'Server name is required';
    } elseif (empty($ip)) {
        $error = 'Server IP is required';
    } else {
        try {
            $data = [
                'server_name' => $name,
                'server_ip' => $ip,
                'http_port' => $httpPort,
                'status' => $status,
                'is_main' => 0
            ];
            
            if ($isEdit) {
                // Don't allow changing main server to non-main
                if ($server['is_main']) {
                    unset($data['is_main']);
                }
                $db->update('servers', $data, 'id = ?', [$serverId]);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $db->insert('servers', $data);
            }
            
            header('Location: ' . ADMIN_PATH . '/servers');
            exit;
        } catch (\Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

ob_start();
?>

<div class="mb-8">
    <a href="<?= ADMIN_PATH ?>/servers" class="text-gray-400 hover:text-cyan-400 transition mb-2 inline-flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Servers
    </a>
    <h1 class="text-3xl font-bold text-white"><?= $pageTitle ?></h1>
</div>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="max-w-2xl">
    <div class="glass rounded-xl p-6 border border-gray-800/50 space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Server Name *</label>
            <input type="text" name="server_name" value="<?= htmlspecialchars($server['server_name'] ?? '') ?>" required
                placeholder="Load Balancer 1"
                class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">IP Address *</label>
                <input type="text" name="server_ip" value="<?= htmlspecialchars($server['server_ip'] ?? '') ?>" required
                    placeholder="192.168.1.100"
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">HTTP Port</label>
                <input type="number" name="http_port" value="<?= $server['http_port'] ?? 80 ?>"
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
            <select name="status" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
                <option value="online" <?= ($server['status'] ?? 'online') === 'online' ? 'selected' : '' ?>>Online</option>
                <option value="offline" <?= ($server['status'] ?? '') === 'offline' ? 'selected' : '' ?>>Offline</option>
                <option value="maintenance" <?= ($server['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
            </select>
        </div>
        
        <div class="flex gap-4 pt-4 border-t border-gray-800">
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold hover:from-cyan-600 hover:to-blue-600 transition">
                <?= $isEdit ? 'Update Server' : 'Add Server' ?>
            </button>
            <a href="<?= ADMIN_PATH ?>/servers" class="px-6 py-2.5 rounded-lg bg-gray-800 text-gray-300 font-semibold hover:bg-gray-700 transition">
                Cancel
            </a>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


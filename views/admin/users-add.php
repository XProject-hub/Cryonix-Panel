<?php
/**
 * Cryonix Panel - Add/Edit User
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
    $maxConnections = (int)($_POST['max_connections'] ?? 1);
    $expDate = $_POST['exp_date'] ?? date('Y-m-d', strtotime('+1 month'));
    $bouquetId = (int)($_POST['bouquet_id'] ?? 0);
    $isBanned = isset($_POST['is_banned']) ? 1 : 0;
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($username)) {
        $error = 'Username is required';
    } elseif (!$isEdit && empty($password)) {
        $error = 'Password is required';
    } else {
        try {
            $data = [
                'username' => $username,
                'max_connections' => $maxConnections,
                'exp_date' => $expDate,
                'bouquet_id' => $bouquetId ?: null,
                'is_banned' => $isBanned,
                'notes' => $notes
            ];
            
            if (!empty($password)) {
                $data['password'] = $password;
            }
            
            if ($isEdit) {
                $db->update('`lines`', $data, 'id = ?', [$userId]);
                $success = 'User updated successfully!';
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $db->insert('`lines`', $data);
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

<div class="mb-8">
    <a href="<?= ADMIN_PATH ?>/users" class="text-gray-400 hover:text-cyan-400 transition mb-2 inline-flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Users
    </a>
    <h1 class="text-3xl font-bold text-white"><?= $pageTitle ?></h1>
</div>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="max-w-2xl">
    <div class="glass rounded-xl p-6 border border-gray-800/50 space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Username *</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? $randomUser) ?>" required
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Password <?= $isEdit ? '' : '*' ?></label>
                <input type="text" name="password" value="<?= $isEdit ? '' : $randomPass ?>" <?= $isEdit ? '' : 'required' ?>
                    placeholder="<?= $isEdit ? 'Leave blank to keep current' : '' ?>"
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Max Connections</label>
                <input type="number" name="max_connections" value="<?= $user['max_connections'] ?? 1 ?>" min="1"
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Expiry Date</label>
                <input type="date" name="exp_date" value="<?= $user['exp_date'] ?? date('Y-m-d', strtotime('+1 month')) ?>"
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Bouquet</label>
            <select name="bouquet_id" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
                <option value="">All Channels</option>
                <?php foreach ($bouquets as $b): ?>
                <option value="<?= $b['id'] ?>" <?= ($user['bouquet_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['bouquet_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Notes</label>
            <textarea name="notes" rows="2" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50"><?= htmlspecialchars($user['notes'] ?? '') ?></textarea>
        </div>
        
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_banned" id="is_banned" <?= ($user['is_banned'] ?? 0) ? 'checked' : '' ?> class="w-4 h-4 rounded bg-dark-900 border-gray-800 text-cyan-500">
            <label for="is_banned" class="text-gray-300">Banned</label>
        </div>
        
        <div class="flex gap-4 pt-4 border-t border-gray-800">
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold hover:from-cyan-600 hover:to-blue-600 transition">
                <?= $isEdit ? 'Update User' : 'Create User' ?>
            </button>
            <a href="<?= ADMIN_PATH ?>/users" class="px-6 py-2.5 rounded-lg bg-gray-800 text-gray-300 font-semibold hover:bg-gray-700 transition">
                Cancel
            </a>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


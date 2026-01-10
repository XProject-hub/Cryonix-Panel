<?php
/**
 * Cryonix Panel - Users Management
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Users / Lines';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$users = [];
$error = '';

try {
    $db = Database::getInstance();
    $users = $db->fetchAll("SELECT * FROM `lines` ORDER BY created_at DESC LIMIT 100") ?: [];
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-white">Users / Lines</h1>
        <p class="text-gray-400">Manage your IPTV subscribers</p>
    </div>
    <a href="<?= ADMIN_PATH ?>/users/add" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold hover:from-cyan-600 hover:to-blue-600 transition">
        + Add User
    </a>
</div>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if (empty($users) && !$error): ?>
<div class="glass rounded-xl p-12 text-center border border-gray-800/50">
    <div class="w-16 h-16 rounded-full bg-gray-800 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
    </div>
    <h3 class="text-lg font-semibold text-white mb-2">No Users Yet</h3>
    <p class="text-gray-400 mb-4">Create your first user/line to get started</p>
    <a href="<?= ADMIN_PATH ?>/users/add" class="inline-block px-6 py-2.5 rounded-xl bg-cyan-500 text-white font-semibold hover:bg-cyan-600 transition">
        Add First User
    </a>
</div>
<?php else: ?>
<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-dark-800">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Username</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Password</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Connections</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Expires</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php foreach ($users as $user): ?>
            <tr class="hover:bg-dark-800/50 transition">
                <td class="px-4 py-3 font-mono text-white"><?= htmlspecialchars($user['username']) ?></td>
                <td class="px-4 py-3 font-mono text-gray-400"><?= htmlspecialchars($user['password']) ?></td>
                <td class="px-4 py-3">
                    <?php if ($user['is_banned']): ?>
                    <span class="px-2 py-1 rounded-full bg-red-500/10 text-red-400 text-xs">Banned</span>
                    <?php elseif (strtotime($user['exp_date']) < time()): ?>
                    <span class="px-2 py-1 rounded-full bg-amber-500/10 text-amber-400 text-xs">Expired</span>
                    <?php else: ?>
                    <span class="px-2 py-1 rounded-full bg-green-500/10 text-green-400 text-xs">Active</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-gray-400"><?= $user['max_connections'] ?></td>
                <td class="px-4 py-3 text-gray-400"><?= date('M d, Y', strtotime($user['exp_date'])) ?></td>
                <td class="px-4 py-3 text-right">
                    <a href="<?= ADMIN_PATH ?>/users/edit/<?= $user['id'] ?>" class="text-cyan-400 hover:text-cyan-300 text-sm">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


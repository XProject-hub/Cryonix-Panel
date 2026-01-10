<?php
$pageTitle = 'User Stats';
$userId = $_GET['id'] ?? 0;
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$user = null;
try { $db = Database::getInstance(); $user = $db->fetch("SELECT * FROM `lines` WHERE id = ?", [$userId]); } catch (\Exception $e) {}
ob_start();
?>
<div class="mb-4">
    <a href="<?= ADMIN_PATH ?>/users" class="text-cryo-400 text-xs hover:underline">&larr; Back to Users</a>
    <h1 class="text-xl font-bold text-white mt-2">User Statistics</h1>
</div>
<?php if ($user): ?>
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="glass rounded-xl p-4 border border-gray-800/50"><div class="text-gray-500 text-xs">Username</div><div class="text-lg font-bold text-white"><?= htmlspecialchars($user['username']) ?></div></div>
    <div class="glass rounded-xl p-4 border border-gray-800/50"><div class="text-gray-500 text-xs">Total Connections</div><div class="text-lg font-bold text-cryo-400">0</div></div>
    <div class="glass rounded-xl p-4 border border-gray-800/50"><div class="text-gray-500 text-xs">Data Usage</div><div class="text-lg font-bold text-green-400">0 GB</div></div>
    <div class="glass rounded-xl p-4 border border-gray-800/50"><div class="text-gray-500 text-xs">Last Activity</div><div class="text-lg font-bold text-amber-400">-</div></div>
</div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <h2 class="text-white font-medium mb-4">Connection History</h2>
    <div class="text-center py-8 text-gray-500">No connection history available</div>
</div>
<?php else: ?>
<div class="glass rounded-xl border border-red-500/50 p-6 text-red-400">User not found</div>
<?php endif; ?>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


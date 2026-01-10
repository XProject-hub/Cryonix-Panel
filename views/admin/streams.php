<?php
/**
 * Cryonix Panel - Streams/Channels Management
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Live Channels';

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$streams = [];
$error = '';

try {
    $db = Database::getInstance();
    $streams = $db->fetchAll("SELECT s.*, c.category_name FROM streams s LEFT JOIN stream_categories c ON s.category_id = c.id ORDER BY s.stream_display_name LIMIT 100") ?: [];
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

ob_start();
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-white">Live Channels</h1>
        <p class="text-gray-400">Manage your live TV streams</p>
    </div>
    <a href="<?= ADMIN_PATH ?>/streams/add" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold hover:from-cyan-600 hover:to-blue-600 transition">
        + Add Channel
    </a>
</div>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if (empty($streams) && !$error): ?>
<div class="glass rounded-xl p-12 text-center border border-gray-800/50">
    <div class="w-16 h-16 rounded-full bg-gray-800 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
    </div>
    <h3 class="text-lg font-semibold text-white mb-2">No Channels Yet</h3>
    <p class="text-gray-400 mb-4">Add your first live channel to start streaming</p>
    <a href="<?= ADMIN_PATH ?>/streams/add" class="inline-block px-6 py-2.5 rounded-xl bg-cyan-500 text-white font-semibold hover:bg-cyan-600 transition">
        Add First Channel
    </a>
</div>
<?php else: ?>
<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-dark-800">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Channel</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Category</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Type</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php foreach ($streams as $stream): ?>
            <tr class="hover:bg-dark-800/50 transition">
                <td class="px-4 py-3 text-white"><?= htmlspecialchars($stream['stream_display_name']) ?></td>
                <td class="px-4 py-3 text-gray-400"><?= htmlspecialchars($stream['category_name'] ?? 'Uncategorized') ?></td>
                <td class="px-4 py-3 text-gray-400"><?= ucfirst($stream['stream_type'] ?? 'live') ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full bg-green-500/10 text-green-400 text-xs">Active</span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="<?= ADMIN_PATH ?>/streams/edit/<?= $stream['id'] ?>" class="text-cyan-400 hover:text-cyan-300 text-sm">Edit</a>
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


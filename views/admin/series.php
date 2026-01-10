<?php
$pageTitle = 'Series';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$series = [];
try { $db = Database::getInstance(); $series = $db->fetchAll("SELECT * FROM series ORDER BY title LIMIT 100") ?: []; } catch (\Exception $e) {}
ob_start();
?>
<div class="flex items-center justify-between mb-8">
    <div><h1 class="text-3xl font-bold text-white">Series</h1><p class="text-gray-400">Manage your TV series</p></div>
    <a href="<?= ADMIN_PATH ?>/series/add" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold">+ Add Series</a>
</div>
<?php if (empty($series)): ?>
<div class="glass rounded-xl p-12 text-center border border-gray-800/50">
    <h3 class="text-lg font-semibold text-white mb-2">No Series Yet</h3>
    <a href="<?= ADMIN_PATH ?>/series/add" class="inline-block px-6 py-2.5 rounded-xl bg-cyan-500 text-white font-semibold mt-4">Add First Series</a>
</div>
<?php else: ?>
<div class="glass rounded-xl border border-gray-800/50 p-4">
    <p class="text-gray-400"><?= count($series) ?> series in library</p>
</div>
<?php endif; ?>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


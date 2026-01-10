<?php
$pageTitle = 'Bouquets';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$bouquets = [];
try { $db = Database::getInstance(); $bouquets = $db->fetchAll("SELECT * FROM bouquets ORDER BY sort_order") ?: []; } catch (\Exception $e) {}
ob_start();
?>
<div class="flex items-center justify-between mb-8">
    <div><h1 class="text-3xl font-bold text-white">Bouquets</h1><p class="text-gray-400">Group channels into packages</p></div>
    <a href="<?= ADMIN_PATH ?>/bouquets/add" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold">+ Add Bouquet</a>
</div>
<?php if (empty($bouquets)): ?>
<div class="glass rounded-xl p-12 text-center border border-gray-800/50">
    <h3 class="text-lg font-semibold text-white mb-2">No Bouquets Yet</h3>
    <a href="<?= ADMIN_PATH ?>/bouquets/add" class="inline-block px-6 py-2.5 rounded-xl bg-cyan-500 text-white font-semibold mt-4">Add First Bouquet</a>
</div>
<?php else: ?>
<div class="grid grid-cols-3 gap-4">
    <?php foreach ($bouquets as $b): ?>
    <div class="glass rounded-xl p-4 border border-gray-800/50">
        <div class="font-semibold text-white"><?= htmlspecialchars($b['bouquet_name']) ?></div>
        <div class="text-sm text-gray-400 mt-1">Order: <?= $b['sort_order'] ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


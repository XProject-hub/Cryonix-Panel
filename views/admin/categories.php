<?php
$pageTitle = 'Categories';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$categories = [];
try { $db = Database::getInstance(); $categories = $db->fetchAll("SELECT * FROM stream_categories ORDER BY category_type, sort_order") ?: []; } catch (\Exception $e) {}
ob_start();
?>
<div class="flex items-center justify-between mb-8">
    <div><h1 class="text-3xl font-bold text-white">Categories</h1><p class="text-gray-400">Organize your content</p></div>
    <a href="<?= ADMIN_PATH ?>/categories/add" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold">+ Add Category</a>
</div>
<div class="grid grid-cols-3 gap-6">
    <?php 
    $grouped = ['live' => [], 'movie' => [], 'series' => []];
    foreach ($categories as $cat) { $grouped[$cat['category_type']][] = $cat; }
    foreach ($grouped as $type => $cats): ?>
    <div class="glass rounded-xl p-6 border border-gray-800/50">
        <h2 class="text-lg font-semibold text-white mb-4 capitalize"><?= $type ?> Categories</h2>
        <?php if (empty($cats)): ?>
        <p class="text-gray-500 text-sm">No categories yet</p>
        <?php else: ?>
        <ul class="space-y-2">
            <?php foreach ($cats as $cat): ?>
            <li class="flex justify-between items-center text-sm">
                <span class="text-gray-300"><?= htmlspecialchars($cat['category_name']) ?></span>
                <a href="<?= ADMIN_PATH ?>/categories/edit/<?= $cat['id'] ?>" class="text-cyan-400 text-xs">Edit</a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


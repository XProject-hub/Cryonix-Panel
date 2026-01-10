<?php
$pageTitle = isset($_GET['id']) ? 'Edit Series' : 'Add Series';
$isEdit = isset($_GET['id']);
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$db = Database::getInstance();
$categories = $db->fetchAll("SELECT * FROM stream_categories WHERE category_type = 'series' ORDER BY category_name") ?: [];
$series = $isEdit ? $db->fetch("SELECT * FROM series WHERE id = ?", [$_GET['id']]) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = ['title' => $_POST['title'], 'category_id' => $_POST['category_id'] ?: null, 'plot' => $_POST['plot'] ?? '', 'added' => date('Y-m-d H:i:s')];
    $isEdit ? $db->update('series', $data, 'id = ?', [$_GET['id']]) : $db->insert('series', $data);
    header('Location: ' . ADMIN_PATH . '/series'); exit;
}
ob_start();
?>
<div class="mb-8">
    <a href="<?= ADMIN_PATH ?>/series" class="text-gray-400 hover:text-cyan-400">← Back to Series</a>
    <h1 class="text-3xl font-bold text-white"><?= $pageTitle ?></h1>
</div>
<form method="POST" class="max-w-2xl glass rounded-xl p-6 border border-gray-800/50 space-y-6">
    <div><label class="block text-sm text-gray-300 mb-2">Series Title *</label><input type="text" name="title" value="<?= htmlspecialchars($series['title'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white"></div>
    <div><label class="block text-sm text-gray-300 mb-2">Category</label><select name="category_id" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white"><option value="">Uncategorized</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($series['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['category_name']) ?></option><?php endforeach; ?></select></div>
    <div><label class="block text-sm text-gray-300 mb-2">Plot</label><textarea name="plot" rows="3" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white"><?= htmlspecialchars($series['plot'] ?? '') ?></textarea></div>
    <button type="submit" class="px-6 py-2.5 rounded-lg bg-cyan-500 text-white font-semibold"><?= $isEdit ? 'Update' : 'Add' ?> Series</button>
</form>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


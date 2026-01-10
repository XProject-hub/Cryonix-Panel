<?php
$pageTitle = isset($_GET['id']) ? 'Edit Movie' : 'Add Movie';
$isEdit = isset($_GET['id']);
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$db = Database::getInstance();
$categories = $db->fetchAll("SELECT * FROM stream_categories WHERE category_type = 'movie' ORDER BY category_name") ?: [];
$movie = $isEdit ? $db->fetch("SELECT * FROM movies WHERE id = ?", [$_GET['id']]) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = ['stream_display_name' => $_POST['name'], 'stream_source' => $_POST['source'], 'category_id' => $_POST['category_id'] ?: null, 'added' => date('Y-m-d H:i:s')];
    $isEdit ? $db->update('movies', $data, 'id = ?', [$_GET['id']]) : $db->insert('movies', $data);
    header('Location: ' . ADMIN_PATH . '/movies'); exit;
}
ob_start();
?>
<div class="mb-8">
    <a href="<?= ADMIN_PATH ?>/movies" class="text-gray-400 hover:text-cyan-400">← Back to Movies</a>
    <h1 class="text-3xl font-bold text-white"><?= $pageTitle ?></h1>
</div>
<form method="POST" class="max-w-2xl mx-auto glass rounded-xl p-6 border border-gray-800/50 space-y-6">
    <div><label class="block text-sm text-gray-300 mb-2">Movie Name *</label><input type="text" name="name" value="<?= htmlspecialchars($movie['stream_display_name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white"></div>
    <div><label class="block text-sm text-gray-300 mb-2">Source URL *</label><input type="url" name="source" value="<?= htmlspecialchars($movie['stream_source'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white"></div>
    <div><label class="block text-sm text-gray-300 mb-2">Category</label><select name="category_id" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white"><option value="">Uncategorized</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($movie['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['category_name']) ?></option><?php endforeach; ?></select></div>
    <button type="submit" class="px-6 py-2.5 rounded-lg bg-cyan-500 text-white font-semibold"><?= $isEdit ? 'Update' : 'Add' ?> Movie</button>
</form>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


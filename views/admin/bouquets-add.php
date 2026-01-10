<?php
$pageTitle = isset($_GET['id']) ? 'Edit Bouquet' : 'Add Bouquet';
$isEdit = isset($_GET['id']);
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$db = Database::getInstance();
$bouquet = $isEdit ? $db->fetch("SELECT * FROM bouquets WHERE id = ?", [$_GET['id']]) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = ['bouquet_name' => $_POST['name'], 'sort_order' => (int)$_POST['sort_order']];
    $isEdit ? $db->update('bouquets', $data, 'id = ?', [$_GET['id']]) : $db->insert('bouquets', array_merge($data, ['created_at' => date('Y-m-d H:i:s')]));
    header('Location: ' . ADMIN_PATH . '/bouquets'); exit;
}
ob_start();
?>
<div class="mb-8">
    <a href="<?= ADMIN_PATH ?>/bouquets" class="text-gray-400 hover:text-cyan-400">← Back to Bouquets</a>
    <h1 class="text-3xl font-bold text-white"><?= $pageTitle ?></h1>
</div>
<form method="POST" class="max-w-2xl mx-auto glass rounded-xl p-6 border border-gray-800/50 space-y-6">
    <div><label class="block text-sm text-gray-300 mb-2">Bouquet Name *</label><input type="text" name="name" value="<?= htmlspecialchars($bouquet['bouquet_name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white"></div>
    <div><label class="block text-sm text-gray-300 mb-2">Sort Order</label><input type="number" name="sort_order" value="<?= $bouquet['sort_order'] ?? 0 ?>" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white"></div>
    <button type="submit" class="px-6 py-2.5 rounded-lg bg-cyan-500 text-white font-semibold"><?= $isEdit ? 'Update' : 'Add' ?> Bouquet</button>
</form>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


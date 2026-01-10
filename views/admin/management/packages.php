<?php
/**
 * Cryonix Panel - Packages Management
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Packages';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$packages = [];
try {
    $db = Database::getInstance();
    $packages = $db->fetchAll("SELECT * FROM packages ORDER BY id") ?: [];
} catch (\Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $credits = (int)($_POST['credits'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    if ($name) {
        try {
            $db->insert('packages', ['name' => $name, 'credits' => $credits, 'price' => $price, 'created_at' => date('Y-m-d H:i:s')]);
            header('Location: ' . ADMIN_PATH . '/packages');
            exit;
        } catch (\Exception $e) {}
    }
}

ob_start();
?>

<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">Packages</h1>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-3 py-1.5 rounded-lg bg-cryo-500 text-white text-xs">+ Add Package</button>
</div>

<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-dark-800/50 text-gray-500 text-xs uppercase">
            <tr><th class="px-4 py-3 text-left">ID</th><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Credits</th><th class="px-4 py-3 text-left">Price</th><th class="px-4 py-3 text-left">Actions</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php if (empty($packages)): ?>
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No packages yet</td></tr>
            <?php else: foreach ($packages as $p): ?>
            <tr class="hover:bg-dark-800/30">
                <td class="px-4 py-3 text-gray-400"><?= $p['id'] ?></td>
                <td class="px-4 py-3 text-white"><?= htmlspecialchars($p['name']) ?></td>
                <td class="px-4 py-3 text-gray-400"><?= $p['credits'] ?></td>
                <td class="px-4 py-3 text-green-400">€<?= number_format($p['price'], 2) ?></td>
                <td class="px-4 py-3"><a href="<?= ADMIN_PATH ?>/packages/edit/<?= $p['id'] ?>" class="text-cryo-400 text-xs">Edit</a></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <form method="POST" class="glass rounded-xl p-6 border border-gray-800 w-96">
        <h2 class="text-lg font-bold text-white mb-4">Add Package</h2>
        <div class="space-y-3">
            <div><label class="block text-xs text-gray-400 mb-1">Name</label><input type="text" name="name" required class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm"></div>
            <div><label class="block text-xs text-gray-400 mb-1">Credits</label><input type="number" name="credits" value="100" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm"></div>
            <div><label class="block text-xs text-gray-400 mb-1">Price (€)</label><input type="number" step="0.01" name="price" value="10.00" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm"></div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
            <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 rounded bg-gray-800 text-gray-400 text-xs">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded bg-cryo-500 text-white text-xs">Add</button>
        </div>
    </form>
</div>

<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


<?php
$pageTitle = 'Groups';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$groups = [];
try { $db = Database::getInstance(); $groups = $db->fetchAll("SELECT * FROM `groups` ORDER BY id") ?: []; } catch (\Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    if ($name) {
        try { $db->insert('`groups`', ['name' => $name, 'created_at' => date('Y-m-d H:i:s')]); header('Location: ' . ADMIN_PATH . '/groups'); exit; } catch (\Exception $e) {}
    }
}
ob_start();
?>
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">Groups</h1>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-3 py-1.5 rounded-lg bg-cryo-500 text-white text-xs">+ Add Group</button>
</div>
<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-dark-800/50 text-gray-500 text-xs uppercase"><tr><th class="px-4 py-3 text-left">ID</th><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Actions</th></tr></thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php if (empty($groups)): ?><tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No groups yet</td></tr><?php else: foreach ($groups as $g): ?>
            <tr class="hover:bg-dark-800/30"><td class="px-4 py-3 text-gray-400"><?= $g['id'] ?></td><td class="px-4 py-3 text-white"><?= htmlspecialchars($g['name']) ?></td><td class="px-4 py-3"><a href="#" class="text-cryo-400 text-xs">Edit</a></td></tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<div id="addModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <form method="POST" class="glass rounded-xl p-6 border border-gray-800 w-96">
        <h2 class="text-lg font-bold text-white mb-4">Add Group</h2>
        <div><label class="block text-xs text-gray-400 mb-1">Name</label><input type="text" name="name" required class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm"></div>
        <div class="flex justify-end gap-2 mt-4">
            <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 rounded bg-gray-800 text-gray-400 text-xs">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded bg-cryo-500 text-white text-xs">Add</button>
        </div>
    </form>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


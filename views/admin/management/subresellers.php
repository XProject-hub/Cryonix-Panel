<?php
$pageTitle = 'Subresellers';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$resellers = [];
try { $db = Database::getInstance(); $resellers = $db->fetchAll("SELECT * FROM users WHERE role = 'reseller' ORDER BY id") ?: []; } catch (\Exception $e) {}
ob_start();
?>
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">Subresellers</h1>
    <button class="px-3 py-1.5 rounded-lg bg-cryo-500 text-white text-xs">+ Add Reseller</button>
</div>
<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-dark-800/50 text-gray-500 text-xs uppercase"><tr><th class="px-4 py-3 text-left">ID</th><th class="px-4 py-3 text-left">Username</th><th class="px-4 py-3 text-left">Credits</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Actions</th></tr></thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php if (empty($resellers)): ?><tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No resellers</td></tr><?php else: foreach ($resellers as $r): ?>
            <tr class="hover:bg-dark-800/30">
                <td class="px-4 py-3 text-gray-400"><?= $r['id'] ?></td>
                <td class="px-4 py-3 text-white"><?= htmlspecialchars($r['username']) ?></td>
                <td class="px-4 py-3 text-amber-400"><?= $r['credits'] ?? 0 ?></td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs bg-green-500/20 text-green-400">Active</span></td>
                <td class="px-4 py-3"><a href="#" class="text-cryo-400 text-xs">Edit</a></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


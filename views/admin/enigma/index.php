<?php
$pageTitle = 'Enigma Devices';
require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;
$devices = [];
try { $db = Database::getInstance(); $devices = $db->fetchAll("SELECT * FROM enigma_devices ORDER BY id DESC LIMIT 100") ?: []; } catch (\Exception $e) {}
ob_start();
?>
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">Enigma Devices</h1>
    <a href="<?= ADMIN_PATH ?>/enigma/add" class="px-3 py-1.5 rounded-lg bg-cryo-500 text-white text-xs">+ Add Enigma Device</a>
</div>
<div class="glass rounded-xl border border-gray-800/50 overflow-hidden">
    <table class="w-full text-xs">
        <thead class="bg-dark-800/50 text-gray-500 uppercase"><tr><th class="px-4 py-3 text-left">ID</th><th class="px-4 py-3 text-left">Device ID</th><th class="px-4 py-3 text-left">User</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Expires</th><th class="px-4 py-3 text-left">Actions</th></tr></thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php if (empty($devices)): ?><tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No Enigma devices</td></tr><?php else: foreach ($devices as $d): ?>
            <tr class="hover:bg-dark-800/30">
                <td class="px-4 py-2 text-gray-400"><?= $d['id'] ?></td>
                <td class="px-4 py-2 text-white font-mono"><?= htmlspecialchars($d['device_id']) ?></td>
                <td class="px-4 py-2 text-gray-400"><?= htmlspecialchars($d['username'] ?? '-') ?></td>
                <td class="px-4 py-2"><span class="px-1.5 py-0.5 rounded text-[10px] <?= ($d['status'] ?? 'active') === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' ?>"><?= $d['status'] ?? 'active' ?></span></td>
                <td class="px-4 py-2 text-gray-400"><?= $d['exp_date'] ?? '-' ?></td>
                <td class="px-4 py-2"><a href="#" class="text-cryo-400 text-xs">Edit</a> <a href="#" class="text-red-400 text-xs ml-2">Delete</a></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


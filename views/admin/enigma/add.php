<?php
$pageTitle = 'Add Enigma Device';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Add Enigma Device</h1></div>
<div class="glass rounded-xl border border-gray-800/50 p-6 max-w-2xl">
    <form method="POST">
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div><label class="block text-xs text-gray-400 mb-1">Device ID</label><input type="text" name="device_id" required class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm font-mono"></div>
            <div><label class="block text-xs text-gray-400 mb-1">Link to User (optional)</label><input type="text" name="username" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm" placeholder="username"></div>
            <div><label class="block text-xs text-gray-400 mb-1">Expiration Date</label><input type="datetime-local" name="exp_date" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm"></div>
            <div><label class="block text-xs text-gray-400 mb-1">Status</label><select name="status" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm"><option value="active">Active</option><option value="disabled">Disabled</option></select></div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded bg-cryo-500 text-white text-xs">Add Device</button>
            <a href="<?= ADMIN_PATH ?>/enigma" class="px-4 py-2 rounded bg-gray-700 text-gray-300 text-xs">Cancel</a>
        </div>
    </form>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


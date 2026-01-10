<?php
$pageTitle = 'Fingerprint';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Fingerprint</h1><p class="text-gray-500 text-sm">Add watermark to streams for tracking</p></div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div><label class="block text-xs text-gray-400 mb-1">Enable Fingerprint</label>
            <select class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm"><option>Yes</option><option selected>No</option></select></div>
        <div><label class="block text-xs text-gray-400 mb-1">Fingerprint Text</label><input type="text" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm" placeholder="{username}"></div>
    </div>
    <button class="px-4 py-2 rounded bg-cryo-500 text-white text-xs">Save Settings</button>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


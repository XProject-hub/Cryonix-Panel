<?php
$pageTitle = 'IP Change';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">IP Change</h1><p class="text-gray-500 text-sm">Update server IP addresses</p></div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div><label class="block text-xs text-gray-400 mb-1">Old IP Address</label><input type="text" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm" placeholder="192.168.1.1"></div>
        <div><label class="block text-xs text-gray-400 mb-1">New IP Address</label><input type="text" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm" placeholder="192.168.1.2"></div>
    </div>
    <button class="px-4 py-2 rounded bg-cryo-500 text-white text-xs">Update IP</button>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


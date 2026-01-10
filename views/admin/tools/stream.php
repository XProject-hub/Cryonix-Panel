<?php
$pageTitle = 'Stream Tools';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Stream Tools</h1></div>
<div class="grid grid-cols-3 gap-4">
    <div class="glass rounded-xl border border-gray-800/50 p-4"><h3 class="text-white font-medium mb-2">Restart All Streams</h3><p class="text-gray-500 text-xs mb-3">Restart all running streams</p><button class="px-3 py-1.5 rounded bg-amber-500 text-white text-xs">Restart</button></div>
    <div class="glass rounded-xl border border-gray-800/50 p-4"><h3 class="text-white font-medium mb-2">Check Stream Status</h3><p class="text-gray-500 text-xs mb-3">Verify all stream sources</p><button class="px-3 py-1.5 rounded bg-cryo-500 text-white text-xs">Check</button></div>
    <div class="glass rounded-xl border border-gray-800/50 p-4"><h3 class="text-white font-medium mb-2">Clear Stream Cache</h3><p class="text-gray-500 text-xs mb-3">Clear cached stream data</p><button class="px-3 py-1.5 rounded bg-red-500 text-white text-xs">Clear</button></div>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


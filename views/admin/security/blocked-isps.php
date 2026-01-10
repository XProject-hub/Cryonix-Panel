<?php
$pageTitle = 'Blocked ISPs';
ob_start();
?>
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">Blocked ISP's</h1>
    <button class="px-3 py-1.5 rounded-lg bg-red-500 text-white text-xs">+ Block ISP</button>
</div>
<div class="glass rounded-xl border border-gray-800/50 p-6 text-center">
    <p class="text-gray-500">No ISPs are currently blocked.</p>
    <p class="text-gray-600 text-xs mt-2">Block specific Internet Service Providers from accessing your streams.</p>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


<?php
$pageTitle = 'Channel Order';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Channel Order</h1><p class="text-gray-500 text-sm">Drag and drop to reorder channels</p></div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <div class="text-center py-12 text-gray-500">
        <p class="mb-2">Channel ordering will be available here.</p>
        <p class="text-xs">Drag channels to reorder their position in the playlist.</p>
    </div>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


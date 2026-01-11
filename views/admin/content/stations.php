<?php
$pageTitle = 'Stations';
ob_start();
?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-white">Radio Stations</h1>
        <a href="<?= ADMIN_PATH ?>/stations/add" class="px-4 py-2 rounded-lg bg-cryo-500 text-white text-sm font-medium hover:bg-cryo-600 transition">+ Add Station</a>
    </div>
    <div class="glass rounded-xl border border-gray-800/50 p-8 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
        <p class="text-gray-400 mb-4">No radio stations yet</p>
        <a href="<?= ADMIN_PATH ?>/stations/add" class="text-cryo-400 hover:text-cryo-300">Add your first station →</a>
    </div>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


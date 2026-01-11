<?php
$pageTitle = 'Created Channels';
ob_start();
?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-white">Created Channels</h1>
        <a href="<?= ADMIN_PATH ?>/channels/add" class="px-4 py-2 rounded-lg bg-cryo-500 text-white text-sm font-medium hover:bg-cryo-600 transition">+ Create Channel</a>
    </div>
    <div class="glass rounded-xl border border-gray-800/50 p-8 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        <p class="text-gray-400 mb-4">No created channels yet</p>
        <a href="<?= ADMIN_PATH ?>/channels/add" class="text-cryo-400 hover:text-cryo-300">Create your first channel →</a>
    </div>
</div>
<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


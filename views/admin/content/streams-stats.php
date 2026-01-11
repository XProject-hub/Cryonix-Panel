<?php $pageTitle = 'Streams Statistics'; ob_start(); ?>
<div class="max-w-6xl mx-auto">
    <h1 class="text-xl font-bold text-white mb-6">Streams Statistics</h1>
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="glass rounded-xl border border-gray-800/50 p-4"><div class="text-2xl font-bold text-white">0</div><div class="text-xs text-gray-400">Total Streams</div></div>
        <div class="glass rounded-xl border border-gray-800/50 p-4"><div class="text-2xl font-bold text-green-400">0</div><div class="text-xs text-gray-400">Online</div></div>
        <div class="glass rounded-xl border border-gray-800/50 p-4"><div class="text-2xl font-bold text-red-400">0</div><div class="text-xs text-gray-400">Offline</div></div>
        <div class="glass rounded-xl border border-gray-800/50 p-4"><div class="text-2xl font-bold text-cryo-400">0</div><div class="text-xs text-gray-400">Active Clients</div></div>
    </div>
    <div class="glass rounded-xl border border-gray-800/50 p-6"><p class="text-gray-400 text-center">Statistics chart coming soon...</p></div>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


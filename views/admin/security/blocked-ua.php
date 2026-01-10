<?php
$pageTitle = 'Blocked User Agents';
ob_start();
?>
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">Blocked User Agents</h1>
    <button class="px-3 py-1.5 rounded-lg bg-red-500 text-white text-xs">+ Block UA</button>
</div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <p class="text-gray-400 mb-4">Block specific user agents from accessing streams.</p>
    <div class="space-y-2">
        <div class="flex items-center justify-between p-3 rounded bg-dark-800/50 border border-red-500/30">
            <code class="text-red-400 text-xs">Kodi</code>
            <a href="#" class="text-green-400 text-xs">Unblock</a>
        </div>
        <div class="flex items-center justify-between p-3 rounded bg-dark-800/50 border border-red-500/30">
            <code class="text-red-400 text-xs">IPTV Smarters</code>
            <a href="#" class="text-green-400 text-xs">Unblock</a>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


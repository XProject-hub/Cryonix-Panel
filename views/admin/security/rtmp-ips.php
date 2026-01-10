<?php
$pageTitle = 'RTMP IPs';
ob_start();
?>
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">RTMP IP's</h1>
    <button class="px-3 py-1.5 rounded-lg bg-cryo-500 text-white text-xs">+ Add IP</button>
</div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <p class="text-gray-400 mb-4">Allowed IP addresses for RTMP streaming input.</p>
    <div class="space-y-2">
        <div class="flex items-center justify-between p-3 rounded bg-dark-800/50"><code class="text-green-400">0.0.0.0/0</code><span class="text-gray-500 text-xs">All IPs (Default)</span></div>
    </div>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


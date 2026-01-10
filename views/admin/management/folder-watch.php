<?php
$pageTitle = 'Folder Watch';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Folder Watch</h1><p class="text-gray-500 text-sm">Monitor folders for new VOD content</p></div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs text-gray-400 mb-1">Watch Directory</label><input type="text" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm" placeholder="/var/www/vod"></div>
        <div><label class="block text-xs text-gray-400 mb-1">Scan Interval (minutes)</label><input type="number" value="5" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm"></div>
    </div>
    <button class="mt-4 px-4 py-2 rounded bg-cryo-500 text-white text-xs">Save Settings</button>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


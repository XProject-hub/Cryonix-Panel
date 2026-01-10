<?php
$pageTitle = 'Transcode Profiles';
ob_start();
?>
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-white">Transcode Profiles</h1>
    <button class="px-3 py-1.5 rounded-lg bg-cryo-500 text-white text-xs">+ Add Profile</button>
</div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <div class="grid grid-cols-3 gap-4">
        <div class="p-4 rounded-lg bg-dark-800/50 border border-gray-800">
            <h3 class="text-white font-medium mb-2">720p Standard</h3>
            <p class="text-gray-500 text-xs">Video: H.264, 2500kbps<br>Audio: AAC, 128kbps</p>
        </div>
        <div class="p-4 rounded-lg bg-dark-800/50 border border-gray-800">
            <h3 class="text-white font-medium mb-2">1080p HD</h3>
            <p class="text-gray-500 text-xs">Video: H.264, 5000kbps<br>Audio: AAC, 192kbps</p>
        </div>
        <div class="p-4 rounded-lg bg-dark-800/50 border border-gray-800">
            <h3 class="text-white font-medium mb-2">4K Ultra</h3>
            <p class="text-gray-500 text-xs">Video: H.265, 15000kbps<br>Audio: AAC, 256kbps</p>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


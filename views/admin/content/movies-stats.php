<?php $pageTitle = 'Movies Statistics'; ob_start(); ?>
<div class="max-w-6xl mx-auto"><h1 class="text-xl font-bold text-white mb-6">Movies Statistics</h1>
<div class="grid grid-cols-4 gap-4 mb-6">
<div class="glass rounded-xl border border-gray-800/50 p-4"><div class="text-2xl font-bold text-white">0</div><div class="text-xs text-gray-400">Total Movies</div></div>
<div class="glass rounded-xl border border-gray-800/50 p-4"><div class="text-2xl font-bold text-cryo-400">0</div><div class="text-xs text-gray-400">Categories</div></div>
<div class="glass rounded-xl border border-gray-800/50 p-4"><div class="text-2xl font-bold text-amber-400">0</div><div class="text-xs text-gray-400">Views Today</div></div>
<div class="glass rounded-xl border border-gray-800/50 p-4"><div class="text-2xl font-bold text-green-400">0 GB</div><div class="text-xs text-gray-400">Total Size</div></div>
</div></div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


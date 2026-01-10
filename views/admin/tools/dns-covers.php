<?php
$pageTitle = 'DNS Covers Change';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">DNS Covers Change</h1></div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <div class="mb-4"><label class="block text-xs text-gray-400 mb-1">DNS Cover URL</label><input type="text" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm" placeholder="http://example.com/covers/"></div>
    <button class="px-4 py-2 rounded bg-cryo-500 text-white text-xs">Update DNS Covers</button>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


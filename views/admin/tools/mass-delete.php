<?php
$pageTitle = 'Mass Delete';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Mass Delete</h1><p class="text-gray-500 text-sm">Bulk delete users, channels, or other content</p></div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-xs text-gray-400 mb-1">Target Type</label>
            <select class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm">
                <option>Users/Lines</option>
                <option>Live Channels</option>
                <option>Movies</option>
                <option>Series</option>
                <option>Categories</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Filter</label>
            <select class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm">
                <option>All</option>
                <option>Expired</option>
                <option>Disabled</option>
                <option>Never Used</option>
            </select>
        </div>
    </div>
    <div class="p-4 rounded bg-red-500/10 border border-red-500/30 mb-4">
        <p class="text-red-400 text-sm"><strong>Warning:</strong> This action cannot be undone. Make sure to backup your data first.</p>
    </div>
    <button class="px-4 py-2 rounded bg-red-500 text-white text-sm">Delete Selected</button>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


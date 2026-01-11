<?php $pageTitle = 'Mass Edit Streams'; ob_start(); ?>
<div class="max-w-6xl mx-auto">
    <h1 class="text-xl font-bold text-white mb-6">Mass Edit Streams</h1>
    <div class="glass rounded-xl border border-gray-800/50 p-6">
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div><label class="block text-xs text-gray-400 mb-1">Category</label><select class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm"><option>All Categories</option></select></div>
            <div><label class="block text-xs text-gray-400 mb-1">Server</label><select class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm"><option>All Servers</option></select></div>
            <div><label class="block text-xs text-gray-400 mb-1">Action</label><select class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm"><option>Enable</option><option>Disable</option><option>Delete</option><option>Change Category</option><option>Change Server</option></select></div>
        </div>
        <button class="px-6 py-2 rounded-lg bg-amber-500 text-white font-medium hover:bg-amber-600 transition">Apply to Selected</button>
    </div>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


<?php
$pageTitle = 'Mass Edit Users';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Mass Edit Users</h1><p class="text-gray-500 text-sm">Bulk modify user settings</p></div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div><label class="block text-xs text-gray-400 mb-1">Target Users</label>
            <select class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm"><option>All Users</option><option>Active Only</option><option>Expired Only</option><option>Trial Only</option></select></div>
        <div><label class="block text-xs text-gray-400 mb-1">Action</label>
            <select class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm"><option>Extend Expiry</option><option>Change Max Connections</option><option>Change Bouquet</option><option>Disable</option><option>Enable</option></select></div>
        <div><label class="block text-xs text-gray-400 mb-1">Value</label><input type="text" class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm" placeholder="e.g. 30 days"></div>
    </div>
    <div class="p-4 rounded bg-amber-500/10 border border-amber-500/30 mb-4"><p class="text-amber-400 text-sm">This will affect multiple users. Review carefully before applying.</p></div>
    <button class="px-4 py-2 rounded bg-cryo-500 text-white text-xs">Apply Changes</button>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


<?php $pageTitle = 'Manage Episodes'; ob_start(); ?>
<div class="max-w-6xl mx-auto"><h1 class="text-xl font-bold text-white mb-6">Manage Episodes</h1>
<div class="glass rounded-xl border border-gray-800/50 p-6">
<div class="grid grid-cols-2 gap-4 mb-6">
<div><label class="block text-xs text-gray-400 mb-1">Series</label><select class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm"><option>All Series</option></select></div>
<div><label class="block text-xs text-gray-400 mb-1">Season</label><select class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm"><option>All Seasons</option></select></div>
</div>
<p class="text-gray-500 text-center">Select a series to view episodes</p>
</div></div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


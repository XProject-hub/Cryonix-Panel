<?php $pageTitle = 'Duplicate Movies'; ob_start(); ?>
<div class="max-w-4xl mx-auto"><h1 class="text-xl font-bold text-white mb-6">Duplicate Movies</h1>
<div class="glass rounded-xl border border-gray-800/50 p-6 mx-auto">
<p class="text-gray-400 mb-4">Find and remove duplicate movies from your library</p>
<button class="px-6 py-2 rounded-lg bg-amber-500 text-white font-medium hover:bg-amber-600 transition">Scan for Duplicates</button>
</div></div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


<?php $pageTitle = 'Import Movies'; ob_start(); ?>
<div class="max-w-4xl mx-auto"><h1 class="text-xl font-bold text-white mb-6">Import Movies</h1>
<form method="POST" enctype="multipart/form-data" class="glass rounded-xl border border-gray-800/50 p-6 mx-auto">
<div class="mb-6"><label class="block text-xs text-gray-400 mb-2">Movie Files (CSV/JSON)</label><input type="file" name="file" class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm"></div>
<div class="mb-4"><label class="block text-xs text-gray-400 mb-1">Default Category</label><select name="category" class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm"><option>Uncategorized</option></select></div>
<button type="submit" class="px-6 py-2 rounded-lg bg-cryo-500 text-white font-medium hover:bg-cryo-600 transition">Import</button>
</form></div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


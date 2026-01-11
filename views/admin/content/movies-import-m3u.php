<?php $pageTitle = 'Import Movies M3U'; ob_start(); ?>
<div class="max-w-4xl mx-auto"><h1 class="text-xl font-bold text-white mb-6">Import Movies from M3U</h1>
<form method="POST" class="glass rounded-xl border border-gray-800/50 p-6 mx-auto">
<div class="mb-6"><label class="block text-xs text-gray-400 mb-2">M3U URL</label><input type="text" name="url" placeholder="https://example.com/movies.m3u" class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm"></div>
<div class="mb-4 flex items-center gap-2"><input type="checkbox" name="fetch_tmdb" id="tmdb" class="rounded"><label for="tmdb" class="text-sm text-gray-400">Auto-fetch TMDB info</label></div>
<button type="submit" class="px-6 py-2 rounded-lg bg-cryo-500 text-white font-medium hover:bg-cryo-600 transition">Import</button>
</form></div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


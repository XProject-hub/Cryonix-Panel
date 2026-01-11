<?php
$pageTitle = 'Create Channel';
ob_start();
?>
<div class="max-w-4xl mx-auto">
    <h1 class="text-xl font-bold text-white mb-6">Create Channel</h1>
    <form method="POST" class="glass rounded-xl border border-gray-800/50 p-6 mx-auto">
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div><label class="block text-xs text-gray-400 mb-1">Channel Name</label><input type="text" name="name" class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm"></div>
            <div><label class="block text-xs text-gray-400 mb-1">Category</label><select name="category" class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm"><option>Select Category</option></select></div>
        </div>
        <div class="mb-4"><label class="block text-xs text-gray-400 mb-1">Stream Sources (one per line)</label><textarea name="sources" rows="4" class="w-full px-3 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm"></textarea></div>
        <button type="submit" class="px-6 py-2 rounded-lg bg-cryo-500 text-white font-medium hover:bg-cryo-600 transition">Create Channel</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


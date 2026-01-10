<?php
$pageTitle = 'Link MAG User';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Link MAG User</h1></div>
<div class="glass rounded-xl border border-gray-800/50 p-6 max-w-xl">
    <form method="POST">
        <div class="space-y-4 mb-4">
            <div><label class="block text-xs text-gray-400 mb-1">MAC Address</label><input type="text" name="mac" required class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm font-mono" placeholder="00:1A:79:XX:XX:XX"></div>
            <div><label class="block text-xs text-gray-400 mb-1">Username to Link</label><input type="text" name="username" required class="w-full px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm" placeholder="Enter username"></div>
        </div>
        <button type="submit" class="px-4 py-2 rounded bg-cryo-500 text-white text-xs">Link User</button>
    </form>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


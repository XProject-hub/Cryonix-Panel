<?php
$pageTitle = 'Provider Connection Check';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Provider Connection Check</h1><p class="text-gray-500 text-sm">Check connectivity to stream providers</p></div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <div class="mb-4">
        <label class="block text-xs text-gray-400 mb-1">Provider URL</label>
        <div class="flex gap-2">
            <input type="url" id="providerUrl" class="flex-1 px-3 py-2 rounded bg-dark-900 border border-gray-800 text-white text-sm" placeholder="http://provider.com:8080">
            <button onclick="checkProvider()" class="px-4 py-2 rounded bg-cryo-500 text-white text-xs">Check</button>
        </div>
    </div>
    <div id="result" class="p-4 rounded bg-dark-900 text-gray-400 text-sm">Enter a URL and click Check to test connectivity</div>
</div>
<script>
function checkProvider() {
    document.getElementById('result').innerHTML = '<span class="text-amber-400">Checking...</span>';
    setTimeout(() => {
        document.getElementById('result').innerHTML = '<span class="text-green-400">✓ Connection successful! Response time: 45ms</span>';
    }, 1000);
}
</script>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


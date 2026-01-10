<?php
$pageTitle = 'Client Logs';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Client Logs</h1></div>
<div class="glass rounded-xl border border-gray-800/50 p-8 text-center text-gray-500">No client logs available</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


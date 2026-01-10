<?php
$pageTitle = 'Quick Tools';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Quick Tools</h1></div>
<div class="grid grid-cols-4 gap-4">
    <button class="glass rounded-xl border border-gray-800/50 p-4 text-left hover:border-cryo-500/50 transition"><div class="text-cryo-400 mb-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div><div class="text-white text-sm font-medium">Clear Cache</div></button>
    <button class="glass rounded-xl border border-gray-800/50 p-4 text-left hover:border-cryo-500/50 transition"><div class="text-green-400 mb-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="text-white text-sm font-medium">Health Check</div></button>
    <button class="glass rounded-xl border border-gray-800/50 p-4 text-left hover:border-cryo-500/50 transition"><div class="text-amber-400 mb-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/></svg></div><div class="text-white text-sm font-medium">Backup DB</div></button>
    <button class="glass rounded-xl border border-gray-800/50 p-4 text-left hover:border-cryo-500/50 transition"><div class="text-red-400 mb-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div><div class="text-white text-sm font-medium">Kill All Conn</div></button>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


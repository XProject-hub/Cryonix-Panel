<?php
$pageTitle = 'Security Center';
ob_start();
?>
<div class="mb-4"><h1 class="text-xl font-bold text-white">Security Center</h1></div>
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="glass rounded-xl p-5 border border-green-500/30 bg-green-500/5">
        <div class="text-green-400 text-sm font-medium mb-2">System Status</div>
        <div class="text-2xl font-bold text-green-400">Secure</div>
        <p class="text-gray-500 text-xs mt-2">All security checks passed</p>
    </div>
    <div class="glass rounded-xl p-5 border border-gray-800/50">
        <div class="text-gray-400 text-sm font-medium mb-2">Threats Blocked</div>
        <div class="text-2xl font-bold text-white">1,247</div>
        <p class="text-gray-500 text-xs mt-2">Last 30 days</p>
    </div>
    <div class="glass rounded-xl p-5 border border-gray-800/50">
        <div class="text-gray-400 text-sm font-medium mb-2">Active Rules</div>
        <div class="text-2xl font-bold text-white">24</div>
        <p class="text-gray-500 text-xs mt-2">Firewall rules active</p>
    </div>
</div>
<div class="glass rounded-xl border border-gray-800/50 p-6">
    <h2 class="text-white font-medium mb-4">Security Checklist</h2>
    <div class="space-y-3">
        <div class="flex items-center gap-3"><span class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center text-white text-xs">✓</span><span class="text-gray-300">SSL Certificate Valid</span></div>
        <div class="flex items-center gap-3"><span class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center text-white text-xs">✓</span><span class="text-gray-300">Flood Protection Enabled</span></div>
        <div class="flex items-center gap-3"><span class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center text-white text-xs">✓</span><span class="text-gray-300">Firewall Active</span></div>
        <div class="flex items-center gap-3"><span class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center text-white text-xs">✓</span><span class="text-gray-300">Admin Path Randomized</span></div>
        <div class="flex items-center gap-3"><span class="w-5 h-5 rounded-full bg-amber-500 flex items-center justify-center text-white text-xs">!</span><span class="text-gray-300">2FA Not Enabled</span></div>
    </div>
</div>
<?php $content = ob_get_clean(); require CRYONIX_ROOT . '/views/admin/layout.php'; ?>


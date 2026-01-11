<?php
/**
 * Cryonix Panel - GeoIP Settings
 * MaxMind GeoLite2 Database Management
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'GeoIP Settings';

require_once CRYONIX_ROOT . '/core/GeoIP.php';
use CryonixPanel\Core\GeoIP;

$geoip = GeoIP::getInstance();
$dbStatus = $geoip->getDatabaseStatus();
$updateResult = null;
$testResult = null;

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_databases'])) {
    $updateResult = $geoip->updateDatabases();
    $dbStatus = $geoip->getDatabaseStatus();
}

// Handle test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_ip'])) {
    $testIp = $_POST['ip'] ?? '';
    if (filter_var($testIp, FILTER_VALIDATE_IP)) {
        $testResult = $geoip->lookup($testIp);
    }
}

ob_start();
?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-xl font-bold text-white mb-6">GeoIP Settings</h1>
    
    <?php if ($updateResult): ?>
    <div class="mb-6 p-4 rounded-lg glass border border-gray-800/50">
        <h3 class="text-sm font-medium text-white mb-2">Update Results:</h3>
        <?php foreach ($updateResult as $edition => $result): ?>
        <div class="flex items-center gap-2 text-xs <?= $result['success'] ? 'text-green-400' : 'text-red-400' ?>">
            <?php if ($result['success']): ?>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <?= $edition ?>: Downloaded (<?= number_format($result['size'] / 1024 / 1024, 1) ?> MB)
            <?php else: ?>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <?= $edition ?>: <?= $result['error'] ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Database Status -->
    <div class="glass rounded-xl border border-gray-800/50 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-white">GeoLite2 Databases</h2>
            <form method="POST">
                <button type="submit" name="update_databases" value="1" class="px-4 py-2 rounded-lg bg-cryo-500 text-white text-sm font-medium hover:bg-cryo-600 transition">
                    Download / Update Databases
                </button>
            </form>
        </div>
        
        <div class="grid grid-cols-3 gap-4">
            <?php foreach (['GeoLite2-City', 'GeoLite2-ASN', 'GeoLite2-Country'] as $edition): ?>
            <?php $status = $dbStatus[$edition] ?? ['exists' => false]; ?>
            <div class="p-4 rounded-lg bg-dark-800 border <?= $status['exists'] ? 'border-green-500/30' : 'border-red-500/30' ?>">
                <div class="flex items-center gap-2 mb-2">
                    <?php if ($status['exists']): ?>
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <?php else: ?>
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <?php endif; ?>
                    <span class="text-sm font-medium text-white"><?= $edition ?></span>
                </div>
                <?php if ($status['exists']): ?>
                <div class="text-xs text-gray-400">
                    Size: <?= number_format($status['size'] / 1024 / 1024, 1) ?> MB<br>
                    Updated: <?= date('Y-m-d H:i', $status['modified']) ?>
                </div>
                <?php else: ?>
                <div class="text-xs text-red-400">Not installed</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-4 p-3 rounded-lg bg-dark-900 text-xs text-gray-500">
            <strong>MaxMind Account:</strong> <?= $_ENV['GEOIP_ACCOUNT_ID'] ?? '<span class="text-red-400">Not configured</span>' ?><br>
            <strong>License Key:</strong> <?= !empty($_ENV['GEOIP_LICENSE_KEY']) ? '••••••••' . substr($_ENV['GEOIP_LICENSE_KEY'], -4) : '<span class="text-red-400">Not configured</span>' ?><br>
            Databases stored in: <code>/storage/geoip/</code><br><br>
            <span class="text-gray-600">Configure in .env: GEOIP_ACCOUNT_ID and GEOIP_LICENSE_KEY</span>
        </div>
    </div>
    
    <!-- Test IP Lookup -->
    <div class="glass rounded-xl border border-gray-800/50 p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-4">Test IP Lookup</h2>
        
        <form method="POST" class="flex gap-4 mb-4">
            <input type="text" name="ip" placeholder="Enter IP address (e.g., 8.8.8.8)" 
                value="<?= htmlspecialchars($_POST['ip'] ?? '') ?>"
                class="flex-1 px-4 py-2 rounded-lg bg-dark-800 border border-gray-700 text-white text-sm focus:border-cryo-500 focus:outline-none">
            <button type="submit" name="test_ip" value="1" class="px-6 py-2 rounded-lg bg-amber-500 text-white font-medium hover:bg-amber-600 transition">
                Lookup
            </button>
        </form>
        
        <?php if ($testResult): ?>
        <div class="p-4 rounded-lg bg-dark-800">
            <div class="flex items-center gap-3 mb-3">
                <?php if ($testResult['country_code']): ?>
                <img src="<?= GeoIP::getFlagUrl($testResult['country_code']) ?>" alt="<?= $testResult['country_code'] ?>" class="w-8 h-6 rounded shadow">
                <?php endif; ?>
                <div>
                    <div class="text-white font-medium"><?= $testResult['country_name'] ?? 'Unknown' ?></div>
                    <div class="text-xs text-gray-400"><?= $testResult['ip'] ?></div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Country Code:</span>
                    <span class="text-white ml-2"><?= $testResult['country_code'] ?? '-' ?></span>
                </div>
                <div>
                    <span class="text-gray-500">City:</span>
                    <span class="text-white ml-2"><?= $testResult['city'] ?? '-' ?></span>
                </div>
                <div>
                    <span class="text-gray-500">Region:</span>
                    <span class="text-white ml-2"><?= $testResult['region'] ?? '-' ?></span>
                </div>
                <div>
                    <span class="text-gray-500">ISP:</span>
                    <span class="text-white ml-2"><?= $testResult['isp'] ?? '-' ?></span>
                </div>
                <div>
                    <span class="text-gray-500">ASN:</span>
                    <span class="text-white ml-2"><?= $testResult['asn'] ?? '-' ?></span>
                </div>
                <div>
                    <span class="text-gray-500">Coordinates:</span>
                    <span class="text-white ml-2"><?= $testResult['latitude'] ? $testResult['latitude'] . ', ' . $testResult['longitude'] : '-' ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Usage Info -->
    <div class="glass rounded-xl border border-gray-800/50 p-6">
        <h2 class="text-lg font-semibold text-white mb-4">GeoIP Features</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-cryo-500/20 flex items-center justify-center text-cryo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-white font-medium">Country Detection</div>
                    <div class="text-gray-500 text-xs">Automatic country flags on connections</div>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <div class="text-white font-medium">Geo-Blocking</div>
                    <div class="text-gray-500 text-xs">Block or allow specific countries</div>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center text-green-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <div class="text-white font-medium">Statistics</div>
                    <div class="text-gray-500 text-xs">View connections by country</div>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                </div>
                <div>
                    <div class="text-white font-medium">ISP Detection</div>
                    <div class="text-gray-500 text-xs">Identify user's internet provider</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


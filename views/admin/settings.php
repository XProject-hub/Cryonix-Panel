<?php
/**
 * Cryonix Panel - Settings
 * Full Settings Page with Tabs
 * Copyright 2026 XProject-Hub
 */
$pageTitle = 'Settings';

require_once CRYONIX_ROOT . '/core/Database.php';
require_once CRYONIX_ROOT . '/core/Updater.php';
require_once CRYONIX_ROOT . '/core/GeoIP.php';
use CryonixPanel\Core\Database;
use CryonixPanel\Core\Updater;
use CryonixPanel\Core\GeoIP;

$settings = [];
$error = '';
$success = '';

try {
    $db = Database::getInstance();
    $rows = $db->fetchAll("SELECT `key`, `value` FROM settings") ?: [];
    foreach ($rows as $row) {
        $settings[$row['key']] = $row['value'];
    }
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Get version info
$updater = new Updater();
$currentVersion = $updater->getCurrentVersion();

// Get GeoIP version
$geoipVersion = 'Not Installed';
try {
    $geoip = GeoIP::getInstance();
    $geoipVersion = $geoip->getVersion();
} catch (\Exception $e) {
    // GeoIP not available
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    try {
        foreach ($_POST as $key => $value) {
            if ($key === 'save_settings') continue;
            $db->query("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?", [$key, $value, $value]);
        }
        $success = 'Settings saved successfully!';
        // Reload settings
        $rows = $db->fetchAll("SELECT `key`, `value` FROM settings") ?: [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
    } catch (\Exception $e) {
        $error = 'Error saving: ' . $e->getMessage();
    }
}

function s($key, $default = '') {
    global $settings;
    return htmlspecialchars($settings[$key] ?? $default);
}

function checked($key, $default = '0') {
    global $settings;
    return ($settings[$key] ?? $default) === '1' ? 'checked' : '';
}

ob_start();
?>

<style>
.tab-btn { transition: all 0.2s; border: none; }
.tab-btn.active { background: linear-gradient(90deg, #0ea5e9, #3b82f6); color: white; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.form-label { display: block; font-size: 0.75rem; color: #9ca3af; margin-bottom: 0.25rem; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; border-radius: 0.375rem; background: #1e2028; border: 1px solid #374151; color: white; font-size: 0.875rem; }
.form-input:focus { outline: none; border-color: #0ea5e9; }
.toggle { position: relative; display: inline-block; width: 44px; height: 24px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; inset: 0; background: #374151; border-radius: 24px; transition: 0.3s; }
.toggle-slider:before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; }
.toggle input:checked + .toggle-slider { background: #10b981; }
.toggle input:checked + .toggle-slider:before { transform: translateX(20px); }
.settings-row { display: grid; grid-template-columns: 200px 1fr; gap: 1rem; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #1e2028; }
.settings-row:last-child { border-bottom: none; }
.settings-row-2col { display: grid; grid-template-columns: 200px 1fr 200px 1fr; gap: 1rem; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #1e2028; }
</style>

<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-white">Settings</h1>
        <button type="submit" form="settingsForm" name="save_settings" value="1" class="px-4 py-2 rounded-lg bg-teal-500 text-white text-sm font-medium hover:bg-teal-600 transition">
            Save Changes
        </button>
    </div>
    
    <?php if ($error): ?>
    <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm"><?= $success ?></div>
    <?php endif; ?>
    
    <!-- Version Info -->
    <div class="flex items-center justify-center gap-12 mb-6 py-4">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-green-500"></span>
            <span class="text-gray-400 text-sm">Installed Version</span>
            <span class="text-white font-bold"><?= $currentVersion ?></span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full <?= $geoipVersion !== 'Not Installed' ? 'bg-green-500' : 'bg-red-500' ?>"></span>
            <span class="text-gray-400 text-sm">GeoLite2 Version</span>
            <span class="text-white font-bold"><?= $geoipVersion ?></span>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="flex gap-1 mb-4">
        <button type="button" class="tab-btn active px-4 py-2 rounded-lg bg-gray-800 text-gray-300 text-xs font-medium" data-tab="general">GENERAL</button>
        <button type="button" class="tab-btn px-4 py-2 rounded-lg bg-gray-800 text-gray-300 text-xs font-medium" data-tab="cryonix">CRYONIX</button>
        <button type="button" class="tab-btn px-4 py-2 rounded-lg bg-gray-800 text-gray-300 text-xs font-medium" data-tab="reseller">RESELLER</button>
        <button type="button" class="tab-btn px-4 py-2 rounded-lg bg-gray-800 text-gray-300 text-xs font-medium" data-tab="streaming">STREAMING</button>
        <button type="button" class="tab-btn px-4 py-2 rounded-lg bg-gray-800 text-gray-300 text-xs font-medium" data-tab="mag">MAG</button>
        <button type="button" class="tab-btn px-4 py-2 rounded-lg bg-gray-800 text-gray-300 text-xs font-medium" data-tab="updates">UPDATES</button>
    </div>
    
    <form id="settingsForm" method="POST">
        
        <!-- GENERAL -->
        <div id="tab-general" class="tab-content active glass rounded-xl border border-gray-800/50 p-6">
            <div class="settings-row">
                <label class="form-label">Server Name</label>
                <input type="text" name="server_name" value="<?= s('server_name', 'Cryonix') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">Service Logo</label>
                <input type="text" name="service_logo" value="<?= s('service_logo') ?>" class="form-input" placeholder="https://example.com/logo.png">
            </div>
            <div class="settings-row">
                <label class="form-label">Service Logo Sidebar</label>
                <input type="text" name="service_logo_sidebar" value="<?= s('service_logo_sidebar') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">Timezone</label>
                <select name="timezone" class="form-input">
                    <option value="UTC" <?= s('timezone') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                    <option value="Europe/London" <?= s('timezone') === 'Europe/London' ? 'selected' : '' ?>>Europe/London [BST +01:00]</option>
                    <option value="Europe/Paris" <?= s('timezone') === 'Europe/Paris' ? 'selected' : '' ?>>Europe/Paris [CET +01:00]</option>
                    <option value="Europe/Berlin" <?= s('timezone') === 'Europe/Berlin' ? 'selected' : '' ?>>Europe/Berlin [CET +01:00]</option>
                    <option value="America/New_York" <?= s('timezone') === 'America/New_York' ? 'selected' : '' ?>>America/New_York [EST -05:00]</option>
                    <option value="America/Los_Angeles" <?= s('timezone') === 'America/Los_Angeles' ? 'selected' : '' ?>>America/Los_Angeles [PST -08:00]</option>
                </select>
            </div>
            <div class="settings-row">
                <label class="form-label">Enigma2 Bouquet Name</label>
                <input type="text" name="enigma2_bouquet_name" value="<?= s('enigma2_bouquet_name', 'Cryonix') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">Live Streaming Pass</label>
                <input type="text" name="live_streaming_pass" value="<?= s('live_streaming_pass') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">Load Balancing Key</label>
                <input type="text" name="load_balancing_key" value="<?= s('load_balancing_key') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">Message Dashboard Resellers</label>
                <textarea name="message_resellers" rows="4" class="form-input"><?= s('message_resellers') ?></textarea>
            </div>
            <div class="settings-row">
                <label class="form-label">Manuals Resellers Dashboard</label>
                <textarea name="manuals_resellers" rows="4" class="form-input"><?= s('manuals_resellers') ?></textarea>
            </div>
        </div>
        
        <!-- CRYONIX -->
        <div id="tab-cryonix" class="tab-content glass rounded-xl border border-gray-800/50 p-6">
            <div class="settings-row-2col">
                <label class="form-label">Player Credentials</label>
                <input type="text" name="player_username" value="<?= s('player_username') ?>" class="form-input" placeholder="Username">
                <label class="form-label"></label>
                <input type="text" name="player_password" value="<?= s('player_password') ?>" class="form-input" placeholder="Password">
            </div>
            <div class="settings-row">
                <label class="form-label">TMDB Key</label>
                <input type="text" name="tmdb_key" value="<?= s('tmdb_key') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">TMDB Language</label>
                <select name="tmdb_language" class="form-input">
                    <option value="en" <?= s('tmdb_language') === 'en' ? 'selected' : '' ?>>Default - EN</option>
                    <option value="de" <?= s('tmdb_language') === 'de' ? 'selected' : '' ?>>Deutsch - DE</option>
                    <option value="fr" <?= s('tmdb_language') === 'fr' ? 'selected' : '' ?>>Français - FR</option>
                </select>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">TMDB HTTP</label>
                <label class="toggle"><input type="checkbox" name="tmdb_http" value="1" <?= checked('tmdb_http') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Logout On IP Change</label>
                <label class="toggle"><input type="checkbox" name="logout_ip_change" value="1" <?= checked('logout_ip_change', '1') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row">
                <label class="form-label">Release Parser</label>
                <select name="release_parser" class="form-input">
                    <option value="python">Python Based (slower, more accurate)</option>
                    <option value="php">PHP Based (faster)</option>
                </select>
            </div>
            <div class="settings-row">
                <label class="form-label">reCAPTCHA V2 - Site Key</label>
                <input type="text" name="recaptcha_site_key" value="<?= s('recaptcha_site_key') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">reCAPTCHA V2 - Secret Key</label>
                <input type="text" name="recaptcha_secret_key" value="<?= s('recaptcha_secret_key') ?>" class="form-input">
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Enable reCAPTCHA</label>
                <label class="toggle"><input type="checkbox" name="enable_recaptcha" value="1" <?= checked('enable_recaptcha') ?>><span class="toggle-slider"></span></label>
                <label class="form-label"></label>
                <div></div>
            </div>
            <div class="settings-row">
                <label class="form-label">Token Telegram</label>
                <input type="text" name="telegram_token" value="<?= s('telegram_token') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">Chat ID Telegram</label>
                <input type="text" name="telegram_chat_id" value="<?= s('telegram_chat_id') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">Cloudflare Connecting IP</label>
                <input type="text" name="cloudflare_ip" value="<?= s('cloudflare_ip', 'HTTP_CF_CONNECTING_IP') ?>" class="form-input">
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Maximum Login Attempts</label>
                <input type="number" name="max_login_attempts" value="<?= s('max_login_attempts', '3') ?>" class="form-input">
                <label class="form-label">Minimum Password Length</label>
                <input type="number" name="min_password_length" value="<?= s('min_password_length', '0') ?>" class="form-input">
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Default Entries to Show</label>
                <select name="default_entries" class="form-input">
                    <option value="10" <?= s('default_entries') === '10' ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= s('default_entries') === '25' ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= s('default_entries') === '50' ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= s('default_entries') === '100' ? 'selected' : '' ?>>100</option>
                </select>
                <label class="form-label">Two Factor Authentication</label>
                <label class="toggle"><input type="checkbox" name="two_factor_auth" value="1" <?= checked('two_factor_auth') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Localhost API</label>
                <label class="toggle"><input type="checkbox" name="localhost_api" value="1" <?= checked('localhost_api', '1') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Dark Mode Login</label>
                <label class="toggle"><input type="checkbox" name="dark_mode_login" value="1" <?= checked('dark_mode_login', '1') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Dashboard Stats</label>
                <label class="toggle"><input type="checkbox" name="dashboard_stats" value="1" <?= checked('dashboard_stats', '1') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Stats Interval</label>
                <input type="number" name="stats_interval" value="<?= s('stats_interval', '600') ?>" class="form-input">
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Dashboard World Map Live</label>
                <label class="toggle"><input type="checkbox" name="world_map_live" value="1" <?= checked('world_map_live', '1') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Dashboard World Map Activity</label>
                <label class="toggle"><input type="checkbox" name="world_map_activity" value="1" <?= checked('world_map_activity') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Download Images</label>
                <label class="toggle"><input type="checkbox" name="download_images" value="1" <?= checked('download_images', '1') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Auto-Refresh by Default</label>
                <label class="toggle"><input type="checkbox" name="auto_refresh" value="1" <?= checked('auto_refresh', '1') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Statistics</label>
                <label class="toggle"><input type="checkbox" name="statistics" value="1" <?= checked('statistics', '1') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Order Streams</label>
                <label class="toggle"><input type="checkbox" name="order_streams" value="1" <?= checked('order_streams') ?>><span class="toggle-slider"></span></label>
            </div>
        </div>
        
        <!-- RESELLER -->
        <div id="tab-reseller" class="tab-content glass rounded-xl border border-gray-800/50 p-6">
            <div class="settings-row">
                <label class="form-label">Footer Copyright</label>
                <input type="text" name="footer_copyright" value="<?= s('footer_copyright', 'X Project 2026') ?>" class="form-input">
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Disable Trials</label>
                <label class="toggle"><input type="checkbox" name="disable_trials" value="1" <?= checked('disable_trials') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Allow Restrictions</label>
                <label class="toggle"><input type="checkbox" name="allow_restrictions" value="1" <?= checked('allow_restrictions') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Change Usernames</label>
                <label class="toggle"><input type="checkbox" name="change_usernames" value="1" <?= checked('change_usernames') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Change Own DNS</label>
                <label class="toggle"><input type="checkbox" name="change_own_dns" value="1" <?= checked('change_own_dns') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Change Own Email Address</label>
                <label class="toggle"><input type="checkbox" name="change_own_email" value="1" <?= checked('change_own_email') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Change Own Password</label>
                <label class="toggle"><input type="checkbox" name="change_own_password" value="1" <?= checked('change_own_password', '1') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Change Own Language</label>
                <label class="toggle"><input type="checkbox" name="change_own_language" value="1" <?= checked('change_own_language') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Reseller Send Mag Events</label>
                <label class="toggle"><input type="checkbox" name="reseller_mag_events" value="1" <?= checked('reseller_mag_events') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Reseller can use IspLock</label>
                <label class="toggle"><input type="checkbox" name="reseller_isplock" value="1" <?= checked('reseller_isplock') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Reseller can use Reset Isp</label>
                <label class="toggle"><input type="checkbox" name="reseller_reset_isp" value="1" <?= checked('reseller_reset_isp') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Reseller can see Manuals</label>
                <label class="toggle"><input type="checkbox" name="reseller_manuals" value="1" <?= checked('reseller_manuals') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Reseller can view Info Dashboard</label>
                <label class="toggle"><input type="checkbox" name="reseller_info_dashboard" value="1" <?= checked('reseller_info_dashboard', '1') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Reseller can view APPS Dashboard</label>
                <label class="toggle"><input type="checkbox" name="reseller_apps_dashboard" value="1" <?= checked('reseller_apps_dashboard', '1') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Reseller can Convert MAG to M3U</label>
                <label class="toggle"><input type="checkbox" name="reseller_mag_m3u" value="1" <?= checked('reseller_mag_m3u') ?>><span class="toggle-slider"></span></label>
            </div>
        </div>
        
        <!-- STREAMING -->
        <div id="tab-streaming" class="tab-content glass rounded-xl border border-gray-800/50 p-6">
            <div class="settings-row">
                <label class="form-label">Flood Limit</label>
                <input type="number" name="flood_limit" value="<?= s('flood_limit', '0') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">Request Frequency in Seconds</label>
                <input type="number" name="request_frequency" value="<?= s('request_frequency', '2') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">Flood IP Exclusions</label>
                <input type="text" name="flood_ip_exclusions" value="<?= s('flood_ip_exclusions') ?>" class="form-input" placeholder="Comma separated IPs">
            </div>
            <div class="settings-row">
                <label class="form-label">Main or Loadbalance Https</label>
                <input type="text" name="main_https" value="<?= s('main_https') ?>" class="form-input">
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Use Https M3U Lines</label>
                <label class="toggle"><input type="checkbox" name="https_m3u" value="1" <?= checked('https_m3u') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Disallow Empty UA</label>
                <label class="toggle"><input type="checkbox" name="disallow_empty_ua" value="1" <?= checked('disallow_empty_ua', '1') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Auto-Kick Users</label>
                <input type="number" name="auto_kick_users" value="<?= s('auto_kick_users', '0') ?>" class="form-input">
                <label class="form-label"></label>
                <div></div>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Client Prebuffer</label>
                <input type="number" name="client_prebuffer" value="<?= s('client_prebuffer', '20') ?>" class="form-input">
                <label class="form-label">Restreamer Prebuffer</label>
                <input type="number" name="restreamer_prebuffer" value="<?= s('restreamer_prebuffer', '0') ?>" class="form-input">
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Split Clients</label>
                <select name="split_clients" class="form-input">
                    <option value="equally">Equally</option>
                    <option value="round_robin">Round Robin</option>
                </select>
                <label class="form-label">Split By</label>
                <select name="split_by" class="form-input">
                    <option value="connections">Connections</option>
                    <option value="bandwidth">Bandwidth</option>
                </select>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Analysis Duration</label>
                <input type="number" name="analysis_duration" value="<?= s('analysis_duration', '4500000') ?>" class="form-input">
                <label class="form-label">Probe Size</label>
                <input type="number" name="probe_size" value="<?= s('probe_size', '4000000') ?>" class="form-input">
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Persistent Connections</label>
                <label class="toggle"><input type="checkbox" name="persistent_connections" value="1" <?= checked('persistent_connections', '1') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Random RTMP IP</label>
                <label class="toggle"><input type="checkbox" name="random_rtmp_ip" value="1" <?= checked('random_rtmp_ip', '1') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Stream Start Delay</label>
                <input type="number" name="stream_start_delay" value="<?= s('stream_start_delay', '0') ?>" class="form-input">
                <label class="form-label">Online Capacity Interval</label>
                <input type="number" name="online_capacity_interval" value="<?= s('online_capacity_interval', '10') ?>" class="form-input">
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Enable ISP's</label>
                <label class="toggle"><input type="checkbox" name="enable_isps" value="1" <?= checked('enable_isps', '1') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Enable Isp Lock</label>
                <label class="toggle"><input type="checkbox" name="enable_isp_lock" value="1" <?= checked('enable_isp_lock') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">VOD Download Speed</label>
                <input type="number" name="vod_download_speed" value="<?= s('vod_download_speed', '200') ?>" class="form-input">
                <label class="form-label">VOD Download Limit</label>
                <input type="number" name="vod_download_limit" value="<?= s('vod_download_limit', '0') ?>" class="form-input">
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Stream Down Video</label>
                <label class="toggle"><input type="checkbox" name="stream_down_video" value="1" <?= checked('stream_down_video', '1') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Priority Backup Stream</label>
                <label class="toggle"><input type="checkbox" name="priority_backup" value="1" <?= checked('priority_backup', '1') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row">
                <label class="form-label">Banned Video URL</label>
                <input type="text" name="banned_video_url" value="<?= s('banned_video_url') ?>" class="form-input" placeholder="http://example.com/statusvideos/output.ts">
            </div>
            <div class="settings-row">
                <label class="form-label">Expired Video URL</label>
                <input type="text" name="expired_video_url" value="<?= s('expired_video_url') ?>" class="form-input" placeholder="http://example.com/statusvideos/output.ts">
            </div>
            <div class="settings-row">
                <label class="form-label">Message of the Day</label>
                <input type="text" name="motd" value="<?= s('motd', 'Welcome to Cryonix') ?>" class="form-input">
            </div>
        </div>
        
        <!-- MAG -->
        <div id="tab-mag" class="tab-content glass rounded-xl border border-gray-800/50 p-6">
            <div class="settings-row-2col">
                <label class="form-label">Show All Categories</label>
                <label class="toggle"><input type="checkbox" name="mag_show_all_categories" value="1" <?= checked('mag_show_all_categories') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">MAG Security</label>
                <label class="toggle"><input type="checkbox" name="mag_security" value="1" <?= checked('mag_security', '1') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Always Enabled Subtitles</label>
                <label class="toggle"><input type="checkbox" name="mag_subtitles" value="1" <?= checked('mag_subtitles') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Connection Problem Indication</label>
                <label class="toggle"><input type="checkbox" name="mag_connection_indication" value="1" <?= checked('mag_connection_indication') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Show Channel Logos</label>
                <label class="toggle"><input type="checkbox" name="mag_channel_logos" value="1" <?= checked('mag_channel_logos') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Show Preview Channel Logos</label>
                <label class="toggle"><input type="checkbox" name="mag_preview_logos" value="1" <?= checked('mag_preview_logos') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Allow STB Password Change</label>
                <label class="toggle"><input type="checkbox" name="mag_stb_password" value="1" <?= checked('mag_stb_password') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Stalker Debug</label>
                <label class="toggle"><input type="checkbox" name="mag_stalker_debug" value="1" <?= checked('mag_stalker_debug') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Mag Lock Image</label>
                <label class="toggle"><input type="checkbox" name="mag_lock_image" value="1" <?= checked('mag_lock_image') ?>><span class="toggle-slider"></span></label>
                <label class="form-label">Disable Mag Portal</label>
                <label class="toggle"><input type="checkbox" name="disable_mag_portal" value="1" <?= checked('disable_mag_portal', '1') ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Default Container</label>
                <select name="mag_default_container" class="form-input">
                    <option value="ts" <?= s('mag_default_container') === 'ts' ? 'selected' : '' ?>>TS</option>
                    <option value="hls" <?= s('mag_default_container') === 'hls' ? 'selected' : '' ?>>HLS</option>
                </select>
                <label class="form-label">Default Theme</label>
                <select name="mag_default_theme" class="form-input">
                    <option value="default">Default</option>
                    <option value="modern">Modern</option>
                </select>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Recording Max Length</label>
                <input type="number" name="mag_recording_max" value="<?= s('mag_recording_max', '180') ?>" class="form-input">
                <label class="form-label">Default Aspect Ratio</label>
                <select name="mag_aspect_ratio" class="form-input">
                    <option value="fit">Fit</option>
                    <option value="16:9">16:9</option>
                    <option value="4:3">4:3</option>
                </select>
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Playback Limit</label>
                <input type="number" name="mag_playback_limit" value="<?= s('mag_playback_limit', '3') ?>" class="form-input">
                <label class="form-label">Max Local Recordings</label>
                <input type="number" name="mag_max_recordings" value="<?= s('mag_max_recordings', '10') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">Teste Download URL</label>
                <input type="text" name="mag_teste_url" value="<?= s('mag_teste_url') ?>" class="form-input">
            </div>
            <div class="settings-row">
                <label class="form-label">Allowed STB Types</label>
                <input type="text" name="mag_allowed_stb" value="<?= s('mag_allowed_stb') ?>" class="form-input" placeholder="MAG250, MAG254, MAG322...">
            </div>
            <div class="settings-row-2col">
                <label class="form-label">Allow Recording</label>
                <label class="toggle"><input type="checkbox" name="mag_allow_recording" value="1" <?= checked('mag_allow_recording') ?>><span class="toggle-slider"></span></label>
                <label class="form-label"></label>
                <div></div>
            </div>
            <div class="settings-row">
                <label class="form-label">Allowed STB Recording</label>
                <input type="text" name="mag_allowed_stb_recording" value="<?= s('mag_allowed_stb_recording') ?>" class="form-input">
            </div>
        </div>
        
        <!-- UPDATES -->
        <div id="tab-updates" class="tab-content glass rounded-xl border border-gray-800/50 p-6">
            <div class="text-center py-8">
                <h2 class="text-xl font-bold text-white mb-4">System Updates</h2>
                <p class="text-gray-400 mb-2">Current Version: <span class="text-cryo-400 font-bold"><?= $currentVersion ?></span></p>
                <p class="text-gray-500 text-sm mb-6">Check for available updates</p>
                
                <div id="updateStatus" class="mb-6">
                    <p class="text-gray-500">Click the button below to check for new versions</p>
                </div>
                
                <button type="button" onclick="checkForUpdates()" class="px-6 py-3 rounded-lg bg-cryo-500 text-white font-medium hover:bg-cryo-600 transition">
                    Check for Updates
                </button>
            </div>
        </div>
        
    </form>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach((btn, i) => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach((b, j) => b.classList.toggle('active', i === j));
        document.querySelectorAll('.tab-content').forEach((c, j) => c.classList.toggle('active', i === j));
    });
});

function checkForUpdates() {
    document.getElementById('updateStatus').innerHTML = '<p class="text-amber-400">Checking for updates...</p>';
    
    fetch('<?= ADMIN_PATH ?>/api/updates/check')
        .then(r => {
            if (!r.ok) throw new Error('Server error');
            return r.text();
        })
        .then(text => {
            if (!text) throw new Error('Empty response');
            return JSON.parse(text);
        })
        .then(data => {
            if (data.error) {
                document.getElementById('updateStatus').innerHTML = '<p class="text-red-400">' + data.error + '</p>';
                return;
            }
            if (data.available) {
                document.getElementById('updateStatus').innerHTML = `
                    <div class="p-4 rounded-lg bg-green-500/10 border border-green-500/30 mb-4">
                        <p class="text-green-400 font-medium">Update Available!</p>
                        <p class="text-gray-400 text-sm">Installed: ${data.current}</p>
                        <p class="text-white font-medium">Latest: ${data.latest}</p>
                    </div>
                    <button type="button" onclick="applyUpdate()" class="px-6 py-3 rounded-lg bg-green-500 text-white font-medium hover:bg-green-600 transition">
                        Apply Update
                    </button>
                `;
            } else {
                document.getElementById('updateStatus').innerHTML = `
                    <div class="p-4 rounded-lg bg-green-500/10 border border-green-500/30">
                        <p class="text-green-400 font-medium">You are running the latest version!</p>
                        <p class="text-gray-400 text-sm">Version: ${data.current || '<?= $currentVersion ?>'}</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            document.getElementById('updateStatus').innerHTML = '<p class="text-red-400">Error: ' + err.message + '</p>';
        });
}

function applyUpdate() {
    if (!confirm('Apply update now? Your settings will be preserved.')) return;
    
    document.getElementById('updateStatus').innerHTML = '<p class="text-amber-400">Applying update... Please wait...</p>';
    
    fetch('<?= ADMIN_PATH ?>/api/updates/apply', { method: 'POST' })
        .then(r => {
            if (!r.ok) throw new Error('Server error');
            return r.text();
        })
        .then(text => {
            if (!text) throw new Error('Empty response');
            return JSON.parse(text);
        })
        .then(data => {
            if (data.success) {
                document.getElementById('updateStatus').innerHTML = '<p class="text-green-400">Update applied! Refreshing...</p>';
                setTimeout(() => location.reload(), 2000);
            } else {
                document.getElementById('updateStatus').innerHTML = '<p class="text-red-400">Update failed: ' + (data.error || 'Unknown error') + '</p>';
            }
        })
        .catch(err => {
            document.getElementById('updateStatus').innerHTML = '<p class="text-red-400">Error: ' + err.message + '</p>';
        });
}
</script>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>

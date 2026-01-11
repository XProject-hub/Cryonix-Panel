<?php
/**
 * Cryonix Panel - Add/Edit Stream
 * Complete Stream Form with Tabs
 * Copyright 2026 XProject-Hub
 */
$pageTitle = isset($_GET['id']) ? 'Edit Stream' : 'Add Stream';
$isEdit = isset($_GET['id']);
$streamId = (int)($_GET['id'] ?? 0);

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$stream = [];
$categories = [];
$bouquets = [];
$servers = [];
$epgSources = [];
$transcodeProfiles = [];
$error = '';
$success = '';

try {
    $db = Database::getInstance();
    
    // Get categories
    $categories = $db->fetchAll("SELECT * FROM stream_categories WHERE category_type = 'live' ORDER BY category_name") ?: [];
    
    // Get bouquets
    $bouquets = $db->fetchAll("SELECT * FROM bouquets ORDER BY bouquet_name") ?: [];
    
    // Get servers
    $servers = $db->fetchAll("SELECT * FROM servers WHERE status = 'online' ORDER BY is_main DESC, server_name") ?: [];
    
    // Load stream if editing
    if ($isEdit && $streamId) {
        $stream = $db->fetch("SELECT * FROM streams WHERE id = ?", [$streamId]) ?: [];
    }
    
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'stream_display_name' => $_POST['stream_name'] ?? '',
            'stream_source' => $_POST['stream_url'] ?? '',
            'stream_type' => $_POST['stream_type'] ?? 'live',
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'stream_icon' => $_POST['stream_icon'] ?? '',
            'epg_channel_id' => $_POST['epg_channel_id'] ?? '',
            'direct_source' => isset($_POST['direct_source']) ? 1 : 0,
            'custom_ffmpeg' => $_POST['custom_ffmpeg'] ?? '',
            'status' => $_POST['start_stream'] ? 'active' : 'inactive'
        ];
        
        if ($isEdit && $streamId) {
            $db->update('streams', $data, 'id = ?', [$streamId]);
            $success = 'Stream updated successfully!';
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->insert('streams', $data);
            $success = 'Stream added successfully!';
        }
        
        // Redirect to streams list
        header('Location: ' . ADMIN_PATH . '/streams?success=1');
        exit;
        
    } catch (\Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

ob_start();
?>

<style>
.tab-btn { transition: all 0.2s; border: none; }
.tab-btn.active { background: linear-gradient(90deg, #14b8a6, #0d9488); color: white; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.form-label { display: block; font-size: 0.75rem; color: #ef4444; margin-bottom: 0.25rem; font-weight: 500; }
.form-label-normal { display: block; font-size: 0.75rem; color: #9ca3af; margin-bottom: 0.25rem; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; border-radius: 0.375rem; background: white; border: 1px solid #d1d5db; color: #111; font-size: 0.875rem; }
.form-input:focus { outline: none; border-color: #14b8a6; }
.toggle { position: relative; display: inline-block; width: 44px; height: 24px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; inset: 0; background: #d1d5db; border-radius: 24px; transition: 0.3s; }
.toggle-slider:before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; }
.toggle input:checked + .toggle-slider { background: #10b981; }
.toggle input:checked + .toggle-slider:before { transform: translateX(20px); }
.form-row { display: grid; grid-template-columns: 140px 1fr; gap: 1rem; align-items: center; margin-bottom: 1rem; }
.form-row-2col { display: grid; grid-template-columns: 140px 1fr 140px 1fr; gap: 1rem; align-items: center; margin-bottom: 1rem; }
.url-input-group { display: flex; gap: 0.25rem; }
.url-btn { padding: 0.5rem; border-radius: 0.375rem; border: none; cursor: pointer; }
</style>

<div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-white"><?= $pageTitle ?></h1>
        <div class="flex items-center gap-2">
            <a href="<?= ADMIN_PATH ?>/streams" class="px-4 py-2 rounded-lg bg-teal-500 text-white text-sm font-medium hover:bg-teal-600 transition">View Streams</a>
            <a href="<?= ADMIN_PATH ?>/streams/import" class="px-4 py-2 rounded-lg bg-purple-500 text-white text-sm font-medium hover:bg-purple-600 transition">Import M3U</a>
        </div>
    </div>
    
    <?php if ($error): ?>
    <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm"><?= $success ?></div>
    <?php endif; ?>
    
    <form method="POST" id="streamForm">
        <!-- Tabs -->
        <div class="flex gap-1 mb-4">
            <button type="button" class="tab-btn active px-4 py-2 rounded-lg bg-gray-200 text-gray-700 text-xs font-medium" data-tab="details">DETAILS</button>
            <button type="button" class="tab-btn px-4 py-2 rounded-lg bg-gray-200 text-gray-700 text-xs font-medium" data-tab="advanced">ADVANCED</button>
            <button type="button" class="tab-btn px-4 py-2 rounded-lg bg-gray-200 text-gray-700 text-xs font-medium" data-tab="map">MAP</button>
            <button type="button" class="tab-btn px-4 py-2 rounded-lg bg-gray-200 text-gray-700 text-xs font-medium" data-tab="restart">RESTART</button>
            <button type="button" class="tab-btn px-4 py-2 rounded-lg bg-gray-200 text-gray-700 text-xs font-medium" data-tab="epg">EPG</button>
            <button type="button" class="tab-btn px-4 py-2 rounded-lg bg-gray-200 text-gray-700 text-xs font-medium" data-tab="servers">SERVERS</button>
        </div>
        
        <!-- DETAILS Tab -->
        <div id="tab-details" class="tab-content active bg-gray-100 rounded-xl p-6">
            <div class="form-row">
                <label class="form-label">Stream Name</label>
                <input type="text" name="stream_name" value="<?= htmlspecialchars($stream['stream_display_name'] ?? '') ?>" class="form-input" required>
            </div>
            <div class="form-row">
                <label class="form-label">Stream URL</label>
                <div class="url-input-group">
                    <input type="text" name="stream_url" value="<?= htmlspecialchars($stream['stream_source'] ?? '') ?>" class="form-input flex-1" required>
                    <button type="button" class="url-btn bg-green-500 text-white" title="Test URL"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></button>
                    <button type="button" class="url-btn bg-gray-400 text-white" title="Move Up"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button>
                    <button type="button" class="url-btn bg-gray-400 text-white" title="Move Down"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>
                    <button type="button" class="url-btn bg-blue-500 text-white" onclick="addUrlField()" title="Add URL"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></button>
                    <button type="button" class="url-btn bg-red-500 text-white" title="Remove URL"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
            </div>
            <div class="form-row">
                <label class="form-label">Category Type</label>
                <select name="category_id" class="form-input">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($stream['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label class="form-label-normal">Add To Bouquets</label>
                <select name="bouquets[]" multiple class="form-input" style="height: 80px;">
                    <?php foreach ($bouquets as $bouquet): ?>
                    <option value="<?= $bouquet['id'] ?>"><?= htmlspecialchars($bouquet['bouquet_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label class="form-label-normal">Stream Logo URL</label>
                <input type="text" name="stream_icon" value="<?= htmlspecialchars($stream['stream_icon'] ?? '') ?>" class="form-input" placeholder="https://example.com/logo.png">
            </div>
            <div class="form-row">
                <label class="form-label-normal">Notes</label>
                <textarea name="notes" rows="3" class="form-input"><?= htmlspecialchars($stream['notes'] ?? '') ?></textarea>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="nextTab('advanced')" class="px-6 py-2 rounded-lg bg-gray-500 text-white font-medium hover:bg-gray-600 transition">Next</button>
            </div>
        </div>
        
        <!-- ADVANCED Tab -->
        <div id="tab-advanced" class="tab-content bg-gray-100 rounded-xl p-6">
            <div class="form-row-2col">
                <label class="form-label-normal">Generate PTS</label>
                <label class="toggle"><input type="checkbox" name="generate_pts" value="1" checked><span class="toggle-slider"></span></label>
                <label class="form-label-normal">Native Frames</label>
                <label class="toggle"><input type="checkbox" name="native_frames" value="1"><span class="toggle-slider"></span></label>
            </div>
            <div class="form-row-2col">
                <label class="form-label-normal">Stream All Codecs</label>
                <label class="toggle"><input type="checkbox" name="stream_all_codecs" value="1"><span class="toggle-slider"></span></label>
                <label class="form-label-normal">Allow Recording</label>
                <label class="toggle"><input type="checkbox" name="allow_recording" value="1" checked><span class="toggle-slider"></span></label>
            </div>
            <div class="form-row-2col">
                <label class="form-label-normal">Allow RTMP Output</label>
                <label class="toggle"><input type="checkbox" name="allow_rtmp" value="1"><span class="toggle-slider"></span></label>
                <label class="form-label-normal">Direct Source</label>
                <label class="toggle"><input type="checkbox" name="direct_source" value="1" <?= ($stream['direct_source'] ?? 0) ? 'checked' : '' ?>><span class="toggle-slider"></span></label>
            </div>
            <div class="form-row-2col">
                <label class="form-label-normal">Custom Channel SID</label>
                <input type="text" name="custom_sid" class="form-input">
                <label class="form-label-normal">Minute Delay</label>
                <input type="number" name="minute_delay" value="0" class="form-input">
            </div>
            <div class="form-row-2col">
                <label class="form-label-normal">Custom FFmpeg Command</label>
                <input type="text" name="custom_ffmpeg" value="<?= htmlspecialchars($stream['custom_ffmpeg'] ?? '') ?>" class="form-input">
                <label class="form-label-normal">On Demand Probesize</label>
                <input type="number" name="probesize" value="128000" class="form-input">
            </div>
            <div class="form-row">
                <label class="form-label-normal">User Agent</label>
                <input type="text" name="user_agent" value="Cryonix IPTV Panel Pro" class="form-input">
            </div>
            <div class="form-row">
                <label class="form-label-normal">HTTP Proxy</label>
                <input type="text" name="http_proxy" class="form-input" placeholder="http://proxy:port">
            </div>
            <div class="form-row">
                <label class="form-label-normal">Cookie</label>
                <input type="text" name="cookie" class="form-input">
            </div>
            <div class="form-row">
                <label class="form-label-normal">Headers</label>
                <input type="text" name="headers" class="form-input" placeholder="Header1: Value1|Header2: Value2">
            </div>
            <div class="form-row">
                <label class="form-label-normal">Transcoding Profile</label>
                <select name="transcode_profile" class="form-input">
                    <option value="">Transcoding Disabled</option>
                    <option value="1">720p H264</option>
                    <option value="2">1080p H264</option>
                    <option value="3">480p H264</option>
                </select>
            </div>
            <div class="flex justify-between">
                <button type="button" onclick="prevTab('details')" class="px-6 py-2 rounded-lg bg-gray-400 text-white font-medium hover:bg-gray-500 transition">Previous</button>
                <button type="button" onclick="nextTab('map')" class="px-6 py-2 rounded-lg bg-gray-500 text-white font-medium hover:bg-gray-600 transition">Next</button>
            </div>
        </div>
        
        <!-- MAP Tab -->
        <div id="tab-map" class="tab-content bg-gray-100 rounded-xl p-6">
            <div class="form-row">
                <label class="form-label-normal">Custom Map</label>
                <div class="flex gap-2">
                    <input type="text" name="custom_map" class="form-input flex-1">
                    <button type="button" class="px-4 py-2 rounded-lg bg-teal-500 text-white"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></button>
                </div>
            </div>
            <div class="p-4 rounded-lg bg-amber-100 border border-amber-300 text-amber-800 text-sm mb-4">
                Custom maps are advanced features and you should only modify these if you know what you're doing. Hit the search icon to map the streams. Once mapped, you can select them from the table below.
            </div>
            <table class="w-full bg-white rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">#</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">TYPE</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">INFORMATION</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">No data available in table</td></tr>
                </tbody>
            </table>
            <div class="flex justify-between mt-4">
                <button type="button" onclick="prevTab('advanced')" class="px-6 py-2 rounded-lg bg-gray-400 text-white font-medium hover:bg-gray-500 transition">Previous</button>
                <button type="button" onclick="nextTab('restart')" class="px-6 py-2 rounded-lg bg-gray-500 text-white font-medium hover:bg-gray-600 transition">Next</button>
            </div>
        </div>
        
        <!-- RESTART Tab -->
        <div id="tab-restart" class="tab-content bg-gray-100 rounded-xl p-6">
            <div class="form-row">
                <label class="form-label-normal">Days to Restart</label>
                <input type="text" name="restart_days" class="form-input" placeholder="Mon,Tue,Wed...">
            </div>
            <div class="form-row">
                <label class="form-label-normal">Time to Restart</label>
                <div class="flex gap-2">
                    <input type="time" name="restart_time" value="06:00" class="form-input flex-1">
                    <button type="button" class="px-4 py-2 rounded-lg bg-gray-300 text-gray-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button>
                </div>
            </div>
            <div class="flex justify-between mt-4">
                <button type="button" onclick="prevTab('map')" class="px-6 py-2 rounded-lg bg-gray-400 text-white font-medium hover:bg-gray-500 transition">Previous</button>
                <button type="button" onclick="nextTab('epg')" class="px-6 py-2 rounded-lg bg-gray-500 text-white font-medium hover:bg-gray-600 transition">Next</button>
            </div>
        </div>
        
        <!-- EPG Tab -->
        <div id="tab-epg" class="tab-content bg-gray-100 rounded-xl p-6">
            <div class="form-row">
                <label class="form-label-normal">EPG Source</label>
                <select name="epg_source" class="form-input">
                    <option value="">No EPG</option>
                    <option value="1">XMLTV Default</option>
                </select>
            </div>
            <div class="form-row">
                <label class="form-label-normal">EPG Channel ID</label>
                <select name="epg_channel_id" class="form-input">
                    <option value="">Select Channel</option>
                </select>
            </div>
            <div class="form-row">
                <label class="form-label-normal">EPG Language</label>
                <select name="epg_language" class="form-input">
                    <option value="">Default</option>
                    <option value="en">English</option>
                    <option value="de">German</option>
                    <option value="fr">French</option>
                </select>
            </div>
            <div class="flex justify-between mt-4">
                <button type="button" onclick="prevTab('restart')" class="px-6 py-2 rounded-lg bg-gray-400 text-white font-medium hover:bg-gray-500 transition">Previous</button>
                <button type="button" onclick="nextTab('servers')" class="px-6 py-2 rounded-lg bg-gray-500 text-white font-medium hover:bg-gray-600 transition">Next</button>
            </div>
        </div>
        
        <!-- SERVERS Tab -->
        <div id="tab-servers" class="tab-content bg-gray-100 rounded-xl p-6">
            <div class="form-row" style="align-items: flex-start;">
                <label class="form-label-normal">Server Tree</label>
                <div class="bg-white rounded-lg border p-4 max-h-64 overflow-y-auto">
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 p-1 hover:bg-gray-100 rounded cursor-pointer">
                            <input type="checkbox" name="server_source" value="source" checked class="rounded">
                            <span class="text-sm font-medium">Stream Source</span>
                        </label>
                        <?php foreach ($servers as $srv): ?>
                        <label class="flex items-center gap-2 p-1 hover:bg-gray-100 rounded cursor-pointer ml-4">
                            <input type="checkbox" name="servers[]" value="<?= $srv['id'] ?>" <?= $srv['is_main'] ? 'checked' : '' ?> class="rounded">
                            <span class="text-sm"><?= $srv['is_main'] ? '| Main Server |' : htmlspecialchars($srv['server_name']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <label class="form-label-normal">On Demand</label>
                <input type="text" name="on_demand" class="form-input">
            </div>
            <div class="form-row">
                <label class="form-label-normal">Timeshift Server</label>
                <select name="timeshift_server" class="form-input">
                    <option value="">Timeshift Disabled</option>
                    <?php foreach ($servers as $srv): ?>
                    <option value="<?= $srv['id'] ?>"><?= htmlspecialchars($srv['server_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row-2col">
                <label class="form-label-normal">Timeshift Days</label>
                <input type="number" name="timeshift_days" value="0" class="form-input">
                <label class="form-label-normal">Start Stream Now</label>
                <label class="toggle"><input type="checkbox" name="start_stream" value="1"><span class="toggle-slider"></span></label>
            </div>
            <div class="flex justify-between mt-4">
                <button type="button" onclick="prevTab('epg')" class="px-6 py-2 rounded-lg bg-gray-400 text-white font-medium hover:bg-gray-500 transition">Previous</button>
                <button type="submit" class="px-8 py-2 rounded-lg bg-cryo-500 text-white font-medium hover:bg-cryo-600 transition">Add</button>
            </div>
        </div>
    </form>
</div>

<script>
const tabs = ['details', 'advanced', 'map', 'restart', 'epg', 'servers'];

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        showTab(tab);
    });
});

function showTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
    document.getElementById(`tab-${tab}`).classList.add('active');
}

function nextTab(tab) {
    showTab(tab);
}

function prevTab(tab) {
    showTab(tab);
}

function addUrlField() {
    // Add additional URL field functionality
    alert('Additional URL fields feature - coming soon');
}
</script>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>

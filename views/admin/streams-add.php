<?php
/**
 * Cryonix Panel - Add/Edit Stream
 * Copyright 2026 XProject-Hub
 */
$pageTitle = isset($_GET['id']) ? 'Edit Channel' : 'Add Channel';
$isEdit = isset($_GET['id']);
$streamId = $_GET['id'] ?? null;

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$stream = null;
$error = '';
$categories = [];

try {
    $db = Database::getInstance();
    $categories = $db->fetchAll("SELECT * FROM stream_categories WHERE category_type = 'live' ORDER BY category_name") ?: [];
    
    if ($isEdit && $streamId) {
        $stream = $db->fetch("SELECT * FROM streams WHERE id = ?", [$streamId]);
        if (!$stream) {
            header('Location: ' . ADMIN_PATH . '/streams');
            exit;
        }
    }
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['stream_display_name'] ?? '');
    $source = trim($_POST['stream_source'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $epgChannelId = trim($_POST['epg_channel_id'] ?? '');
    $streamIcon = trim($_POST['stream_icon'] ?? '');
    
    if (empty($name)) {
        $error = 'Channel name is required';
    } elseif (empty($source)) {
        $error = 'Stream source URL is required';
    } else {
        try {
            $data = [
                'stream_display_name' => $name,
                'stream_source' => $source,
                'category_id' => $categoryId ?: null,
                'epg_channel_id' => $epgChannelId ?: null,
                'stream_icon' => $streamIcon ?: null,
                'stream_type' => 'live'
            ];
            
            if ($isEdit) {
                $db->update('streams', $data, 'id = ?', [$streamId]);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $db->insert('streams', $data);
            }
            
            header('Location: ' . ADMIN_PATH . '/streams');
            exit;
        } catch (\Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

ob_start();
?>

<div class="max-w-2xl mx-auto mb-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-white"><?= $pageTitle ?></h1>
        <a href="<?= ADMIN_PATH ?>/streams" class="px-4 py-2 rounded-lg bg-dark-800 text-gray-400 hover:text-white text-xs transition">← Back to Channels</a>
    </div>
</div>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="max-w-2xl mx-auto">
    <div class="glass rounded-xl p-6 border border-gray-800/50 space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Channel Name *</label>
            <input type="text" name="stream_display_name" value="<?= htmlspecialchars($stream['stream_display_name'] ?? '') ?>" required
                class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Stream Source URL *</label>
            <input type="url" name="stream_source" value="<?= htmlspecialchars($stream['stream_source'] ?? '') ?>" required
                placeholder="http://example.com/stream.m3u8"
                class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                <select name="category_id" class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
                    <option value="">Uncategorized</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($stream['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">EPG Channel ID</label>
                <input type="text" name="epg_channel_id" value="<?= htmlspecialchars($stream['epg_channel_id'] ?? '') ?>"
                    placeholder="channel.id"
                    class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Channel Icon URL</label>
            <input type="url" name="stream_icon" value="<?= htmlspecialchars($stream['stream_icon'] ?? '') ?>"
                placeholder="https://example.com/logo.png"
                class="w-full px-4 py-2.5 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
        </div>
        
        <div class="flex gap-4 pt-4 border-t border-gray-800">
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold hover:from-cyan-600 hover:to-blue-600 transition">
                <?= $isEdit ? 'Update Channel' : 'Add Channel' ?>
            </button>
            <a href="<?= ADMIN_PATH ?>/streams" class="px-6 py-2.5 rounded-lg bg-gray-800 text-gray-300 font-semibold hover:bg-gray-700 transition">
                Cancel
            </a>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
require CRYONIX_ROOT . '/views/admin/layout.php';
?>


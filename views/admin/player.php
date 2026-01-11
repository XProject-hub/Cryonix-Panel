<?php
/**
 * Cryonix Panel - Stream Player
 * Test stream playback
 * Copyright 2026 XProject-Hub
 */

require_once CRYONIX_ROOT . '/core/Database.php';
use CryonixPanel\Core\Database;

$streamId = (int)($_GET['id'] ?? 0);
$stream = null;
$error = '';

try {
    $db = Database::getInstance();
    $stream = $db->fetch("SELECT * FROM streams WHERE id = ?", [$streamId]);
    
    if (!$stream) {
        $error = 'Stream not found';
    }
} catch (\Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

$streamUrl = $stream['stream_source'] ?? '';
$streamName = $stream['stream_display_name'] ?? 'Unknown Stream';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($streamName) ?> - Cryonix Player</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <style>
        body { background: #0a0b0d; }
        .player-container { max-width: 100%; max-height: 100vh; }
        video { width: 100%; height: auto; max-height: calc(100vh - 120px); background: #000; }
    </style>
</head>
<body class="text-white min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900 border-b border-gray-800 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            </div>
            <div>
                <h1 class="font-bold text-white"><?= htmlspecialchars($streamName) ?></h1>
                <p class="text-xs text-gray-400">Stream ID: <?= $streamId ?></p>
            </div>
        </div>
        <button onclick="window.close()" class="px-4 py-2 rounded-lg bg-gray-800 text-gray-300 hover:bg-gray-700 text-sm">
            Close
        </button>
    </header>
    
    <?php if ($error): ?>
    <div class="flex-1 flex items-center justify-center">
        <div class="text-center">
            <svg class="w-16 h-16 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-red-400 text-lg"><?= htmlspecialchars($error) ?></p>
        </div>
    </div>
    <?php else: ?>
    <!-- Player -->
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="player-container w-full max-w-4xl">
            <video id="videoPlayer" controls autoplay class="rounded-lg shadow-2xl">
                Your browser does not support the video tag.
            </video>
        </div>
    </div>
    
    <!-- Info Bar -->
    <footer class="bg-gray-900 border-t border-gray-800 px-4 py-3">
        <div class="flex items-center justify-between text-sm">
            <div class="flex items-center gap-4">
                <span id="status" class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></span>
                    <span class="text-gray-400">Connecting...</span>
                </span>
                <span class="text-gray-600">|</span>
                <span class="text-gray-400">Source: <span class="text-gray-300 max-w-xs truncate inline-block align-bottom" title="<?= htmlspecialchars($streamUrl) ?>"><?= htmlspecialchars(substr($streamUrl, 0, 50)) ?>...</span></span>
            </div>
            <div class="flex items-center gap-4">
                <span id="resolution" class="text-gray-400">-</span>
                <span id="bitrate" class="text-gray-400">-</span>
            </div>
        </div>
    </footer>
    
    <script>
        const video = document.getElementById('videoPlayer');
        const streamUrl = <?= json_encode($streamUrl) ?>;
        const statusEl = document.getElementById('status');
        
        function updateStatus(status, color) {
            statusEl.innerHTML = `<span class="w-2 h-2 rounded-full bg-${color}-500"></span><span class="text-gray-400">${status}</span>`;
        }
        
        function playStream() {
            // Check if it's HLS stream
            if (streamUrl.includes('.m3u8')) {
                if (Hls.isSupported()) {
                    const hls = new Hls({
                        debug: false,
                        enableWorker: true,
                        lowLatencyMode: true
                    });
                    hls.loadSource(streamUrl);
                    hls.attachMedia(video);
                    hls.on(Hls.Events.MANIFEST_PARSED, () => {
                        updateStatus('Playing', 'green');
                        video.play();
                    });
                    hls.on(Hls.Events.ERROR, (event, data) => {
                        if (data.fatal) {
                            updateStatus('Error: ' + data.type, 'red');
                        }
                    });
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = streamUrl;
                    video.addEventListener('loadedmetadata', () => {
                        updateStatus('Playing', 'green');
                        video.play();
                    });
                }
            } else {
                // Direct stream (TS, MP4, etc)
                video.src = streamUrl;
                video.addEventListener('loadeddata', () => {
                    updateStatus('Playing', 'green');
                });
                video.addEventListener('error', (e) => {
                    updateStatus('Error loading stream', 'red');
                    console.error('Video error:', e);
                });
                video.play().catch(e => {
                    updateStatus('Click to play', 'yellow');
                });
            }
        }
        
        // Update resolution info
        video.addEventListener('loadedmetadata', () => {
            document.getElementById('resolution').textContent = video.videoWidth + 'x' + video.videoHeight;
        });
        
        // Start playback
        playStream();
    </script>
    <?php endif; ?>
</body>
</html>


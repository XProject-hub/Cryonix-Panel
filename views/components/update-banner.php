<!-- Update Notification Banner - Include in admin layout -->
<div id="update-banner" class="hidden fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-cyan-500 to-blue-500 text-white px-4 py-3 shadow-lg">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <div>
                <span class="font-semibold">Panel Update Available!</span>
                <span id="update-version" class="ml-2 text-cyan-100"></span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="showUpdateModal()" class="px-4 py-1.5 bg-white text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition">
                View Details
            </button>
            <button onclick="dismissUpdate()" class="text-white/80 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div id="update-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="hideUpdateModal()"></div>
        <div class="relative bg-gray-900 rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-gray-800">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Panel Update</h3>
                    <p class="text-gray-400 text-sm">New version available</p>
                </div>
            </div>
            
            <div class="bg-gray-800/50 rounded-xl p-4 mb-4">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-400">Current Version:</span>
                    <span id="current-version" class="text-white font-mono">-</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-400">Latest Version:</span>
                    <span id="latest-version" class="text-green-400 font-mono">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Released:</span>
                    <span id="release-date" class="text-white">-</span>
                </div>
            </div>
            
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-300 mb-2">What's New:</h4>
                <div id="release-notes" class="text-sm text-gray-400 bg-gray-800/30 rounded-lg p-3 max-h-32 overflow-y-auto">
                    Loading...
                </div>
            </div>
            
            <!-- IMPORTANT: Safe update notice -->
            <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-3 mb-4">
                <div class="flex gap-2">
                    <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-green-300">
                        <strong>100% Safe Update:</strong><br>
                        ✓ Your <strong>database</strong> stays untouched<br>
                        ✓ All <strong>users/lines</strong> are preserved<br>
                        ✓ All <strong>streams/VOD/series</strong> stay intact<br>
                        ✓ Your <strong>settings</strong> remain unchanged<br>
                        ✓ Automatic backup created before update
                    </div>
                </div>
            </div>
            
            <div id="update-progress" class="hidden mb-4">
                <div class="flex items-center gap-2 text-cyan-400 mb-2">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span id="update-status">Downloading update...</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                    <div id="update-bar" class="bg-cyan-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
            </div>
            
            <div id="update-success" class="hidden mb-4 bg-green-500/10 border border-green-500/20 rounded-lg p-4">
                <div class="flex items-center gap-2 text-green-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="font-semibold">Update Complete!</span>
                </div>
                <p class="text-sm text-green-300 mt-1">All your data is safe. Refresh to apply changes.</p>
            </div>
            
            <div id="update-error" class="hidden mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                <div class="flex items-center gap-2 text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-semibold">Update Failed</span>
                </div>
                <p id="error-message" class="text-sm text-red-300 mt-1"></p>
            </div>
            
            <div class="flex gap-3">
                <button onclick="hideUpdateModal()" id="cancel-btn" class="flex-1 py-2.5 rounded-lg bg-gray-800 text-gray-300 font-semibold hover:bg-gray-700 transition">
                    Later
                </button>
                <button onclick="applyUpdate()" id="update-btn" class="flex-1 py-2.5 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-semibold hover:from-cyan-600 hover:to-blue-600 transition">
                    Update Now
                </button>
                <button onclick="location.reload()" id="refresh-btn" class="hidden flex-1 py-2.5 rounded-lg bg-green-500 text-white font-semibold hover:bg-green-600 transition">
                    Refresh Page
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let updateData = null;

document.addEventListener('DOMContentLoaded', () => checkForUpdates());

function checkForUpdates() {
    fetch('/admin/update/check')
        .then(r => r.json())
        .then(data => {
            if (data.available) {
                updateData = data;
                document.getElementById('update-banner').classList.remove('hidden');
                document.getElementById('update-version').textContent = `v${data.current} → v${data.latest}`;
            }
        })
        .catch(e => console.log('Update check:', e));
}

function showUpdateModal() {
    if (!updateData) return;
    document.getElementById('current-version').textContent = 'v' + updateData.current;
    document.getElementById('latest-version').textContent = 'v' + updateData.latest;
    document.getElementById('release-date').textContent = updateData.date ? new Date(updateData.date).toLocaleDateString() : 'Recent';
    document.getElementById('release-notes').textContent = updateData.notes || 'Bug fixes and improvements';
    document.getElementById('update-modal').classList.remove('hidden');
}

function hideUpdateModal() {
    document.getElementById('update-modal').classList.add('hidden');
}

function dismissUpdate() {
    document.getElementById('update-banner').classList.add('hidden');
}

function applyUpdate() {
    const btn = document.getElementById('update-btn');
    const progress = document.getElementById('update-progress');
    const bar = document.getElementById('update-bar');
    const status = document.getElementById('update-status');
    
    btn.disabled = true;
    btn.innerHTML = '⏳ Updating...';
    document.getElementById('cancel-btn').disabled = true;
    progress.classList.remove('hidden');
    
    let pct = 0;
    const interval = setInterval(() => {
        pct += Math.random() * 15;
        if (pct > 90) pct = 90;
        bar.style.width = pct + '%';
        if (pct < 25) status.textContent = 'Creating backup...';
        else if (pct < 50) status.textContent = 'Downloading files...';
        else if (pct < 75) status.textContent = 'Applying update...';
        else status.textContent = 'Finalizing...';
    }, 500);
    
    fetch('/admin/update/apply', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            clearInterval(interval);
            bar.style.width = '100%';
            progress.classList.add('hidden');
            
            if (data.success) {
                document.getElementById('update-success').classList.remove('hidden');
                btn.classList.add('hidden');
                document.getElementById('cancel-btn').classList.add('hidden');
                document.getElementById('refresh-btn').classList.remove('hidden');
                document.getElementById('update-banner').classList.add('hidden');
            } else {
                document.getElementById('update-error').classList.remove('hidden');
                document.getElementById('error-message').textContent = data.error;
                btn.disabled = false;
                btn.textContent = 'Retry';
            }
        })
        .catch(e => {
            clearInterval(interval);
            document.getElementById('update-error').classList.remove('hidden');
            document.getElementById('error-message').textContent = e.message;
        });
}
</script>


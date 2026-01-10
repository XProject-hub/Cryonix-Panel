<?php
// Get admin path
$adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cryonix Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Space Grotesk', 'system-ui'] },
                    colors: {
                        'cryo': { 400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7' },
                        'dark': { 900: '#0f1115', 950: '#0a0b0d' }
                    }
                }
            }
        }
    </script>
    <style>.glass { backdrop-filter: blur(12px); background: rgba(15, 17, 21, 0.9); }</style>
</head>
<body class="bg-dark-950 text-gray-100 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cryo-500 to-cryo-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold">Cryonix Panel</h1>
            <p class="text-gray-500 text-sm">IPTV Management</p>
        </div>
        
        <div class="glass rounded-2xl p-8 border border-gray-800/50">
            <h2 class="text-xl font-semibold mb-6">Admin Login</h2>
            
            <?php if (!empty($_SESSION['error'])): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>
            
            <form action="<?= $adminPath ?>/login" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Username</label>
                    <input type="text" name="username" required autofocus class="w-full px-4 py-3 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cryo-500/50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 rounded-lg bg-dark-900 border border-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-cryo-500/50">
                </div>
                <button type="submit" class="w-full py-3 rounded-lg bg-cryo-500 text-white font-semibold hover:bg-cryo-600 transition-all">Sign In</button>
            </form>
        </div>
        
        <p class="mt-6 text-center text-xs text-gray-600">&copy; 2026 XProject-Hub</p>
    </div>
</body>
</html>


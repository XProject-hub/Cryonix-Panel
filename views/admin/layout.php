<?php
/**
 * Cryonix Panel - Admin Layout
 * Xtream UI Style Top Navigation
 * Copyright 2026 XProject-Hub
 */
$adminPath = defined('ADMIN_PATH') ? ADMIN_PATH : '/admin';
$currentRoute = $_SERVER['ADMIN_ROUTE'] ?? '/dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> - Cryonix Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'system-ui'] },
                    colors: {
                        'cryo': { 400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7' },
                        'dark': { 800: '#1e2028', 900: '#0f1115', 950: '#0a0b0d' }
                    }
                }
            }
        }
    </script>
    <style>
        .glass { backdrop-filter: blur(12px); background: rgba(15, 17, 21, 0.95); }
        .nav-item { position: relative; }
        .nav-item:hover .dropdown { display: block; }
        .dropdown { display: none; position: absolute; top: 100%; left: 0; min-width: 200px; z-index: 50; }
        .nav-link.active { background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%); }
        .header-gradient { background: linear-gradient(135deg, #1a5276 0%, #2e86ab 50%, #48a9a6 100%); }
        body { background: linear-gradient(180deg, #0f1419 0%, #1a1d24 100%); min-height: 100vh; }
    </style>
</head>
<body class="text-gray-100 font-sans">
    <!-- Top Header Bar -->
    <header class="header-gradient shadow-xl">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-14">
                <!-- Logo -->
                <a href="<?= $adminPath ?>/dashboard" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center backdrop-blur">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-white tracking-tight">XTV</span>
                </a>
                
                <!-- Navigation -->
                <nav class="flex items-center gap-1">
                    <!-- Dashboard -->
                    <a href="<?= $adminPath ?>/dashboard" class="nav-link px-4 py-2 rounded-lg text-white/90 hover:bg-white/10 font-medium transition flex items-center gap-2 <?= $currentRoute === '/dashboard' ? 'active' : '' ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        DASHBOARD
                    </a>
                    
                    <!-- Servers Dropdown -->
                    <div class="nav-item">
                        <button class="nav-link px-4 py-2 rounded-lg text-white/90 hover:bg-white/10 font-medium transition flex items-center gap-2 <?= str_starts_with($currentRoute, '/servers') ? 'active' : '' ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                            SERVERS
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="dropdown glass rounded-lg shadow-2xl border border-gray-800/50 py-2 mt-1">
                            <a href="<?= $adminPath ?>/servers" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">All Servers</a>
                            <a href="<?= $adminPath ?>/servers/add" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Add Load Balancer</a>
                        </div>
                    </div>
                    
                    <!-- Users Dropdown -->
                    <div class="nav-item">
                        <button class="nav-link px-4 py-2 rounded-lg text-white/90 hover:bg-white/10 font-medium transition flex items-center gap-2 <?= str_starts_with($currentRoute, '/users') ? 'active' : '' ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                            USERS
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="dropdown glass rounded-lg shadow-2xl border border-gray-800/50 py-2 mt-1">
                            <a href="<?= $adminPath ?>/users" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">All Users</a>
                            <a href="<?= $adminPath ?>/users/add" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Add User</a>
                            <div class="border-t border-gray-700/50 my-1"></div>
                            <a href="<?= $adminPath ?>/users?filter=online" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Online Users</a>
                            <a href="<?= $adminPath ?>/users?filter=expired" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Expired Users</a>
                        </div>
                    </div>
                    
                    <!-- Content Dropdown -->
                    <div class="nav-item">
                        <button class="nav-link px-4 py-2 rounded-lg text-white/90 hover:bg-white/10 font-medium transition flex items-center gap-2 <?= str_starts_with($currentRoute, '/streams') || str_starts_with($currentRoute, '/movies') || str_starts_with($currentRoute, '/series') ? 'active' : '' ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            CONTENT
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="dropdown glass rounded-lg shadow-2xl border border-gray-800/50 py-2 mt-1">
                            <a href="<?= $adminPath ?>/streams" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Live Channels</a>
                            <a href="<?= $adminPath ?>/streams/add" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Add Channel</a>
                            <div class="border-t border-gray-700/50 my-1"></div>
                            <a href="<?= $adminPath ?>/movies" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Movies (VOD)</a>
                            <a href="<?= $adminPath ?>/movies/add" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Add Movie</a>
                            <div class="border-t border-gray-700/50 my-1"></div>
                            <a href="<?= $adminPath ?>/series" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Series</a>
                            <a href="<?= $adminPath ?>/series/add" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Add Series</a>
                        </div>
                    </div>
                    
                    <!-- Bouquets Dropdown -->
                    <div class="nav-item">
                        <button class="nav-link px-4 py-2 rounded-lg text-white/90 hover:bg-white/10 font-medium transition flex items-center gap-2 <?= str_starts_with($currentRoute, '/bouquets') || str_starts_with($currentRoute, '/categories') ? 'active' : '' ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            BOUQUETS
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="dropdown glass rounded-lg shadow-2xl border border-gray-800/50 py-2 mt-1">
                            <a href="<?= $adminPath ?>/bouquets" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">All Bouquets</a>
                            <a href="<?= $adminPath ?>/bouquets/add" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Add Bouquet</a>
                            <div class="border-t border-gray-700/50 my-1"></div>
                            <a href="<?= $adminPath ?>/categories" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Categories</a>
                            <a href="<?= $adminPath ?>/categories/add" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">Add Category</a>
                        </div>
                    </div>
                    
                    <!-- Settings -->
                    <a href="<?= $adminPath ?>/settings" class="nav-link px-4 py-2 rounded-lg text-white/90 hover:bg-white/10 font-medium transition flex items-center gap-2 <?= str_starts_with($currentRoute, '/settings') ? 'active' : '' ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                        SETTINGS
                    </a>
                </nav>
                
                <!-- User Menu -->
                <div class="nav-item">
                    <button class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition">
                        <div class="w-7 h-7 rounded-full bg-cryo-500 flex items-center justify-center text-white text-sm font-bold">
                            <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                        </div>
                        <span class="text-white font-medium"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                        <svg class="w-3 h-3 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dropdown glass rounded-lg shadow-2xl border border-gray-800/50 py-2 mt-1 right-0 left-auto">
                        <a href="<?= $adminPath ?>/license" class="block px-4 py-2 hover:bg-cryo-500/20 text-gray-300 hover:text-white">License</a>
                        <div class="border-t border-gray-700/50 my-1"></div>
                        <a href="<?= $adminPath ?>/logout" class="block px-4 py-2 hover:bg-red-500/20 text-red-400 hover:text-red-300">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Alert Banner (if any) -->
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="bg-cryo-500/20 border-b border-cryo-500/30 px-4 py-2">
        <div class="container mx-auto flex items-center gap-2 text-cryo-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
    </div>
    <?php unset($_SESSION['flash_message']); endif; ?>
    
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6">
        <?= $content ?? '' ?>
    </main>
    
    <!-- Footer -->
    <footer class="border-t border-gray-800/30 py-4 mt-8">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            &copy; 2026 Cryonix Panel v<?= file_exists(CRYONIX_ROOT . '/VERSION') ? trim(file_get_contents(CRYONIX_ROOT . '/VERSION')) : '1.0.0' ?> | Powered by XProject-Hub
        </div>
    </footer>
</body>
</html>

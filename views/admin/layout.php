<?php
/**
 * Cryonix Panel - Admin Layout
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
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Space Grotesk', 'system-ui'] },
                    colors: {
                        'cryo': { 400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7' },
                        'dark': { 800: '#1e2028', 900: '#0f1115', 950: '#0a0b0d' }
                    }
                }
            }
        }
    </script>
    <style>
        .glass { backdrop-filter: blur(12px); background: rgba(15, 17, 21, 0.9); }
        .sidebar-link.active { background: linear-gradient(90deg, rgba(14,165,233,0.2) 0%, transparent 100%); border-left-color: #0ea5e9; }
    </style>
</head>
<body class="bg-dark-950 text-gray-100 font-sans min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 min-h-screen bg-dark-900 border-r border-gray-800/50 fixed">
            <div class="p-4 border-b border-gray-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cryo-500 to-cryo-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-white">Cryonix</div>
                        <div class="text-xs text-gray-500">IPTV Panel</div>
                    </div>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="<?= $adminPath ?>/dashboard" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition border-l-2 border-transparent <?= $currentRoute === '/dashboard' ? 'active text-white' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="<?= $adminPath ?>/users" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition border-l-2 border-transparent <?= str_starts_with($currentRoute, '/users') ? 'active text-white' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    Users / Lines
                </a>
                <a href="<?= $adminPath ?>/streams" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition border-l-2 border-transparent <?= str_starts_with($currentRoute, '/streams') ? 'active text-white' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Live Channels
                </a>
                <a href="<?= $adminPath ?>/movies" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition border-l-2 border-transparent <?= str_starts_with($currentRoute, '/movies') ? 'active text-white' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
                    Movies (VOD)
                </a>
                <a href="<?= $adminPath ?>/series" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition border-l-2 border-transparent <?= str_starts_with($currentRoute, '/series') ? 'active text-white' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Series
                </a>
                <a href="<?= $adminPath ?>/categories" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition border-l-2 border-transparent <?= str_starts_with($currentRoute, '/categories') ? 'active text-white' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Categories
                </a>
                <a href="<?= $adminPath ?>/bouquets" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition border-l-2 border-transparent <?= str_starts_with($currentRoute, '/bouquets') ? 'active text-white' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Bouquets
                </a>
                
                <div class="pt-4 mt-4 border-t border-gray-800/50">
                    <a href="<?= $adminPath ?>/settings" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition border-l-2 border-transparent <?= str_starts_with($currentRoute, '/settings') ? 'active text-white' : '' ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                        Settings
                    </a>
                    <a href="<?= $adminPath ?>/license" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition border-l-2 border-transparent <?= str_starts_with($currentRoute, '/license') ? 'active text-white' : '' ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        License
                    </a>
                </div>
            </nav>
            
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-cryo-500/20 flex items-center justify-center text-cryo-400 text-sm font-bold">
                            <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                        </div>
                        <span class="text-sm text-gray-400"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                    </div>
                    <a href="<?= $adminPath ?>/logout" class="text-gray-500 hover:text-red-400 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </a>
                </div>
            </div>
        </aside>
        
        <!-- Main content -->
        <main class="flex-1 ml-64 p-8">
            <?= $content ?? '' ?>
        </main>
    </div>
</body>
</html>


<?php
/**
 * Cryonix Panel - Admin Layout
 * Top Navigation Style
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        .glass { backdrop-filter: blur(12px); background: rgba(15, 17, 21, 0.95); }
        .nav-item { position: relative; }
        .nav-item:hover .dropdown { display: block; }
        .dropdown { 
            display: none; 
            position: absolute; 
            top: 100%; 
            left: 0; 
            min-width: 180px; 
            z-index: 50;
            padding-top: 4px;
        }
        .dropdown::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 0;
            right: 0;
            height: 14px;
        }
        .nav-link.active { background: rgba(14, 165, 233, 0.2); color: #38bdf8; }
        /* Nested submenu */
        .submenu-item .submenu { display: none; position: absolute; left: 100%; top: 0; margin-left: 2px; }
        .submenu-item:hover .submenu { display: block; }
        .submenu::before { content: ''; position: absolute; top: 0; left: -10px; width: 10px; height: 100%; }
    </style>
</head>
<body class="bg-dark-950 text-gray-100 font-sans min-h-screen">
    <!-- Top Navigation -->
    <header class="bg-dark-900 border-b border-gray-800/50 sticky top-0 z-40">
        <div class="flex items-center justify-between px-4 h-12">
            <!-- Logo -->
            <a href="<?= $adminPath ?>/dashboard" class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cryo-500 to-cryo-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-white">Cryonix</span>
            </a>
            
            <!-- Navigation -->
            <nav class="flex items-center gap-0.5">
                <!-- Dashboard -->
                <div class="nav-item">
                    <button class="nav-link px-3 py-1.5 rounded text-xs font-medium transition flex items-center gap-1 <?= $currentRoute === '/dashboard' || str_starts_with($currentRoute, '/connections') || str_starts_with($currentRoute, '/activity') || str_starts_with($currentRoute, '/process') ? 'active' : 'text-gray-400 hover:text-white hover:bg-dark-800' ?>">
                        DASHBOARD <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dropdown">
                        <div class="glass rounded-lg shadow-xl border border-gray-800/50 py-1">
                            <a href="<?= $adminPath ?>/dashboard" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Dashboard</a>
                            <a href="<?= $adminPath ?>/connections" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Live Connections</a>
                            <a href="<?= $adminPath ?>/activity" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Activity Logs</a>
                            <a href="<?= $adminPath ?>/process" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Process Monitor</a>
                        </div>
                    </div>
                </div>
                
                <!-- Servers -->
                <div class="nav-item">
                    <button class="nav-link px-3 py-1.5 rounded text-xs font-medium transition flex items-center gap-1 <?= str_starts_with($currentRoute, '/servers') ? 'active' : 'text-gray-400 hover:text-white hover:bg-dark-800' ?>">
                        SERVERS <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dropdown">
                        <div class="glass rounded-lg shadow-xl border border-gray-800/50 py-1">
                            <a href="<?= $adminPath ?>/servers/add" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Add Load Balancer</a>
                            <a href="<?= $adminPath ?>/servers/install" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Install Load Balancer</a>
                            <a href="<?= $adminPath ?>/servers" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Manage Servers</a>
                        </div>
                    </div>
                </div>
                
                <!-- Management -->
                <div class="nav-item">
                    <button class="nav-link px-3 py-1.5 rounded text-xs font-medium transition flex items-center gap-1 text-gray-400 hover:text-white hover:bg-dark-800">
                        MANAGEMENT <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dropdown">
                        <div class="glass rounded-lg shadow-xl border border-gray-800/50 py-1 min-w-[160px]">
                            <!-- Service Setup -->
                            <div class="submenu-item relative">
                                <a href="#" class="flex items-center justify-between px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">
                                    <span>+ Service Setup</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <div class="submenu glass rounded-lg shadow-xl border border-gray-800/50 py-1 min-w-[180px]">
                                    <a href="<?= $adminPath ?>/packages" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Packages</a>
                                    <a href="<?= $adminPath ?>/categories" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Categories</a>
                                    <a href="<?= $adminPath ?>/groups" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Groups</a>
                                    <a href="<?= $adminPath ?>/epg" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">EPG's</a>
                                    <a href="<?= $adminPath ?>/channel-order" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Channel Order</a>
                                    <a href="<?= $adminPath ?>/folder-watch" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Folder Watch</a>
                                    <a href="<?= $adminPath ?>/subresellers" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Subresellers</a>
                                    <a href="<?= $adminPath ?>/transcode" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Transcode Profiles</a>
                                    <a href="<?= $adminPath ?>/provider-check" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Provider Con Check</a>
                                </div>
                            </div>
                            <!-- Security -->
                            <div class="submenu-item relative">
                                <a href="#" class="flex items-center justify-between px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">
                                    <span>+ Security</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <div class="submenu glass rounded-lg shadow-xl border border-gray-800/50 py-1 min-w-[180px]">
                                    <a href="<?= $adminPath ?>/security/flood" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Logins Flood</a>
                                    <a href="<?= $adminPath ?>/security/center" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Security Center</a>
                                    <a href="<?= $adminPath ?>/security/blocked-ips" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Blocked IP's</a>
                                    <a href="<?= $adminPath ?>/security/blocked-isps" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Blocked ISP's</a>
                                    <a href="<?= $adminPath ?>/security/rtmp-ips" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">RTMP IP's</a>
                                    <a href="<?= $adminPath ?>/security/blocked-ua" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Blocked User Agents</a>
                                </div>
                            </div>
                            <!-- Tools -->
                            <div class="submenu-item relative">
                                <a href="#" class="flex items-center justify-between px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">
                                    <span>+ Tools</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <div class="submenu glass rounded-lg shadow-xl border border-gray-800/50 py-1 min-w-[180px]">
                                    <a href="<?= $adminPath ?>/tools/mass-delete" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Mass Delete</a>
                                    <a href="<?= $adminPath ?>/tools/fingerprint" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Fingerprint</a>
                                    <a href="<?= $adminPath ?>/tools/stream" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Stream Tools</a>
                                    <a href="<?= $adminPath ?>/tools/ip-change" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">IP Change</a>
                                    <a href="<?= $adminPath ?>/tools/dns-covers" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">DNS Covers Change</a>
                                    <a href="<?= $adminPath ?>/tools/quick" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Quick Tools</a>
                                </div>
                            </div>
                            <!-- Logs -->
                            <div class="submenu-item relative">
                                <a href="#" class="flex items-center justify-between px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">
                                    <span>+ Logs</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <div class="submenu glass rounded-lg shadow-xl border border-gray-800/50 py-1 min-w-[180px]">
                                    <a href="<?= $adminPath ?>/logs/reseller" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Reseller Logs</a>
                                    <a href="<?= $adminPath ?>/logs/credit" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Credit Logs</a>
                                    <a href="<?= $adminPath ?>/logs/login" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Login Logs</a>
                                    <a href="<?= $adminPath ?>/logs/client" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Client Logs</a>
                                    <a href="<?= $adminPath ?>/logs/stream" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Stream Logs</a>
                                    <a href="<?= $adminPath ?>/logs/ip-usage" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Line IP Usage</a>
                                    <a href="<?= $adminPath ?>/logs/panel" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Panel Logs</a>
                                    <a href="<?= $adminPath ?>/logs/mag-events" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">MAG Event Logs</a>
                                    <a href="<?= $adminPath ?>/logs/mag-claims" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Mag Claims</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Resellers -->
                <div class="nav-item">
                    <button class="nav-link px-3 py-1.5 rounded text-xs font-medium transition flex items-center gap-1 text-gray-400 hover:text-white hover:bg-dark-800">
                        RESELLERS <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dropdown">
                        <div class="glass rounded-lg shadow-xl border border-gray-800/50 py-1">
                            <a href="<?= $adminPath ?>/resellers" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">All Resellers</a>
                            <a href="<?= $adminPath ?>/resellers/add" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Add Reseller</a>
                        </div>
                    </div>
                </div>
                
                <!-- Users -->
                <div class="nav-item">
                    <button class="nav-link px-3 py-1.5 rounded text-xs font-medium transition flex items-center gap-1 <?= str_starts_with($currentRoute, '/users') || str_starts_with($currentRoute, '/mag') || str_starts_with($currentRoute, '/enigma') ? 'active' : 'text-gray-400 hover:text-white hover:bg-dark-800' ?>">
                        USERS <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dropdown">
                        <div class="glass rounded-lg shadow-xl border border-gray-800/50 py-1 min-w-[160px]">
                            <!-- User Lines -->
                            <div class="submenu-item relative">
                                <a href="#" class="flex items-center justify-between px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">
                                    <span>+ User Lines</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <div class="submenu glass rounded-lg shadow-xl border border-gray-800/50 py-1 min-w-[160px]">
                                    <a href="<?= $adminPath ?>/users/add" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Add User</a>
                                    <a href="<?= $adminPath ?>/users" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Manage Users</a>
                                </div>
                            </div>
                            <!-- MAG Devices -->
                            <div class="submenu-item relative">
                                <a href="#" class="flex items-center justify-between px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">
                                    <span>+ MAG Devices</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <div class="submenu glass rounded-lg shadow-xl border border-gray-800/50 py-1 min-w-[180px]">
                                    <a href="<?= $adminPath ?>/mag/add" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Add MAG Device</a>
                                    <a href="<?= $adminPath ?>/mag" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Manage MAG Devices</a>
                                    <a href="<?= $adminPath ?>/mag/link" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Link MAG User</a>
                                </div>
                            </div>
                            <!-- Enigma Devices -->
                            <div class="submenu-item relative">
                                <a href="#" class="flex items-center justify-between px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">
                                    <span>+ Enigma Devices</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <div class="submenu glass rounded-lg shadow-xl border border-gray-800/50 py-1 min-w-[180px]">
                                    <a href="<?= $adminPath ?>/enigma/add" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Add Enigma Device</a>
                                    <a href="<?= $adminPath ?>/enigma" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Manage Enigma Devices</a>
                                    <a href="<?= $adminPath ?>/enigma/link" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Link Enigma User</a>
                                </div>
                            </div>
                            <a href="<?= $adminPath ?>/users/mass-edit" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Mass Edit Users</a>
                        </div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="nav-item">
                    <button class="nav-link px-3 py-1.5 rounded text-xs font-medium transition flex items-center gap-1 <?= str_starts_with($currentRoute, '/streams') || str_starts_with($currentRoute, '/movies') || str_starts_with($currentRoute, '/series') ? 'active' : 'text-gray-400 hover:text-white hover:bg-dark-800' ?>">
                        CONTENT <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dropdown">
                        <div class="glass rounded-lg shadow-xl border border-gray-800/50 py-1">
                            <a href="<?= $adminPath ?>/streams" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Live Channels</a>
                            <a href="<?= $adminPath ?>/streams/add" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Add Channel</a>
                            <div class="border-t border-gray-800/50 my-1"></div>
                            <a href="<?= $adminPath ?>/movies" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Movies (VOD)</a>
                            <a href="<?= $adminPath ?>/series" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Series</a>
                        </div>
                    </div>
                </div>
                
                <!-- Bouquets -->
                <div class="nav-item">
                    <button class="nav-link px-3 py-1.5 rounded text-xs font-medium transition flex items-center gap-1 <?= str_starts_with($currentRoute, '/bouquets') ? 'active' : 'text-gray-400 hover:text-white hover:bg-dark-800' ?>">
                        BOUQUETS <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dropdown">
                        <div class="glass rounded-lg shadow-xl border border-gray-800/50 py-1">
                            <a href="<?= $adminPath ?>/bouquets" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">All Bouquets</a>
                            <a href="<?= $adminPath ?>/bouquets/add" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Add Bouquet</a>
                        </div>
                    </div>
                </div>
                
                <!-- Apps IPTV -->
                <div class="nav-item">
                    <button class="nav-link px-3 py-1.5 rounded text-xs font-medium transition flex items-center gap-1 text-gray-400 hover:text-white hover:bg-dark-800">
                        APPS IPTV <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dropdown">
                        <div class="glass rounded-lg shadow-xl border border-gray-800/50 py-1">
                            <a href="<?= $adminPath ?>/apps" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">Manage Apps</a>
                            <a href="<?= $adminPath ?>/epg" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">EPG Manager</a>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Right Side -->
            <div class="flex items-center gap-3">
                <a href="<?= $adminPath ?>/settings" class="text-gray-400 hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                
                <!-- User Menu -->
                <div class="nav-item">
                    <button class="flex items-center gap-2 px-2 py-1 rounded bg-dark-800 hover:bg-dark-700 transition">
                        <div class="w-6 h-6 rounded-full bg-cryo-500/20 flex items-center justify-center text-cryo-400 text-xs font-bold">
                            <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                        </div>
                        <span class="text-xs text-gray-300"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                        <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dropdown" style="left:auto;right:0;">
                        <div class="glass rounded-lg shadow-xl border border-gray-800/50 py-1">
                            <a href="<?= $adminPath ?>/license" class="block px-3 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-cryo-500/10">License</a>
                            <div class="border-t border-gray-800/50 my-1"></div>
                            <a href="<?= $adminPath ?>/logout" class="block px-3 py-1.5 text-xs text-red-400 hover:text-red-300 hover:bg-red-500/10">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="p-6 pb-16">
        <?= $content ?? '' ?>
    </main>
    
    <!-- Footer -->
    <footer class="fixed bottom-0 left-0 right-0 h-10 bg-dark-900 border-t border-gray-800/50 flex items-center justify-between px-6 text-xs">
        <span class="text-gray-500">IPTV Management by <span class="text-cryo-400">X Project</span></span>
        <span class="text-gray-600">Version <?= file_exists(CRYONIX_ROOT . '/VERSION') ? trim(file_get_contents(CRYONIX_ROOT . '/VERSION')) : '1.0.0' ?></span>
    </footer>
</body>
</html>

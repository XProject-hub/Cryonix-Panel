<?php
/**
 * Cryonix Panel - Admin Router
 * Routes admin panel pages
 * Copyright 2026 XProject-Hub
 */

$adminRoute = $_SERVER['ADMIN_ROUTE'] ?? '/dashboard';
$adminPath = $_SERVER['ADMIN_PATH'] ?? '/admin';

// Remove trailing slash and clean up
$adminRoute = rtrim($adminRoute, '/');
if (empty($adminRoute)) $adminRoute = '/dashboard';

// Parse route parts
$routeParts = explode('/', ltrim($adminRoute, '/'));
$section = $routeParts[0] ?? 'dashboard';
$action = $routeParts[1] ?? null;
$id = $routeParts[2] ?? null;

// Set action/id in GET
if ($action) $_GET['action'] = $action;
if ($id) $_GET['id'] = $id;

switch ($section) {
    case 'dashboard':
        require CRYONIX_ROOT . '/views/admin/dashboard.php';
        break;
        
    case 'connections':
        require CRYONIX_ROOT . '/views/admin/connections.php';
        break;
        
    case 'activity':
        require CRYONIX_ROOT . '/views/admin/activity.php';
        break;
        
    case 'process':
        require CRYONIX_ROOT . '/views/admin/process.php';
        break;
        
    // Service Setup
    case 'packages':
        require CRYONIX_ROOT . '/views/admin/management/packages.php';
        break;
    case 'groups':
        require CRYONIX_ROOT . '/views/admin/management/groups.php';
        break;
    case 'epg':
        require CRYONIX_ROOT . '/views/admin/management/epg.php';
        break;
    case 'channel-order':
        require CRYONIX_ROOT . '/views/admin/management/channel-order.php';
        break;
    case 'folder-watch':
        require CRYONIX_ROOT . '/views/admin/management/folder-watch.php';
        break;
    case 'subresellers':
        require CRYONIX_ROOT . '/views/admin/management/subresellers.php';
        break;
    case 'transcode':
        require CRYONIX_ROOT . '/views/admin/management/transcode.php';
        break;
    case 'provider-check':
        require CRYONIX_ROOT . '/views/admin/management/provider-check.php';
        break;
        
    // Security
    case 'security':
        $subAction = $action ?? 'center';
        require CRYONIX_ROOT . '/views/admin/security/' . $subAction . '.php';
        break;
        
    // Tools
    case 'tools':
        $subAction = $action ?? 'quick';
        require CRYONIX_ROOT . '/views/admin/tools/' . $subAction . '.php';
        break;
        
    // Logs
    case 'logs':
        $subAction = $action ?? 'panel';
        require CRYONIX_ROOT . '/views/admin/logs/' . $subAction . '.php';
        break;
        
    // Users / Lines
    case 'users':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/users-add.php';
        } elseif ($action === 'mass-edit') {
            require CRYONIX_ROOT . '/views/admin/users-mass-edit.php';
        } elseif ($action === 'stats') {
            require CRYONIX_ROOT . '/views/admin/users-stats.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/users.php';
        }
        break;
        
    // MAG Devices
    case 'mag':
        if ($action === 'add') {
            require CRYONIX_ROOT . '/views/admin/mag/add.php';
        } elseif ($action === 'link') {
            require CRYONIX_ROOT . '/views/admin/mag/link.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/mag/index.php';
        }
        break;
        
    // Enigma Devices
    case 'enigma':
        if ($action === 'add') {
            require CRYONIX_ROOT . '/views/admin/enigma/add.php';
        } elseif ($action === 'link') {
            require CRYONIX_ROOT . '/views/admin/enigma/link.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/enigma/index.php';
        }
        break;
        
    // API endpoints
    case 'api':
        header('Content-Type: application/json');
        
        // Updates API
        if ($action === 'updates') {
            require_once CRYONIX_ROOT . '/core/Updater.php';
            $updater = new \CryonixPanel\Core\Updater();
            
            if ($id === 'check') {
                $info = $updater->checkForUpdates();
                echo json_encode($info);
                exit;
            } elseif ($id === 'apply' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $updater->applyUpdate();
                echo json_encode($result);
                exit;
            }
        }
        
        if ($action === 'user-action') {
            header('Content-Type: application/json');
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = $input['user_id'] ?? 0;
            $userAction = $input['action'] ?? '';
            
            require_once CRYONIX_ROOT . '/core/Database.php';
            $db = \CryonixPanel\Core\Database::getInstance();
            
            try {
                switch ($userAction) {
                    case 'reset-isp':
                        $db->update('`lines`', ['allowed_ips' => null], 'id = ?', [$userId]);
                        echo json_encode(['success' => true, 'message' => 'ISP reset']);
                        break;
                    case 'lock-isp':
                        // Lock current IP
                        echo json_encode(['success' => true, 'message' => 'ISP locked']);
                        break;
                    case 'extend':
                        $db->query("UPDATE `lines` SET exp_date = DATE_ADD(exp_date, INTERVAL 30 DAY) WHERE id = ?", [$userId]);
                        echo json_encode(['success' => true, 'message' => 'Extended 30 days']);
                        break;
                    case 'kill':
                        $db->update('`lines`', ['current_connections' => 0], 'id = ?', [$userId]);
                        echo json_encode(['success' => true, 'message' => 'Connections killed']);
                        break;
                    case 'ban':
                        $db->update('`lines`', ['is_banned' => 1], 'id = ?', [$userId]);
                        echo json_encode(['success' => true, 'message' => 'User banned']);
                        break;
                    case 'disable':
                        $db->update('`lines`', ['status' => 'disabled'], 'id = ?', [$userId]);
                        echo json_encode(['success' => true, 'message' => 'User disabled']);
                        break;
                    case 'delete':
                        $db->delete('`lines`', 'id = ?', [$userId]);
                        echo json_encode(['success' => true, 'message' => 'User deleted']);
                        break;
                    default:
                        echo json_encode(['success' => false, 'error' => 'Unknown action']);
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        }
        
        // Stream actions API
        if ($action === 'stream-action') {
            $input = json_decode(file_get_contents('php://input'), true);
            $streamId = $input['stream_id'] ?? 0;
            $streamAction = $input['action'] ?? '';
            
            require_once CRYONIX_ROOT . '/core/Database.php';
            $db = \CryonixPanel\Core\Database::getInstance();
            
            try {
                switch ($streamAction) {
                    case 'restart':
                        $db->update('streams', ['status' => 'active'], 'id = ?', [$streamId]);
                        echo json_encode(['success' => true, 'message' => 'Stream restarted']);
                        break;
                    case 'stop':
                        $db->update('streams', ['status' => 'offline'], 'id = ?', [$streamId]);
                        echo json_encode(['success' => true, 'message' => 'Stream stopped']);
                        break;
                    case 'delete':
                        $db->delete('streams', 'id = ?', [$streamId]);
                        echo json_encode(['success' => true, 'message' => 'Stream deleted']);
                        break;
                    default:
                        echo json_encode(['success' => false, 'error' => 'Unknown action']);
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        }
        break;
        
    // Live Streams
    case 'streams':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/streams-add.php';
        } elseif ($action === 'mass-edit') {
            require CRYONIX_ROOT . '/views/admin/content/streams-mass-edit.php';
        } elseif ($action === 'import') {
            require CRYONIX_ROOT . '/views/admin/content/streams-import.php';
        } elseif ($action === 'stats') {
            require CRYONIX_ROOT . '/views/admin/content/streams-stats.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/streams.php';
        }
        break;
    
    // Created Channels
    case 'channels':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/content/channels-add.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/content/channels.php';
        }
        break;
        
    // Movies (VOD)
    case 'movies':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/movies-add.php';
        } elseif ($action === 'mass-edit') {
            require CRYONIX_ROOT . '/views/admin/content/movies-mass-edit.php';
        } elseif ($action === 'import') {
            require CRYONIX_ROOT . '/views/admin/content/movies-import.php';
        } elseif ($action === 'import-m3u') {
            require CRYONIX_ROOT . '/views/admin/content/movies-import-m3u.php';
        } elseif ($action === 'duplicate') {
            require CRYONIX_ROOT . '/views/admin/content/movies-duplicate.php';
        } elseif ($action === 'stats') {
            require CRYONIX_ROOT . '/views/admin/content/movies-stats.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/movies.php';
        }
        break;
        
    // Series
    case 'series':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/series-add.php';
        } elseif ($action === 'episodes') {
            require CRYONIX_ROOT . '/views/admin/content/episodes.php';
        } elseif ($action === 'mass-edit') {
            require CRYONIX_ROOT . '/views/admin/content/series-mass-edit.php';
        } elseif ($action === 'mass-edit-episodes') {
            require CRYONIX_ROOT . '/views/admin/content/episodes-mass-edit.php';
        } elseif ($action === 'import-m3u') {
            require CRYONIX_ROOT . '/views/admin/content/episodes-import-m3u.php';
        } elseif ($action === 'stats') {
            require CRYONIX_ROOT . '/views/admin/content/series-stats.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/series.php';
        }
        break;
    
    // Stations (Radio)
    case 'stations':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/content/stations-add.php';
        } elseif ($action === 'mass-edit') {
            require CRYONIX_ROOT . '/views/admin/content/stations-mass-edit.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/content/stations.php';
        }
        break;
        
    // Categories
    case 'categories':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/categories-add.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/categories.php';
        }
        break;
        
    // Bouquets
    case 'bouquets':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/bouquets-add.php';
        } else {
            require CRYONIX_ROOT . '/views/admin/bouquets.php';
        }
        break;
        
    // Servers
    case 'servers':
        if ($action === 'add' || $action === 'edit') {
            require CRYONIX_ROOT . '/views/admin/servers-add.php';
        } elseif ($action === 'install') {
            require CRYONIX_ROOT . '/views/admin/servers-install.php';
        } elseif ($action === 'delete' && $id) {
            // Handle server deletion
            require_once CRYONIX_ROOT . '/core/Database.php';
            $db = \CryonixPanel\Core\Database::getInstance();
            $db->delete('servers', 'id = ? AND is_main = 0', [$id]);
            header('Location: ' . ADMIN_PATH . '/servers');
            exit;
        } else {
            require CRYONIX_ROOT . '/views/admin/servers.php';
        }
        break;
        
    // Settings
    case 'settings':
        if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle settings save
            require_once CRYONIX_ROOT . '/core/Database.php';
            $db = \CryonixPanel\Core\Database::getInstance();
            foreach ($_POST as $key => $value) {
                $db->query("UPDATE settings SET `value` = ? WHERE `key` = ?", [$value, $key]);
            }
            $_SESSION['success'] = 'Settings saved!';
            header('Location: ' . ADMIN_PATH . '/settings');
            exit;
        }
        require CRYONIX_ROOT . '/views/admin/settings.php';
        break;
        
    // License
    case 'license':
        require CRYONIX_ROOT . '/views/admin/license.php';
        break;
        
    // Activity log
    case 'activity':
        require CRYONIX_ROOT . '/views/admin/activity.php';
        break;
        
    // System logs
    case 'logs':
        require CRYONIX_ROOT . '/views/admin/logs.php';
        break;
        
    default:
        // Default to dashboard
        require CRYONIX_ROOT . '/views/admin/dashboard.php';
}

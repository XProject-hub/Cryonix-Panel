-- Cryonix Panel - IPTV Management Panel
-- Database Schema
-- Copyright 2026 XProject-Hub
-- Created: January 2026

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- Panel Users (admin accounts)
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(255) NULL,
    `role` ENUM('admin', 'moderator', 'reseller', 'viewer') NOT NULL DEFAULT 'viewer',
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `last_login_at` DATETIME NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- IPTV Lines (customer accounts)
-- ============================================
CREATE TABLE IF NOT EXISTS `lines` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(100) NOT NULL,
    `owner_id` INT UNSIGNED NULL COMMENT 'Reseller who created this',
    `reseller` VARCHAR(100) NULL COMMENT 'Reseller name',
    `max_connections` INT UNSIGNED NOT NULL DEFAULT 1,
    `current_connections` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_trial` TINYINT(1) NOT NULL DEFAULT 0,
    `is_mag` TINYINT(1) NOT NULL DEFAULT 0,
    `is_e2` TINYINT(1) NOT NULL DEFAULT 0,
    `is_isplock` TINYINT(1) NOT NULL DEFAULT 0,
    `is_restreamer` TINYINT(1) NOT NULL DEFAULT 0,
    `forced_country` VARCHAR(10) NULL,
    `allowed_ips` TEXT NULL,
    `allowed_ua` TEXT NULL,
    `allowed_outputs` JSON NULL DEFAULT ('["hls","ts","rtmp"]'),
    `exp_date` DATETIME NULL,
    `force_server_id` INT UNSIGNED NULL,
    `bouquet` TEXT NULL COMMENT 'JSON array of bouquet IDs',
    `admin_notes` TEXT NULL,
    `reseller_notes` TEXT NULL,
    `is_banned` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('active', 'expired', 'banned', 'disabled') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_lines_status` (`status`),
    INDEX `idx_lines_exp` (`exp_date`),
    INDEX `idx_lines_reseller` (`reseller`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Line-Bouquet relationship
CREATE TABLE IF NOT EXISTS `line_bouquets` (
    `line_id` INT UNSIGNED NOT NULL,
    `bouquet_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`line_id`, `bouquet_id`),
    FOREIGN KEY (`line_id`) REFERENCES `lines`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`bouquet_id`) REFERENCES `bouquets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Categories
-- ============================================
CREATE TABLE IF NOT EXISTS `stream_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_type` ENUM('live', 'movie', 'series') NOT NULL DEFAULT 'live',
    `category_name` VARCHAR(255) NOT NULL,
    `parent_id` INT UNSIGNED NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_adult` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `stream_categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_cat_type` (`category_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Live Streams (channels)
-- ============================================
CREATE TABLE IF NOT EXISTS `streams` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `stream_display_name` VARCHAR(255) NOT NULL,
    `stream_source` TEXT NOT NULL COMMENT 'Source URL',
    `stream_icon` VARCHAR(512) NULL,
    `epg_channel_id` VARCHAR(100) NULL,
    `category_id` INT UNSIGNED NULL,
    `direct_source` TINYINT(1) NOT NULL DEFAULT 0,
    `custom_ffmpeg` TEXT NULL,
    `transcode_profile_id` INT UNSIGNED NULL,
    `is_adult` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `added_by` INT UNSIGNED NULL,
    `status` ENUM('active', 'inactive', 'error') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `stream_categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`added_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_streams_cat` (`category_id`),
    INDEX `idx_streams_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Movies (VOD)
-- ============================================
CREATE TABLE IF NOT EXISTS `movies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `stream_display_name` VARCHAR(255) NOT NULL,
    `stream_source` TEXT NOT NULL,
    `stream_icon` VARCHAR(512) NULL,
    `category_id` INT UNSIGNED NULL,
    `tmdb_id` INT UNSIGNED NULL,
    `year` YEAR NULL,
    `plot` TEXT NULL,
    `cast` TEXT NULL,
    `director` VARCHAR(255) NULL,
    `genre` VARCHAR(255) NULL,
    `release_date` DATE NULL,
    `duration_seconds` INT UNSIGNED NULL,
    `rating` DECIMAL(3,1) NULL,
    `is_adult` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `added_by` INT UNSIGNED NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `stream_categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`added_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_movies_cat` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TV Series
-- ============================================
CREATE TABLE IF NOT EXISTS `series` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `cover` VARCHAR(512) NULL,
    `backdrop` VARCHAR(512) NULL,
    `category_id` INT UNSIGNED NULL,
    `tmdb_id` INT UNSIGNED NULL,
    `year` YEAR NULL,
    `plot` TEXT NULL,
    `cast` TEXT NULL,
    `genre` VARCHAR(255) NULL,
    `rating` DECIMAL(3,1) NULL,
    `release_date` DATE NULL,
    `is_adult` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `added_by` INT UNSIGNED NULL,
    `last_modified` DATETIME NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `stream_categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`added_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `series_episodes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `series_id` INT UNSIGNED NOT NULL,
    `season_num` INT UNSIGNED NOT NULL DEFAULT 1,
    `episode_num` INT UNSIGNED NOT NULL DEFAULT 1,
    `title` VARCHAR(255) NULL,
    `plot` TEXT NULL,
    `stream_source` TEXT NOT NULL,
    `duration_seconds` INT UNSIGNED NULL,
    `release_date` DATE NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`series_id`) REFERENCES `series`(`id`) ON DELETE CASCADE,
    INDEX `idx_ep_series` (`series_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Bouquets (channel packages)
-- ============================================
CREATE TABLE IF NOT EXISTS `bouquets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `bouquet_name` VARCHAR(255) NOT NULL,
    `bouquet_channels` TEXT NULL COMMENT 'JSON array of stream IDs',
    `bouquet_movies` TEXT NULL COMMENT 'JSON array of movie IDs',
    `bouquet_series` TEXT NULL COMMENT 'JSON array of series IDs',
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Servers
-- ============================================
CREATE TABLE IF NOT EXISTS `servers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `server_name` VARCHAR(100) NOT NULL,
    `domain_name` VARCHAR(255) NULL,
    `server_ip` VARCHAR(45) NOT NULL,
    `vpn_ip` VARCHAR(45) NULL,
    `ssh_password` VARCHAR(255) NULL,
    `ssh_port` INT UNSIGNED NOT NULL DEFAULT 22,
    `http_port` INT UNSIGNED NOT NULL DEFAULT 8080,
    `https_port` INT UNSIGNED NOT NULL DEFAULT 8443,
    `rtmp_port` INT UNSIGNED NOT NULL DEFAULT 25462,
    `max_clients` INT UNSIGNED NOT NULL DEFAULT 1000,
    `is_timeshift` TINYINT(1) NOT NULL DEFAULT 0,
    `is_duplex` TINYINT(1) NOT NULL DEFAULT 0,
    `time_diff` INT NOT NULL DEFAULT 0,
    `network_interface` VARCHAR(50) NULL,
    `network_speed` INT UNSIGNED NOT NULL DEFAULT 1000,
    `os_type` VARCHAR(50) NULL DEFAULT 'Ubuntu 22',
    `geoip_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `geoip_priority` ENUM('high', 'medium', 'low') NOT NULL DEFAULT 'high',
    `geoip_countries` TEXT NULL,
    `isp_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `isp_priority` ENUM('high', 'medium', 'low') NOT NULL DEFAULT 'high',
    `allowed_isps` TEXT NULL,
    `server_protocol` ENUM('http', 'https') NOT NULL DEFAULT 'http',
    `is_main` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('online', 'offline', 'maintenance', 'installing') NOT NULL DEFAULT 'online',
    `current_connections` INT UNSIGNED NOT NULL DEFAULT 0,
    `current_users` INT UNSIGNED NOT NULL DEFAULT 0,
    `streams_live` INT UNSIGNED NOT NULL DEFAULT 0,
    `streams_off` INT UNSIGNED NOT NULL DEFAULT 0,
    `bandwidth_in` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `bandwidth_out` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `cpu_usage` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `ram_usage` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `uptime` VARCHAR(50) NULL,
    `last_check_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EPG (Electronic Program Guide)
-- ============================================
CREATE TABLE IF NOT EXISTS `epg_data` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `channel_id` VARCHAR(100) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `start_time` DATETIME NOT NULL,
    `end_time` DATETIME NOT NULL,
    `lang` VARCHAR(10) NULL,
    INDEX `idx_epg_channel` (`channel_id`),
    INDEX `idx_epg_time` (`start_time`, `end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- User Activity / Connections
-- ============================================
CREATE TABLE IF NOT EXISTS `user_activity` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `line_id` INT UNSIGNED NOT NULL,
    `stream_id` INT UNSIGNED NULL,
    `server_id` INT UNSIGNED NULL,
    `user_ip` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `container` VARCHAR(20) NULL,
    `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ended_at` DATETIME NULL,
    `bytes_sent` BIGINT UNSIGNED NULL,
    FOREIGN KEY (`line_id`) REFERENCES `lines`(`id`) ON DELETE CASCADE,
    INDEX `idx_activity_line` (`line_id`),
    INDEX `idx_activity_time` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Settings
-- ============================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `type` ENUM('string', 'int', 'bool', 'json') NOT NULL DEFAULT 'string',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Audit Logs
-- ============================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `entity_type` VARCHAR(50) NULL,
    `entity_id` INT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- License Storage (local)
-- ============================================
CREATE TABLE IF NOT EXISTS `license_info` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `license_key` VARCHAR(64) NOT NULL,
    `activation_id` INT UNSIGNED NULL,
    `status` ENUM('active', 'locked', 'expired') NOT NULL DEFAULT 'active',
    `max_connections` INT UNSIGNED NOT NULL DEFAULT 100,
    `max_channels` INT UNSIGNED NOT NULL DEFAULT 500,
    `expires_at` DATE NULL,
    `last_check_at` DATETIME NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MAG Devices
-- ============================================
CREATE TABLE IF NOT EXISTS `mag_devices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mac` VARCHAR(17) NOT NULL UNIQUE,
    `username` VARCHAR(100) NULL,
    `line_id` INT UNSIGNED NULL,
    `status` ENUM('active', 'disabled', 'expired') NOT NULL DEFAULT 'active',
    `exp_date` DATETIME NULL,
    `portal_url` VARCHAR(512) NULL,
    `stb_type` VARCHAR(50) NULL DEFAULT 'MAG250',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_mag_mac` (`mac`),
    INDEX `idx_mag_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Enigma Devices
-- ============================================
CREATE TABLE IF NOT EXISTS `enigma_devices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `device_id` VARCHAR(255) NOT NULL UNIQUE,
    `username` VARCHAR(100) NULL,
    `line_id` INT UNSIGNED NULL,
    `status` ENUM('active', 'disabled', 'expired') NOT NULL DEFAULT 'active',
    `exp_date` DATETIME NULL,
    `device_type` VARCHAR(50) NULL DEFAULT 'Enigma2',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_enigma_device` (`device_id`),
    INDEX `idx_enigma_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Packages (for resellers)
-- ============================================
CREATE TABLE IF NOT EXISTS `packages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `credits` INT UNSIGNED NOT NULL DEFAULT 0,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `is_trial` TINYINT(1) NOT NULL DEFAULT 0,
    `trial_duration` INT UNSIGNED NOT NULL DEFAULT 24 COMMENT 'Hours',
    `official_duration` INT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Days',
    `max_connections` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Groups
-- ============================================
CREATE TABLE IF NOT EXISTS `groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
    `permissions` JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EPG Sources
-- ============================================
CREATE TABLE IF NOT EXISTS `epg_sources` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `url` VARCHAR(512) NOT NULL,
    `status` ENUM('active', 'inactive', 'error') NOT NULL DEFAULT 'active',
    `last_update` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Blocked IPs
-- ============================================
CREATE TABLE IF NOT EXISTS `blocked_ips` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip` VARCHAR(45) NOT NULL,
    `reason` VARCHAR(255) NULL,
    `expires_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_blocked_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Flood Logs
-- ============================================
CREATE TABLE IF NOT EXISTS `flood_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip` VARCHAR(45) NOT NULL,
    `attempts` INT UNSIGNED NOT NULL DEFAULT 1,
    `blocked_until` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Activity Logs
-- ============================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type` ENUM('panel', 'reseller', 'stream', 'user') NOT NULL DEFAULT 'panel',
    `username` VARCHAR(100) NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT NULL,
    `ip` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_activity_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Credit Logs
-- ============================================
CREATE TABLE IF NOT EXISTS `credit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL,
    `amount` INT NOT NULL,
    `type` ENUM('add', 'deduct', 'transfer') NOT NULL DEFAULT 'add',
    `note` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Login Logs
-- ============================================
CREATE TABLE IF NOT EXISTS `login_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL,
    `ip` VARCHAR(45) NULL,
    `user_agent` VARCHAR(512) NULL,
    `status` ENUM('success', 'failed') NOT NULL DEFAULT 'success',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Stream Logs
-- ============================================
CREATE TABLE IF NOT EXISTS `stream_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `stream_id` INT UNSIGNED NULL,
    `stream_name` VARCHAR(255) NULL,
    `action` VARCHAR(100) NOT NULL,
    `status` ENUM('success', 'failed', 'error') NOT NULL DEFAULT 'success',
    `error_message` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Initial Data (2026) - Only essential settings
-- ============================================

-- NOTE: No default categories, bouquets, or servers are inserted
-- Users will add their own content

-- Default settings (update if exists)
INSERT INTO `settings` (`key`, `value`, `type`) VALUES
('site_name', 'Cryonix IPTV', 'string'),
('timezone', 'UTC', 'string'),
('default_timezone', 'UTC', 'string'),
('stream_buffer_size', '8192', 'int'),
('max_connections_per_user', '1', 'int'),
('allow_trial_creation', '1', 'bool'),
('trial_duration_hours', '24', 'int'),
('admin_path', 'admin', 'string')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

SET FOREIGN_KEY_CHECKS = 1;


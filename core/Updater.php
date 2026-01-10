<?php
/**
 * Cryonix Panel - Update System
 * Copyright 2026 XProject-Hub
 */

namespace CryonixPanel\Core;

class Updater {
    private string $githubRepo = 'XProject-hub/Cryonix-Panel';
    private string $currentVersion;
    private string $installDir;
    private ?string $githubToken = null;
    
    public function __construct() {
        $this->installDir = dirname(__DIR__);
        $this->currentVersion = $this->getCurrentVersion();
        
        // Load GitHub token from env for private repos
        $this->githubToken = $_ENV['GITHUB_TOKEN'] ?? null;
    }
    
    public function getCurrentVersion(): string {
        $versionFile = $this->installDir . '/VERSION';
        return file_exists($versionFile) ? trim(file_get_contents($versionFile)) : '1.0.0';
    }
    
    private function getHeaders(): array {
        $headers = ['User-Agent: CryonixPanel/1.0', 'Accept: application/vnd.github.v3+json'];
        if ($this->githubToken) {
            $headers[] = 'Authorization: token ' . $this->githubToken;
        }
        return $headers;
    }
    
    public function checkForUpdates(): array {
        $url = "https://api.github.com/repos/{$this->githubRepo}/releases/latest";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $this->getHeaders()
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($code !== 200) {
            return $this->checkCommits();
        }
        
        $data = json_decode($response, true);
        $latest = ltrim($data['tag_name'] ?? '1.0.0', 'v');
        
        return [
            'available' => version_compare($latest, $this->currentVersion, '>'),
            'current' => $this->currentVersion,
            'latest' => $latest,
            'notes' => $data['body'] ?? 'Bug fixes and improvements',
            'date' => $data['published_at'] ?? null,
            'url' => $data['zipball_url'] ?? null
        ];
    }
    
    private function checkCommits(): array {
        $url = "https://api.github.com/repos/{$this->githubRepo}/commits/main";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $this->getHeaders()
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        
        // Compare commit date with our VERSION file date
        $latestVersion = date('Y.m.d.Hi');
        
        return [
            'available' => true,
            'current' => $this->currentVersion,
            'latest' => $latestVersion,
            'notes' => $data['commit']['message'] ?? 'Latest updates and improvements',
            'date' => $data['commit']['committer']['date'] ?? null,
            'url' => "https://github.com/{$this->githubRepo}/archive/refs/heads/main.zip"
        ];
    }
    
    public function applyUpdate(): array {
        $info = $this->checkForUpdates();
        
        if (!$info['available']) {
            return ['success' => false, 'error' => 'No update available'];
        }
        
        if (empty($info['url'])) {
            return ['success' => false, 'error' => 'No download URL available'];
        }
        
        try {
            // Create backup directory
            $backupDir = $this->installDir . '/backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            $backup = $backupDir . '/' . date('Y-m-d_H-i-s');
            mkdir($backup, 0755, true);
            
            // Download update
            $zipFile = '/tmp/cryonix-panel-update-' . time() . '.zip';
            $downloadUrl = $info['url'];
            
            $ch = curl_init($downloadUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_HTTPHEADER => $this->getHeaders()
            ]);
            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode !== 200 || empty($data)) {
                return ['success' => false, 'error' => 'Download failed: ' . ($error ?: "HTTP $httpCode")];
            }
            
            file_put_contents($zipFile, $data);
            
            if (!file_exists($zipFile) || filesize($zipFile) < 1000) {
                return ['success' => false, 'error' => 'Downloaded file is invalid'];
            }
            
            // Extract
            $extractDir = '/tmp/cryonix-panel-update-' . time();
            mkdir($extractDir, 0755, true);
            
            $zip = new \ZipArchive();
            $result = $zip->open($zipFile);
            if ($result !== true) {
                return ['success' => false, 'error' => 'Failed to open zip file: ' . $result];
            }
            
            $zip->extractTo($extractDir);
            $zip->close();
            
            // Find extracted folder (GitHub adds prefix like "Cryonix-Panel-main")
            $folders = glob($extractDir . '/*', GLOB_ONLYDIR);
            if (empty($folders)) {
                return ['success' => false, 'error' => 'No files found in update package'];
            }
            $sourceDir = $folders[0];
            
            // Backup current files (except .env, storage, backups)
            $this->backupFiles($this->installDir, $backup, ['.env', 'storage', 'backups', '.git']);
            
            // Apply update files (preserve .env, storage, backups)
            $preserved = ['.env', 'storage', 'backups', '.git', '.admin_path'];
            $this->copyDir($sourceDir, $this->installDir, $preserved);
            
            // Update version file
            file_put_contents($this->installDir . '/VERSION', $info['latest']);
            
            // Fix permissions
            $this->fixPermissions($this->installDir);
            
            // Cleanup
            @unlink($zipFile);
            $this->deleteDir($extractDir);
            
            return [
                'success' => true,
                'version' => $info['latest'],
                'backup' => $backup
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }
    
    private function backupFiles(string $src, string $dst, array $exclude = []): void {
        if (!is_dir($src)) return;
        if (!is_dir($dst)) mkdir($dst, 0755, true);
        
        $items = scandir($src);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            if (in_array($item, $exclude)) continue;
            
            $srcPath = $src . '/' . $item;
            $dstPath = $dst . '/' . $item;
            
            if (is_dir($srcPath)) {
                $this->backupFiles($srcPath, $dstPath, $exclude);
            } else {
                copy($srcPath, $dstPath);
            }
        }
    }
    
    private function copyDir(string $src, string $dst, array $preserve = []): void {
        if (!is_dir($src)) return;
        if (!is_dir($dst)) mkdir($dst, 0755, true);
        
        $items = scandir($src);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $srcPath = $src . '/' . $item;
            $dstPath = $dst . '/' . $item;
            
            // Skip preserved files/directories
            if (in_array($item, $preserve) && file_exists($dstPath)) {
                continue;
            }
            
            if (is_dir($srcPath)) {
                if (!is_dir($dstPath)) {
                    mkdir($dstPath, 0755, true);
                }
                $this->copyDir($srcPath, $dstPath, $preserve);
            } else {
                // Create parent directory if needed
                $parentDir = dirname($dstPath);
                if (!is_dir($parentDir)) {
                    mkdir($parentDir, 0755, true);
                }
                copy($srcPath, $dstPath);
            }
        }
    }
    
    private function deleteDir(string $dir): void {
        if (!is_dir($dir)) return;
        
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
    
    private function fixPermissions(string $dir): void {
        // Try to set ownership to www-data
        @chown($dir, 'www-data');
        @chgrp($dir, 'www-data');
        
        // Recursively fix permissions
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            @chown($item->getPathname(), 'www-data');
            @chgrp($item->getPathname(), 'www-data');
        }
    }
}

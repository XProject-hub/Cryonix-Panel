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
    
    public function __construct() {
        $this->installDir = dirname(__DIR__);
        $this->currentVersion = $this->getCurrentVersion();
    }
    
    public function getCurrentVersion(): string {
        $versionFile = $this->installDir . '/VERSION';
        return file_exists($versionFile) ? trim(file_get_contents($versionFile)) : '1.0.0';
    }
    
    public function checkForUpdates(): array {
        $url = "https://api.github.com/repos/{$this->githubRepo}/releases/latest";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['User-Agent: CryonixPanel/1.0', 'Accept: application/vnd.github.v3+json']
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($code !== 200) {
            // Check commits if no releases
            return $this->checkCommits();
        }
        
        $data = json_decode($response, true);
        $latest = ltrim($data['tag_name'] ?? '1.0.0', 'v');
        
        return [
            'available' => version_compare($latest, $this->currentVersion, '>'),
            'current' => $this->currentVersion,
            'latest' => $latest,
            'notes' => $data['body'] ?? '',
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
            CURLOPT_HTTPHEADER => ['User-Agent: CryonixPanel/1.0']
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        
        return [
            'available' => true,
            'current' => $this->currentVersion,
            'latest' => date('Y.m.d'),
            'notes' => $data['commit']['message'] ?? 'Latest updates',
            'date' => $data['commit']['committer']['date'] ?? null,
            'url' => "https://github.com/{$this->githubRepo}/archive/refs/heads/main.zip"
        ];
    }
    
    public function applyUpdate(): array {
        $info = $this->checkForUpdates();
        if (!$info['available']) return ['success' => false, 'error' => 'No update available'];
        
        try {
            // Backup
            $backup = $this->installDir . '/backups/' . date('Y-m-d_H-i-s');
            @mkdir($backup, 0755, true);
            
            // Download
            $zip = '/tmp/cryonix-update.zip';
            $ch = curl_init($info['url']);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTPHEADER => ['User-Agent: CryonixPanel/1.0']]);
            file_put_contents($zip, curl_exec($ch));
            curl_close($ch);
            
            // Extract
            $tmp = '/tmp/cryonix-update';
            $z = new \ZipArchive();
            $z->open($zip);
            $z->extractTo($tmp);
            $z->close();
            
            // Apply (preserve .env, storage)
            $src = glob($tmp . '/*')[0];
            $this->copyFiles($src, $this->installDir, ['.env', 'storage', 'backups']);
            
            // Update version
            file_put_contents($this->installDir . '/VERSION', $info['latest']);
            
            // Cleanup
            @unlink($zip);
            $this->rmdir($tmp);
            
            return ['success' => true, 'version' => $info['latest']];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function copyFiles($src, $dst, $exclude = []) {
        foreach (scandir($src) as $f) {
            if ($f === '.' || $f === '..' || in_array($f, $exclude)) continue;
            $s = "$src/$f"; $d = "$dst/$f";
            is_dir($s) ? $this->copyFiles($s, $d, $exclude) : copy($s, $d);
        }
    }
    
    private function rmdir($dir) {
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            $p = "$dir/$f";
            is_dir($p) ? $this->rmdir($p) : unlink($p);
        }
        rmdir($dir);
    }
}


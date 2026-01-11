<?php
/**
 * Cryonix Panel - GeoIP Service
 * MaxMind GeoLite2 Integration
 * Copyright 2026 XProject-Hub
 */

namespace CryonixPanel\Core;

class GeoIP {
    private static ?self $instance = null;
    private ?object $cityReader = null;
    private ?object $asnReader = null;
    private string $dbPath;
    
    // MaxMind credentials (from .env)
    private string $accountId;
    private string $licenseKey;
    
    private function __construct() {
        $this->dbPath = dirname(__DIR__) . '/storage/geoip';
        
        // Load credentials from .env
        $this->accountId = $_ENV['GEOIP_ACCOUNT_ID'] ?? '';
        $this->licenseKey = $_ENV['GEOIP_LICENSE_KEY'] ?? '';
        
        if (!is_dir($this->dbPath)) {
            mkdir($this->dbPath, 0755, true);
        }
        
        $this->loadDatabases();
    }
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadDatabases(): void {
        $cityDb = $this->dbPath . '/GeoLite2-City.mmdb';
        $asnDb = $this->dbPath . '/GeoLite2-ASN.mmdb';
        
        // Try to load MaxMind Reader if available
        if (file_exists($cityDb) && class_exists('MaxMind\Db\Reader')) {
            try {
                $this->cityReader = new \MaxMind\Db\Reader($cityDb);
            } catch (\Exception $e) {
                error_log("GeoIP City DB error: " . $e->getMessage());
            }
        }
        
        if (file_exists($asnDb) && class_exists('MaxMind\Db\Reader')) {
            try {
                $this->asnReader = new \MaxMind\Db\Reader($asnDb);
            } catch (\Exception $e) {
                error_log("GeoIP ASN DB error: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Get location info for an IP address
     */
    public function lookup(string $ip): array {
        $result = [
            'ip' => $ip,
            'country_code' => null,
            'country_name' => null,
            'city' => null,
            'region' => null,
            'latitude' => null,
            'longitude' => null,
            'isp' => null,
            'org' => null,
            'asn' => null
        ];
        
        // Skip private IPs
        if ($this->isPrivateIP($ip)) {
            $result['country_code'] = 'LO';
            $result['country_name'] = 'Local Network';
            return $result;
        }
        
        // Try MaxMind database
        if ($this->cityReader) {
            try {
                $record = $this->cityReader->get($ip);
                if ($record) {
                    $result['country_code'] = $record['country']['iso_code'] ?? null;
                    $result['country_name'] = $record['country']['names']['en'] ?? null;
                    $result['city'] = $record['city']['names']['en'] ?? null;
                    $result['region'] = $record['subdivisions'][0]['names']['en'] ?? null;
                    $result['latitude'] = $record['location']['latitude'] ?? null;
                    $result['longitude'] = $record['location']['longitude'] ?? null;
                }
            } catch (\Exception $e) {
                // IP not found in database
            }
        }
        
        // Try ASN database for ISP info
        if ($this->asnReader) {
            try {
                $record = $this->asnReader->get($ip);
                if ($record) {
                    $result['isp'] = $record['autonomous_system_organization'] ?? null;
                    $result['org'] = $record['autonomous_system_organization'] ?? null;
                    $result['asn'] = $record['autonomous_system_number'] ?? null;
                }
            } catch (\Exception $e) {
                // IP not found
            }
        }
        
        // Fallback to IP-API if no local database
        if (!$result['country_code'] && !$this->cityReader) {
            $result = $this->lookupOnline($ip);
        }
        
        return $result;
    }
    
    /**
     * Fallback online lookup using ip-api.com
     */
    private function lookupOnline(string $ip): array {
        $result = [
            'ip' => $ip,
            'country_code' => null,
            'country_name' => null,
            'city' => null,
            'region' => null,
            'latitude' => null,
            'longitude' => null,
            'isp' => null,
            'org' => null,
            'asn' => null
        ];
        
        try {
            $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,regionName,city,lat,lon,isp,org,as");
            if ($json) {
                $data = json_decode($json, true);
                if ($data && $data['status'] === 'success') {
                    $result['country_code'] = $data['countryCode'] ?? null;
                    $result['country_name'] = $data['country'] ?? null;
                    $result['city'] = $data['city'] ?? null;
                    $result['region'] = $data['regionName'] ?? null;
                    $result['latitude'] = $data['lat'] ?? null;
                    $result['longitude'] = $data['lon'] ?? null;
                    $result['isp'] = $data['isp'] ?? null;
                    $result['org'] = $data['org'] ?? null;
                    $result['asn'] = $data['as'] ?? null;
                }
            }
        } catch (\Exception $e) {
            // Online lookup failed
        }
        
        return $result;
    }
    
    /**
     * Check if IP is private/local
     */
    private function isPrivateIP(string $ip): bool {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
    
    /**
     * Get country flag emoji
     */
    public static function getFlag(string $countryCode): string {
        if (empty($countryCode) || strlen($countryCode) !== 2) {
            return '🏳️';
        }
        
        $countryCode = strtoupper($countryCode);
        
        // Convert country code to flag emoji
        $flag = '';
        foreach (str_split($countryCode) as $char) {
            $flag .= mb_chr(ord($char) + 127397);
        }
        
        return $flag;
    }
    
    /**
     * Get country flag as image URL
     */
    public static function getFlagUrl(string $countryCode): string {
        if (empty($countryCode)) {
            return '';
        }
        $code = strtolower($countryCode);
        return "https://flagcdn.com/24x18/{$code}.png";
    }
    
    /**
     * Download/update GeoIP databases using geoipupdate
     */
    public function updateDatabases(): array {
        $results = [];
        
        if (empty($this->accountId) || empty($this->licenseKey)) {
            return [
                'error' => 'MaxMind credentials not configured. Add GEOIP_ACCOUNT_ID and GEOIP_LICENSE_KEY to .env'
            ];
        }
        
        // Create temporary GeoIP.conf
        $confContent = "AccountID {$this->accountId}\n";
        $confContent .= "LicenseKey {$this->licenseKey}\n";
        $confContent .= "EditionIDs GeoLite2-City GeoLite2-ASN GeoLite2-Country\n";
        $confContent .= "DatabaseDirectory {$this->dbPath}\n";
        
        $confFile = '/tmp/GeoIP.conf';
        file_put_contents($confFile, $confContent);
        
        // Check if geoipupdate is installed
        exec('which geoipupdate 2>&1', $whichOutput, $whichExitCode);
        
        if ($whichExitCode !== 0) {
            // Try to install geoipupdate
            exec('apt-get update && apt-get install -y geoipupdate 2>&1', $installOutput, $installExitCode);
            
            if ($installExitCode !== 0) {
                // Fallback to manual download
                return $this->manualDownload();
            }
        }
        
        // Create database directory
        if (!is_dir($this->dbPath)) {
            mkdir($this->dbPath, 0755, true);
        }
        
        // Run geoipupdate
        exec("geoipupdate -f {$confFile} -d {$this->dbPath} -v 2>&1", $output, $exitCode);
        
        // Cleanup config
        @unlink($confFile);
        
        $outputStr = implode("\n", $output);
        
        if ($exitCode === 0) {
            $editions = ['GeoLite2-City', 'GeoLite2-ASN', 'GeoLite2-Country'];
            foreach ($editions as $edition) {
                $file = $this->dbPath . "/{$edition}.mmdb";
                if (file_exists($file)) {
                    $results[$edition] = ['success' => true, 'size' => filesize($file)];
                } else {
                    $results[$edition] = ['success' => false, 'error' => 'File not created'];
                }
            }
        } else {
            $results['error'] = "geoipupdate failed: " . $outputStr;
            
            // Try manual download as fallback
            return $this->manualDownload();
        }
        
        // Reload databases
        $this->loadDatabases();
        
        return $results;
    }
    
    /**
     * Manual database download (fallback if geoipupdate fails)
     */
    private function manualDownload(): array {
        $results = [];
        $editions = ['GeoLite2-City', 'GeoLite2-ASN', 'GeoLite2-Country'];
        
        if (!is_dir($this->dbPath)) {
            mkdir($this->dbPath, 0755, true);
        }
        
        foreach ($editions as $edition) {
            $url = "https://download.maxmind.com/app/geoip_download?edition_id={$edition}&license_key={$this->licenseKey}&suffix=tar.gz";
            $tarFile = "/tmp/{$edition}.tar.gz";
            
            try {
                // Download
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 300
                ]);
                $data = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode !== 200 || empty($data)) {
                    $results[$edition] = ['success' => false, 'error' => "Download failed (HTTP {$httpCode})"];
                    continue;
                }
                
                file_put_contents($tarFile, $data);
                
                // Extract
                exec("cd /tmp && tar -xzf {$tarFile} 2>&1", $output, $exitCode);
                
                if ($exitCode !== 0) {
                    $results[$edition] = ['success' => false, 'error' => 'Extraction failed'];
                    continue;
                }
                
                // Find and copy mmdb file
                $mmdbFiles = glob("/tmp/{$edition}_*/*.mmdb");
                if (empty($mmdbFiles)) {
                    $results[$edition] = ['success' => false, 'error' => 'MMDB file not found'];
                    continue;
                }
                
                $dest = $this->dbPath . "/{$edition}.mmdb";
                copy($mmdbFiles[0], $dest);
                
                // Cleanup
                @unlink($tarFile);
                exec("rm -rf /tmp/{$edition}_*");
                
                $results[$edition] = ['success' => true, 'size' => filesize($dest)];
                
            } catch (\Exception $e) {
                $results[$edition] = ['success' => false, 'error' => $e->getMessage()];
            }
        }
        
        // Reload databases
        $this->loadDatabases();
        
        return $results;
    }
    
    /**
     * Check if databases exist
     */
    public function getDatabaseStatus(): array {
        $status = [];
        
        $editions = ['GeoLite2-City', 'GeoLite2-ASN', 'GeoLite2-Country'];
        
        foreach ($editions as $edition) {
            $file = $this->dbPath . "/{$edition}.mmdb";
            if (file_exists($file)) {
                $status[$edition] = [
                    'exists' => true,
                    'size' => filesize($file),
                    'modified' => filemtime($file),
                    'version' => date('Y.m', filemtime($file))
                ];
            } else {
                $status[$edition] = ['exists' => false];
            }
        }
        
        return $status;
    }
    
    /**
     * Get combined database version string
     */
    public function getVersion(): string {
        $status = $this->getDatabaseStatus();
        
        // Check if City database exists (main one)
        if (isset($status['GeoLite2-City']) && $status['GeoLite2-City']['exists']) {
            return date('Y.m', $status['GeoLite2-City']['modified']);
        }
        
        // Check any database
        foreach ($status as $edition => $info) {
            if ($info['exists']) {
                return date('Y.m', $info['modified']);
            }
        }
        
        return 'Not Installed';
    }
}


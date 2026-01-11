<?php
/**
 * Cryonix Panel - Stream Probe
 * Get real stream information using FFprobe
 * Copyright 2026 XProject-Hub
 */

namespace CryonixPanel\Core;

class StreamProbe {
    
    /**
     * Probe a stream URL and get real information
     */
    public static function probe(string $url, int $timeout = 10): array {
        $result = [
            'success' => false,
            'online' => false,
            'codec_video' => null,
            'codec_audio' => null,
            'resolution' => null,
            'width' => 0,
            'height' => 0,
            'bitrate' => 0,
            'fps' => 0,
            'duration' => null,
            'error' => null
        ];
        
        if (empty($url)) {
            $result['error'] = 'Empty URL';
            return $result;
        }
        
        // Check if ffprobe is available
        $ffprobe = shell_exec('which ffprobe 2>/dev/null');
        if (empty($ffprobe)) {
            $result['error'] = 'FFprobe not installed';
            return $result;
        }
        
        // Build ffprobe command
        $cmd = sprintf(
            'timeout %d ffprobe -v quiet -print_format json -show_format -show_streams %s 2>&1',
            $timeout,
            escapeshellarg($url)
        );
        
        $output = shell_exec($cmd);
        
        if (empty($output)) {
            $result['error'] = 'No response from stream';
            return $result;
        }
        
        $data = json_decode($output, true);
        
        if (!$data || !isset($data['streams'])) {
            $result['error'] = 'Invalid stream or cannot parse';
            return $result;
        }
        
        $result['success'] = true;
        $result['online'] = true;
        
        // Parse streams
        foreach ($data['streams'] as $stream) {
            if ($stream['codec_type'] === 'video' && !$result['codec_video']) {
                $result['codec_video'] = strtoupper($stream['codec_name'] ?? 'unknown');
                $result['width'] = (int)($stream['width'] ?? 0);
                $result['height'] = (int)($stream['height'] ?? 0);
                $result['resolution'] = $result['width'] . 'x' . $result['height'];
                
                // FPS
                if (isset($stream['r_frame_rate'])) {
                    $fps = explode('/', $stream['r_frame_rate']);
                    if (count($fps) === 2 && $fps[1] > 0) {
                        $result['fps'] = round($fps[0] / $fps[1], 2);
                    }
                }
            }
            
            if ($stream['codec_type'] === 'audio' && !$result['codec_audio']) {
                $result['codec_audio'] = strtolower($stream['codec_name'] ?? 'unknown');
            }
        }
        
        // Bitrate from format
        if (isset($data['format']['bit_rate'])) {
            $result['bitrate'] = (int)($data['format']['bit_rate'] / 1000); // kbps
        }
        
        // Duration
        if (isset($data['format']['duration'])) {
            $result['duration'] = (float)$data['format']['duration'];
        }
        
        return $result;
    }
    
    /**
     * Quick check if stream is online (faster than full probe)
     */
    public static function isOnline(string $url, int $timeout = 5): bool {
        if (empty($url)) {
            return false;
        }
        
        // Use curl for quick check
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'CryonixPanel/1.0'
        ]);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode >= 200 && $httpCode < 400;
    }
    
    /**
     * Format bitrate for display
     */
    public static function formatBitrate(int $kbps): string {
        if ($kbps >= 1000) {
            return round($kbps / 1000, 1) . ' Mbps';
        }
        return $kbps . ' kbps';
    }
    
    /**
     * Format stream info string
     */
    public static function formatInfo(array $probe): string {
        if (!$probe['success']) {
            return 'Offline';
        }
        
        $parts = [];
        
        if ($probe['bitrate']) {
            $parts[] = self::formatBitrate($probe['bitrate']);
        }
        
        if ($probe['resolution'] && $probe['resolution'] !== '0x0') {
            $parts[] = $probe['resolution'];
        }
        
        if ($probe['codec_video']) {
            $codec = $probe['codec_video'];
            if ($probe['codec_audio']) {
                $codec .= ' + ' . $probe['codec_audio'];
            }
            if ($probe['fps']) {
                $codec .= ' @ ' . $probe['fps'] . ' FPS';
            }
            $parts[] = $codec;
        }
        
        return implode(' | ', $parts) ?: 'Unknown';
    }
}


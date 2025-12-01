<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class ViteHelper
{
    protected $devServerUrl = 'http://localhost:5173';
    protected $manifestPath;

    public function __construct()
    {
        $this->manifestPath = public_path('build/manifest.json');
    }

    public function vite($entries)
    {
        // Handle string representation of array from Blade directive
        if (is_string($entries)) {
            // Try to parse as JSON first (in case it's a JSON string)
            $decoded = json_decode($entries, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $entries = $decoded;
            } else {
                // If it looks like a PHP array string, try to evaluate it safely
                if (preg_match('/\[(.*?)\]/', $entries, $matches)) {
                    $items = array_map('trim', explode(',', $matches[1]));
                    $entries = array_map(function($item) {
                        return trim($item, " '\"");
                    }, $items);
                } else {
                    $entries = [$entries];
                }
            }
        }

        // Ensure it's an array
        if (!is_array($entries)) {
            $entries = [$entries];
        }

        if ($this->isDevServerRunning()) {
            return $this->devServerTags($entries);
        }

        return $this->productionTags($entries);
    }

    protected function isDevServerRunning()
    {
        if (app()->environment('production')) {
            return false;
        }

        $ch = curl_init($this->devServerUrl . '/@vite/client');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    protected function devServerTags($entries)
    {
        $tags = '<script type="module" src="' . $this->devServerUrl . '/@vite/client"></script>' . "\n";
        
        $jsEntries = [];
        $cssEntries = [];
        
        // Separate CSS and JS entries
        foreach ($entries as $entry) {
            if (pathinfo($entry, PATHINFO_EXTENSION) === 'css') {
                $cssEntries[] = $entry;
            } else {
                $jsEntries[] = $entry;
            }
        }
        
        // Load CSS files by importing them in a script
        foreach ($cssEntries as $cssEntry) {
            $tags .= '<script type="module">import "' . $this->devServerUrl . '/' . $cssEntry . '";</script>' . "\n";
        }
        
        // Load JS files
        foreach ($jsEntries as $jsEntry) {
            $tags .= '<script type="module" src="' . $this->devServerUrl . '/' . $jsEntry . '"></script>' . "\n";
        }

        return $tags;
    }

    protected function productionTags($entries)
    {
        if (!File::exists($this->manifestPath)) {
            return '<!-- Vite manifest not found. Run "npm run build" -->';
        }

        $manifest = json_decode(File::get($this->manifestPath), true);
        $tags = '';
        $cssFiles = [];

        foreach ($entries as $entry) {
            if (!isset($manifest[$entry])) {
                continue;
            }

            $file = $manifest[$entry];
            $filePath = $file['file'] ?? null;
            
            if (!$filePath) {
                continue;
            }

            // Collect CSS files first
            if (pathinfo($filePath, PATHINFO_EXTENSION) === 'css') {
                $cssFiles[] = $filePath;
            }
            // Check for CSS imports in JS files
            elseif (isset($file['css']) && is_array($file['css'])) {
                foreach ($file['css'] as $cssFile) {
                    $cssFiles[] = $cssFile;
                }
            }

            // Output JS files
            if (pathinfo($filePath, PATHINFO_EXTENSION) === 'js') {
                $tags .= '<script type="module" src="' . asset('build/' . $filePath) . '"></script>' . "\n";
            }
        }

        // Output all CSS files
        foreach (array_unique($cssFiles) as $cssFile) {
            $tags .= '<link rel="stylesheet" href="' . asset('build/' . $cssFile) . '">' . "\n";
        }

        return $tags;
    }
}


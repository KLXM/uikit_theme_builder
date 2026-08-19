<?php

namespace UikitThemeBuilder;

/**
 * Google Fonts Manager
 * Verwaltet das Herunterladen und Speichern von Google Fonts für lokale DSGVO-konforme Nutzung
 */
class GoogleFontsManager
{
    private string $fontsDir;
    private string $assetsDir;
    private string $dataFile;
    
    public function __construct()
    {
        $this->fontsDir = \rex_path::addonData('uikit_theme_builder') . 'fonts/';
        $this->assetsDir = \rex_path::assets('addons/uikit_theme_builder/fonts/');
        $this->dataFile = \rex_path::addonData('uikit_theme_builder') . 'fonts.json';
        
        // Verzeichnisse erstellen
        if (!is_dir($this->fontsDir)) {
            \rex_dir::create($this->fontsDir);
        }
        if (!is_dir($this->assetsDir)) {
            \rex_dir::create($this->assetsDir);
        }
    }
    
    /**
     * Google Font herunterladen und lokal speichern
     */
    public function downloadFont(string $fontFamily, array $variants = ['400']): array
    {
        try {
            // Google Fonts CSS URL erstellen
            $fontUrl = $this->buildGoogleFontsUrl($fontFamily, $variants);
            
            // Debug: URL loggen
            \rex_logger::factory()->log('info', 'GoogleFontsManager: Attempting to fetch ' . $fontUrl, [], 'uikit_theme_builder');
            
            // CSS-Inhalt von Google abrufen
            $cssContent = $this->fetchGoogleFontsCss($fontUrl);
            
            if (!$cssContent) {
                return ['success' => false, 'message' => 'Konnte Font-CSS nicht abrufen'];
            }
            
            // Font-Dateien aus CSS extrahieren und herunterladen
            $downloadedFiles = $this->downloadFontFiles($cssContent, $fontFamily);
            
            if (empty($downloadedFiles)) {
                return ['success' => false, 'message' => 'Keine Font-Dateien gefunden'];
            }
            
            // Lokale CSS-Datei erstellen
            $localCss = $this->createLocalCss($cssContent, $downloadedFiles, $fontFamily, $variants);
            $cssFile = $this->assetsDir . $this->sanitizeFontName($fontFamily) . '.css';
            file_put_contents($cssFile, $localCss);

            $cssUrl = \rex_url::assets('addons/uikit_theme_builder/fonts/' . $this->sanitizeFontName($fontFamily) . '.css');
            
            // Font-Info speichern
            $this->saveFontInfo($fontFamily, [
                'variants' => $variants,
                'files' => $downloadedFiles,
                'css_file' => $cssFile,
                'css_url' => $cssUrl,
                'category' => $this->detectFontCategory($fontFamily),
                'downloaded' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => true, 
                'message' => "Font '{$fontFamily}' erfolgreich heruntergeladen",
                'files' => $downloadedFiles
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Google Fonts URL erstellen
     */
    private function buildGoogleFontsUrl(string $fontFamily, array $variants): string
    {
        // Font-Familie bereinigen und URL-encodieren
        $family = trim($fontFamily);
        $family = str_replace(' ', '+', $family);
        
        // Standard-Varianten falls leer
        if (empty($variants)) {
            $variants = ['400'];
        }
        
        // Nur gültige Gewichte verwenden
        $validWeights = [];
        foreach ($variants as $variant) {
            $weight = trim($variant);
            // 'regular' zu '400' konvertieren
            if ($weight === 'regular') {
                $weight = '400';
            }
            if (preg_match('/^(100|200|300|400|500|600|700|800|900)$/', $weight)) {
                $validWeights[] = $weight;
            }
        }
        
        if (empty($validWeights)) {
            $validWeights = ['400'];
        }
        
        // Duplikate entfernen und sortieren
        $validWeights = array_unique($validWeights);
        sort($validWeights);
        
        $weights = implode(';', $validWeights);
        
        return "https://fonts.googleapis.com/css2?family={$family}:wght@{$weights}&display=swap";
    }
    
    /**
     * CSS-Inhalt von Google Fonts abrufen
     */
    private function fetchGoogleFontsCss(string $url): string
    {
        // Debug: URL ausgeben
        error_log("GoogleFontsManager: Fetching URL: " . $url);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    // User-Agent für moderne Browser (wichtig für woff2)
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ],
                'timeout' => 30,
                'ignore_errors' => true // Um HTTP-Fehler-Details zu erhalten
            ]
        ]);
        
        $css = file_get_contents($url, false, $context);
        
        if ($css === false) {
            // Detaillierte Fehlermeldung
            $error = error_get_last();
            $errorMsg = 'Konnte Google Fonts CSS nicht abrufen';
            if ($error && isset($error['message'])) {
                $errorMsg .= ': ' . $error['message'];
            }
            throw new \Exception($errorMsg . ' (URL: ' . $url . ')');
        }
        
        return $css;
    }
    
    /**
     * Test-Methode für URL-Generierung (Debug)
     */
    public function testUrlGeneration(string $fontFamily, array $variants = ['400']): string
    {
        return $this->buildGoogleFontsUrl($fontFamily, $variants);
    }
    
    /**
     * Font-Dateien aus CSS extrahieren und herunterladen
     */
    private function downloadFontFiles(string $css, string $fontFamily): array
    {
        $downloadedFiles = [];
        $fontDir = $this->fontsDir . $this->sanitizeFontName($fontFamily) . '/';
        
        if (!is_dir($fontDir)) {
            \rex_dir::create($fontDir);
        }
        
        // URLs aus CSS extrahieren (woff2 und woff)
        preg_match_all('/url\((https:\/\/fonts\.gstatic\.com\/[^)]+)\)/', $css, $matches);
        
        foreach ($matches[1] as $fontUrl) {
            $fileName = basename(parse_url($fontUrl, PHP_URL_PATH));
            $localPath = $fontDir . $fileName;
            
            // Font-Datei herunterladen
            $fontData = file_get_contents($fontUrl);
            
            if ($fontData !== false) {
                file_put_contents($localPath, $fontData);
                
                // Auch in Assets-Verzeichnis kopieren (öffentlich zugänglich)
                $assetsPath = $this->assetsDir . $this->sanitizeFontName($fontFamily) . '/';
                if (!is_dir($assetsPath)) {
                    \rex_dir::create($assetsPath);
                }
                copy($localPath, $assetsPath . $fileName);
                
                $downloadedFiles[$fontUrl] = [
                    'local_path' => $localPath,
                    'public_path' => $assetsPath . $fileName,
                    'url' => $this->sanitizeFontName($fontFamily) . '/' . $fileName, // Relativer Pfad von CSS-Datei aus
                    'filename' => $fileName
                ];
            }
        }
        
        return $downloadedFiles;
    }
    
    /**
     * Lokale CSS-Datei erstellen (ersetzt Google URLs durch lokale)
     */
    private function createLocalCss(string $originalCss, array $downloadedFiles, string $fontFamily, array $variants = ['400']): string
    {
        $localCss = $originalCss;
        
        // Google URLs durch lokale URLs ersetzen
        foreach ($downloadedFiles as $originalUrl => $fileInfo) {
            $localCss = str_replace($originalUrl, $fileInfo['url'], $localCss);
        }
        
        // Header hinzufügen
        $header = "/* Local Google Font: {$fontFamily} */\n";
        $header .= "/* Downloaded: " . date('Y-m-d H:i:s') . " */\n";
        $header .= "/* Original URL: " . $this->buildGoogleFontsUrl($fontFamily, $variants) . " */\n\n";
        
        return $header . $localCss;
    }
    
    /**
     * Font-Info in JSON-Datei speichern
     */
    private function saveFontInfo(string $fontFamily, array $info): void
    {
        $fonts = $this->getDownloadedFonts();
        $fonts[$fontFamily] = $info;
        
        file_put_contents($this->dataFile, json_encode($fonts, JSON_PRETTY_PRINT));
    }
    
    /**
     * Alle heruntergeladenen Fonts abrufen
     */
    public function getDownloadedFonts(): array
    {
        if (!file_exists($this->dataFile)) {
            return [];
        }
        
        $content = file_get_contents($this->dataFile);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * Font löschen
     */
    public function deleteFont(string $fontFamily): array
    {
        try {
            $fonts = $this->getDownloadedFonts();
            
            if (!isset($fonts[$fontFamily])) {
                return ['success' => false, 'message' => 'Font nicht gefunden'];
            }
            
            $fontInfo = $fonts[$fontFamily];
            
            // Dateien löschen
            $fontDir = $this->fontsDir . $this->sanitizeFontName($fontFamily) . '/';
            $assetsDir = $this->assetsDir . $this->sanitizeFontName($fontFamily) . '/';
            
            if (is_dir($fontDir)) {
                \rex_dir::delete($fontDir);
            }
            
            if (is_dir($assetsDir)) {
                \rex_dir::delete($assetsDir);
            }
            
            // CSS-Datei löschen
            if (isset($fontInfo['css_file']) && file_exists($fontInfo['css_file'])) {
                unlink($fontInfo['css_file']);
            }
            
            // Aus JSON entfernen
            unset($fonts[$fontFamily]);
            file_put_contents($this->dataFile, json_encode($fonts, JSON_PRETTY_PRINT));
            
            return ['success' => true, 'message' => "Font '{$fontFamily}' wurde gelöscht"];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * System-Fonts abrufen (Fallback-Liste)
     */
    public function getSystemFonts(): array
    {
        return [
            ['family' => 'Arial', 'category' => 'sans-serif'],
            ['family' => 'Helvetica', 'category' => 'sans-serif'],
            ['family' => 'Times New Roman', 'category' => 'serif'],
            ['family' => 'Times', 'category' => 'serif'],
            ['family' => 'Courier New', 'category' => 'monospace'],
            ['family' => 'Courier', 'category' => 'monospace'],
            ['family' => 'Verdana', 'category' => 'sans-serif'],
            ['family' => 'Georgia', 'category' => 'serif'],
            ['family' => 'Palatino', 'category' => 'serif'],
            ['family' => 'Garamond', 'category' => 'serif'],
            ['family' => 'Bookman', 'category' => 'serif'],
            ['family' => 'Comic Sans MS', 'category' => 'cursive'],
            ['family' => 'Trebuchet MS', 'category' => 'sans-serif'],
            ['family' => 'Arial Black', 'category' => 'sans-serif'],
            ['family' => 'Impact', 'category' => 'sans-serif']
        ];
    }
    
    /**
     * Alle verfügbaren Fonts (heruntergeladen + System) für Typography Widget
     */
    public function getAllAvailableFonts(): array
    {
        $availableFonts = [];
        
        // Heruntergeladene Google Fonts
        $downloadedFonts = $this->getDownloadedFonts();
        foreach ($downloadedFonts as $fontFamily => $info) {
            $availableFonts[] = [
                'family' => $fontFamily,
                'category' => $info['category'] ?? 'sans-serif',
                'source' => 'google',
                'variants' => $info['variants'] ?? ['400'],
                'css_file' => $info['css_file'] ?? null
            ];
        }
        
        // System Fonts
        foreach ($this->getSystemFonts() as $font) {
            $availableFonts[] = [
                'family' => $font['family'],
                'category' => $font['category'],
                'source' => 'system',
                'variants' => ['400', '700'], // Standard Annahme für System-Fonts
                'css_file' => null
            ];
        }
        
        return $availableFonts;
    }
    
    /**
     * CSS für verwendete Fonts generieren
     */
    public function generateFontCss(array $usedFonts): string
    {
        $css = "/* UIKit Theme Builder - Font CSS */\n";
        $css .= "/* Generated: " . date('Y-m-d H:i:s') . " */\n\n";
        
        $downloadedFonts = $this->getDownloadedFonts();
        
        foreach ($usedFonts as $fontFamily) {
            if (isset($downloadedFonts[$fontFamily])) {
                $fontInfo = $downloadedFonts[$fontFamily];
                if (isset($fontInfo['css_file']) && file_exists($fontInfo['css_file'])) {
                    $css .= "/* {$fontFamily} */\n";
                    $css .= file_get_contents($fontInfo['css_file']) . "\n\n";
                }
            }
        }
        
        return $css;
    }
    
    /**
     * Font-Namen für Dateisystem bereinigen
     */
    private function sanitizeFontName(string $fontName): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $fontName);
    }
    
    /**
     * Font-Kategorie erkennen (sehr basic)
     */
    private function detectFontCategory(string $fontFamily): string
    {
        $serifIndicators = ['serif', 'times', 'georgia', 'garamond', 'baskerville', 'playfair', 'merriweather', 'lora'];
        $monospaceIndicators = ['mono', 'code', 'courier', 'consolas', 'fira', 'source code'];
        
        $lowerName = strtolower($fontFamily);
        
        foreach ($serifIndicators as $indicator) {
            if (strpos($lowerName, $indicator) !== false) {
                return 'serif';
            }
        }
        
        foreach ($monospaceIndicators as $indicator) {
            if (strpos($lowerName, $indicator) !== false) {
                return 'monospace';
            }
        }
        
        return 'sans-serif'; // Standard-Annahme
    }
}
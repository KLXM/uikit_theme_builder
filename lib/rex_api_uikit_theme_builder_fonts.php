<?php
/**
 * Google Fonts API Endpoint
 * REX API Function für Google Fonts Abfrage mit API Key Support
 */
class rex_api_uikit_theme_builder_fonts extends rex_api_function
{
    protected $published = true;

    /**
     * Zentrale Methode für das Senden von JSON-Antworten
     * Stellt sicher, dass immer erst der Output Buffer geleert wird
     */
    protected function sendResponse($data, $statusCode = 200)
    {
        rex_response::cleanOutputBuffers();
        if ($statusCode !== 200) {
            rex_response::setStatus($statusCode);
        }
        rex_response::sendJson($data);
        exit;
    }

    public function execute()
    {
        try {
            // Cache-Konfiguration
            $cacheFile = rex_path::addonCache('uikit_theme_builder', 'google-fonts.json');
            $cacheTime = 7 * 24 * 60 * 60; // 7 Tage

            // Prüfe ob Cache existiert und aktuell ist
            if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
                $cachedData = json_decode(file_get_contents($cacheFile), true);
                $this->sendResponse($cachedData);
            }

            // Google Fonts API Key aus Config
            $addon = rex_addon::get('uikit_theme_builder');
            $apiKey = $addon->getConfig('google_fonts_api_key', '');
            
            // Entferne mögliche Anführungszeichen
            $apiKey = trim($apiKey, '"\'');

            if (!empty($apiKey)) {
                // Versuche Google Fonts API mit Key abzurufen
                try {
                    $fonts = $this->fetchFromGoogleAPI($apiKey);
                    
                    // Cache speichern
                    rex_file::put($cacheFile, json_encode(['items' => $fonts]));
                    
                    $this->sendResponse(['items' => $fonts]);
                } catch (Exception $e) {
                    rex_logger::factory()->log('warning', 'Google Fonts API error: ' . $e->getMessage());
                    // Fallback auf statische Liste
                }
            }

            // Fallback: Umfangreiche statische Font-Liste
            $fonts = $this->getFallbackFonts();
            
            // Cache Fallback
            rex_file::put($cacheFile, json_encode(['items' => $fonts]));
            
            $this->sendResponse(['items' => $fonts]);

        } catch (Exception $e) {
            rex_logger::logException($e);
            $this->sendResponse(['error' => $e->getMessage()], rex_response::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Ruft Fonts von Google Fonts API ab
     */
    protected function fetchFromGoogleAPI($apiKey)
    {
        // Limit auf 1000 setzen (Maximum der API) statt Default 100
        $url = 'https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=' . urlencode($apiKey) . '&limit=1000';
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10,
                'user_agent' => 'REDAXO UIKit Theme Builder'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('API request failed');
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['items'])) {
            throw new Exception('Invalid API response');
        }
        
        // Vereinfache Datenstruktur für Frontend
        return array_map(function($font) {
            return [
                'family' => $font['family'],
                'category' => $font['category'] ?? 'sans-serif',
                'variants' => $font['variants'] ?? ['regular'],
                'subsets' => $font['subsets'] ?? ['latin']
            ];
        }, $data['items']);
    }

    /**
     * Liefert umfangreiche Fallback-Liste populärer Google Fonts
     */
    protected function getFallbackFonts()
    {
        return [
            // Sans-Serif (Top 50)
            ['family' => 'Roboto', 'category' => 'sans-serif'],
            ['family' => 'Open Sans', 'category' => 'sans-serif'],
            ['family' => 'Lato', 'category' => 'sans-serif'],
            ['family' => 'Montserrat', 'category' => 'sans-serif'],
            ['family' => 'Source Sans Pro', 'category' => 'sans-serif'],
            ['family' => 'Raleway', 'category' => 'sans-serif'],
            ['family' => 'Poppins', 'category' => 'sans-serif'],
            ['family' => 'Nunito', 'category' => 'sans-serif'],
            ['family' => 'Inter', 'category' => 'sans-serif'],
            ['family' => 'Rubik', 'category' => 'sans-serif'],
            ['family' => 'Work Sans', 'category' => 'sans-serif'],
            ['family' => 'Fira Sans', 'category' => 'sans-serif'],
            ['family' => 'Noto Sans', 'category' => 'sans-serif'],
            ['family' => 'Ubuntu', 'category' => 'sans-serif'],
            ['family' => 'Mukta', 'category' => 'sans-serif'],
            ['family' => 'Josefin Sans', 'category' => 'sans-serif'],
            ['family' => 'Barlow', 'category' => 'sans-serif'],
            ['family' => 'Oxygen', 'category' => 'sans-serif'],
            ['family' => 'Archivo', 'category' => 'sans-serif'],
            ['family' => 'Quicksand', 'category' => 'sans-serif'],
            ['family' => 'Hind', 'category' => 'sans-serif'],
            ['family' => 'DM Sans', 'category' => 'sans-serif'],
            ['family' => 'Manrope', 'category' => 'sans-serif'],
            ['family' => 'Space Grotesk', 'category' => 'sans-serif'],
            
            // Serif
            ['family' => 'Playfair Display', 'category' => 'serif'],
            ['family' => 'Merriweather', 'category' => 'serif'],
            ['family' => 'Lora', 'category' => 'serif'],
            ['family' => 'PT Serif', 'category' => 'serif'],
            ['family' => 'Libre Baskerville', 'category' => 'serif'],
            ['family' => 'Crimson Text', 'category' => 'serif'],
            ['family' => 'Noto Serif', 'category' => 'serif'],
            ['family' => 'Source Serif Pro', 'category' => 'serif'],
            ['family' => 'Bitter', 'category' => 'serif'],
            ['family' => 'Cormorant', 'category' => 'serif'],
            ['family' => 'Spectral', 'category' => 'serif'],
            
            // Display
            ['family' => 'Bebas Neue', 'category' => 'display'],
            ['family' => 'Anton', 'category' => 'display'],
            ['family' => 'Righteous', 'category' => 'display'],
            ['family' => 'Fredoka', 'category' => 'display'],
            
            // Handwriting
            ['family' => 'Dancing Script', 'category' => 'handwriting'],
            ['family' => 'Pacifico', 'category' => 'handwriting'],
            ['family' => 'Great Vibes', 'category' => 'handwriting'],
            ['family' => 'Caveat', 'category' => 'handwriting'],
            ['family' => 'Satisfy', 'category' => 'handwriting'],
            ['family' => 'Kaushan Script', 'category' => 'handwriting'],
            
            // Monospace
            ['family' => 'Fira Code', 'category' => 'monospace'],
            ['family' => 'Source Code Pro', 'category' => 'monospace'],
            ['family' => 'JetBrains Mono', 'category' => 'monospace'],
            ['family' => 'IBM Plex Mono', 'category' => 'monospace'],
            ['family' => 'Roboto Mono', 'category' => 'monospace'],
        ];
    }
}

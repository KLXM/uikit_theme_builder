<?php

namespace UikitThemeBuilder;

/**
 * ThemeExporter
 * Exportiert Themes mit allen Dependencies (Google Fonts, StyleSets)
 */
class ThemeExporter
{
    private UikitThemeBuilderManager $themeManager;
    private StyleSetManager $styleSetManager;
    private GoogleFontsManager $fontsManager;

    public function __construct()
    {
        $this->themeManager = new UikitThemeBuilderManager();
        $this->styleSetManager = new StyleSetManager();
        $this->fontsManager = new GoogleFontsManager();
    }

    /**
     * Theme exportieren
     * 
     * @param string $themeName Name des zu exportierenden Themes
     * @param bool $includeStyleSets StyleSets mit einbetten (für standalone Export)
     * @return array Export-Daten
     */
    public function exportTheme(string $themeName, bool $includeStyleSets = true): array
    {
        // Theme laden
        $themeData = $this->themeManager->loadTheme($themeName);
        
        if (!$themeData) {
            throw new \Exception("Theme '{$themeName}' nicht gefunden");
        }

        $export = [
            'version' => '1.0',
            'exported_at' => date('Y-m-d H:i:s'),
            'addon_version' => \rex_addon::get('uikit_theme_builder')->getVersion(),
            'theme' => [
                'name' => $themeName,
                'theme_data' => $themeData['data'],
                'created' => $themeData['created'],
            ],
            'dependencies' => [
                'google_fonts' => [],
                'style_sets' => []
            ]
        ];

        // Google Fonts extrahieren
        $export['dependencies']['google_fonts'] = $this->extractGoogleFonts($themeData['data']);

        // StyleSets extrahieren
        if ($includeStyleSets && isset($themeData['data']['style_sets']['selected_style_sets'])) {
            $export['dependencies']['style_sets'] = $this->extractStyleSets(
                $themeData['data']['style_sets']['selected_style_sets']
            );
        }

        return $export;
    }

    /**
     * Google Fonts aus Theme-Daten extrahieren
     */
    private function extractGoogleFonts(array $themeData): array
    {
        $fonts = [];
        
        // Typography Widget durchsuchen
        if (isset($themeData['typography'])) {
            $typography = $themeData['typography'];
            
            // Alle Font-Family Felder
            $fontFields = [
                'global-font-family',
                'base-heading-font-family',
                'navbar-nav-item-font-family',
                'base-blockquote-font-family',
            ];
            
            foreach ($fontFields as $field) {
                if (!empty($typography[$field])) {
                    $fontFamilyString = $typography[$field];
                    
                    // Extrahiere nur die erste Font-Familie (vor dem ersten Komma)
                    $fontFamily = $this->extractFirstFont($fontFamilyString);
                    
                    // System-Fonts und inherit überspringen
                    if ($fontFamily && !$this->isSystemFont($fontFamily) && $fontFamily !== 'inherit') {
                        // Prüfen ob bereits in Liste
                        $exists = false;
                        foreach ($fonts as $font) {
                            if ($font['family'] === $fontFamily) {
                                $exists = true;
                                break;
                            }
                        }
                        
                        if (!$exists) {
                            $fonts[] = [
                                'family' => $fontFamily,
                                'variants' => $this->detectFontVariants($typography, $fontFamily)
                            ];
                        }
                    }
                }
            }
        }
        
        return $fonts;
    }

    /**
     * Font-Varianten aus Theme-Daten ermitteln
     */
    private function detectFontVariants(array $typography, string $fontFamily): array
    {
        $variants = ['400']; // Standard
        
        // Sammle alle verwendeten Weights
        $weightFields = [
            'base-body-font-weight',
            'base-heading-font-weight',
            'navbar-nav-item-font-weight'
        ];
        
        foreach ($weightFields as $field) {
            if (!empty($typography[$field])) {
                $weight = $typography[$field];
                if (preg_match('/^(100|200|300|400|500|600|700|800|900)$/', $weight)) {
                    $variants[] = $weight;
                }
            }
        }
        
        // Bold hinzufügen falls nicht vorhanden
        if (!in_array('700', $variants)) {
            $variants[] = '700';
        }
        
        return array_unique($variants);
    }

    /**
     * StyleSets aus Theme-Daten extrahieren
     */
    private function extractStyleSets(array $styleSetIds): array
    {
        $styleSets = [];
        
        foreach ($styleSetIds as $id) {
            $styleSet = $this->styleSetManager->getStyleSetById((int)$id);
            
            if ($styleSet) {
                $styleSets[] = [
                    'id' => $styleSet['id'],
                    'slug' => $styleSet['slug'],
                    'name' => $styleSet['name'],
                    'description' => $styleSet['description'],
                    'styles_data' => $styleSet['styles_data'],
                    'is_active' => $styleSet['is_active'],
                    'created' => $styleSet['created'],
                ];
            }
        }
        
        return $styleSets;
    }

    /**
     * Extrahiert die erste Font-Familie aus einem Font-Stack String
     */
    private function extractFirstFont(string $fontFamilyString): ?string
    {
        // Entferne Anführungszeichen und trim
        $fontFamilyString = trim($fontFamilyString, ' "\'"');
        
        // Splitte bei Komma und nimm die erste Font
        $fonts = array_map('trim', explode(',', $fontFamilyString));
        
        if (empty($fonts)) {
            return null;
        }
        
        // Erste Font bereinigen
        $firstFont = trim($fonts[0], ' "\'"');
        
        return $firstFont ?: null;
    }

    /**
     * Prüfen ob Font eine System-Font ist
     */
    private function isSystemFont(string $fontFamily): bool
    {
        $systemFonts = [
            'Arial', 'Helvetica', 'Times New Roman', 'Times', 
            'Courier New', 'Courier', 'Verdana', 'Georgia', 
            'Palatino', 'Garamond', 'Bookman', 'Comic Sans MS', 
            'Trebuchet MS', 'Arial Black', 'Impact', 'sans-serif',
            'serif', 'monospace', 'cursive', 'fantasy', '-apple-system',
            'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue',
            'system-ui'
        ];
        
        // Case-insensitive Vergleich
        foreach ($systemFonts as $systemFont) {
            if (strcasecmp($fontFamily, $systemFont) === 0) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Export als JSON-String
     */
    public function exportThemeAsJson(string $themeName, bool $includeStyleSets = true): string
    {
        $data = $this->exportTheme($themeName, $includeStyleSets);
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Export als Download-Datei
     */
    public function exportThemeAsDownload(string $themeName, bool $includeStyleSets = true): void
    {
        $json = $this->exportThemeAsJson($themeName, $includeStyleSets);
        $filename = $this->sanitizeFilename($themeName) . '-' . date('Y-m-d') . '.json';
        
        // Sicherstellen dass kein Output vor dem Download kommt
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        echo $json;
        exit;
    }

    /**
     * Dateinamen bereinigen
     */
    private function sanitizeFilename(string $name): string
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9_-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);
        return trim($name, '-');
    }
}

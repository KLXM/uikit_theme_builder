<?php

namespace UikitThemeBuilder;

use Less_Parser;
use Exception;

/**
 * UIKit Theme Builder Manager
 * Zentrale Klasse für Theme-Kompilierung und -Verwaltung
 */
class UikitThemeBuilderManager
{
    private string $dataPath;
    private string $assetsPath;
    private string $uikitPath;
    private string $uikitLessPath;
    private string $tempDir;
    
    public function __construct()
    {
        $this->dataPath = \rex_path::addonData('uikit_theme_builder');
        $this->assetsPath = \rex_path::addonAssets('uikit_theme_builder');
        $this->uikitPath = \rex_path::addon('uikit_theme_builder', 'sources/uikit');
        $this->uikitLessPath = \rex_path::addon('uikit_theme_builder', 'sources/uikit/less');
        $this->tempDir = $this->dataPath . 'themes/temp';
        
        // Verzeichnisse sicherstellen
        if (!is_dir($this->tempDir)) {
            \rex_dir::create($this->tempDir);
        }
    }
    
    /**
     * Theme kompilieren
     */
    public function compileTheme(string $themeName, array $themeData): array
    {
        try {
            // LESS-Variablen generieren
            $lessVariables = $this->generateLessVariables($themeData);
            
            // Custom LESS-Code generieren
            $customLess = $this->generateCustomLess($themeData);
            
            // Debug-Output nur im Debug-Modus anzeigen
            $debugOutput = '';
            if (\rex::isDebugMode()) {
                $debugOutput .= '<div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; border-radius: 5px;">';
                $debugOutput .= '<h4 style="margin-top: 0; color: #495057;">🐛 Debug Information (Debug Mode: ON)</h4>';
                
                $debugOutput .= '<h5>Theme Data Structure:</h5>';
                $debugOutput .= '<pre style="background: #e9ecef; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px;">';
                $debugOutput .= htmlspecialchars(print_r($themeData, true));
                $debugOutput .= '</pre>';
                
                $flatData = $this->flattenArray($themeData);
                $debugOutput .= '<h5>Flattened Data (before LESS conversion):</h5>';
                $debugOutput .= '<pre style="background: #e9ecef; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px;">';
                $debugOutput .= htmlspecialchars(print_r($flatData, true));
                $debugOutput .= '</pre>';
                
                $debugOutput .= '<h5>Generated LESS Variables:</h5>';
                $debugOutput .= '<pre style="background: #e9ecef; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px;">';
                $debugOutput .= htmlspecialchars($lessVariables);
                $debugOutput .= '</pre>';
                
                if ($customLess) {
                    $debugOutput .= '<h5>Generated Custom LESS:</h5>';
                    $debugOutput .= '<pre style="background: #e9ecef; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px;">';
                    $debugOutput .= htmlspecialchars($customLess);
                    $debugOutput .= '</pre>';
                }
                
                $debugOutput .= '</div>';
            } else {
                // Flattened Data ist auch außerhalb Debug-Modus nötig
                $flatData = $this->flattenArray($themeData);
            }
            
            // Temporäre LESS-Datei erstellen
            $tempLessFile = $this->createTempLessFile($lessVariables, $customLess, $themeName);
            
            // LESS kompilieren
            $compilationStart = microtime(true);
            $css = $this->compileLess($tempLessFile);
            $compilationTime = microtime(true) - $compilationStart;
            
            // Absolute Dateisystempfade in relative Web-Pfade umwandeln
            $css = $this->fixAssetPaths($css);
            
            // Extra Styles CSS hinzufügen (falls verfügbar und für dieses Theme konfiguriert)
            $extraStylesCss = $this->getExtraStylesCss($themeName, $themeData);
            if ($extraStylesCss) {
                $css .= "\n\n/* Extra Styles Integration */\n" . $extraStylesCss;
                if (\rex::isDebugMode()) {
                    $debugOutput .= '<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px;">';
                    $debugOutput .= '<h5 style="color: #155724;">✅ Extra Styles Integration</h5>';
                    $debugOutput .= '<p>Extra Styles CSS wurde erfolgreich hinzugefügt (' . strlen($extraStylesCss) . ' Bytes)</p>';
                    $debugOutput .= '</div>';
                }
            } elseif (\rex::isDebugMode()) {
                $debugOutput .= '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px;">';
                $debugOutput .= '<h5 style="color: #856404;">ℹ️ Extra Styles Status</h5>';
                $debugOutput .= '<p>Keine Extra Styles für Theme "' . $themeName . '" verfügbar</p>';
                $debugOutput .= '</div>';
            }
            
            // CSS-Datei speichern
            $cssFile = $this->saveCssFile($themeName, $css);
            
            // Google Fonts Cache für dieses Theme erstellen
            $this->saveThemeGoogleFonts($themeName, $themeData);
            
            // Aufräumen
            unlink($tempLessFile);
            
            return [
                'success' => true,
                'css' => $css,
                'css_file' => $cssFile,
                'debug_output' => $debugOutput,
                'compilation_info' => [
                    'compilation_time' => $compilationTime,
                    'variables_count' => count($this->flattenArray($themeData)),
                    'size' => strlen($css)
                ]
            ];
            
        } catch (Exception $e) {
            if (isset($tempLessFile) && file_exists($tempLessFile)) {
                unlink($tempLessFile);
            }
            throw $e;
        }
    }
    
    /**
     * LESS-Variablen aus Theme-Daten generieren
     */
    private function generateLessVariables(array $themeData): string
    {
        $lessContent = "// UIKit Theme Builder - Generated Variables\n";
        $lessContent .= "// Generated at: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Metadaten und Custom Styles aus Theme-Daten entfernen bevor flattening
        $themeDataForVariables = $themeData;
        unset($themeDataForVariables['custom_styles']);
        unset($themeDataForVariables['_meta']); // Metadaten nicht in LESS-Variablen konvertieren
        
        // DEBUG: Nur im Debug-Modus anzeigen
        if (\rex::isDebugMode()) {
            $lessContent .= "// DEBUG: Original breakpoints data\n";
            if (isset($themeData['breakpoints'])) {
                foreach ($themeData['breakpoints'] as $key => $value) {
                    $lessContent .= "// $key = $value\n";
                }
            }
            $lessContent .= "\n";
        }
        
        // Widget-spezifische Variable-Generierung verwenden
        $allVariables = [];
        $processedKeys = [];
        
        // Widgets laden und deren generateLessVariables Methoden verwenden
        $widgets = [
            new \UikitThemeBuilder\Widget\ColorsWidget(),
            new \UikitThemeBuilder\Widget\TypographyWidget(),
            new \UikitThemeBuilder\Widget\FormWidget(),
            new \UikitThemeBuilder\Widget\CardWidget(),
            new \UikitThemeBuilder\Widget\SearchWidget(),
            new \UikitThemeBuilder\Widget\BreakpointsWidget(),
            new \UikitThemeBuilder\Widget\BorderWidget(),
            new \UikitThemeBuilder\Widget\ShadowWidget(),
            new \UikitThemeBuilder\Widget\ContainerWidget(),
            new \UikitThemeBuilder\Widget\NavbarWidget(),
            new \UikitThemeBuilder\Widget\ComponentsWidget(),
            new \UikitThemeBuilder\Widget\ExtraStylesWidget()
        ];
        
        foreach ($widgets as $widget) {
            $widgetKey = $widget->getKey();
            if (isset($themeDataForVariables[$widgetKey])) {
                $widgetVariables = $widget->generateLessVariables($themeDataForVariables[$widgetKey]);
                $allVariables = array_merge($allVariables, $widgetVariables);
                
                // Merke verarbeitete Widget-Daten um doppelte Variablen zu vermeiden
                $processedKeys[] = $widgetKey;
            }
        }
        
        // Theme-Daten ohne bereits verarbeitete Widget-Daten für Flattening
        $flatteningData = $themeDataForVariables;
        foreach ($processedKeys as $processedKey) {
            unset($flatteningData[$processedKey]);
        }
        
        $flatData = $this->flattenArray($flatteningData);
        
        // DEBUG: Nur im Debug-Modus anzeigen
        if (\rex::isDebugMode()) {
            // DEBUG: Flattened data - alle Keys anzeigen
            $lessContent .= "// DEBUG: All flattened keys\n";
            foreach ($flatData as $key => $value) {
                $lessContent .= "// $key = $value\n";
            }
            $lessContent .= "\n";
            
            // DEBUG: Widget-generated variables
            $lessContent .= "// DEBUG: Widget-generated variables\n";
            foreach ($allVariables as $key => $value) {
                $lessContent .= "// WIDGET: $key = $value\n";
            }
            $lessContent .= "\n";
        }
        
        // Variablen kategorisiert ausgeben
        $categories = [
            'global' => 'Global Settings',
            'base' => 'Base Typography', 
            'breakpoint' => 'Breakpoints',
            'border' => 'Borders',
            'box-shadow' => 'Shadows',
            'drop-shadow' => 'Drop Shadows',
            'container' => 'Container Sizes',
            'form' => 'Form Settings',
            'card' => 'Card Settings',
            'search' => 'Search Settings',
            'button' => 'Button Styles',
            'navbar' => 'Navbar Settings',
            'tooltip' => 'Tooltip Settings',
            'badge' => 'Badge Settings'
        ];
        
        // Widget-Variablen haben Priorität - mit Flat-Data zusammenführen
        $allGeneratedVars = array_merge($flatData, $allVariables);
        
        $categorizedVars = [];
        
        foreach ($allGeneratedVars as $varName => $value) {
            $category = 'other';
            foreach ($categories as $prefix => $label) {
                if (strpos($varName, $prefix) === 0) {
                    $category = $prefix;
                    break;
                }
            }
            
            if (!isset($categorizedVars[$category])) {
                $categorizedVars[$category] = [];
            }
            $categorizedVars[$category][$varName] = $value;
        }
        
        // Kategorien in LESS ausgeben
        foreach ($categorizedVars as $category => $vars) {
            $label = $categories[$category] ?? ucfirst($category);
            $lessContent .= "// {$label}\n";
            
            foreach ($vars as $varName => $value) {
                $lessContent .= "@{$varName}: {$value};\n";
            }
            
            $lessContent .= "\n";
        }
        
        // Fix für Icon Button Variables - direkt nach den Variablen
        $lessContent .= "// Icon Button Fix - explizite Werte statt Variable-Referenzen\n";
        $lessContent .= '@icon-button-background: #f8f8f8;' . "\n";
        $lessContent .= '@icon-button-hover-background: darken(#f8f8f8, 5%);' . "\n";
        $lessContent .= '@icon-button-active-background: darken(#f8f8f8, 10%);' . "\n\n";
        
        return $lessContent;
    }
    
    /**
     * Custom LESS-Code aus Theme-Daten extrahieren
     */
    public function generateCustomLess(array $themeData): string
    {
        $customLess = '';
        
        // WICHTIG: Alle Widgets durchgehen und deren generateLess() Methoden aufrufen
        $widgets = [
            'colors' => new \UikitThemeBuilder\Widget\ColorsWidget(),
            'typography' => new \UikitThemeBuilder\Widget\TypographyWidget(),
            'form' => new \UikitThemeBuilder\Widget\FormWidget(),
            'card' => new \UikitThemeBuilder\Widget\CardWidget(),
            'search' => new \UikitThemeBuilder\Widget\SearchWidget(),
            'components' => new \UikitThemeBuilder\Widget\ComponentsWidget(),
            'navbar' => new \UikitThemeBuilder\Widget\NavbarWidget(),
            'extra_styles' => new \UikitThemeBuilder\Widget\ExtraStylesWidget(),
            'google_fonts' => new \UikitThemeBuilder\Widget\GoogleFontsWidget(),
            'custom_styles' => new \UikitThemeBuilder\Widget\CustomStylesWidget(),
            'style_sets' => new \UikitThemeBuilder\Widget\StyleSetSelectionWidget(),
            'shadow' => new \UikitThemeBuilder\Widget\ShadowWidget(),
            'border' => new \UikitThemeBuilder\Widget\BorderWidget(),
            'container' => new \UikitThemeBuilder\Widget\ContainerWidget(),
            'breakpoints' => new \UikitThemeBuilder\Widget\BreakpointsWidget(),
        ];
        
        foreach ($widgets as $widgetKey => $widget) {
            if (isset($themeData[$widgetKey]) && method_exists($widget, 'generateLess')) {
                $widgetLess = $widget->generateLess($themeData[$widgetKey]);
                if (!empty($widgetLess)) {
                    $widgetName = $widget->getName();
                    $customLess .= "// {$widgetName} Widget - Custom LESS\n";
                    $customLess .= $widgetLess . "\n\n";
                }
            }
        }
        
        return $customLess;
    }
    
    /**
     * Temporäre LESS-Datei erstellen
     */
    private function createTempLessFile(string $lessVariables, string $customLess, string $themeName): string
    {
        $tempFile = $this->tempDir . '/theme_' . $themeName . '_' . time() . '.less';
        
        // Basis UIKit importieren
        $lessContent = "// UIKit Theme Builder - Generated Theme\n";
        $lessContent .= "// Theme: {$themeName}\n";
        $lessContent .= "// Generated at: " . date('Y-m-d H:i:s') . "\n\n";
        
        // UIkit Methode: Import Core + Theme, DANN Variablen überschreiben
        $lessContent .= "// Step 1: Import UIKit Core + Theme\n";
        $lessContent .= '@import "' . $this->uikitLessPath . '/components/_import.less";' . "\n";
        $lessContent .= '@import "' . $this->uikitLessPath . '/theme/_import.less";' . "\n\n";
        
        // DANN: Theme-Variablen definieren (überschreibt UIKit-Defaults)
        // In Less werden später definierte Variablen bevorzugt
        $lessContent .= "// Step 2: Override Variables with Theme Settings\n";
        $lessContent .= $lessVariables . "\n\n";
        
        // Extra Styles importieren
        $lessContent .= "// Step 3: Import Extra Styles\n";
        $lessContent .= '@import "' . \rex_path::addon('uikit_theme_builder', 'sources/extra.less') . '";' . "\n\n";
        
        // ZULETZT: Custom CSS-Regeln, die UIKit-Styles überschreiben
        if ($customLess) {
            $lessContent .= "// Step 4: Custom CSS Rules (Override UIKit)\n";
            $lessContent .= "// These CSS rules override UIKit's default styles\n";
            $lessContent .= $customLess . "\n";
        }
        
        file_put_contents($tempFile, $lessContent);
        
        return $tempFile;
    }
    
    /**
     * LESS zu CSS kompilieren
     */
    private function compileLess(string $lessFile): string
    {
        $importDirs = [
            dirname($lessFile) => '',
            \rex_path::addon('uikit_theme_builder', 'sources') => '',
            $this->uikitLessPath => ''
        ];
        
        $parser = new Less_Parser(['compress' => false, 'import_dirs' => $importDirs]);
        $parser->parseFile($lessFile);
        
        return $parser->getCss();
    }
    
    /**
     * Absolute Dateisystempfade in CSS zu relativen Web-Pfaden umwandeln
     */
    private function fixAssetPaths(string $css): string
    {
        // Absolute Pfade zum UIKit Addon Assets-Verzeichnis
        $addonAssetsPath = \rex_path::addonAssets('uikit_theme_builder');
        $addonAssetsUrl = \rex_url::addonAssets('uikit_theme_builder');
        
        // Pattern: url(/absoluter/pfad/...) oder url("/absoluter/pfad/...")
        // Ersetze durch relative URLs die von / ausgehen
        $css = preg_replace_callback(
            '/url\(["\']?([^"\')\s]+)["\']?\)/i',
            function($matches) use ($addonAssetsPath, $addonAssetsUrl) {
                $path = $matches[1];
                
                // Wenn es ein absoluter Dateisystempfad ist
                if (strpos($path, '/') === 0 || strpos($path, 'var/www') !== false) {
                    // Versuche den Pfad relativ zum Addon-Assets zu machen
                    if (strpos($path, $addonAssetsPath) !== false) {
                        $relativePath = str_replace($addonAssetsPath, '', $path);
                        return 'url("' . $addonAssetsUrl . $relativePath . '")';
                    }
                    
                    // Versuche UIKit uikit/src/images/ zu finden
                    if (strpos($path, 'uikit/src/images/') !== false) {
                        $parts = explode('uikit/src/images/', $path);
                        if (count($parts) === 2) {
                            return 'url("' . $addonAssetsUrl . '/uikit/src/images/' . $parts[1] . '")';
                        }
                    }
                    
                    // Versuche uikit-less/less/images/ zu finden
                    if (strpos($path, 'uikit-less/less/images/') !== false) {
                        $parts = explode('uikit-less/less/images/', $path);
                        if (count($parts) === 2) {
                            return 'url("' . $addonAssetsUrl . '/uikit/src/images/' . $parts[1] . '")';
                        }
                    }
                    
                    // Allgemein: /images/backgrounds/ Pattern
                    if (preg_match('#/images/backgrounds/([^/]+\.svg)$#', $path, $m)) {
                        return 'url("' . $addonAssetsUrl . '/uikit/src/images/backgrounds/' . $m[1] . '")';
                    }
                }
                
                // Wenn bereits eine relative URL oder data: URI, unverändert lassen
                return $matches[0];
            },
            $css
        );
        
        return $css;
    }
    
    /**
     * CSS-Datei speichern
     */
    private function saveCssFile(string $themeName, string $css): string
    {
        // Header hinzufügen
        $header = "/*\n * UIKit Theme Builder - Compiled CSS\n";
        $header .= " * Theme: {$themeName}\n";
        $header .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
        $header .= " * Source: UIKit Theme Builder\n */\n\n";
        
        $cssWithHeader = $header . $css;
        
        // In Data-Verzeichnis speichern
        $dataFile = $this->dataPath . 'themes/compiled/' . $themeName . '.css';
        file_put_contents($dataFile, $cssWithHeader);
        
        // In Assets-Verzeichnis kopieren (öffentlich zugänglich)
        $assetsDir = $this->assetsPath . 'themes/compiled';
        if (!is_dir($assetsDir)) {
            \rex_dir::create($assetsDir);
        }
        
        $assetsFile = $assetsDir . '/' . $themeName . '.css';
        file_put_contents($assetsFile, $cssWithHeader);
        
        return $assetsFile;
    }
    
    /**
     * Theme speichern
     */
    public function saveTheme(string $themeName, array $themeData): bool
    {
        // Validierung: 'new_theme' ist reserviert und darf nicht verwendet werden
        if (strtolower($themeName) === 'new_theme') {
            throw new \InvalidArgumentException('Der Theme-Name "new_theme" ist reserviert und kann nicht verwendet werden.');
        }
        
        $themeInfo = [
            'name' => $themeName,
            'created' => date('Y-m-d H:i:s'),
            'modified' => time(),
            'version' => '1.0.0',
            'data' => $themeData
        ];
        
        $filename = $this->dataPath . 'themes/saved/' . $themeName . '.json';
        $success = file_put_contents($filename, json_encode($themeInfo, JSON_PRETTY_PRINT)) !== false;
        
        // Google Fonts für dieses Theme extrahieren und separat speichern
        if ($success) {
            $this->saveThemeGoogleFonts($themeName, $themeData);
            
            // Theme-Farben in Datenbank cachen für DomainContext
            $this->cacheThemeColors($themeName, $themeData);
        }
        
        return $success;
    }
    
    /**
     * Theme laden (mit automatischer Migration alter Variablennamen)
     */
    public function loadTheme(string $themeName): ?array
    {
        $filename = $this->dataPath . 'themes/saved/' . $themeName . '.json';
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $content = file_get_contents($filename);
        $themeData = json_decode($content, true);
        
        // Automatische Migration: Alte Variablennamen zu neuen konvertieren
        if ($themeData && isset($themeData['data'])) {
            $themeData['data'] = $this->migrateThemeData($themeData['data']);
        }
        
        return $themeData;
    }
    
    /**
     * Migriert alte Theme-Daten zu neuen Variablennamen
     */
    private function migrateThemeData(array $data): array
    {
        // Typography: font_fallback_* → font-fallback-*
        if (isset($data['typography'])) {
            $migrations = [
                'font_fallback_sans_serif' => 'font-fallback-sans-serif',
                'font_fallback_serif' => 'font-fallback-serif',
                'font_fallback_monospace' => 'font-fallback-monospace',
                'font_fallback_cursive' => 'font-fallback-cursive'
            ];
            
            foreach ($migrations as $old => $new) {
                if (isset($data['typography'][$old])) {
                    $data['typography'][$new] = $data['typography'][$old];
                    unset($data['typography'][$old]);
                }
            }
        }
        
        // Shadows: box_shadow_* → global-*-box-shadow
        if (isset($data['shadows'])) {
            $shadowMigrations = [
                'box_shadow_small' => 'global-small-box-shadow',
                'box_shadow_medium' => 'global-medium-box-shadow',
                'box_shadow_large' => 'global-large-box-shadow',
                'box_shadow_xlarge' => 'global-xlarge-box-shadow',
                'drop_shadow_small' => 'global-small-drop-shadow',
                'drop_shadow_medium' => 'global-medium-drop-shadow',
                'drop_shadow_large' => 'global-large-drop-shadow',
                'drop_shadow_xlarge' => 'global-xlarge-drop-shadow'
            ];
            
            foreach ($shadowMigrations as $old => $new) {
                if (isset($data['shadows'][$old])) {
                    $data['shadows'][$new] = $data['shadows'][$old];
                    unset($data['shadows'][$old]);
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Alle Themes auflisten
     */
    public function listThemes(): array
    {
        $themesDir = $this->dataPath . 'themes/saved';
        $themes = [];
        
        if (!is_dir($themesDir)) {
            return $themes;
        }
        
        foreach (glob($themesDir . '/*.json') as $file) {
            $themeName = basename($file, '.json');
            $themeData = $this->loadTheme($themeName);
            
            if ($themeData) {
                $themes[$themeName] = [
                    'name' => $themeName,
                    'created' => $themeData['created'] ?? 'Unbekannt',
                    'modified' => $themeData['modified'] ?? time(),
                    'version' => $themeData['version'] ?? '1.0.0',
                    'data' => $themeData['data'] ?? []
                ];
            }
        }
        
        return $themes;
    }
    
    /**
     * Theme löschen
     */
    public function deleteTheme(string $themeName): bool
    {
        $files = [
            $this->dataPath . 'themes/saved/' . $themeName . '.json',
            $this->dataPath . 'themes/compiled/' . $themeName . '.css',
            $this->assetsPath . 'themes/compiled/' . $themeName . '.css'
        ];
        
        $success = true;
        
        foreach ($files as $file) {
            if (file_exists($file) && !unlink($file)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Array flach machen für LESS-Variablen
     * HINWEIS: Keine automatische Konvertierung mehr von _ zu -
     * Alle Widget-Keys müssen bereits im korrekten UIkit-Format sein (mit Hyphens)
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // Für Widget-Kategorien: Direkt die Werte ohne Kategorie-Präfix verarbeiten
                // "spacing" wird vom Live Theme Editor für global-margin/global-gutter genutzt
                // (siehe LiveThemeState::FIELDS) - ohne Whitelist-Eintrag würde daraus
                // "spacing-global-margin" statt der von UIkit erwarteten "@global-margin".
                $widgetCategories = ['colors', 'typography', 'breakpoints', 'borders', 'shadows', 'spacing'];
                
                if ($prefix === '' && in_array($key, $widgetCategories)) {
                    // Widget-Daten direkt verarbeiten ohne Kategorie-Präfix
                    $result = array_merge($result, $this->flattenArray($value, ''));
                } else {
                    // Normale Verarbeitung mit Präfix (mit Hyphen)
                    $newKey = $prefix ? $prefix . '-' . $key : $key;
                    $result = array_merge($result, $this->flattenArray($value, $newKey));
                }
            } else {
                // Keys sollten bereits in korrektem Format sein (mit Hyphens)
                $newKey = $prefix ? $prefix . '-' . $key : $key;
                
                // Nur echte UIKit-Variablen durchlassen
                if ($this->isValidUikitVariable($newKey)) {
                    $result[$newKey] = $value;
                }
            }
        }
        
        return $result;
    }

    /**
     * Prüft ob eine Variable eine echte UIKit-Variable ist
     */
    private function isValidUikitVariable(string $varName): bool
    {
        // Für jetzt alle Variablen durchlassen, die sinnvoll aussehen
        $excludePatterns = [
            'breakpoint-2xlarge', // Gibt es nicht in UIKit
        ];
        
        foreach ($excludePatterns as $pattern) {
            if (strpos($varName, $pattern) !== false) {
                // Debug: Was wird ausgeschlossen
                error_log("UIKit Variable excluded: $varName (matches pattern: $pattern)");
                return false;
            }
        }
        
        // Debug: Was wird durchgelassen
        if (strpos($varName, 'breakpoint') !== false) {
            error_log("UIKit Breakpoint variable allowed: $varName");
        }
        
        return true; // Alle anderen durchlassen
    }
    
    /**
     * Google Fonts CSS-Links für Templates und Preview generieren
     * 
     * @param string|null $themeName Theme-Name (optional, wenn null wird das aktive Theme verwendet)
     * @return string HTML <link> Tags für Google Fonts
     */
    public static function getGoogleFontsHtml(?string $themeName = null): string
    {
        try {
            // Aktuelles Theme laden wenn kein Name angegeben
            if ($themeName === null) {
                $themeName = self::getActiveTheme();
                if (!$themeName) {
                    return '';
                }
            }
            
            // Google Fonts aus Cache-Datei laden (effizient!)
            $usedGoogleFonts = self::getThemeGoogleFonts($themeName);
            
            if (empty($usedGoogleFonts)) {
                // DEBUG-Log
                if (\rex::isDebugMode()) {
                    \rex_logger::factory()->debug("getGoogleFontsHtml - No Google Fonts found for theme '$themeName'");
                }
                return '';
            }
            
            // CSS-Links generieren
            $html = "<!-- UIKit Theme Builder - Google Fonts for theme: $themeName -->\n";
            foreach ($usedGoogleFonts as $fontFamily) {
                $cssUrl = \rex_url::assets('addons/uikit_theme_builder/fonts/' . self::sanitizeFontName($fontFamily) . '.css');
                $html .= '<link rel="stylesheet" href="' . $cssUrl . '">' . "\n";
                
                // DEBUG-Log
                if (\rex::isDebugMode()) {
                    \rex_logger::factory()->debug("getGoogleFontsHtml - Adding font: $fontFamily -> $cssUrl");
                }
            }
            
            return $html;
            
        } catch (\Exception $e) {
            \rex_logger::factory()->log('error', 'Error generating Google Fonts HTML: ' . $e->getMessage(), [], 'uikit_theme_builder');
            return '';
        }
    }
    
    /**
     * Aktives Theme ermitteln
     */
    public static function getActiveTheme(): ?string
    {
        $configFile = \rex_path::addonData('uikit_theme_builder') . 'config.json';
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            return $config['active_theme'] ?? null;
        }
        return null;
    }
    
    /**
     * Aktives Theme setzen
     */
    public static function setActiveTheme(string $themeName): bool
    {
        try {
            $configFile = \rex_path::addonData('uikit_theme_builder') . 'config.json';
            
            // Bestehende Config laden oder neue erstellen
            $config = [];
            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true) ?: [];
            }
            
            // Theme setzen
            $config['active_theme'] = $themeName;
            
            // Config speichern
            $success = file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
            
            return $success !== false;
            
        } catch (\Exception $e) {
            \rex_logger::factory()->log('error', 'Error setting active theme: ' . $e->getMessage(), [], 'uikit_theme_builder');
            return false;
        }
    }
    
    /**
     * Preview HTML für ein Theme generieren
     * 
     * @param string $themeName Theme-Name
     * @return string|null HTML-Inhalt oder null bei Fehler
     */
    public static function generatePreviewHtml(string $themeName): ?string
    {
        try {
            // Template-Datei laden
            $templateFile = \rex_path::addon('uikit_theme_builder') . 'sources/preview-template.html';
            if (!file_exists($templateFile)) {
                return null;
            }
            
            $template = file_get_contents($templateFile);
            
            // CSS URL für das spezifische Theme (nicht das Standard UIKit)
            $cssUrl = \rex_url::addonAssets('uikit_theme_builder', 'themes/compiled/' . $themeName . '.css?v=' . time());
            $jsUrl = \rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/js/uikit.min.js');
            
            // Prüfen ob das kompilierte Theme existiert
            $compiledThemeFile = \rex_path::addonAssets('uikit_theme_builder', 'themes/compiled/' . $themeName . '.css');
            if (!file_exists($compiledThemeFile)) {
                // Theme noch nicht kompiliert - verwende UIKit Standard aus compiled_uikit
                $cssUrl = \rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/css/uikit.min.css');
            }
            
            // Platzhalter ersetzen
            $html = str_replace('{{THEME_CSS_URL}}', $cssUrl, $template);
            $html = str_replace('{{THEME_JS_URL}}', $jsUrl, $html);
            
            // Google Fonts einbinden (falls vorhanden)
            $googleFontsHtml = self::getGoogleFontsHtml($themeName);
            if ($googleFontsHtml) {
                // Google Fonts vor dem Theme CSS einfügen
                $html = str_replace('{{THEME_CSS_URL}}', $googleFontsHtml . "\n    " . '{{THEME_CSS_URL}}', $html);
                $html = str_replace('{{THEME_CSS_URL}}', $cssUrl, $html);
            }
            
            return $html;
            
        } catch (\Exception $e) {
            \rex_logger::factory()->log('error', 'Error generating preview HTML: ' . $e->getMessage(), [], 'uikit_theme_builder');
            return null;
        }
    }

    /**
     * Verfügbare Themes auflisten
     */
    public static function getAvailableThemes(): array
    {
        $themesDir = \rex_path::addonData('uikit_theme_builder') . 'themes/';
        $themes = [];
        
        if (is_dir($themesDir)) {
            $files = glob($themesDir . '*.json');
            foreach ($files as $file) {
                $themeName = basename($file, '.json');
                $themes[] = $themeName;
            }
        }
        
        return $themes;
    }
    
    /**
     * Verwendete Google Fonts aus Theme-Daten extrahieren
     */
    private static function extractUsedGoogleFonts(array $themeData): array
    {
        $usedFonts = [];
        $fontManager = new GoogleFontsManager();
        $downloadedFonts = $fontManager->getDownloadedFonts();
        
        // DEBUG: Heruntergeladene Fonts anzeigen
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('extractUsedGoogleFonts - Downloaded Fonts: ' . print_r(array_keys($downloadedFonts), true));
        }
        
        // Alle Font-Werte aus den Theme-Daten sammeln
        $flatData = self::flattenArrayStatic($themeData);
        
        // DEBUG: Alle flachen Theme-Daten anzeigen
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('extractUsedGoogleFonts - Flat theme data: ' . print_r($flatData, true));
        }
        
        foreach ($flatData as $key => $value) {
            // Nur Font-bezogene Keys prüfen
            if (strpos($key, 'font') !== false && is_string($value) && !empty($value)) {
                // DEBUG: Font-Key gefunden
                if (\rex::isDebugMode()) {
                    \rex_logger::factory()->debug("extractUsedGoogleFonts - Found font key: $key = $value");
                }
                
                // Prüfen ob es eine heruntergeladene Google Font ist
                $fontFamily = trim(explode(',', $value)[0], '"\''); // Erste Font aus der Familie
                
                // DEBUG: Extrahierte Font-Familie
                if (\rex::isDebugMode()) {
                    \rex_logger::factory()->debug("extractUsedGoogleFonts - Extracted font family: '$fontFamily'");
                }
                
                if (isset($downloadedFonts[$fontFamily])) {
                    $usedFonts[] = $fontFamily;
                    
                    // DEBUG: Font gefunden
                    if (\rex::isDebugMode()) {
                        \rex_logger::factory()->debug("extractUsedGoogleFonts - Font found in downloaded fonts: '$fontFamily'");
                    }
                } else {
                    // DEBUG: Font nicht gefunden
                    if (\rex::isDebugMode()) {
                        \rex_logger::factory()->debug("extractUsedGoogleFonts - Font NOT found in downloaded fonts: '$fontFamily'");
                    }
                }
            }
        }
        
        // DEBUG: Resultat
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('extractUsedGoogleFonts - Result: ' . print_r($usedFonts, true));
        }
        
        return array_unique($usedFonts);
    }
    
    /**
     * Google Fonts für ein Theme extrahieren und separat speichern
     */
    private function saveThemeGoogleFonts(string $themeName, array $themeData): void
    {
        $usedFonts = self::extractUsedGoogleFonts($themeData);
        
        // Font-Informationen für dieses Theme speichern
        $fontInfo = [
            'theme' => $themeName,
            'updated' => date('Y-m-d H:i:s'),
            'fonts' => $usedFonts
        ];
        
        $filename = $this->dataPath . 'themes/google_fonts/' . $themeName . '.json';
        
        // Verzeichnis erstellen falls nicht vorhanden
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($filename, json_encode($fontInfo, JSON_PRETTY_PRINT));
        
        // DEBUG-Log
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug("saveThemeGoogleFonts - Saved fonts for theme '$themeName': " . print_r($usedFonts, true));
        }
    }
    
    /**
     * Google Fonts für ein Theme aus der Cache-Datei laden
     */
    public static function getThemeGoogleFonts(string $themeName): array
    {
        $dataPath = \rex_path::addonData('uikit_theme_builder');
        $filename = $dataPath . 'themes/google_fonts/' . $themeName . '.json';
        
        if (!file_exists($filename)) {
            // DEBUG-Log
            if (\rex::isDebugMode()) {
                \rex_logger::factory()->debug("getThemeGoogleFonts - No font cache found for theme '$themeName'");
            }
            return [];
        }
        
        $fontInfo = json_decode(file_get_contents($filename), true);
        $fonts = $fontInfo['fonts'] ?? [];
        
        // DEBUG-Log
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug("getThemeGoogleFonts - Loaded fonts for theme '$themeName': " . print_r($fonts, true));
        }
        
        return $fonts;
    }
    
    /**
     * Statische Version von flattenArray für static Methoden
     */
    private static function flattenArrayStatic(array $array, string $prefix = ''): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '_' . $key : $key;
            
            if (is_array($value)) {
                $result = array_merge($result, self::flattenArrayStatic($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Font-Namen für Dateisystem bereinigen (statische Version)
     */
    private static function sanitizeFontName(string $fontName): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $fontName);
    }
    
    /**
     * Extra Styles CSS abrufen und in Theme-Compilation integrieren
     * Unterstützt sowohl die alte Tabelle als auch die neuen Style-Sets
     * 
     * @param string $themeName Theme-Name für Theme-spezifische Filterung
     * @param array $themeData Theme-Daten mit Style-Set-Auswahl
     * @return string|null CSS Content oder null wenn Extra Styles nicht verfügbar
     */
    private function getExtraStylesCss(string $themeName, array $themeData = []): ?string
    {
        $css = '';
        
        // 1. Neue Style-Sets verarbeiten (bevorzugt)
        if (!empty($themeData['style_sets']['selected_style_sets'])) {
            $styleSetManager = new StyleSetManager();
            $selectedStyleSetIds = $themeData['style_sets']['selected_style_sets'];
            
            foreach ($selectedStyleSetIds as $styleSetId) {
                $styleSet = $styleSetManager->getStyleSetById($styleSetId);
                if ($styleSet && $styleSet['is_active']) {
                    $styleSetCss = $styleSetManager->generateCssForStyleSet($styleSet);
                    if ($styleSetCss) {
                        $css .= "\n\n/* Style-Set: " . $styleSet['name'] . " */\n";
                        $css .= $styleSetCss;
                    }
                }
            }
        }
        
        // 2. Alte Extra Styles Tabelle (für Rückwärtskompatibilität)
        $legacyCss = $this->getLegacyExtraStylesCss($themeName);
        if ($legacyCss) {
            $css .= "\n\n/* Legacy Extra Styles */\n" . $legacyCss;
        }
        
        return !empty($css) ? $css : null;
    }
    
    /**
     * Legacy Extra Styles aus alter Tabelle
     */
    private function getLegacyExtraStylesCss(string $themeName): ?string
    {
        // Prüfen ob rex_theme_builder_extra Tabelle existiert
        $sql = \rex_sql::factory();
        try {
            $sql->setQuery("SHOW TABLES LIKE 'rex_theme_builder_extra'");
            if (!$sql->getRows()) {
                return null; // Tabelle existiert nicht
            }
        } catch (\Exception $e) {
            return null;
        }
        
        try {
            // Extra Styles für dieses Theme oder "all" laden
            $sql = \rex_sql::factory();
            $query = "
                SELECT css_styles 
                FROM " . \rex::getTable('theme_builder_extra') . " 
                WHERE status = 'active' 
                AND (
                    compile_themes IS NULL 
                    OR compile_themes = '' 
                    OR compile_themes = 'all'
                    OR JSON_CONTAINS(compile_themes, ?)
                    OR JSON_CONTAINS(compile_themes, '\"all\"')
                )
                ORDER BY priority ASC, name ASC
            ";
            
            $sql->setQuery($query, [json_encode($themeName)]);
            
            $cssStyles = [];
            for ($i = 0; $i < $sql->getRows(); $i++) {
                $css = $sql->getValue('css_styles');
                if (!empty($css)) {
                    $cssStyles[] = $css;
                }
                $sql->next();
            }
            
            if (empty($cssStyles)) {
                return null;
            }
            
            // CSS zusammenführen
            $finalCss = "/* === EXTRA STYLES INTEGRATION für Theme: $themeName === */\n";
            $finalCss .= implode("\n\n", $cssStyles);
            $finalCss .= "\n/* === END EXTRA STYLES === */\n";
            
            return $finalCss;
            
        } catch (\Exception $e) {
            // Bei Fehlern in Extra Styles still fail
            if (\rex::isDebugMode()) {
                \rex_logger::factory()->warning('Extra Styles Integration Fehler: ' . $e->getMessage());
            }
        }
        
        return null;
    }
    
    /**
     * Theme-Farben in Datenbank cachen
     * Speichert alle wichtigen Farben aus dem Theme für schnellen Zugriff durch DomainContext
     */
    private function cacheThemeColors(string $themeName, array $themeData): void
    {
        // Farb-Definitionen aus Theme extrahieren
        $colors = $themeData['colors'] ?? [];
        
        // Mapping: color_type => [ui_class, color_label]
        $colorMap = [
            'global-primary-background' => ['uk-card-primary', 'Primary'],
            'global-secondary-background' => ['uk-card-secondary', 'Secondary'],
            'card-default-background' => ['uk-card-default', 'Default'],
            'global-muted-background' => ['uk-card-muted', 'Muted'],
            'global-background' => ['uk-background-default', 'Standard Hintergrund'],
            'global-inverse-color' => ['uk-light', 'Hell (auf dunklem Hintergrund)'],
        ];
        
        // Alte Einträge für dieses Theme löschen
        $sql = \rex_sql::factory();
        $sql->setQuery('DELETE FROM ' . \rex::getTable('uikit_theme_colors') . ' WHERE theme_name = ?', [$themeName]);
        
        // Neue Einträge speichern
        foreach ($colorMap as $colorType => $info) {
            $colorValue = $colors[$colorType] ?? '';
            
            if (!empty($colorValue)) {
                $sql = \rex_sql::factory();
                $sql->setTable(\rex::getTable('uikit_theme_colors'));
                $sql->setValue('theme_name', $themeName);
                $sql->setValue('color_type', $colorType);
                $sql->setValue('color_value', $colorValue);
                $sql->setValue('color_label', $info[1]);
                $sql->setValue('ui_class', $info[0]);
                
                try {
                    $sql->insert();
                } catch (\rex_sql_exception $e) {
                    if (\rex::isDebugMode()) {
                        \rex_logger::factory()->error("cacheThemeColors - Error caching color '$colorType': " . $e->getMessage());
                    }
                }
            }
        }
        
        // Cache leeren damit DomainContext frische Daten holt
        if (class_exists('UikitThemeBuilder\DomainContext')) {
            \UikitThemeBuilder\DomainContext::clearCache();
        }
        
        // DEBUG-Log
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug("cacheThemeColors - Cached " . count($colorMap) . " colors for theme '$themeName'");
        }
    }
}
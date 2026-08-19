<?php

namespace UikitThemeBuilder;

/**
 * Zentrale Pfadverwaltung für UIKit Theme Builder
 * Alle Pfade werden aus der package.yml gelesen und sind zentral konfigurierbar
 */
class PathManager
{
    private static ?array $paths = null;
    
    /**
     * Lädt die Pfad-Konfiguration aus package.yml
     */
    private static function loadPaths(): array
    {
        if (self::$paths === null) {
            // Fallback-Werte falls Config leer ist
            $defaults = [
                'source' => 'build/src',
                'less' => 'build/src/less',
                'images' => 'build/src/images',
                'styles_file' => 'build/src/styles.less',
                'dist' => 'assets/dist',
                'themes_saved' => 'themes/saved',
                'themes_compiled' => 'themes/compiled',
                'themes_compiled_public' => 'assets/addons/uikit_theme_builder/themes/compiled',
                'themes_temp' => 'cache/themes/temp'
            ];
            
            // Config-Werte einzeln laden (REDAXO speichert als flache Schlüssel)
            $config = [];
            foreach (array_keys($defaults) as $key) {
                $configValue = \rex_config::get('uikit_theme_builder', "paths.{$key}");
                if ($configValue !== null) {
                    $config[$key] = $configValue;
                }
            }
            
            // DEBUG: Config anzeigen
            if (\rex::isDebugMode()) {
                \rex_logger::factory()->debug('PathManager Config loaded: ' . print_r($config, true), [], 'uikit_theme_builder');
            }
            
            self::$paths = array_merge($defaults, $config);
            
            // DEBUG: Merged Paths anzeigen
            if (\rex::isDebugMode()) {
                \rex_logger::factory()->debug('PathManager Final Paths: ' . print_r(self::$paths, true), [], 'uikit_theme_builder');
            }
        }
        
        return self::$paths;
    }
    
    /**
     * Gibt den absoluten Pfad für einen konfigurierten Ordner/Datei zurück
     * 
     * @param string $key Schlüssel aus der paths Config
     * @param string $subPath Optionaler Unterpfad
     * @return string Absoluter Pfad
     */
    public static function getPath(string $key, string $subPath = ''): string
    {
        $paths = self::loadPaths();
        
        if (!isset($paths[$key])) {
            throw new \InvalidArgumentException("Unknown path key: {$key}");
        }
        
        $configuredPath = $paths[$key];
        
        // Für öffentliche Theme-Pfade: Frontend-Root verwenden
        if ($key === 'themes_compiled_public') {
            $basePath = \rex_path::frontend($configuredPath);
        }
        // Für Theme-Verzeichnisse: Addon Data Path verwenden
        elseif (str_starts_with($key, 'themes_')) {
            // Prüfe ob es ein Cache-Pfad ist
            if (str_starts_with($configuredPath, 'cache/')) {
                // Cache-Pfad: verwende Addon Cache Path
                $cachePath = str_replace('cache/', '', $configuredPath);
                $basePath = \rex_path::addonCache('uikit_theme_builder', $cachePath);
            } else {
                // Normaler Theme-Pfad: verwende Addon Data Path
                $basePath = \rex_path::addonData('uikit_theme_builder', $configuredPath);
            }
        } else {
            // Für andere Pfade: Addon-Root verwenden
            $basePath = \rex_path::addon('uikit_theme_builder', $configuredPath);
        }
        
        if (!empty($subPath)) {
            $basePath = rtrim($basePath, '/') . '/' . ltrim($subPath, '/');
        }
        
        return $basePath;
    }
    
    /**
     * Gibt die URL für einen Pfad zurück (für Assets)
     * 
     * @param string $key Schlüssel aus der paths Config
     * @param string $subPath Optionaler Unterpfad
     * @return string URL für Assets
     */
    public static function getUrl(string $key, string $subPath = ''): string
    {
        $paths = self::loadPaths();
        
        if (!isset($paths[$key])) {
            throw new \InvalidArgumentException("Unknown path key: {$key}");
        }
        
        $configuredPath = $paths[$key];
        
        if (!empty($subPath)) {
            $configuredPath = rtrim($configuredPath, '/') . '/' . ltrim($subPath, '/');
        }
        
        // Für öffentliche Theme-Pfade: Frontend URL verwenden
        if ($key === 'themes_compiled_public') {
            return \rex_url::frontend($configuredPath);
        }
        
        // Für Theme-Verzeichnisse: Addon Data URL verwenden (normalerweise nicht öffentlich zugänglich)
        if (str_starts_with($key, 'themes_')) {
            // Theme-Verzeichnisse sind normalerweise nicht über URL erreichbar
            // Falls doch nötig, müsste eine spezielle Route erstellt werden
            throw new \InvalidArgumentException("Theme directories are not accessible via URL: {$key}. Use themes_compiled_public for public access.");
        }
        
        // Für andere Pfade: Addon Assets URL verwenden
        return \rex_url::addonAssets('uikit_theme_builder', $configuredPath);
    }
    
    /**
     * Gibt den Source-Ordner zurück (build/src)
     */
    public static function getSourcePath(string $subPath = ''): string
    {
        return self::getPath('source', $subPath);
    }
    
    /**
     * Gibt den LESS-Ordner zurück (build/src/less)
     */
    public static function getLessPath(string $subPath = ''): string
    {
        return self::getPath('less', $subPath);
    }
    
    /**
     * Gibt den Images-Ordner zurück (build/src/images)
     */
    public static function getImagesPath(string $subPath = ''): string
    {
        return self::getPath('images', $subPath);
    }
    
    /**
     * Gibt den Pfad zur styles.less Datei zurück
     */
    public static function getStylesFilePath(): string
    {
        return self::getPath('styles_file');
    }
    
    /**
     * Gibt den Dist-Ordner zurück (assets/dist)
     */
    public static function getDistPath(string $subPath = ''): string
    {
        return self::getPath('dist', $subPath);
    }
    
    /**
     * Gibt URL für Dist Assets zurück
     */
    public static function getDistUrl(string $subPath = ''): string
    {
        return self::getUrl('dist', $subPath);
    }
    
    /**
     * Gibt den Pfad für gespeicherte Themes zurück (Addon Data)
     */
    public static function getThemesSavedPath(string $subPath = ''): string
    {
        return self::getPath('themes_saved', $subPath);
    }
    
    /**
     * Gibt den Pfad für kompilierte Themes zurück (Addon Data)
     */
    public static function getThemesCompiledPath(string $subPath = ''): string
    {
        return self::getPath('themes_compiled', $subPath);
    }
    
    /**
     * Gibt den Pfad für temporäre Theme-Dateien zurück (Addon Cache)
     */
    public static function getThemesTempPath(string $subPath = ''): string
    {
        return self::getPath('themes_temp', $subPath);
    }
    
    /**
     * Gibt den öffentlichen Pfad für kompilierte Themes zurück (Frontend)
     */
    public static function getThemesCompiledPublicPath(string $subPath = ''): string
    {
        return self::getPath('themes_compiled_public', $subPath);
    }
    
    /**
     * Gibt die öffentliche URL für kompilierte Themes zurück
     */
    public static function getThemesCompiledPublicUrl(string $subPath = ''): string
    {
        return self::getUrl('themes_compiled_public', $subPath);
    }
    
    /**
     * Leert das temporäre Cache-Verzeichnis
     */
    public static function clearTempCache(): bool
    {
        try {
            $tempPath = self::getThemesTempPath();
            if (is_dir($tempPath)) {
                \rex_dir::delete($tempPath);
                \rex_dir::create($tempPath);
                return true;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Gibt Informationen über Cache-Größe zurück
     */
    public static function getCacheInfo(): array
    {
        $tempPath = self::getThemesTempPath();
        $size = 0;
        $files = 0;
        
        if (is_dir($tempPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempPath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                    $files++;
                }
            }
        }
        
        return [
            'path' => $tempPath,
            'size_bytes' => $size,
            'size_human' => self::formatBytes($size),
            'files_count' => $files,
            'exists' => is_dir($tempPath)
        ];
    }
    
    /**
     * Formatiert Bytes in menschenlesbare Größe
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Stellt sicher dass alle konfigurierten Verzeichnisse existieren
     */
    public static function ensureDirectoriesExist(): void
    {
        $paths = self::loadPaths();
        
        $directoriesToCreate = [
            'source',
            'less', 
            'images',
            'dist',
            'themes_saved',
            'themes_compiled', 
            'themes_compiled_public',  // Öffentlicher Theme-Ordner
            'themes_temp'  // Cache-Verzeichnis wird auch erstellt
        ];
        
        foreach ($directoriesToCreate as $key) {
            if (isset($paths[$key])) {
                $fullPath = self::getPath($key);
                if (!is_dir($fullPath)) {
                    \rex_dir::create($fullPath);
                }
            }
        }
    }
    
    /**
     * Gibt alle konfigurierten Pfade zurück
     */
    public static function getAllPaths(): array
    {
        return self::loadPaths();
    }
    
    /**
     * Erneuert den Cache (z.B. nach Config-Änderungen)
     */
    public static function clearCache(): void
    {
        self::$paths = null;
    }
    
    /**
     * Debug-Funktion: Zeigt aktuelle Pfad-Konfiguration
     */
    public static function debugPaths(): array
    {
        // Einzelne Config-Werte laden (so wie REDAXO sie speichert)
        $configRaw = [];
        $allPaths = [
            'source', 'less', 'images', 'styles_file', 'dist', 
            'themes_saved', 'themes_compiled', 'themes_compiled_public', 'themes_temp'
        ];
        
        foreach ($allPaths as $key) {
            $configValue = \rex_config::get('uikit_theme_builder', "paths.{$key}");
            $configRaw["paths.{$key}"] = $configValue;
        }
        
        $paths = self::loadPaths();
        
        return [
            'config_raw' => $configRaw,
            'paths_final' => $paths,
            'themes_compiled_public' => [
                'config_key' => 'paths.themes_compiled_public',
                'config_value' => \rex_config::get('uikit_theme_builder', 'paths.themes_compiled_public', 'not_set'),
                'final_value' => $paths['themes_compiled_public'] ?? 'not_set',
                'full_path' => self::getPath('themes_compiled_public')
            ],
            'themes_saved' => [
                'config_key' => 'paths.themes_saved',
                'config_value' => \rex_config::get('uikit_theme_builder', 'paths.themes_saved', 'not_set'),
                'final_value' => $paths['themes_saved'] ?? 'not_set',
                'full_path' => self::getPath('themes_saved'),
                'directory_exists' => is_dir(self::getPath('themes_saved'))
            ]
        ];
    }
}
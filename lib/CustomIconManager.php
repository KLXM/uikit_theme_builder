<?php

namespace UikitThemeBuilder;

/**
 * Custom Icon Manager
 * Verwaltet Upload, Validierung und Metadaten für Custom Icons
 */
class CustomIconManager
{
    private string $iconsDir;
    private string $assetsIconsDir;
    private string $metaFile;
    
    public function __construct()
    {
        $this->iconsDir = \rex_path::addonData('uikit_theme_builder', 'icons/');
        $this->assetsIconsDir = \rex_path::addonData('uikit_theme_builder', 'icons/custom/');
        $this->metaFile = \rex_path::addonData('uikit_theme_builder', 'custom-icons.json');
        
        // Verzeichnisse erstellen
        if (!is_dir($this->iconsDir)) {
            \rex_dir::create($this->iconsDir);
        }
        if (!is_dir($this->assetsIconsDir)) {
            \rex_dir::create($this->assetsIconsDir);
        }

        $this->migrateLegacyIconsFromAddonAssets();
    }

    /**
     * Bestehende Icons aus altem addonAssets-Pfad nach addonData migrieren.
     */
    private function migrateLegacyIconsFromAddonAssets(): void
    {
        $legacyDir = \rex_path::addonAssets('uikit_theme_builder', 'icons/custom/');
        if (!is_dir($legacyDir)) {
            return;
        }

        $legacyFiles = glob($legacyDir . '*.svg');
        if (!is_array($legacyFiles) || [] === $legacyFiles) {
            return;
        }

        foreach ($legacyFiles as $legacyFile) {
            $targetFile = $this->assetsIconsDir . basename($legacyFile);
            if (!file_exists($targetFile)) {
                \rex_file::copy($legacyFile, $targetFile);
            }
        }
    }
    
    /**
     * Custom Icon hochladen
     */
    public function uploadIcon(array $file, string $name, string $category = 'custom', array $tags = []): array
    {
        try {
            // Name validieren
            $name = $this->sanitizeIconName($name);
            if (empty($name)) {
                return ['success' => false, 'message' => 'Ungültiger Icon-Name'];
            }
            
            // Prüfen ob Name bereits existiert
            if ($this->iconExists($name)) {
                return ['success' => false, 'message' => 'Icon mit diesem Namen existiert bereits'];
            }
            
            // Datei validieren
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => 'Upload-Fehler'];
            }
            
            if ($file['size'] > 51200) { // 50KB
                return ['success' => false, 'message' => 'Datei zu groß (max. 50KB)'];
            }
            
            $svgContent = file_get_contents($file['tmp_name']);
            
            // SVG validieren
            $validation = $this->validateSvg($svgContent);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // SVG normalisieren
            $normalizedSvg = $this->normalizeSvg($svgContent);
            
            // Icon speichern
            $iconFile = $this->assetsIconsDir . $name . '.svg';
            file_put_contents($iconFile, $normalizedSvg);
            
            // Metadaten speichern
            $this->saveIconMeta($name, [
                'category' => $category,
                'tags' => $tags,
                'uploaded' => date('Y-m-d H:i:s'),
                'file' => $iconFile,
                'original_size' => $file['size'],
                'normalized_size' => strlen($normalizedSvg)
            ]);
            
            // Icons neu kompilieren
            $builder = new CustomIconBuilder();
            $builder->rebuild();
            
            return [
                'success' => true,
                'message' => "Icon '{$name}' erfolgreich hochgeladen",
                'icon_name' => 'custom-' . $name
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Icon löschen
     */
    public function deleteIcon(string $name): array
    {
        try {
            $iconFile = $this->assetsIconsDir . $name . '.svg';
            
            if (!file_exists($iconFile)) {
                return ['success' => false, 'message' => 'Icon nicht gefunden'];
            }
            
            // Datei löschen
            unlink($iconFile);
            
            // Metadaten entfernen
            $this->removeIconMeta($name);
            
            // Icons neu kompilieren
            $builder = new CustomIconBuilder();
            $builder->rebuild();
            
            return ['success' => true, 'message' => "Icon '{$name}' gelöscht"];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Alle Custom Icons abrufen
     */
    public function getCustomIcons(): array
    {
        $icons = [];
        $meta = $this->loadMetadata();
        
        $files = glob($this->assetsIconsDir . '*.svg');
        foreach ($files as $file) {
            $name = basename($file, '.svg');
            $icons[$name] = [
                'name' => $name,
                'full_name' => 'custom-' . $name,
                'file' => $file,
                'url' => '',
                'svg' => file_get_contents($file),
                'category' => $meta[$name]['category'] ?? 'custom',
                'tags' => $meta[$name]['tags'] ?? [],
                'uploaded' => $meta[$name]['uploaded'] ?? null,
                'size' => filesize($file)
            ];
        }
        
        return $icons;
    }
    
    /**
     * SVG validieren
     */
    private function validateSvg(string $content): array
    {
        // XML-Validierung
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        
        if ($xml === false) {
            return ['valid' => false, 'message' => 'Ungültiges SVG-Format'];
        }
        
        // Prüfe auf gefährliche Tags
        $dangerous = ['script', 'object', 'embed', 'iframe', 'link'];
        foreach ($dangerous as $tag) {
            if (stripos($content, '<' . $tag) !== false) {
                return ['valid' => false, 'message' => 'SVG enthält nicht erlaubte Tags'];
            }
        }
        
        // Prüfe auf externe Referenzen
        if (preg_match('/(href|src|xlink:href)\s*=\s*["\']https?:/', $content)) {
            return ['valid' => false, 'message' => 'SVG enthält externe Referenzen'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * SVG normalisieren für UIkit
     */
    private function normalizeSvg(string $content): string
    {
        // XML-Deklaration entfernen
        $content = preg_replace('/<\?xml[^?]+\?>\s*/', '', $content);
        
        // Whitespace normalisieren
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        // Originale viewBox extrahieren (falls vorhanden)
        $originalViewBox = '0 0 20 20';
        if (preg_match('/viewBox\s*=\s*["\']([^"\']+)["\']/', $content, $matches)) {
            $originalViewBox = $matches[1];
        } else {
            // Versuche aus width/height abzuleiten
            $width = 20;
            $height = 20;
            if (preg_match('/width\s*=\s*["\']?([0-9.]+)["\']?/', $content, $w)) {
                $width = $w[1];
            }
            if (preg_match('/height\s*=\s*["\']?([0-9.]+)["\']?/', $content, $h)) {
                $height = $h[1];
            }
            $originalViewBox = "0 0 $width $height";
        }
        
        // Alle viewBox, width, height entfernen
        $content = preg_replace('/\s+viewBox\s*=\s*["\'][^"\']*["\']/', '', $content);
        $content = preg_replace('/\s+width\s*=\s*["\'][^"\']*["\']/', '', $content);
        $content = preg_replace('/\s+height\s*=\s*["\'][^"\']*["\']/', '', $content);
        
        // UIKit Standard: width="20" height="20" aber originale viewBox beibehalten
        $content = preg_replace('/<svg/', '<svg width="20" height="20" viewBox="' . $originalViewBox . '"', $content, 1);
        
        // xmlns sicherstellen
        $hasXmlns = preg_match('/xmlns\s*=\s*["\']http:\/\/www\.w3\.org\/2000\/svg["\']/', $content);
        if (!$hasXmlns) {
            $content = preg_replace('/<svg/', '<svg xmlns="http://www.w3.org/2000/svg"', $content, 1);
        }
        // Doppelte xmlns entfernen
        $content = preg_replace('/(xmlns="http:\/\/www\.w3\.org\/2000\/svg")(\s+xmlns="http:\/\/www\.w3\.org\/2000\/svg")+/', '$1', $content);
        
        // Kommentare entfernen (z.B. FontAwesome Lizenz)
        $content = preg_replace('/<!--.*?-->/s', '', $content);
        
        // Finale Whitespace-Bereinigung
        $content = preg_replace('/\s+/', ' ', $content);
        $content = preg_replace('/>\s+</', '><', $content);
        $content = trim($content);
        
        return $content;
    }
    
    /**
     * Icon-Name bereinigen
     */
    public function sanitizeIconName(string $name): string
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);
        $name = trim($name, '-');
        return $name;
    }
    
    /**
     * Prüfen ob Icon existiert
     */
    public function iconExists(string $name): bool
    {
        return file_exists($this->assetsIconsDir . $name . '.svg');
    }
    
    /**
     * Icon neu normalisieren (überschreibt bestehende Datei)
     */
    public function renormalizeIcon(string $name): array
    {
        try {
            $iconFile = $this->assetsIconsDir . $name . '.svg';
            
            if (!file_exists($iconFile)) {
                return ['success' => false, 'message' => 'Icon nicht gefunden'];
            }
            
            // Original-SVG laden
            $svgContent = file_get_contents($iconFile);
            
            // SVG validieren
            $validation = $this->validateSvg($svgContent);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // SVG neu normalisieren
            $normalizedSvg = $this->normalizeSvg($svgContent);
            
            // Icon überschreiben
            file_put_contents($iconFile, $normalizedSvg);
            
            // Metadaten aktualisieren
            $meta = $this->loadMetadata();
            if (isset($meta[$name])) {
                $meta[$name]['normalized_size'] = strlen($normalizedSvg);
                $meta[$name]['renormalized'] = date('Y-m-d H:i:s');
                $this->saveIconMeta($name, $meta[$name]);
            }
            
            return [
                'success' => true,
                'message' => "Icon '$name' erfolgreich neu normalisiert"
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Metadaten laden
     */
    private function loadMetadata(): array
    {
        if (!file_exists($this->metaFile)) {
            return [];
        }
        
        $data = json_decode(file_get_contents($this->metaFile), true);
        return is_array($data) ? $data : [];
    }
    
    /**
     * Icon-Metadaten speichern
     */
    private function saveIconMeta(string $name, array $meta): void
    {
        $allMeta = $this->loadMetadata();
        $allMeta[$name] = $meta;
        file_put_contents($this->metaFile, json_encode($allMeta, JSON_PRETTY_PRINT));
    }
    
    /**
     * Icon-Metadaten entfernen
     */
    private function removeIconMeta(string $name): void
    {
        $allMeta = $this->loadMetadata();
        unset($allMeta[$name]);
        file_put_contents($this->metaFile, json_encode($allMeta, JSON_PRETTY_PRINT));
    }
    
    /**
     * Statistiken abrufen
     */
    public function getStats(): array
    {
        $icons = $this->getCustomIcons();
        return [
            'total' => count($icons),
            'total_size' => array_sum(array_column($icons, 'size')),
            'categories' => array_unique(array_column($icons, 'category'))
        ];
    }
}

<?php

namespace UikitThemeBuilder;

/**
 * Preview Controller - Saubere Preview ohne Backend-CSS
 */
class PreviewController
{
    /**
     * Clean Preview ohne Backend-Styles anzeigen
     */
    public function showPreview(string $themeName): void
    {
        // Response Headers setzen
        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: SAMEORIGIN'); // Erlaubt iframe im gleichen Origin
        
        // Template laden
        $templateFile = \rex_path::addon('uikit_theme_builder') . 'sources/preview-template.html';
        if (!file_exists($templateFile)) {
            $this->showError('Preview-Template nicht gefunden');
            return;
        }
        
        $template = file_get_contents($templateFile);
        
        // CSS/JS URLs generieren - aus compiled_uikit
        $cssUrl = $this->getThemeCssUrl($themeName);
        $jsUrl = \rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/js/uikit.min.js');
        $iconsUrl = \rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/js/uikit-icons.min.js');
        
        // Debug: URLs ausgeben
        $debugUrls = [
            'css_url' => $cssUrl,
            'js_url' => $jsUrl, 
            'icons_url' => $iconsUrl,
            'js_file_exists' => file_exists(\rex_path::addonAssets('uikit_theme_builder', 'compiled_uikit/js/uikit.min.js')),
            'icons_file_exists' => file_exists(\rex_path::addonAssets('uikit_theme_builder', 'compiled_uikit/js/uikit-icons.min.js'))
        ];
        
        // Google Fonts über GoogleFontsManager laden
        $googleFontsHtml = $this->getGoogleFontsForTheme($themeName);
        
        // Custom Icons Namen für JavaScript bereitstellen
        $customIconNames = $this->getCustomIconNames();
        $customIconsJson = json_encode($customIconNames);
        
        // Template-Variablen ersetzen
        $html = str_replace('{{THEME_NAME}}', htmlspecialchars($themeName), $template);
        $html = str_replace('{{THEME_CSS_URL}}', $cssUrl, $html);
        $html = str_replace('{{THEME_JS_URL}}', $jsUrl, $html);
        $html = str_replace('{{UIKIT_ICONS_URL}}', $iconsUrl, $html);
        $html = str_replace('{{GOOGLE_FONTS}}', $googleFontsHtml, $html);
        $html = str_replace('{{CUSTOM_ICON_NAMES}}', $customIconsJson, $html);
        
        // Debug-URLs in HTML-Kommentar einbetten
        $html = str_replace('<head>', '<head>' . "\n<!-- Preview URLs Debug:\n" . print_r($debugUrls, true) . "-->\n", $html);
        
        // Cache-Buster für CSS
        $cacheBuster = time();
        $html = str_replace('.css"', '.css?v=' . $cacheBuster . '"', $html);
        
        echo $html;
    }
    
    /**
     * CSS URL für Theme generieren
     */
    private function getThemeCssUrl(string $themeName): string
    {
        // Kompilierte Theme-CSS prüfen
        $compiledThemeFile = \rex_path::addonAssets('uikit_theme_builder', 'themes/compiled/' . $themeName . '.css');
        
        // Debug: Theme-CSS Status ausgeben
        $debugInfo = [
            'theme_name' => $themeName,
            'compiled_file_path' => $compiledThemeFile,
            'file_exists' => file_exists($compiledThemeFile),
            'file_size' => file_exists($compiledThemeFile) ? filesize($compiledThemeFile) : 0
        ];
        
        if (file_exists($compiledThemeFile) && filesize($compiledThemeFile) > 0) {
            // Kompiliertes Theme verwenden
            $url = \rex_url::addonAssets('uikit_theme_builder', 'themes/compiled/' . $themeName . '.css');
            $debugInfo['using_url'] = $url;
            $debugInfo['status'] = 'using_compiled_theme';
        } else {
            // Fallback: Standard UIKit CSS aus compiled_uikit
            $url = \rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/css/uikit.min.css');
            $debugInfo['using_url'] = $url;
            $debugInfo['status'] = 'using_fallback_css';
        }
        
        // Debug-Info in HTML-Kommentar einbetten (nur sichtbar im Quellcode)
        echo "<!-- UIKit Theme Builder Debug:\n" . print_r($debugInfo, true) . "-->\n";
        
        return $url;
    }
    
    /**
     * Google Fonts für Theme laden
     */
    private function getGoogleFontsForTheme(string $themeName): string
    {
        $googleFontsManager = new GoogleFontsManager();
        $downloadedFonts = $googleFontsManager->getDownloadedFonts();
        
        if (empty($downloadedFonts)) {
            return '<!-- Keine Google Fonts heruntergeladen -->';
        }
        
        $html = '';
        
        // CSS-Links für alle heruntergeladenen Fonts einbinden
        foreach ($downloadedFonts as $fontFamily => $fontInfo) {
            if (isset($fontInfo['css_file']) && file_exists($fontInfo['css_file'])) {
                if (isset($fontInfo['css_url']) && '' !== (string) $fontInfo['css_url']) {
                    $cssUrl = (string) $fontInfo['css_url'];
                } elseif (str_starts_with((string) $fontInfo['css_file'], \rex_path::assets())) {
                    $relativePath = str_replace(\rex_path::assets(), '', (string) $fontInfo['css_file']);
                    $cssUrl = \rex_url::assets($relativePath);
                } else {
                    $relativePath = str_replace(\rex_path::addonAssets('uikit_theme_builder'), '', (string) $fontInfo['css_file']);
                    $cssUrl = \rex_url::addonAssets('uikit_theme_builder', $relativePath);
                }
                
                $html .= '<link rel="stylesheet" href="' . htmlspecialchars($cssUrl) . '">' . "\n    ";
            }
        }
        
        return $html;
    }
    
    /**
     * Custom Icon Namen abrufen
     */
    private function getCustomIconNames(): array
    {
        $customIconManager = new CustomIconManager();
        $customIcons = $customIconManager->getCustomIcons();
        
        // Nur die Icon-Namen (Keys) zurückgeben
        return array_keys($customIcons);
    }
    
    /**
     * Fehlerseite anzeigen
     */
    private function showError(string $message): void
    {
        echo '<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Preview Error</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
        .error { background: white; padding: 30px; border-radius: 8px; max-width: 500px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #dc3545; margin-bottom: 20px; }
        p { color: #6c757d; }
    </style>
</head>
<body>
    <div class="error">
        <h1>Preview Error</h1>
        <p>' . htmlspecialchars($message) . '</p>
    </div>
</body>
</html>';
    }
}
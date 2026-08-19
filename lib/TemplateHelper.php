<?php

namespace UikitThemeBuilder;

/**
 * Template Helper für UIKit Theme Builder
 * Vereinfacht die Integration in REDAXO Templates
 */
class TemplateHelper
{
    /**
     * Komplette UIKit Assets einbinden (CSS + Icons)
     * 
     * @param string|null $themeName Theme-Name (optional)
     * @param bool $minified Minified Versionen verwenden (default: true)
     * @return string HTML für CSS/JS Einbindung
     */
    public static function includeAllStyles(?string $themeName = null, bool $minified = true): string
    {
        $html = '';
        
        // Wenn Theme angegeben: Theme CSS laden
        if ($themeName) {
            $html .= self::includeThemeCSS($themeName, $minified);
            $html .= self::includeGoogleFonts($themeName);
        } else {
            // Fallback: Standard UIKit CSS aus compiled_uikit
            $html .= self::includeUIKitCSS($minified);
        }
        
        return $html;
    }
    
    /**
     * UIKit CSS einbinden (aus compiled_uikit)
     */
    public static function includeUIKitCSS(bool $minified = true): string
    {
        $file = $minified ? 'uikit.min.css' : 'uikit.css';
        $path = \rex_path::addonAssets('uikit_theme_builder', 'compiled_uikit/css/' . $file);
        
        if (!file_exists($path)) {
            return '';
        }
        
        $url = \rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/css/' . $file);
        $version = filemtime($path);
        
        return '<link rel="stylesheet" href="' . $url . '?v=' . $version . '">' . "\n";
    }
    
    /**
     * Theme CSS einbinden
     */
    public static function includeThemeCSS(string $themeName, bool $minified = true): string
    {
        // Theme CSS Pfad ermitteln (nur .css, keine .min.css)
        $themePath = \rex_path::addonAssets('uikit_theme_builder', 'themes/compiled/' . $themeName . '.css');
        
        if (!file_exists($themePath)) {
            return '';
        }
        
        $url = \rex_url::addonAssets('uikit_theme_builder', 'themes/compiled/' . $themeName . '.css');
        $version = filemtime($themePath);
        
        return '<link rel="stylesheet" href="' . $url . '?v=' . $version . '">' . "\n";
    }
    
    /**
     * Google Fonts einbinden
     */
    public static function includeGoogleFonts(string $themeName): string
    {
        $manager = new UikitThemeBuilderManager();
        return $manager->getGoogleFontsHtml($themeName);
    }
    
    /**
     * UIKit JavaScript einbinden (aus compiled_uikit)
     * 
     * @param bool $minified Minified Version verwenden
     * @param bool $includeIcons Icons einbinden
     * @return string HTML Script-Tags
     */
    public static function includeUIKitJS(bool $minified = true, bool $includeIcons = true): string
    {
        $html = '';
        
        // UIKit Core aus compiled_uikit
        $coreFile = $minified ? 'uikit.min.js' : 'uikit.js';
        $corePath = \rex_path::addonAssets('uikit_theme_builder', 'compiled_uikit/js/' . $coreFile);
        
        if (file_exists($corePath)) {
            $url = \rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/js/' . $coreFile);
            $version = filemtime($corePath);
            $html .= '<script src="' . $url . '?v=' . $version . '"></script>' . "\n";
        }
        
        // UIKit Icons Extended
        if ($includeIcons) {
            $html .= self::includeUIKitIcons($minified);
        }
        
        return $html;
    }
    
    /**
     * UIKit Icons Extended einbinden (aus compiled_uikit)
     * 
     * @param bool $minified Minified Version verwenden
     * @return string HTML Script-Tag
     */
    public static function includeUIKitIcons(bool $minified = true): string
    {
        // Zuerst: Icons aus compiled_uikit Ordner prüfen
        $file = $minified ? 'uikit-icons.min.js' : 'uikit-icons.js';
        $path = \rex_path::addonAssets('uikit_theme_builder', 'compiled_uikit/js/' . $file);
        
        if (file_exists($path)) {
            $url = \rex_url::addonAssets('uikit_theme_builder', 'compiled_uikit/js/' . $file);
            $version = filemtime($path);
            return '<script src="' . $url . '?v=' . $version . '"></script>' . "\n";
        }
        
        // Fallback: Extended Icons aus assets/js/
        $file = $minified ? 'uikit-icons-extended.min.js' : 'uikit-icons-extended.js';
        $path = \rex_path::addonAssets('uikit_theme_builder', 'js/' . $file);
        
        if (file_exists($path)) {
            $url = \rex_url::addonAssets('uikit_theme_builder', 'js/' . $file);
            $version = filemtime($path);
            return '<script src="' . $url . '?v=' . $version . '"></script>' . "\n";
        }
        
        return '';
    }
    
    /**
     * Komplette JavaScript-Einbindung (UIKit + Icons)
     * 
     * @param bool $minified Minified Versionen verwenden
     * @return string HTML Script-Tags
     */
    public static function includeAllJS(bool $minified = true): string
    {
        return self::includeUIKitJS($minified, true);
    }
}

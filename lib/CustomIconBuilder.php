<?php

namespace UikitThemeBuilder;

/**
 * Custom Icon Builder
 * Erstellt uikit-icons-extended.js aus UIkit Standard + Custom Icons
 */
class CustomIconBuilder
{
    private string $originalIconsFile;
    private string $extendedIconsFile;
    private string $customIconsDir;
    
    public function __construct()
    {
        // Original UIkit Icons (aus Addon Assets)
        $this->originalIconsFile = \rex_path::addonAssets('uikit_theme_builder', 'compiled_uikit/js/uikit-icons.js');
        
        // Extended Icons Ausgabe
        $this->extendedIconsFile = \rex_path::addonAssets('uikit_theme_builder', 'js/uikit-icons-extended.js');
        
        // Custom Icons Verzeichnis
        $this->customIconsDir = \rex_path::addonData('uikit_theme_builder', 'icons/custom/');
    }
    
    /**
     * Icons neu kompilieren
     */
    public function rebuild(): array
    {
        try {
            // UIkit Standard Icons SVGs aus Original-Datei extrahieren
            $uikitIconsSvg = $this->extractUikitIconsSvg();
            
            // Custom Icons SVGs laden
            $customManager = new CustomIconManager();
            $customIcons = $customManager->getCustomIcons();
            $customIconsSvg = [];
            foreach ($customIcons as $icon) {
                $customIconsSvg[$icon['full_name']] = $icon['svg'];
            }
            
            // Alle Icons mergen
            $allIconsSvg = array_merge($uikitIconsSvg, $customIconsSvg);
            
            // Extended Icons-Datei generieren (komplett, nicht additiv)
            $this->generateCompleteIconsFile($allIconsSvg);
            
            return [
                'success' => true,
                'uikit_count' => count($uikitIconsSvg),
                'custom_count' => count($customIconsSvg),
                'total_count' => count($allIconsSvg),
                'file' => $this->extendedIconsFile
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * UIkit Icons SVGs aus Original-Datei extrahieren
     */
    private function extractUikitIconsSvg(): array
    {
        $icons = [];
        
        // Prüfe ob Original-Datei existiert
        if (!file_exists($this->originalIconsFile)) {
            return $icons;
        }
        
        // Original UIkit Icons JS-Datei laden
        $content = \rex_file::get($this->originalIconsFile);
        if (!is_string($content) || '' === $content) {
            return $icons;
        }

        // UIKit >= 3.25 nutzt doppelt gequotete SVG-Strings mit Escape-Sequenzen.
        // Beispiel: "home": "<svg width=\"20\" ...</svg>"
        if (preg_match_all('/"([^"]+)":\s*"((?:\\\\.|[^"\\\\])*)"/', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $iconName = (string) $match[1];
                $iconSvg = stripcslashes((string) $match[2]);

                if (str_starts_with($iconSvg, '<svg') && str_contains($iconSvg, '</svg>')) {
                    $icons[$iconName] = $iconSvg;
                }
            }
        }

        // Fallback für ältere Formate mit single quotes
        if ([] === $icons && preg_match_all('/"([^"]+)":\s*\'((?:\\\\.|[^\'\\\\])*)\'/', $content, $legacyMatches, PREG_SET_ORDER)) {
            foreach ($legacyMatches as $match) {
                $iconName = (string) $match[1];
                $iconSvg = stripcslashes((string) $match[2]);

                if (str_starts_with($iconSvg, '<svg') && str_contains($iconSvg, '</svg>')) {
                    $icons[$iconName] = $iconSvg;
                }
            }
        }
        
        return $icons;
    }
    
    /**
     * UIkit Standard Icons extrahieren
     */
    private function getUikitStandardIcons(): array
    {
        // Hardcoded UIkit Icon-Liste (alle Standard-Icons)
        // Diese Liste basiert auf UIkit 3.x Standard-Icons
        return [
            'album', 'arrow-down', 'arrow-left', 'arrow-right', 'arrow-up',
            'ban', 'bell', 'bold', 'bolt', 'bookmark',
            'calendar', 'camera', 'cart', 'check', 'chevron-double-left',
            'chevron-double-right', 'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up',
            'clock', 'close', 'code', 'cog', 'comment',
            'commenting', 'comments', 'copy', 'credit-card', 'database',
            'desktop', 'download', 'dribbble', 'expand', 'facebook',
            'file-edit', 'file-pdf', 'file-text', 'file', 'flickr',
            'folder', 'forward', 'foursquare', 'future', 'github-alt',
            'github', 'gitter', 'google-plus', 'google', 'grid',
            'happy', 'hashtag', 'heart', 'history', 'home',
            'image', 'info', 'instagram', 'italic', 'joomla',
            'laptop', 'lifesaver', 'link', 'linkedin', 'list',
            'location', 'lock', 'mail', 'menu', 'minus-circle',
            'minus', 'more-vertical', 'more', 'move', 'nut',
            'pagekit', 'paint-bucket', 'pencil', 'phone-landscape', 'phone',
            'pinterest', 'play-circle', 'play', 'plus-circle', 'plus',
            'pull', 'push', 'question', 'receiver', 'refresh',
            'reply', 'rss', 'search', 'server', 'settings',
            'shrink', 'sign-in', 'sign-out', 'social', 'soundcloud',
            'star', 'strikethrough', 'table', 'tablet-landscape', 'tablet',
            'tag', 'thumbnails', 'trash', 'tripadvisor', 'tumblr',
            'tv', 'twitter', 'uikit', 'unlock', 'upload',
            'user', 'users', 'video-camera', 'vimeo', 'warning',
            'whatsapp', 'wordpress', 'world', 'xing', 'yelp',
            'youtube'
        ];
    }
    
    /**
     * Custom Icons laden
     */
    private function loadCustomIcons(): array
    {
        $customIcons = [];
        
        if (!is_dir($this->customIconsDir)) {
            return $customIcons;
        }
        
        $files = glob($this->customIconsDir . '*.svg');
        foreach ($files as $file) {
            $name = basename($file, '.svg');
            $customIcons[] = 'custom-' . $name;
        }
        
        return $customIcons;
    }
    
    /**
     * Komplette Icons JavaScript-Datei generieren (UIkit + Custom)
     */
    private function generateCompleteIconsFile(array $allIconsSvg): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $totalCount = count($allIconsSvg);
        
        // Custom Icons zählen (die mit 'custom-' Prefix)
        $customCount = 0;
        foreach (array_keys($allIconsSvg) as $name) {
            if (strpos($name, 'custom-') === 0) {
                $customCount++;
            }
        }
        
        $js = <<<JS
/*! UIkit Icons Extended (Complete) | Generated: {$timestamp} */
/*! Total Icons: {$totalCount} | UIKit: {UIKIT_COUNT} | Custom: {$customCount} */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
    typeof define === 'function' && define.amd ? define('uikiticonsextended', factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.UIkitIconsExtended = factory());
})(this, (function () { 'use strict';

    var Icons = {

JS;
        
        // Alle Icon-Daten einfügen
        $iconEntries = [];
        foreach ($allIconsSvg as $name => $svg) {
            // Whitespace und Zeilenumbrüche entfernen
            $svg = preg_replace('/\s+/', ' ', trim($svg));
            // JavaScript-Escaping
            $svg = addslashes($svg);
            $iconEntries[] = "        \"{$name}\": \"{$svg}\"";
        }
        $js .= implode(",\n", $iconEntries) . "\n";
        
        $uikitCount = $totalCount - $customCount;
        $js = str_replace('{UIKIT_COUNT}', $uikitCount, $js);
        
        $js .= <<<JS
    };

    function plugin(UIkit) {
        if (plugin.installed) {
            return;
        }

        UIkit.icon.add(Icons);
        plugin.installed = true;
    }

    if (typeof window !== 'undefined' && window.UIkit) {
        window.UIkit.use(plugin);
    }

    return plugin;

}));

JS;
        
        // Datei speichern
        file_put_contents($this->extendedIconsFile, $js);
    }

    
    /**
     * JavaScript-Code generieren
     */
    private function generateIconsJavaScript(array $iconList, array $iconData): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $totalCount = count($iconList);
        $customCount = count($iconData);
        
        $js = <<<JS
/*! UIkit Icons Extended | Generated: {$timestamp} */
/*! Total Icons: {$totalCount} | Custom Icons: {$customCount} */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
    typeof define === 'function' && define.amd ? define('uikiticonsextended', factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.UIkitIconsExtended = factory());
})(this, (function () { 'use strict';

    function plugin(UIkit) {
        if (plugin.installed) {
            return;
        }
        
        // Custom Icons registrieren
        const customIcons = {

JS;
        
        // Custom Icon-Daten einfügen
        $iconEntries = [];
        foreach ($iconData as $name => $svg) {
            // Whitespace und Zeilenumbrüche entfernen
            $svg = preg_replace('/\s+/', ' ', trim($svg));
            // JavaScript-Escaping
            $svg = addslashes($svg);
            $iconEntries[] = "            '{$name}': '{$svg}'";
        }
        $js .= implode(",\n", $iconEntries) . "\n";
        
        $js .= <<<JS
        };
        
        // Icons zu UIkit hinzufügen
        for (const [name, svg] of Object.entries(customIcons)) {
            UIkit.icon.add(name, svg);
        }
        
        plugin.installed = true;
    }
    
    if (typeof window !== "undefined" && window.UIkit) {
        window.UIkit.use(plugin);
    }

    return plugin;

}));

JS;
        
        return $js;
    }
    
    /**
     * Alle verfügbaren Icons abrufen (für Icon Picker)
     */
    public function getAllAvailableIcons(): array
    {
        $icons = [];
        
        // UIkit Standard Icons aus extrahierten SVGs
        $uikitIconsSvg = $this->extractUikitIconsSvg();
        $iconCategories = $this->getIconCategoriesMap();
        
        foreach ($uikitIconsSvg as $iconName => $svg) {
            // Kategorie bestimmen
            $category = 'other';
            foreach ($iconCategories as $cat => $iconList) {
                if (in_array($iconName, $iconList)) {
                    $category = $cat;
                    break;
                }
            }
            
            $icons[] = [
                'name' => $iconName,
                'category' => $category,
                'source' => 'uikit',
                'tags' => [],
                'svg' => $svg,
            ];
        }
        
        // Custom Icons
        $customManager = new CustomIconManager();
        $customIcons = $customManager->getCustomIcons();
        
        foreach ($customIcons as $icon) {
            $icons[] = [
                'name' => $icon['full_name'],
                'category' => $icon['category'],
                'source' => 'custom',
                'tags' => $icon['tags'],
                'svg' => $icon['svg'],
            ];
        }
        
        return $icons;
    }
    
    /**
     * Icon-Kategorien Map für Klassifizierung
     */
    private function getIconCategoriesMap(): array
    {
        return [
            'navigation' => ['home', 'menu', 'close', 'search', 'arrow-left', 'arrow-right', 'arrow-up', 'arrow-down', 
                           'chevron-left', 'chevron-right', 'chevron-up', 'chevron-down', 'chevron-double-left', 'chevron-double-right',
                           'expand', 'shrink', 'forward', 'reply', 'push', 'pull', 'sign-in', 'sign-out'],
            'interface' => ['cog', 'settings', 'user', 'users', 'lock', 'unlock', 'bell', 'calendar', 'clock', 'mail', 
                          'phone', 'comment', 'comments', 'commenting', 'check', 'ban', 'info', 'warning', 'question',
                          'star', 'heart', 'bookmark', 'tag', 'hashtag', 'bolt', 'nut', 'lifesaver', 'happy', 'receiver'],
            'media' => ['image', 'video-camera', 'camera', 'play', 'play-circle', 'album', 'file', 'file-text', 
                       'file-pdf', 'file-edit', 'folder', 'download', 'upload', 'future', 'history'],
            'social' => ['facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'github', 'github-alt', 'google',
                        'google-plus', 'pinterest', 'tumblr', 'vimeo', 'flickr', 'dribbble', 'foursquare', 'soundcloud',
                        'whatsapp', 'gitter', 'joomla', 'pagekit', 'wordpress', 'tripadvisor', 'yelp', 'xing', 'uikit',
                        'behance', 'etsy', 'discord', 'reddit', 'telegram', 'tiktok', 'threads', 'twitch', 'rss', 'social'],
            'editing' => ['pencil', 'trash', 'copy', 'move', 'plus', 'plus-circle', 'minus', 'minus-circle', 
                         'bold', 'italic', 'strikethrough', 'code', 'paint-bucket', 'refresh'],
            'devices' => ['desktop', 'laptop', 'tablet', 'tablet-landscape', 'phone', 'phone-landscape', 'tv'],
            'objects' => ['cart', 'credit-card', 'database', 'server', 'world', 'location', 'link', 'list', 'table', 
                         'thumbnails', 'grid']
        ];
    }
    
    /**
     * UIkit Icons mit Kategorien
     */
    private function getUikitIconsWithCategories(): array
    {
        // UIkit Standard Icons kategorisiert
        $categories = [
            'navigation' => ['home', 'menu', 'close', 'search', 'arrow-left', 'arrow-right', 'arrow-up', 'arrow-down', 
                           'chevron-left', 'chevron-right', 'chevron-up', 'chevron-down', 'chevron-double-left', 'chevron-double-right',
                           'expand', 'shrink', 'forward', 'reply', 'push', 'pull'],
            'interface' => ['cog', 'settings', 'user', 'users', 'lock', 'unlock', 'bell', 'calendar', 'clock', 'mail', 
                          'phone', 'comment', 'comments', 'commenting', 'check', 'ban', 'info', 'warning', 'question',
                          'star', 'heart', 'bookmark', 'tag', 'hashtag', 'bolt'],
            'media' => ['image', 'video-camera', 'camera', 'play', 'play-circle', 'album', 'file', 'file-text', 
                       'file-pdf', 'file-edit', 'folder', 'download', 'upload'],
            'social' => ['facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'github', 'github-alt', 'google',
                        'google-plus', 'pinterest', 'tumblr', 'vimeo', 'flickr', 'dribbble', 'foursquare', 'soundcloud',
                        'whatsapp', 'gitter', 'joomla', 'pagekit', 'wordpress', 'tripadvisor', 'yelp', 'xing', 'uikit'],
            'editing' => ['pencil', 'trash', 'copy', 'move', 'plus', 'plus-circle', 'minus', 'minus-circle', 
                         'bold', 'italic', 'strikethrough', 'code', 'paint-bucket'],
            'devices' => ['desktop', 'laptop', 'tablet', 'tablet-landscape', 'phone', 'phone-landscape', 'tv'],
            'objects' => ['grid', 'list', 'table', 'thumbnails', 'credit-card', 'cart', 'database', 'server',
                         'receiver', 'lifesaver', 'nut', 'world', 'location', 'link', 'rss', 'refresh', 
                         'history', 'future', 'social', 'sign-in', 'sign-out', 'happy', 'more', 'more-vertical']
        ];
        
        $result = [];
        foreach ($categories as $category => $icons) {
            foreach ($icons as $icon) {
                $result[] = [
                    'name' => $icon,
                    'category' => $category,
                    'tags' => []
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Prüfen ob Extended Icons existieren
     */
    public function hasExtendedIcons(): bool
    {
        return file_exists($this->extendedIconsFile);
    }
    
    /**
     * Extended Icons-Datei URL
     */
    public function getExtendedIconsUrl(): string
    {
        return \rex_url::addonAssets('uikit_theme_builder', 'js/uikit-icons-extended.js?v=' . time());
    }
}

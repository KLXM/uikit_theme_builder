<?php

namespace UikitThemeBuilder;

/**
 * UIKit Theme Builder - Extra Styles Integration
 * Helper-Klasse für die Verwendung von Extra Styles in Modulen
 */
class ExtraStylesIntegration
{
    /**
     * Prüft ob Extra Styles AddOn verfügbar ist
     * 
     * @return bool
     */
    public static function isAvailable(): bool
    {
        try {
            $addon = \rex_addon::get('extra_styles');
            return $addon->isInstalled() && $addon->isAvailable();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Gibt Select-Optionen für einen Style-Typ zurück
     * Wrapper für ExtraStyles::getSelectOptions() mit Fallback
     * 
     * @param string $type Typ: card, section, background, border
     * @return array Select-Optionen
     */
    public static function getSelectOptions(string $type): array
    {
        $defaultOptions = self::prefixOptionLabels(self::getFallbackOptions($type), 'Default');
        $themeOptions = self::prefixOptionLabels(self::getThemeOptions($type), 'Theme');

        if ($themeOptions === []) {
            return $defaultOptions;
        }

        return array_merge($defaultOptions, $themeOptions);
    }

    /**
     * Liefert Optionen aus dem Extra Styles AddOn (Theme-Optionen)
     *
     * @param string $type
     * @return array<string, string>
     */
    private static function getThemeOptions(string $type): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        try {
            if (class_exists('\ExtraStyles\ExtraStyles')) {
                $options = \ExtraStyles\ExtraStyles::getSelectOptions($type);
                if (is_array($options)) {
                    return $options;
                }
            }
        } catch (\Exception $e) {
            // Bei Fehler leere Theme-Optionen liefern, Defaults bleiben aktiv
        }

        return [];
    }
    
    /**
     * Fallback-Optionen wenn Extra Styles nicht verfügbar ist
     * 
     * @param string $type
     * @return array
     */
    private static function getFallbackOptions(string $type): array
    {
        switch ($type) {
            case 'card':
                return [
                    'utb_default' => 'UTB Standard',
                    'utb_primary' => 'UTB Hauptfarbe',
                    'utb_secondary' => 'UTB Sekundärfarbe',
                    'utb_muted' => 'UTB Muted',
                    'utb_transparent' => 'UTB Transparent',
                ];
                
            case 'section':
                return [
                    'utb_default' => 'UTB Standard',
                    'utb_primary' => 'UTB Primär',
                    'utb_secondary' => 'UTB Sekundär',
                    'utb_muted' => 'UTB Muted',
                ];
                
            case 'background':
                return [
                    'utb_default' => 'UTB Standard',
                    'utb_primary' => 'UTB Primär',
                    'utb_secondary' => 'UTB Sekundär',
                    'utb_muted' => 'UTB Muted',
                    'utb_transparent' => 'UTB Transparent',
                ];
                
            case 'border':
                return [
                    'utb_default' => 'UTB Standard',
                    'utb_primary' => 'UTB Primär',
                    'utb_secondary' => 'UTB Sekundär',
                ];
                
            default:
                return [
                    'utb_default' => 'UTB Standard',
                ];
        }
    }
    
    /**
     * Erweiterte Select-Optionen mit Mergefähigkeit
     * Kombiniert Extra Styles mit benutzerdefinierten Optionen
     * 
     * @param string $type Style-Typ
     * @param array $additionalOptions Zusätzliche Optionen
     * @param bool $addAfter Zusätzliche Optionen nach (true) oder vor (false) Extra Styles
     * @return array Kombinierte Select-Optionen
     */
    public static function getMergedSelectOptions(string $type, array $additionalOptions = [], bool $addAfter = true): array
    {
        $extraStylesOptions = self::getSelectOptions($type);
        
        if ($additionalOptions === []) {
            return $extraStylesOptions;
        }
        
        if ($addAfter) {
            return array_merge($extraStylesOptions, $additionalOptions);
        } else {
            return array_merge($additionalOptions, $extraStylesOptions);
        }
    }

    /**
     * Fügt den Anzeigenamen ein Präfix zur visuellen Trennung hinzu.
     *
     * @param array<string, string> $options
     * @param string $prefix
     * @return array<string, string>
     */
    private static function prefixOptionLabels(array $options, string $prefix): array
    {
        $prefixed = [];

        foreach ($options as $key => $label) {
            $prefixed[(string) $key] = $prefix . ': ' . (string) $label;
        }

        return $prefixed;
    }
    
    /**
     * Generiert CSS-Klasse aus Style-Typ und Wert
     * 
     * @param string $type Style-Typ (card, section, background, border)
     * @param string $value Gewählter Wert
     * @return string CSS-Klassen-String
     */
    public static function generateCssClass(string $type, string $value): string
    {
        if ($value === '' || $value === 'default' || $value === 'utb_default') {
            return '';
        }
        
        // Bei speziellen Werten (z.B. "transparent uk-light") direkt zurückgeben
        if (strpos($value, ' ') !== false) {
            return $value;
        }
        
        // Standard UIKit-Klassen
        $uikitClasses = [
            'primary',
            'secondary',
            'muted',
            'transparent',
            'utb_primary' => 'primary',
            'utb_secondary' => 'secondary',
            'utb_muted' => 'muted',
            'utb_transparent' => 'transparent',
        ];

        if (isset($uikitClasses[$value])) {
            return "uk-{$type}-{$uikitClasses[$value]}";
        }

        if (in_array($value, $uikitClasses, true)) {
            return "uk-{$type}-{$value}";
        }
        
        // Extra Styles Klasse (Custom Slug)
        return "uk-{$type}-{$value}";
    }
    
    /**
     * Debug-Information über Extra Styles Status
     * Nur im Debug-Modus verfügbar
     * 
     * @return string HTML-Debug-Output
     */
    public static function getDebugInfo(): string
    {
        if (!\rex::isDebugMode()) {
            return '';
        }
        
        $debug = '<div class="alert alert-info" style="margin: 10px 0;">';
        $debug .= '<h5>🔧 Extra Styles Integration Status</h5>';
        
        if (self::isAvailable()) {
            $debug .= '<p><span style="color: green;">✅ Extra Styles AddOn verfügbar</span></p>';
            
            try {
                $cardOptions = self::getSelectOptions('card');
                $debug .= '<p>Card-Optionen: ' . count($cardOptions) . ' verfügbar</p>';
                
                if (class_exists('\ExtraStyles\ExtraStyles')) {
                    $allStyles = \ExtraStyles\ExtraStyles::getAll();
                    $debug .= '<p>Gesamt Custom-Styles: ' . count($allStyles) . '</p>';
                }
            } catch (\Exception $e) {
                $debug .= '<p style="color: orange;">⚠️ Fehler beim Laden: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        } else {
            $debug .= '<p style="color: red;">❌ Extra Styles AddOn nicht verfügbar - Fallback-Optionen werden verwendet</p>';
        }
        
        $debug .= '</div>';
        return $debug;
    }
}
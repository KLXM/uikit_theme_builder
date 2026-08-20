<?php

namespace UikitThemeBuilder;

/**
 * Domain Context Helper - stellt Theme- und Style-Informationen basierend auf aktueller Domain bereit
 */
class DomainContext
{
    private static ?array $currentContext = null;
    private static ?array $themeColors = null;
    private static ?string $forcedTheme = null;
    
    /**
     * Setzt ein Theme manuell (für Backend/YForm-Kontext ohne Domain)
     * 
     * @param string|null $themeName Der Theme-Name oder null zum Zurücksetzen
     */
    public static function setTheme(?string $themeName): void
    {
        self::$forcedTheme = $themeName;
        // Cache zurücksetzen damit getContext() neu lädt
        self::$currentContext = null;
        self::$themeColors = null;
    }
    
    /**
     * Gibt den aktuell erzwungenen Theme-Namen zurück
     */
    public static function getForcedTheme(): ?string
    {
        return self::$forcedTheme;
    }
    
    /**
     * Setzt den Context zurück (Cache leeren)
     */
    public static function resetContext(): void
    {
        self::$currentContext = null;
        self::$themeColors = null;
        self::$forcedTheme = null;
    }
    
    /**
     * Gibt den Theme-Namen für die aktuelle Domain zurück
     */
    public static function getCurrentTheme(): ?string
    {
        $context = self::getContext();
        return $context['theme'] ?? null;
    }
    
    /**
     * Gibt die Domain-ID zurück
     */
    public static function getCurrentDomainId(): ?int
    {
        $context = self::getContext();
        return $context['domain_id'] ?? null;
    }
    
    /**
     * Gibt vollständige Kontext-Informationen zurück
     */
    public static function getContext(): array
    {
        if (self::$currentContext !== null) {
            return self::$currentContext;
        }
        
        $context = [
            'domain_id' => null,
            'domain_name' => null,
            'theme' => null,
            'theme_css_url' => null,
            'is_backend' => \rex::isBackend(),
            'enable_transparent' => 1,
            'enable_light_dark' => 1,
            'enable_bg_utilities' => 1,
        ];
        
        // Wenn Theme manuell gesetzt wurde (z.B. im YForm-Backend)
        if (self::$forcedTheme !== null) {
            $context['theme'] = self::$forcedTheme;
            $context['theme_css_url'] = \rex_url::assets(
                'addons/uikit_theme_builder/themes/compiled/' . self::$forcedTheme . '.css'
            );
            
            // Utility-Einstellungen aus Theme-JSON laden (falls vorhanden)
            $utilitySettings = self::loadUtilitySettingsFromTheme(self::$forcedTheme);
            $context['enable_transparent'] = $utilitySettings['enable_transparent'];
            $context['enable_light_dark'] = $utilitySettings['enable_light_dark'];
            $context['enable_bg_utilities'] = $utilitySettings['enable_bg_utilities'];
            
            self::$currentContext = $context;
            return $context;
        }
        
        // Domain ermitteln (funktioniert auch im Backend!)
        if (class_exists('rex_yrewrite') && \rex_addon::get('yrewrite')->isAvailable()) {
            $domain = \rex_yrewrite::getCurrentDomain();
            
            if ($domain) {
                $context['domain_id'] = $domain->getId();
                $context['domain_name'] = $domain->getName();
                
                // Theme und Utility-Einstellungen aus Datenbank holen
                $sql = \rex_sql::factory();
                $sql->setQuery('
                    SELECT theme_name, enable_transparent, enable_light_dark, enable_bg_utilities
                    FROM ' . \rex::getTable('uikit_theme_domains') . ' 
                    WHERE domain_id = ?
                    LIMIT 1
                ', [$domain->getId()]);
                
                if ($sql->getRows() > 0) {
                    $themeName = $sql->getValue('theme_name');
                    $context['theme'] = $themeName;
                    $context['theme_css_url'] = \rex_url::assets(
                        'addons/uikit_theme_builder/themes/compiled/' . $themeName . '.css'
                    );
                    $context['enable_transparent'] = (int)$sql->getValue('enable_transparent');
                    $context['enable_light_dark'] = (int)$sql->getValue('enable_light_dark');
                    $context['enable_bg_utilities'] = (int)$sql->getValue('enable_bg_utilities');
                }
            }
        }
        
        self::$currentContext = $context;
        return $context;
    }
    
    /**
     * Gibt Card-Style-Optionen für MForm RadioColorField zurück
     */
    public static function getCardStyleOptions(): array
    {
        $colors = self::getThemeColors();
        $context = self::getContext();
        
        if (empty($colors)) {
            // Fallback wenn kein Theme zugeordnet
            return [
                'uk-card-default' => ['color' => '#ffffff', 'label' => 'Default (Weiß)'],
                'uk-card-primary' => ['color' => '#1e87f0', 'label' => 'Primary'],
                'uk-card-secondary' => ['color' => '#222222', 'label' => 'Secondary']
            ];
        }
        
        $options = [];
        
        // Standard-Karten aus Theme-Farben
        foreach ($colors as $color) {
            if ($color['ui_class']) {
                $options[$color['ui_class']] = [
                    'color' => $color['color_value'],
                    'label' => $color['color_label'] ?? ucfirst($color['color_type'])
                ];
            }
        }
        
        // Extra Styles aus Style-Sets hinzufügen (nur card-type)
        $extraStyles = self::getExtraStyles();
        foreach ($extraStyles as $styleSet) {
            if (!empty($styleSet['styles_data'])) {
                foreach ($styleSet['styles_data'] as $style) {
                    if (!empty($style['enabled']) && !empty($style['slug']) && $style['type'] === 'card') {
                        $className = 'uk-card-' . $style['slug'];
                        $options[$className] = [
                            'color' => $style['background_color'] ?? '#ffffff',
                            'label' => $style['name'] ?: ucfirst($style['slug'])
                        ];
                    }
                }
            }
        }
        
        // Utility-Optionen nur wenn aktiviert
        if ($context['enable_transparent']) {
            $options['uk-card-transparent'] = [
                'color' => 'rgba(255,255,255,0.1)', 
                'label' => 'Transparent'
            ];
        }
        
        return $options;
    }
    
    /**
     * Gibt Background-Style-Optionen zurück
     */
    public static function getBackgroundOptions(): array
    {
        $colors = self::getThemeColors();
        $context = self::getContext();
        
        $options = [];
        
        foreach ($colors as $color) {
            if ($color['ui_class']) {
                $bgClass = str_replace('uk-card-', 'uk-background-', $color['ui_class']);
                $options[$bgClass] = [
                    'color' => $color['color_value'],
                    'label' => $color['color_label'] ?? ucfirst($color['color_type'])
                ];
            }
        }
        
        // Fallback-Optionen wenn keine Theme-Farben vorhanden
        if (empty($options)) {
            $options = [
                'uk-background-default' => ['color' => '#ffffff', 'label' => 'Default (Weiß)'],
                'uk-background-muted' => ['color' => '#f8f8f8', 'label' => 'Muted (Grau)'],
                'uk-background-primary' => ['color' => '#1e87f0', 'label' => 'Primary'],
                'uk-background-secondary' => ['color' => '#222222', 'label' => 'Secondary']
            ];
        }
        
        // Extra Styles aus Style-Sets hinzufügen (background, section, button types)
        $extraStyles = self::getExtraStyles();
        foreach ($extraStyles as $styleSet) {
            if (!empty($styleSet['styles_data'])) {
                foreach ($styleSet['styles_data'] as $style) {
                    if (!empty($style['enabled']) && !empty($style['slug']) && in_array($style['type'], ['background', 'section'])) {
                        $className = 'uk-background-' . $style['slug'];
                        $options[$className] = [
                            'color' => $style['background_color'] ?? '#ffffff',
                            'label' => $style['name'] ?: ucfirst($style['slug'])
                        ];
                    }
                }
            }
        }
        
        // Utility-Optionen nur wenn aktiviert
        if ($context['enable_bg_utilities']) {
            $options['uk-background-transparent'] = [
                'color' => 'rgba(255,255,255,0.1)', 
                'label' => 'Transparent'
            ];
            $options['uk-background-white'] = [
                'color' => '#ffffff', 
                'label' => 'Weiß'
            ];
        }
        
        return $options;
    }
    
    /**
     * Gibt Text-Color-Klassen zurück (für uk-text-* Utilities)
     */
    public static function getTextColorOptions(): array
    {
        $colors = self::getThemeColors();
        $context = self::getContext();
        
        $options = [
            '' => ['color' => 'inherit', 'label' => 'Standard (Inherit)'],
            'uk-text-muted' => ['color' => '#999999', 'label' => 'Muted (Grau)']
        ];
        
        foreach ($colors as $color) {
            if (in_array($color['color_type'], ['primary', 'secondary', 'danger', 'warning', 'success'])) {
                $textClass = 'uk-text-' . $color['color_type'];
                $options[$textClass] = [
                    'color' => $color['color_value'],
                    'label' => $color['color_label'] ?? ucfirst($color['color_type'])
                ];
            }
        }
        
        // Utility-Optionen nur wenn aktiviert
        if ($context['enable_light_dark']) {
            $options['uk-light'] = [
                'color' => '#ffffff', 
                'label' => 'Hell (auf dunklem Hintergrund)'
            ];
            $options['uk-dark'] = [
                'color' => '#333333', 
                'label' => 'Dunkel (auf hellem Hintergrund)'
            ];
        }
        
        return $options;
    }
    
    /**
     * Lädt alle Farben für das aktuelle Theme
     * Versucht zuerst aus der Datenbank, dann aus der Theme-JSON
     */
    private static function getThemeColors(): array
    {
        if (self::$themeColors !== null) {
            return self::$themeColors;
        }
        
        $themeName = self::getCurrentTheme();
        
        if (!$themeName) {
            self::$themeColors = [];
            return self::$themeColors;
        }
        
        // Zuerst aus Datenbank versuchen
        $sql = \rex_sql::factory();
        $sql->setQuery('
            SELECT * 
            FROM ' . \rex::getTable('uikit_theme_colors') . ' 
            WHERE theme_name = ?
            ORDER BY 
                FIELD(color_type, "primary", "secondary", "default", "muted", "success", "warning", "danger")
        ', [$themeName]);
        
        self::$themeColors = $sql->getArray();
        
        // Falls keine DB-Einträge, direkt aus Theme-JSON laden
        if (empty(self::$themeColors)) {
            self::$themeColors = self::loadColorsFromThemeJson($themeName);
        }
        
        return self::$themeColors;
    }
    
    /**
     * Lädt Farben direkt aus der Theme-JSON-Datei
     * Nur die relevanten Card/Section-Farben: primary, secondary, default
     */
    private static function loadColorsFromThemeJson(string $themeName): array
    {
        $themeManager = new \UikitThemeBuilder\UikitThemeBuilderManager();
        $themeData = $themeManager->loadTheme($themeName);
        
        if (!$themeData || empty($themeData['data']['colors'])) {
            return [];
        }
        
        $colors = $themeData['data']['colors'];
        $result = [];
        
        // Nur die relevanten Card/Section-Farben (primary, secondary, default)
        // Keine success, warning, danger, muted - diese sind für Cards nicht vorgesehen
        $colorMapping = [
            'global-background' => ['type' => 'default', 'class' => 'uk-card-default', 'label' => 'Default (Weiß)'],
            'global-primary-background' => ['type' => 'primary', 'class' => 'uk-card-primary', 'label' => 'Primary'],
            'global-secondary-background' => ['type' => 'secondary', 'class' => 'uk-card-secondary', 'label' => 'Secondary'],
        ];
        
        foreach ($colorMapping as $jsonKey => $mapping) {
            if (isset($colors[$jsonKey])) {
                $result[] = [
                    'theme_name' => $themeName,
                    'color_type' => $mapping['type'],
                    'color_value' => $colors[$jsonKey],
                    'color_label' => $mapping['label'],
                    'ui_class' => $mapping['class']
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Lädt Utility-Einstellungen aus Theme-JSON oder gibt Standardwerte zurück
     * 
     * @param string $themeName Name des Themes
     * @return array Utility-Einstellungen
     */
    private static function loadUtilitySettingsFromTheme(string $themeName): array
    {
        // Standard-Einstellungen (alle aktiviert)
        $defaults = [
            'enable_transparent' => 1,
            'enable_light_dark' => 1,
            'enable_bg_utilities' => 1,
        ];
        
        $themeManager = new \UikitThemeBuilder\UikitThemeBuilderManager();
        $themeData = $themeManager->loadTheme($themeName);
        
        if (!$themeData || empty($themeData['data'])) {
            return $defaults;
        }
        
        $data = $themeData['data'];
        
        // Utility-Einstellungen aus Theme laden (falls vorhanden)
        // Diese können im Theme als 'utilities' oder 'settings' Sektion gespeichert sein
        if (isset($data['utilities'])) {
            return [
                'enable_transparent' => $data['utilities']['enable_transparent'] ?? 1,
                'enable_light_dark' => $data['utilities']['enable_light_dark'] ?? 1,
                'enable_bg_utilities' => $data['utilities']['enable_bg_utilities'] ?? 1,
            ];
        }
        
        if (isset($data['settings'])) {
            return [
                'enable_transparent' => $data['settings']['enable_transparent'] ?? 1,
                'enable_light_dark' => $data['settings']['enable_light_dark'] ?? 1,
                'enable_bg_utilities' => $data['settings']['enable_bg_utilities'] ?? 1,
            ];
        }
        
        // Prüfen ob Card-Transparent in den Extra-Styles definiert ist
        // Wenn "style_sets" ausgewählt sind, könnten dort Transparenz-Styles enthalten sein
        if (isset($data['style_sets']['selected_style_sets']) && !empty($data['style_sets']['selected_style_sets'])) {
            // Bei vorhandenen Style-Sets: Utilities aktivieren
            return $defaults;
        }
        
        return $defaults;
    }
    
    /**
     * Gibt alle verfügbaren Extra Styles für das aktuelle Theme zurück
     * Lädt nur die Style-Sets die für dieses Theme ausgewählt wurden
     */
    public static function getExtraStyles(): array
    {
        $themeName = self::getCurrentTheme();
        
        if (!$themeName) {
            return [];
        }
        
        // Theme-Daten laden um ausgewählte Style-Sets zu ermitteln
        $themeManager = new \UikitThemeBuilder\UikitThemeBuilderManager();
        $themeData = $themeManager->loadTheme($themeName);
        
        if (!$themeData || empty($themeData['data']['style_sets']['selected_style_sets'])) {
            return [];
        }
        
        $selectedStyleSetIds = $themeData['data']['style_sets']['selected_style_sets'];
        
        if (empty($selectedStyleSetIds)) {
            return [];
        }
        
        // Nur die ausgewählten Style-Sets laden
        $sql = \rex_sql::factory();
        $placeholders = implode(',', array_fill(0, count($selectedStyleSetIds), '?'));
        $sql->setQuery('
            SELECT * 
            FROM ' . \rex::getTable('uikit_style_sets') . ' 
            WHERE id IN (' . $placeholders . ') 
            AND is_active = 1
            ORDER BY name ASC
        ', $selectedStyleSetIds);
        
        $results = $sql->getArray();
        
        // JSON-Daten dekodieren
        foreach ($results as &$result) {
            $result['styles_data'] = json_decode($result['styles_data'], true) ?: [];
        }
        
        return $results;
    }
    
    /**
     * Gibt Extra Style-Optionen für Select-Felder zurück
     */
    public static function getExtraStyleOptions(): array
    {
        $styles = self::getExtraStyles();
        
        $options = ['' => '-- Standard --'];
        
        foreach ($styles as $style) {
            // Style-Sets haben keinen slug, verwenden wir die ID
            $options[$style['id']] = $style['name'];
        }
        
        return $options;
    }
    
    /**
     * Gibt alle verfügbaren Themes zurück (für Einstellungen)
     * 
     * @return array Key-Value Array mit theme_name => label
     */
    public static function getAvailableThemes(): array
    {
        $themes = [];
        
        // Theme Manager verwenden um alle gespeicherten Themes zu finden
        if (class_exists('UikitThemeBuilder\UikitThemeBuilderManager')) {
            $themeManager = new \UikitThemeBuilder\UikitThemeBuilderManager();
            $allThemes = $themeManager->listThemes();
            
            foreach ($allThemes as $themeName => $themeData) {
                $themes[$themeName] = ucfirst(str_replace(['_', '-'], ' ', $themeName));
            }
        }
        
        // Fallback: Aus der Farben-Tabelle lesen falls Manager nicht verfügbar
        if (empty($themes)) {
            $sql = \rex_sql::factory();
            $sql->setQuery('
                SELECT DISTINCT theme_name 
                FROM ' . \rex::getTable('uikit_theme_colors') . '
                ORDER BY theme_name
            ');
            
            foreach ($sql as $row) {
                $themeName = $row->getValue('theme_name');
                $themes[$themeName] = ucfirst(str_replace(['_', '-'], ' ', $themeName));
            }
        }
        
        return $themes;
    }

    /**
     * Auf die im Settings-Multiselect ("Live Theme Editor: wählbare Themes") freigegebenen
     * Themes eingeschränkte Liste für das "Theme wechseln"-Dropdown des Live Theme Editors.
     * Leere Auswahl = keine Einschränkung (alle Themes, wie bisher). Das aktuell zugewiesene
     * Theme bleibt immer enthalten, damit das Dropdown nie ohne den eigenen Ist-Zustand dasteht.
     *
     * Gespeichert wird das Multiselect wie bei REDAXO-Config-Forms üblich als
     * pipe-separierter String ("|theme-a|theme-b|"), nicht als Array - siehe rex_form_element::setValue().
     */
    public static function getLiveEditorAvailableThemes(string $currentTheme): array
    {
        $all = self::getAvailableThemes();

        $raw = (string) \rex_config::get('uikit_theme_builder', 'live_editor_available_themes', '');
        $allowed = array_filter(explode('|', trim($raw, '|')), static fn ($v) => '' !== $v);

        if (empty($allowed)) {
            return $all;
        }

        $allowed = array_flip($allowed);
        $allowed[$currentTheme] = true;

        return array_intersect_key($all, $allowed);
    }

    /**
     * Cache zurücksetzen (z.B. nach Theme-Wechsel)
     */
    public static function clearCache(): void
    {
        self::$currentContext = null;
        self::$themeColors = null;
    }
}

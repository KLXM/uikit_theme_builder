<?php

namespace UikitThemeBuilder\Widget;

/**
 * Navbar Widget - UIKit Navbar Styling
 */
class NavbarWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Navbar';
    }

    public function getKey(): string
    {
        return 'navbar';
    }

    public function getDescription(): string
    {
        return 'Konfiguration der UIKit-Navbar-Styling-Optionen';
    }

    public function getFields(): array
    {
        // Google Fonts laden
        $fontManager = new \UikitThemeBuilder\GoogleFontsManager();
        $availableFonts = $fontManager->getAllAvailableFonts();
        
        // Font-Optionen dynamisch erstellen
        $fontOptions = [
            'inherit' => 'Vom Theme erben',
            '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif' => 'System Font Stack',
            'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' => 'Modern System UI'
        ];
        
        // Gruppierung nach Quelle
        $groupedFonts = [];
        foreach ($availableFonts as $font) {
            $source = $font['source'];
            if (!isset($groupedFonts[$source])) {
                $groupedFonts[$source] = [];
            }
            $groupedFonts[$source][] = $font;
        }
        
        // Google Fonts hinzufügen
        if (isset($groupedFonts['google'])) {
            foreach ($groupedFonts['google'] as $font) {
                $fontFamily = $font['family'];
                $category = $font['category'];
                
                $defaultFallback = match($category) {
                    'serif' => ', "Times New Roman", Times, serif',
                    'sans-serif' => ', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                    'monospace' => ', "SF Mono", Monaco, Consolas, monospace', 
                    'cursive' => ', cursive',
                    default => ', sans-serif'
                };
                
                $fontValue = '"' . $fontFamily . '"' . $defaultFallback;
                $fontOptions[$fontValue] = $fontFamily . ' (Google)';
            }
        }
        
        // System Fonts hinzufügen
        if (isset($groupedFonts['system'])) {
            foreach ($groupedFonts['system'] as $font) {
                $fontFamily = $font['family'];
                $fallback = $font['category'] === 'serif' ? ', serif' : ', sans-serif';
                $fontValue = $fontFamily . $fallback;
                $fontOptions[$fontValue] = $fontFamily . ' (System)';
            }
        }
        
        return [
            // Container/Wrapper Einstellungen
            'navbar-container-background' => [
                'label' => 'Container Hintergrund',
                'type' => 'color',
                'default' => 'rgba(255, 255, 255, 0.95)',
                'help' => 'Hintergrundfarbe des äußeren Navbar-Containers'
            ],
            'navbar-container-border' => [
                'label' => 'Container Border',
                'type' => 'text',
                'default' => 'none',
                'help' => 'Border des Navbar-Containers (z.B. "1px solid #e5e5e5")'
            ],
            'navbar-container-box-shadow' => [
                'label' => 'Container Schatten',
                'type' => 'text',
                'default' => '0 2px 4px rgba(0,0,0,0.1)',
                'help' => 'Box-Shadow des Navbar-Containers'
            ],
            'navbar-background' => [
                'label' => 'Navbar Hintergrund',
                'type' => 'color',
                'default' => 'transparent',
                'help' => 'Hintergrundfarbe der Navbar selbst (meist transparent wenn Container-BG gesetzt)'
            ],
            'navbar-nav-item-height' => [
                'label' => 'Navigation Item Höhe',
                'type' => 'number',
                'default' => '80',
                'unit' => 'px',
                'min' => 40,
                'max' => 120,
                'help' => 'Höhe der Navigations-Items (Default: 80px)'
            ],
            'navbar-nav-item-color' => [
                'label' => 'Navigation Text Farbe',
                'type' => 'color',
                'default' => '#333333',
                'help' => 'Textfarbe der Navigations-Items'
            ],
            'navbar-nav-item-font-size' => [
                'label' => 'Navigation Schriftgröße',
                'type' => 'text',
                'default' => '1rem',
                'help' => 'Schriftgröße der Navigations-Items (z.B. 1rem, 0.875rem, 14px, 1em)'
            ],
            'navbar-nav-item-font-weight' => [
                'label' => 'Navigation Schriftstärke',
                'type' => 'select',
                'default' => '400',
                'options' => [
                    '300' => 'Light (300)',
                    '400' => 'Normal (400)',
                    '500' => 'Medium (500)',
                    '600' => 'Semi-Bold (600)',
                    '700' => 'Bold (700)'
                ],
                'help' => 'Schriftstärke der Navigations-Items (erste Ebene)'
            ],
            'navbar-nav-item-font-family' => [
                'label' => 'Navigation Schriftart',
                'type' => 'select',
                'default' => 'inherit',
                'options' => $fontOptions,
                'help' => 'Schriftfamilie für Navigations-Items'
            ],
            'navbar-nav-item-hover-color' => [
                'label' => 'Navigation Hover Farbe',
                'type' => 'color',
                'default' => '#005d40',
                'help' => 'Farbe bei Hover über Navigations-Items'
            ],
            'navbar-nav-item-padding-horizontal' => [
                'label' => 'Navigation Item Padding',
                'type' => 'number',
                'default' => '16',
                'unit' => 'px',
                'min' => 8,
                'max' => 32,
                'help' => 'Horizontaler Abstand der Navigations-Items (Default: 16px)'
            ],
            'navbar-dropdown-background' => [
                'label' => 'Dropdown Hintergrund',
                'type' => 'color',
                'default' => 'rgba(255, 255, 255, 0.95)',
                'help' => 'Hintergrundfarbe der Dropdown-Menüs'
            ],
            'navbar-dropdown-margin' => [
                'label' => 'Dropdown Abstand zur Navbar',
                'type' => 'number',
                'default' => '0',
                'unit' => 'px',
                'min' => 0,
                'max' => 30,
                'help' => 'Vertikaler Abstand zwischen Navbar und Dropdown-Menü (Default UIkit: 15px, empfohlen für direktes Ansetzen: 0px)'
            ],
            'navbar-dropdown-width' => [
                'label' => 'Dropdown Breite',
                'type' => 'number',
                'default' => '220',
                'unit' => 'px',
                'min' => 150,
                'max' => 400,
                'help' => 'Breite der Dropdown-Menüs (Default: 220px)'
            ],
            'navbar-dropdown-padding' => [
                'label' => 'Dropdown Padding',
                'type' => 'number',
                'default' => '12',
                'unit' => 'px',
                'min' => 6,
                'max' => 24,
                'help' => 'Innenabstand der Dropdown-Menüs (Default: 12px)'
            ],
            'navbar-dropdown-color' => [
                'label' => 'Dropdown Text Farbe',
                'type' => 'color',
                'default' => '#333333',
                'help' => 'Textfarbe in Dropdown-Menüs'
            ],
            'navbar-dropdown-font-size' => [
                'label' => 'Dropdown Schriftgröße',
                'type' => 'text',
                'default' => '0.875rem',
                'help' => 'Schriftgröße der Dropdown-Items (z.B. 0.875rem, 14px)'
            ],
            'navbar-dropdown-font-weight' => [
                'label' => 'Dropdown Schriftstärke',
                'type' => 'select',
                'default' => '400',
                'options' => [
                    '300' => 'Light (300)',
                    '400' => 'Normal (400)',
                    '500' => 'Medium (500)',
                    '600' => 'Semi-Bold (600)',
                    '700' => 'Bold (700)'
                ],
                'help' => 'Schriftstärke der Dropdown-Items'
            ],
            'navbar-dropdown-nav-item-hover-color' => [
                'label' => 'Dropdown Hover Farbe',
                'type' => 'color',
                'default' => '#005d40',
                'help' => 'Textfarbe bei Hover über Dropdown-Items'
            ],
            'navbar-dropdown-nav-sublist-item-color' => [
                'label' => 'Dropdown Sub-Level Farbe',
                'type' => 'color',
                'default' => '',
                'help' => 'Textfarbe für tiefere Navigationsebenen (leer lassen zum Erben)'
            ],
            'navbar-dropdown-nav-sublist-item-hover-color' => [
                'label' => 'Dropdown Sub-Level Hover',
                'type' => 'color',
                'default' => '',
                'help' => 'Hover-Farbe für tiefere Navigationsebenen (leer lassen zum Erben)'
            ]
        ];
    }

    public function getDefaultValues(): array
    {
        return [
            'navbar-container-background' => 'rgba(255, 255, 255, 0.95)',
            'navbar-container-border' => 'none',
            'navbar-container-box-shadow' => '0 2px 4px rgba(0,0,0,0.1)',
            'navbar-background' => 'transparent',
            'navbar-nav-item-height' => '80px',
            'navbar-nav-item-color' => '#333333',
            'navbar-nav-item-font-size' => '1rem',
            'navbar-nav-item-font-weight' => '400',
            'navbar-nav-item-font-family' => 'inherit',
            'navbar-nav-item-hover-color' => '#005d40',
            'navbar-nav-item-padding-horizontal' => '16px',
            'navbar-dropdown-background' => 'rgba(255, 255, 255, 0.95)',
            'navbar-dropdown-width' => '220px',
            'navbar-dropdown-padding' => '12px',
            'navbar-dropdown-color' => '#333333',
            'navbar-dropdown-font-size' => '0.875rem',
            'navbar-dropdown-font-weight' => '400',
            'navbar-dropdown-nav-item-hover-color' => '#005d40'
        ];
    }

    public function renderForm(array $values = []): string
    {
        $fields = $this->getFields();
        $output = '';

        // Debug-Ausgabe hinzufügen
        if (\rex::isDebugMode()) {
            $output .= '<div class="uk-alert-primary" uk-alert>';
            $output .= '<h4>🐛 Debug: NavbarWidget Werte</h4>';
            $output .= '<pre>' . htmlspecialchars(print_r($values, true)) . '</pre>';
            $output .= '</div>';
        }

        $output .= '<div class="uk-grid-small" uk-grid>';
        
        // Container/Wrapper Einstellungen
        $output .= '<div class="uk-width-1-1">';
        $output .= '<h4><span uk-icon="icon: world; ratio: 0.8" class="uk-margin-small-right uk-text-primary"></span>Container & Wrapper</h4>';
        $output .= '<div class="uk-grid-small uk-child-width-1-2@m" uk-grid>';
        
        // Container Background
        $output .= '<div>';
        $output .= $this->renderFormRow(
            'Container Hintergrund',
            $this->renderColorPicker('navbar-container-background', $values['navbar-container-background'] ?? $fields['navbar-container-background']['default'], ['alpha' => true]),
            'Hintergrund des äußeren Containers'
        );
        $output .= '</div>';
        
        // Container Border
        $output .= '<div>';
        $output .= $this->renderFormRow(
            'Container Border',
            '<input type="text" name="navbar-container-border" value="' . rex_escape($values['navbar-container-border'] ?? $fields['navbar-container-border']['default']) . '" class="uk-input" placeholder="z.B. 1px solid #e5e5e5">',
            'Border des Containers'
        );
        $output .= '</div>';
        
        // Container Box Shadow
        $output .= '<div>';
        $output .= $this->renderFormRow(
            'Container Schatten',
            '<input type="text" name="navbar-container-box-shadow" value="' . rex_escape($values['navbar-container-box-shadow'] ?? $fields['navbar-container-box-shadow']['default']) . '" class="uk-input" placeholder="z.B. 0 2px 4px rgba(0,0,0,0.1)">',
            'Box-Shadow des Containers'
        );
        $output .= '</div>';
        
        $output .= '</div>';
        $output .= '</div>';
        
        $output .= '<div class="uk-width-1-1"><hr class="uk-margin-medium"></div>';
        
        // Navigation Grundeinstellungen
        $output .= '<div class="uk-width-1-2@m">';
        $output .= '<h4><span uk-icon="icon: menu; ratio: 0.8" class="uk-margin-small-right uk-text-primary"></span>Navigation Grundeinstellungen</h4>';
        
        // Navbar Background
        $output .= $this->renderFormRow(
            'Navbar Hintergrund',
            $this->renderColorPicker('navbar-background', $values['navbar-background'] ?? $fields['navbar-background']['default'], ['alpha' => true]),
            'Hintergrundfarbe (unterstützt rgba für Transparenz)'
        );
        
        // Navigation Item Height
        $value = $values['navbar-nav-item-height'] ?? $fields['navbar-nav-item-height']['default'];
        if (is_string($value) && substr($value, -2) === 'px') {
            $value = (int) str_replace('px', '', $value);
        }
        $output .= $this->renderFormRow(
            'Item Höhe',
            $this->renderNumberInput('navbar-nav-item-height', $value, $fields['navbar-nav-item-height']),
            'Höhe der Navigations-Items'
        );
        
        // Navigation Colors
        $output .= $this->renderFormRow(
            'Text Farbe',
            $this->renderColorPicker('navbar-nav-item-color', $values['navbar-nav-item-color'] ?? $fields['navbar-nav-item-color']['default'], ['alpha' => true]),
            'Textfarbe der Navigation'
        );
        
        $output .= $this->renderFormRow(
            'Hover Farbe',
            $this->renderColorPicker('navbar-nav-item-hover-color', $values['navbar-nav-item-hover-color'] ?? $fields['navbar-nav-item-hover-color']['default'], ['alpha' => true]),
            'Farbe bei Hover'
        );
        
        $output .= '</div>';
        
        // Typography & Spacing
        $output .= '<div class="uk-width-1-2@m">';
        $output .= '<h4><span uk-icon="icon: text; ratio: 0.8" class="uk-margin-small-right uk-text-primary"></span>Typography & Spacing</h4>';
        
        // Font Size (freie Eingabe)
        $output .= $this->renderFormRow(
            'Schriftgröße',
            '<input type="text" class="uk-input uk-form-small" name="navbar-nav-item-font-size" value="' . rex_escape($values['navbar-nav-item-font-size'] ?? $fields['navbar-nav-item-font-size']['default']) . '" placeholder="z.B. 1rem, 14px">',
            'z.B. 1rem, 0.875rem, 14px, 1em'
        );
        
        // Font Weight
        $output .= $this->renderFormRow(
            'Schriftstärke',
            $this->renderSelectInput('navbar-nav-item-font-weight', $fields['navbar-nav-item-font-weight']['options'], $values['navbar-nav-item-font-weight'] ?? $fields['navbar-nav-item-font-weight']['default'], ['class' => 'uk-form-small']),
            'Schriftstärke der Navigation'
        );
        
        // Font Family
        $output .= $this->renderFormRow(
            'Schriftart',
            $this->renderFontSelect('navbar-nav-item-font-family', $values['navbar-nav-item-font-family'] ?? $fields['navbar-nav-item-font-family']['default'], $fields['navbar-nav-item-font-family']['options']),
            'Schriftfamilie für Navigation'
        );
        
        // Padding
        $paddingValue = $values['navbar-nav-item-padding-horizontal'] ?? $fields['navbar-nav-item-padding-horizontal']['default'];
        if (is_string($paddingValue) && substr($paddingValue, -2) === 'px') {
            $paddingValue = (int) str_replace('px', '', $paddingValue);
        }
        $output .= $this->renderFormRow(
            'Item Padding',
            $this->renderNumberInput('navbar-nav-item-padding-horizontal', $paddingValue, $fields['navbar-nav-item-padding-horizontal']),
            'Horizontaler Abstand der Items'
        );
        
        $output .= '</div>';
        
        // Dropdown Settings
        $output .= '<div class="uk-width-1-1">';
        $output .= '<hr class="uk-margin-medium">';
        $output .= '<h4><span uk-icon="icon: triangle-down; ratio: 0.8" class="uk-margin-small-right uk-text-primary"></span>Dropdown Einstellungen</h4>';
        $output .= '<div class="uk-grid-small uk-child-width-1-2@m" uk-grid>';
        
        // Dropdown Background
        $output .= '<div>';
        $output .= $this->renderFormRow(
            'Dropdown Hintergrund',
            $this->renderColorPicker('navbar-dropdown-background', $values['navbar-dropdown-background'] ?? $fields['navbar-dropdown-background']['default'], ['alpha' => true]),
            'Hintergrundfarbe der Dropdowns'
        );
        $output .= '</div>';
        
        // Dropdown Color
        $output .= '<div>';
        $output .= $this->renderFormRow(
            'Dropdown Text',
            $this->renderColorPicker('navbar-dropdown-color', $values['navbar-dropdown-color'] ?? $fields['navbar-dropdown-color']['default'], ['alpha' => true]),
            'Textfarbe in Dropdowns'
        );
        $output .= '</div>';
        
        // Dropdown Hover Color
        $output .= '<div>';
        $output .= $this->renderFormRow(
            'Dropdown Hover',
            $this->renderColorPicker('navbar-dropdown-nav-item-hover-color', $values['navbar-dropdown-nav-item-hover-color'] ?? $fields['navbar-dropdown-nav-item-hover-color']['default'], ['alpha' => true]),
            'Hover-Farbe in Dropdowns'
        );
        $output .= '</div>';

        // Dropdown Sublist Color
        $output .= '<div>';
        $output .= $this->renderFormRow(
            'Sub-Level Text',
            $this->renderColorPicker('navbar-dropdown-nav-sublist-item-color', $values['navbar-dropdown-nav-sublist-item-color'] ?? $fields['navbar-dropdown-nav-sublist-item-color']['default'], ['alpha' => true]),
            'Farbe tieferer Ebenen (Fallback: Grau)'
        );
        $output .= '</div>';

        // Dropdown Sublist Hover Color
        $output .= '<div>';
        $output .= $this->renderFormRow(
            'Sub-Level Hover',
            $this->renderColorPicker('navbar-dropdown-nav-sublist-item-hover-color', $values['navbar-dropdown-nav-sublist-item-hover-color'] ?? $fields['navbar-dropdown-nav-sublist-item-hover-color']['default'], ['alpha' => true]),
            'Hover-Farbe tieferer Ebenen'
        );
        $output .= '</div>';
        
        // Dropdown Font Size
        $output .= '<div>';
        $output .= $this->renderFormRow(
            'Dropdown Schriftgröße',
            '<input type="text" class="uk-input uk-form-small" name="navbar-dropdown-font-size" value="' . rex_escape($values['navbar-dropdown-font-size'] ?? $fields['navbar-dropdown-font-size']['default']) . '" placeholder="z.B. 0.875rem">',
            'z.B. 0.875rem, 14px'
        );
        $output .= '</div>';
        
        // Dropdown Font Weight
        $output .= '<div>';
        $output .= $this->renderFormRow(
            'Dropdown Schriftstärke',
            $this->renderSelectInput('navbar-dropdown-font-weight', $fields['navbar-dropdown-font-weight']['options'], $values['navbar-dropdown-font-weight'] ?? $fields['navbar-dropdown-font-weight']['default'], ['class' => 'uk-form-small']),
            'Schriftstärke in Dropdowns'
        );
        $output .= '</div>';
        
        // Dropdown Width & Padding
        $output .= '<div>';
        $dropdownWidthValue = $values['navbar-dropdown-width'] ?? $fields['navbar-dropdown-width']['default'];
        if (is_string($dropdownWidthValue) && substr($dropdownWidthValue, -2) === 'px') {
            $dropdownWidthValue = (int) str_replace('px', '', $dropdownWidthValue);
        }
        $output .= $this->renderFormRow(
            'Dropdown Breite',
            $this->renderNumberInput('navbar-dropdown-width', $dropdownWidthValue, $fields['navbar-dropdown-width']),
            'Breite der Dropdown-Menüs'
        );
        
        $dropdownPaddingValue = $values['navbar-dropdown-padding'] ?? $fields['navbar-dropdown-padding']['default'];
        if (is_string($dropdownPaddingValue) && substr($dropdownPaddingValue, -2) === 'px') {
            $dropdownPaddingValue = (int) str_replace('px', '', $dropdownPaddingValue);
        }
        $output .= $this->renderFormRow(
            'Dropdown Padding',
            $this->renderNumberInput('navbar-dropdown-padding', $dropdownPaddingValue, $fields['navbar-dropdown-padding']),
            'Innenabstand der Dropdowns'
        );
        $output .= '</div>';
        
        $output .= '</div>';
        $output .= '</div>';
        
        $output .= '</div>';

        return $output;
    }

    /**
     * Rendert ein Input-Feld das sowohl Farben als auch Text (rgba) akzeptiert.
     * Aktuell ungenutzt (renderForm() nutzt durchgehend renderColorPicker()).
     */
    private function renderColorOrTextInput(string $name, string $value): string
    {
        $id = 'colorinput-' . str_replace(['[', ']', '.'], ['-', '-', '-'], $name) . '-' . uniqid();
        
        $output = '<div class="uk-position-relative">';
        
        // Text-Input für direkten Wert
        $output .= '<input type="text" name="' . rex_escape($name) . '" id="' . $id . '-input" value="' . rex_escape($value) . '" class="uk-input" placeholder="z.B. rgba(255, 255, 255, 0.95) oder #ffffff">';
        
        // Native Color Picker Button rechts im Input
        $output .= '<input type="color" id="' . $id . '-color" value="' . $this->extractHexColor($value) . '" class="uk-position-absolute uk-position-center-right" style="right: 5px; height: 30px; width: 30px; padding: 0; border: none; border-radius: 3px; cursor: pointer;" title="Farbwähler öffnen">';
        
        $output .= '</div>';
        
        // JavaScript für Synchronisation
        $output .= '<script>';
        $output .= 'document.addEventListener("DOMContentLoaded", function() {';
        $output .= '  const textInput = document.getElementById("' . $id . '-input");';
        $output .= '  const colorInput = document.getElementById("' . $id . '-color");';
        
        // Color Input ändert Text Input
        $output .= '  colorInput.addEventListener("input", function() {';
        $output .= '    textInput.value = this.value;';
        $output .= '    textInput.dispatchEvent(new Event("input"));';
        $output .= '  });';
        
        // Text Input versucht Color Input zu aktualisieren
        $output .= '  textInput.addEventListener("input", function() {';
        $output .= '    const value = this.value.trim();';
        $output .= '    if (value.match(/^#[0-9A-Fa-f]{6}$/)) {';
        $output .= '      colorInput.value = value;';
        $output .= '    }';
        $output .= '  });';
        
        $output .= '});';
        $output .= '</script>';
        
        return $output;
    }
    
    /**
     * Extrahiert eine HEX-Farbe aus verschiedenen Farbformaten
     */
    private function extractHexColor(string $value): string
    {
        // Bereits HEX-Format
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            return $value;
        }
        
        // RGB/RGBA Format extrahieren
        if (preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)/', $value, $matches)) {
            $r = (int)$matches[1];
            $g = (int)$matches[2];
            $b = (int)$matches[3];
            return sprintf("#%02x%02x%02x", $r, $g, $b);
        }
        
        // Fallback
        return '#ffffff';
    }

    /**
     * Rendert ein Font-Select mit Live-Preview wie im TypographyWidget
     */
    private function renderFontSelect(string $name, string $value, array $options): string
    {
        $previewId = 'font-preview-' . str_replace(['-', '_'], '', $name);
        
        $output = '<select name="' . \rex_escape($name) . '" class="uk-select">';
        foreach ($options as $optValue => $optLabel) {
            $selected = ($optValue === $value) ? ' selected' : '';
            $output .= '<option value="' . \rex_escape($optValue) . '"' . $selected . '>' . \rex_escape($optLabel) . '</option>';
        }
        $output .= '</select>';
        
        // Preview-Element
        $output .= '<div id="' . $previewId . '" class="uk-margin-small-top uk-padding-small uk-background-muted uk-border-rounded" style="min-height: 60px; display: flex; align-items: center; justify-content: center;">';
        $output .= '<span class="font-preview-text" style="font-size: 18px; transition: font-family 0.3s ease;">The quick brown fox jumps over the lazy dog</span>';
        $output .= '</div>';
        
        // JavaScript für Live-Preview
        $output .= '<script>';
        $output .= '(function() {';
        $output .= '  const select = document.querySelector(\'select[name="' . $name . '"]\');';
        $output .= '  const preview = document.getElementById(\'' . $previewId . '\').querySelector(\'.font-preview-text\');';
        $output .= '  const systemFonts = [';
        $output .= '    \'Arial\', \'Verdana\', \'Helvetica\', \'Tahoma\', \'Trebuchet MS\',';
        $output .= '    \'Times New Roman\', \'Times\', \'Georgia\', \'Garamond\',';
        $output .= '    \'Courier New\', \'Courier\', \'Monaco\', \'Consolas\',';
        $output .= '    \'system-ui\', \'BlinkMacSystemFont\', \'-apple-system\', \'Segoe UI\',';
        $output .= '    \'Roboto\', \'Ubuntu\', \'Cantarell\', \'Helvetica Neue\',';
        $output .= '    \'Palatino\', \'Bookman\', \'Comic Sans MS\', \'Arial Black\', \'Impact\',';
        $output .= '    \'sans-serif\', \'serif\', \'monospace\', \'cursive\', \'fantasy\'';
        $output .= '  ];';
        $output .= '  function extractFirstFont(fontFamily) {';
        $output .= '    if (!fontFamily) return \'\';';
        $output .= '    const cleaned = fontFamily.replaceAll(\'"\', \'\').replaceAll("\'", \'\').trim();';
        $output .= '    return cleaned.split(\',\')[0].trim();';
        $output .= '  }';
        $output .= '  function isSystemFont(fontName) {';
        $output .= '    return systemFonts.some(sf => sf.toLowerCase() === fontName.toLowerCase());';
        $output .= '  }';
        $output .= '  function updatePreview() {';
        $output .= '    const fullFont = select.value;';
        $output .= '    const firstFont = extractFirstFont(fullFont);';
        $output .= '    if (firstFont && firstFont !== \'inherit\') {';
        $output .= '      preview.style.fontFamily = fullFont;';
        $output .= '      if (!isSystemFont(firstFont)) {';
        // NIE live fonts.googleapis.com kontaktieren - nur lokal bereits heruntergeladene Fonts
        // (siehe GoogleFontsManager). Fehlt die lokale Datei, bleibt es beim Fallback-Stack.
        $output .= '        const link = document.createElement(\'link\');';
        $output .= '        link.rel = \'stylesheet\';';
        $output .= '        link.href = ' . json_encode(\rex_url::addonAssets('uikit_theme_builder', 'fonts/')) . ' + firstFont.replace(/[^a-zA-Z0-9_-]/g, \'_\') + \'.css\';';
        $output .= '        document.head.appendChild(link);';
        $output .= '      }';
        $output .= '    } else {';
        $output .= '      preview.style.fontFamily = \'\';';
        $output .= '    }';
        $output .= '  }';
        $output .= '  if (select) {';
        $output .= '    select.addEventListener(\'change\', updatePreview);';
        $output .= '    updatePreview();';
        $output .= '  }';
        $output .= '})();';
        $output .= '</script>';
        
        return $output;
    }


    public function processFormData(array $formData): array
    {
        $processed = [];
        
        // Debug: Was kommt rein
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('NavbarWidget::processFormData - Input: ' . print_r($formData, true));
        }
        
        foreach ($this->getFields() as $key => $field) {
            if (isset($formData[$key])) {
                $value = trim($formData[$key]);
                
                // Bei Number-Feldern px anhängen, Text/Color-Felder direkt verwenden
                if ($field['type'] === 'number') {
                    $numValue = (int) $value;
                    $numValue = max($field['min'], min($field['max'], $numValue));
                    $processed[$key] = $numValue . 'px';
                } else {
                    // Text und Color-Felder direkt verwenden
                    $processed[$key] = $value;
                }
            } else {
                // Default-Wert verwenden
                $processed[$key] = $field['default'];
                if ($field['type'] === 'number') {
                    $processed[$key] .= 'px';
                }
            }
        }
        
        // Debug: Was kommt raus
        if (\rex::isDebugMode()) {
            \rex_logger::factory()->debug('NavbarWidget::processFormData - Output: ' . print_r($processed, true));
        }
        
        return $processed;
    }

    public function validateFormData(array $data): array
    {
        $errors = [];
        $fields = $this->getFields();
        
        foreach ($data as $key => $value) {
            if (isset($fields[$key])) {
                $field = $fields[$key];
                
                if ($field['type'] === 'number') {
                    $numValue = (int) str_replace('px', '', $value);
                    
                    if ($numValue < $field['min']) {
                        $errors[] = $field['label'] . ' muss mindestens ' . $field['min'] . 'px sein';
                    }
                    
                    if ($numValue > $field['max']) {
                        $errors[] = $field['label'] . ' darf maximal ' . $field['max'] . 'px sein';
                    }
                }
                
                if ($field['type'] === 'color' && !empty($value)) {
                    // Einfache Farb-Validierung
                    if (!preg_match('/^(#[0-9A-Fa-f]{6}|rgba?\([^)]+\)|hsla?\([^)]+\))$/', $value)) {
                        $errors[] = $field['label'] . ' muss eine gültige Farbe sein (HEX, RGB, RGBA, HSL, HSLA)';
                    }
                }
            }
        }
        
        return $errors;
    }

    public function generateLessVariables(array $data): array
    {
        // Keys sind bereits korrekt mit Hyphens
        
        // Mapping für Dropdown Text Color auf Nav Item Color, damit auch die Links gefärbt werden
        if (isset($data['navbar-dropdown-color']) && !empty($data['navbar-dropdown-color'])) {
            $data['navbar-dropdown-nav-item-color'] = $data['navbar-dropdown-color'];
            
            // Fallback für Sublist Items wenn nicht explizit gesetzt
            if (empty($data['navbar-dropdown-nav-sublist-item-color'])) {
                $data['navbar-dropdown-nav-sublist-item-color'] = $data['navbar-dropdown-color'];
            }
        }
        
        // Fallback für Sublist Item Hover
        if (empty($data['navbar-dropdown-nav-sublist-item-hover-color']) && !empty($data['navbar-dropdown-nav-item-hover-color'])) {
            $data['navbar-dropdown-nav-sublist-item-hover-color'] = $data['navbar-dropdown-nav-item-hover-color'];
        }

        // UIKit erwartet: @navbar-nav-item-height, @navbar-nav-item-color etc.
        return $data;
    }

    public function generateLess(array $data): string
    {
        $less = '';
        
        // Nur Custom-Variablen die UIKit nicht kennt via CSS-Regeln anwenden
        // UIKit kennt KEINE navbar-container-* Variablen
        if (!empty($data['navbar-container-background']) || 
            !empty($data['navbar-container-border']) || 
            !empty($data['navbar-container-box-shadow'])) {
            
            $less .= "// Navbar Widget - Custom Container Styling\n";
            $less .= ".uk-navbar-container:not(.uk-navbar-transparent) {\n";
            
            if (!empty($data['navbar-container-background'])) {
                $less .= "  background: @navbar-container-background;\n";
            }
            if (!empty($data['navbar-container-border'])) {
                $less .= "  border: @navbar-container-border;\n";
            }
            if (!empty($data['navbar-container-box-shadow'])) {
                $less .= "  box-shadow: @navbar-container-box-shadow;\n";
            }
            
            $less .= "}\n\n";
        }
        
        // Font-Weight für Navigation (erste Ebene)
        if (!empty($data['navbar-nav-item-font-weight']) && $data['navbar-nav-item-font-weight'] !== '400') {
            $less .= "// Navbar Font Weight\n";
            $less .= ".uk-navbar-nav > li > a {\n";
            $less .= "  font-weight: @navbar-nav-item-font-weight;\n";
            $less .= "}\n\n";
        }
        
        // Dropdown Typography
        if (!empty($data['navbar-dropdown-font-size']) || 
            (!empty($data['navbar-dropdown-font-weight']) && $data['navbar-dropdown-font-weight'] !== '400')) {
            
            $less .= "// Navbar Dropdown Typography\n";
            $less .= ".uk-navbar-dropdown-nav > li > a {\n";
            
            if (!empty($data['navbar-dropdown-font-size'])) {
                $less .= "  font-size: @navbar-dropdown-font-size;\n";
            }
            if (!empty($data['navbar-dropdown-font-weight']) && $data['navbar-dropdown-font-weight'] !== '400') {
                $less .= "  font-weight: @navbar-dropdown-font-weight;\n";
            }
            
            $less .= "}\n\n";
        }
        
        return $less;
    }
}
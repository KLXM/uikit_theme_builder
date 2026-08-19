<?php

namespace UikitThemeBuilder\Widget;

class ShadowWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'Shadows';
    }

    public function getKey(): string
    {
        return 'shadows';
    }

    public function getDescription(): string
    {
        return 'Box-Shadow Definitionen für verschiedene Schattierungen';
    }

    public function getDefaultValues(): array
    {
        return [
            'global-small-box-shadow' => '0 2px 8px rgba(0,0,0,0.08)',
            'global-medium-box-shadow' => '0 5px 15px rgba(0,0,0,0.08)',
            'global-large-box-shadow' => '0 14px 25px rgba(0,0,0,0.16)',
            'global-xlarge-box-shadow' => '0 28px 50px rgba(0,0,0,0.16)',
            'global-small-drop-shadow' => 'drop-shadow(0 2px 8px rgba(0,0,0,0.08))',
            'global-medium-drop-shadow' => 'drop-shadow(0 5px 15px rgba(0,0,0,0.08))',
            'global-large-drop-shadow' => 'drop-shadow(0 14px 25px rgba(0,0,0,0.16))',
            'global-xlarge-drop-shadow' => 'drop-shadow(0 28px 50px rgba(0,0,0,0.16))'
        ];
    }

    public function getFields(): array
    {
        return [
            'global-small-box-shadow' => [
                'label' => 'Small Box Shadow',
                'type' => 'text',
                'default' => '0 2px 8px rgba(0,0,0,0.08)',
                'placeholder' => '0 2px 8px rgba(0,0,0,0.08)',
                'help' => 'Kleiner Box-Shadow für subtile Elevation'
            ],
            'global-medium-box-shadow' => [
                'label' => 'Medium Box Shadow',
                'type' => 'text',
                'default' => '0 5px 15px rgba(0,0,0,0.08)',
                'placeholder' => '0 5px 15px rgba(0,0,0,0.08)',
                'help' => 'Mittlerer Box-Shadow für normale Elevation'
            ],
            'global-large-box-shadow' => [
                'label' => 'Large Box Shadow',
                'type' => 'text',
                'default' => '0 14px 25px rgba(0,0,0,0.16)',
                'placeholder' => '0 14px 25px rgba(0,0,0,0.16)',
                'help' => 'Großer Box-Shadow für hohe Elevation'
            ],
            'global-xlarge-box-shadow' => [
                'label' => 'XLarge Box Shadow',
                'type' => 'text',
                'default' => '0 28px 50px rgba(0,0,0,0.16)',
                'placeholder' => '0 28px 50px rgba(0,0,0,0.16)',
                'help' => 'Sehr großer Box-Shadow für maximale Elevation'
            ],
            'global-small-drop-shadow' => [
                'label' => 'Small Drop Shadow',
                'type' => 'text',
                'default' => 'drop-shadow(0 2px 8px rgba(0,0,0,0.08))',
                'placeholder' => 'drop-shadow(0 2px 8px rgba(0,0,0,0.08))',
                'help' => 'Kleiner Drop-Shadow für SVGs und Filter'
            ],
            'global-medium-drop-shadow' => [
                'label' => 'Medium Drop Shadow',
                'type' => 'text',
                'default' => 'drop-shadow(0 5px 15px rgba(0,0,0,0.08))',
                'placeholder' => 'drop-shadow(0 5px 15px rgba(0,0,0,0.08))',
                'help' => 'Mittlerer Drop-Shadow für SVGs und Filter'
            ],
            'global-large-drop-shadow' => [
                'label' => 'Large Drop Shadow',
                'type' => 'text',
                'default' => 'drop-shadow(0 14px 25px rgba(0,0,0,0.16))',
                'placeholder' => 'drop-shadow(0 14px 25px rgba(0,0,0,0.16))',
                'help' => 'Großer Drop-Shadow für SVGs und Filter'
            ],
            'global-xlarge-drop-shadow' => [
                'label' => 'XLarge Drop Shadow',
                'type' => 'text',
                'default' => 'drop-shadow(0 28px 50px rgba(0,0,0,0.16))',
                'placeholder' => 'drop-shadow(0 28px 50px rgba(0,0,0,0.16))',
                'help' => 'Sehr großer Drop-Shadow für SVGs und Filter'
            ]
        ];
    }

    public function renderForm(array $values = []): string
    {
        $fields = $this->getFields();
        $output = '';

        foreach ($fields as $key => $field) {
            $value = $values[$key] ?? $field['default'];
            
            $input = '<div>';
            
            // Input mit Designer Button
            $input .= '<div class="uk-flex uk-flex-middle">';
            $input .= $this->renderTextInput($key, $value, ['placeholder' => $field['placeholder'], 'class' => 'uk-flex-1 shadow-input']);
            $input .= '<button type="button" class="uk-button uk-button-default uk-button-small uk-margin-small-left shadow-designer-btn" data-target="' . $key . '">';
            $input .= '<span uk-icon="icon: paint-bucket; ratio: 0.8"></span> Designer';
            $input .= '</button>';
            $input .= '</div>';
            
            // Live Preview für Shadows
            $shadowStyle = strpos($key, 'drop-shadow') !== false ? 
                'filter: ' . $value . ';' : 
                'box-shadow: ' . $value . ';';
                
            $input .= '<div class="uk-margin-small-top uk-flex uk-flex-middle">';
            $input .= '<span class="uk-text-meta uk-margin-small-right">Preview:</span>';
            $input .= '<div class="shadow-preview" style="width: 60px; height: 40px; background: white; border: 1px solid #ddd; ' . $shadowStyle . '" data-preview="' . $key . '"></div>';
            $input .= '</div>';
            
            // Shadow Designer Modal (versteckt)
            $input .= $this->renderShadowDesigner($key, $value);
            
            $input .= '</div>';
            
            if (!empty($field['help'])) {
                $input .= '<div class="uk-text-meta uk-text-small uk-margin-small-top">' . htmlspecialchars($field['help']) . '</div>';
            }
            
            $output .= $this->renderFormRow($field['label'], $input);
        }

        return $output;
    }

    public function processFormData(array $formData): array
    {
        $processed = [];
        $defaults = $this->getDefaultValues();
        
        foreach ($this->getFields() as $key => $field) {
            if (isset($formData[$key])) {
                $value = trim($formData[$key]);
                $processed[$key] = !empty($value) ? $value : ($defaults[$key] ?? '');
            } else {
                // Fallback auf Standardwert
                $processed[$key] = $defaults[$key] ?? '';
            }
        }
        return $processed;
    }

    public function validateFormData(array $data): array
    {
        $errors = [];
        
        foreach ($data as $key => $value) {
            // Basic CSS Syntax Check für Shadows
            if (strpos($key, 'box-shadow') !== false) {
                // Box-Shadow Format validieren
                if (!preg_match('/^[\d\s\-px(),rgba\.]+$/', $value)) {
                    $errors[] = 'Ungültiges Box-Shadow Format für ' . $key;
                }
            } elseif (strpos($key, 'drop-shadow') !== false) {
                // Drop-Shadow Format validieren
                if (!preg_match('/^drop-shadow\([\d\s\-px(),rgba\.]+\)$/', $value)) {
                    $errors[] = 'Ungültiges Drop-Shadow Format für ' . $key . ' (muss mit drop-shadow() beginnen)';
                }
            }
        }
        
        return $errors;
    }

    public function generateLessVariables(array $data): array
    {
        $variables = [];
        
        // Keys sind bereits in korrektem UIkit Format (z.B. global-small-box-shadow)
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                $variables[$key] = $value;
            }
        }
        
        return $variables;
    }
    
    /**
     * Rendert das Shadow Designer Modal
     */
    private function renderShadowDesigner(string $key, string $value): string
    {
        $isDropShadow = strpos($key, 'drop-shadow') !== false;
        
        // Shadow-Werte parsen (falls vorhanden)
        $shadowValues = $this->parseShadowValue($value);
        
        $modal = '<div id="shadow-designer-' . $key . '" uk-modal style="z-index: 999999 !important;">';
        $modal .= '<div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical" style="width: 85%; max-width: 700px; z-index: 999999 !important;">';
        
        // Modal Header
        $modal .= '<div class="uk-modal-header">';
        $modal .= '<h2 class="uk-modal-title">Shadow Designer</h2>';
        $modal .= '<div class="uk-flex uk-flex-middle">';
        $modal .= '<button class="uk-button uk-button-primary uk-button-small uk-margin-small-right uk-modal-close" type="button">Fertig</button>';
        $modal .= '<button class="uk-modal-close-default" type="button" uk-close></button>';
        $modal .= '</div>';
        $modal .= '</div>';
        
        // Modal Body mit 2-Spalten Layout
        $modal .= '<div class="uk-grid-divider" uk-grid>';
        
        // Linke Spalte: Controls
        $modal .= '<div class="uk-width-1-2@m">';
        $modal .= '<h3 class="uk-heading-bullet">Einstellungen</h3>';
        
        // CSS für Range-Slider
        $modal .= '<style>';
        $modal .= '#shadow-designer-' . $key . ' .uk-range {';
        $modal .= '  -webkit-appearance: none;';
        $modal .= '  appearance: none;';
        $modal .= '  height: 8px;';
        $modal .= '  background: #e5e5e5;';
        $modal .= '  border-radius: 4px;';
        $modal .= '  outline: none;';
        $modal .= '}';
        $modal .= '#shadow-designer-' . $key . ' .uk-range::-webkit-slider-thumb {';
        $modal .= '  -webkit-appearance: none;';
        $modal .= '  appearance: none;';
        $modal .= '  width: 20px;';
        $modal .= '  height: 20px;';
        $modal .= '  background: #005d40;';
        $modal .= '  border-radius: 50%;';
        $modal .= '  cursor: pointer;';
        $modal .= '  border: 2px solid white;';
        $modal .= '  box-shadow: 0 2px 4px rgba(0,0,0,0.2);';
        $modal .= '}';
        $modal .= '#shadow-designer-' . $key . ' .uk-range::-moz-range-thumb {';
        $modal .= '  width: 20px;';
        $modal .= '  height: 20px;';
        $modal .= '  background: #005d40;';
        $modal .= '  border-radius: 50%;';
        $modal .= '  cursor: pointer;';
        $modal .= '  border: 2px solid white;';
        $modal .= '  box-shadow: 0 2px 4px rgba(0,0,0,0.2);';
        $modal .= '}';
        $modal .= '#shadow-designer-' . $key . ' .uk-range::-webkit-slider-track {';
        $modal .= '  height: 8px;';
        $modal .= '  background: #e5e5e5;';
        $modal .= '  border-radius: 4px;';
        $modal .= '}';
        $modal .= '#shadow-designer-' . $key . ' .uk-range::-moz-range-track {';
        $modal .= '  height: 8px;';
        $modal .= '  background: #e5e5e5;';
        $modal .= '  border-radius: 4px;';
        $modal .= '  border: none;';
        $modal .= '}';
        $modal .= '</style>';
        
        // Shadow Controls
        if (!$isDropShadow) {
            $modal .= '<div class="uk-margin-small">';
            $modal .= '<div class="uk-flex uk-flex-between uk-flex-middle">';
            $modal .= '<label class="uk-form-label uk-margin-remove">Horizontal Offset</label>';
            $modal .= '<span class="uk-text-meta shadow-value-x">' . $shadowValues['x'] . 'px</span>';
            $modal .= '</div>';
            $modal .= '<input type="range" class="uk-range uk-width-1-1 uk-margin-small-top" min="-50" max="50" value="' . $shadowValues['x'] . '" data-control="x" data-target="' . $key . '">';
            $modal .= '</div>';
            
            $modal .= '<div class="uk-margin-small">';
            $modal .= '<div class="uk-flex uk-flex-between uk-flex-middle">';
            $modal .= '<label class="uk-form-label uk-margin-remove">Vertical Offset</label>';
            $modal .= '<span class="uk-text-meta shadow-value-y">' . $shadowValues['y'] . 'px</span>';
            $modal .= '</div>';
            $modal .= '<input type="range" class="uk-range uk-width-1-1 uk-margin-small-top" min="-50" max="50" value="' . $shadowValues['y'] . '" data-control="y" data-target="' . $key . '">';
            $modal .= '</div>';
        }
        
        $modal .= '<div class="uk-margin-small">';
        $modal .= '<div class="uk-flex uk-flex-between uk-flex-middle">';
        $modal .= '<label class="uk-form-label uk-margin-remove">Blur Radius</label>';
        $modal .= '<span class="uk-text-meta shadow-value-blur">' . $shadowValues['blur'] . 'px</span>';
        $modal .= '</div>';
        $modal .= '<input type="range" class="uk-range uk-width-1-1 uk-margin-small-top" min="0" max="50" value="' . $shadowValues['blur'] . '" data-control="blur" data-target="' . $key . '">';
        $modal .= '</div>';
        
        $modal .= '<div class="uk-margin-small">';
        $modal .= '<label class="uk-form-label">Shadow Color</label>';
        $modal .= '<input type="color" class="uk-input" value="' . $shadowValues['color'] . '" data-control="color" data-target="' . $key . '" style="height: 40px;">';
        $modal .= '</div>';
        
        $modal .= '<div class="uk-margin-small">';
        $modal .= '<div class="uk-flex uk-flex-between uk-flex-middle">';
        $modal .= '<label class="uk-form-label uk-margin-remove">Opacity</label>';
        $modal .= '<span class="uk-text-meta shadow-value-opacity">' . $shadowValues['opacity'] . '</span>';
        $modal .= '</div>';
        $modal .= '<input type="range" class="uk-range uk-width-1-1 uk-margin-small-top" min="0" max="1" step="0.1" value="' . $shadowValues['opacity'] . '" data-control="opacity" data-target="' . $key . '">';
        $modal .= '</div>';
        
        // Inset Option (nur für box-shadow)
        if (!$isDropShadow) {
            $modal .= '<div class="uk-margin-small">';
            $modal .= '<label class="uk-form-label">';
            $modal .= '<input type="checkbox" class="uk-checkbox uk-margin-small-right" ' . ($shadowValues['inset'] ? 'checked' : '') . ' data-control="inset" data-target="' . $key . '">';
            $modal .= 'Inset Shadow (innerer Schatten)';
            $modal .= '</label>';
            $modal .= '</div>';
            
            // Neumorphismus Presets
            $modal .= '<div class="uk-margin-small">';
            $modal .= '<label class="uk-form-label">Neumorphismus Presets</label>';
            $modal .= '<div class="uk-grid-small uk-child-width-1-2" uk-grid>';
            
            // Raised/Elevated Preset
            $modal .= '<div>';
            $modal .= '<button type="button" class="uk-button uk-button-default uk-button-small uk-width-1-1 neumorph-preset" ';
            $modal .= 'data-target="' . $key . '" data-preset="raised" title="Erhöhte Oberfläche">';
            $modal .= '<span uk-icon="icon: plus; ratio: 0.7"></span> Raised';
            $modal .= '</button>';
            $modal .= '</div>';
            
            // Pressed/Inset Preset
            $modal .= '<div>';
            $modal .= '<button type="button" class="uk-button uk-button-default uk-button-small uk-width-1-1 neumorph-preset" ';
            $modal .= 'data-target="' . $key . '" data-preset="pressed" title="Eingedrückte Oberfläche">';
            $modal .= '<span uk-icon="icon: minus; ratio: 0.7"></span> Pressed';
            $modal .= '</button>';
            $modal .= '</div>';
            
            // Flat Preset
            $modal .= '<div>';
            $modal .= '<button type="button" class="uk-button uk-button-default uk-button-small uk-width-1-1 neumorph-preset" ';
            $modal .= 'data-target="' . $key . '" data-preset="flat" title="Flache Oberfläche">';
            $modal .= '<span uk-icon="icon: ban; ratio: 0.7"></span> Flat';
            $modal .= '</button>';
            $modal .= '</div>';
            
            // Floating Preset
            $modal .= '<div>';
            $modal .= '<button type="button" class="uk-button uk-button-default uk-button-small uk-width-1-1 neumorph-preset" ';
            $modal .= 'data-target="' . $key . '" data-preset="floating" title="Schwebende Oberfläche">';
            $modal .= '<span uk-icon="icon: cloud; ratio: 0.7"></span> Floating';
            $modal .= '</button>';
            $modal .= '</div>';
            
            $modal .= '</div>';
            $modal .= '</div>';
        }
        
        // Linke Spalte schließen
        $modal .= '</div>';
        
        // Rechte Spalte: Preview
        $modal .= '<div class="uk-width-1-2@m">';
        $modal .= '<h3 class="uk-heading-bullet uk-margin-small-bottom">Live Preview</h3>';
        
        // Live Preview Bereich
        $modal .= '<div class="uk-card uk-card-muted uk-card-body uk-text-center uk-padding-small">';
        $modal .= '<div class="uk-margin-small-bottom">';
        $modal .= '<h5 class="uk-text-muted uk-margin-remove-bottom">Shadow-Effekt</h5>';
        $modal .= '<div class="shadow-designer-preview" style="width: 100px; height: 60px; background: white; border: 1px solid #ddd; margin: 15px auto; border-radius: 4px;"></div>';
        $modal .= '</div>';
        
        // CSS Output
        $modal .= '<div class="uk-margin-small">';
        $modal .= '<h6 class="uk-text-muted uk-margin-remove-bottom">CSS Output</h6>';
        $modal .= '<div class="uk-background-muted uk-padding-small uk-border-rounded">';
        $modal .= '<code class="shadow-css-output uk-text-small" style="word-break: break-all; font-size: 11px;">box-shadow: none;</code>';
        $modal .= '</div>';
        $modal .= '</div>';
        
        // Neumorphismus Info (kompakter)
        if (!$isDropShadow) {
            $modal .= '<div class="uk-margin-small">';
            $modal .= '<div class="uk-text-small uk-text-muted uk-text-center">';
            $modal .= '💡 <strong>Tipp:</strong> Kombinieren Sie helle + dunkle Schatten für Neumorphismus';
            $modal .= '</div>';
            $modal .= '</div>';
        }
        
        $modal .= '</div>';
        
        // Grid schließen
        $modal .= '</div>';
        
        $modal .= '</div>';
        $modal .= '</div>';
        
        return $modal;
    }
    
    /**
     * Parst einen Shadow-Wert in einzelne Komponenten
     */
    private function parseShadowValue(string $value): array
    {
        $defaults = [
            'x' => 0,
            'y' => 2,
            'blur' => 8,
            'spread' => 0,
            'color' => '#000000',
            'opacity' => 0.08,
            'inset' => false
        ];
        
        if (empty($value) || $value === 'none') {
            return $defaults;
        }
        
        // Prüfen ob inset vorhanden ist
        $isInset = strpos($value, 'inset') !== false;
        $defaults['inset'] = $isInset;
        
        // inset aus dem String entfernen für weitere Parsing
        $cleanValue = str_replace('inset', '', $value);
        $cleanValue = trim($cleanValue);
        
        // Einfache Regex für grundlegende Shadow-Werte
        if (preg_match('/(-?\d+)px\s+(-?\d+)px\s+(\d+)px\s+rgba?\(([^)]+)\)/', $cleanValue, $matches)) {
            $defaults['x'] = (int)$matches[1];
            $defaults['y'] = (int)$matches[2];
            $defaults['blur'] = (int)$matches[3];
            
            // RGBA Farbe parsen
            $rgba = explode(',', $matches[4]);
            if (count($rgba) >= 3) {
                $r = (int)trim($rgba[0]);
                $g = (int)trim($rgba[1]);
                $b = (int)trim($rgba[2]);
                $a = isset($rgba[3]) ? (float)trim($rgba[3]) : 1;
                
                $defaults['color'] = sprintf("#%02x%02x%02x", $r, $g, $b);
                $defaults['opacity'] = $a;
            }
        }
        
        return $defaults;
    }
}
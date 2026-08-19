<?php

namespace UikitThemeBuilder\Widget;

/**
 * Abstract Widget Class
 * Basis-Klasse für alle Theme Builder Widgets
 */
abstract class AbstractWidget
{
    public function __construct()
    {
        // Widgets definieren ihre Eigenschaften über abstrakte Methoden
    }

    // Abstrakte Methoden für Widget-Eigenschaften
    abstract public function getName(): string;
    abstract public function getKey(): string;
    abstract public function getDescription(): string;
    abstract public function getDefaultValues(): array;
    abstract public function getFields(): array;

    // Interface-Methoden für Form-Handling
    abstract public function renderForm(array $values = []): string;
    abstract public function processFormData(array $formData): array;
    abstract public function validateFormData(array $data): array;
    abstract public function generateLessVariables(array $data): array;    // Helper methods
    protected function renderFormRow(string $label, string $input, string $help = ''): string
    {
        $html = '<div class="uk-margin">';
        $html .= '<label class="uk-form-label">' . htmlspecialchars($label) . '</label>';
        $html .= '<div class="uk-form-controls">';
        $html .= $input;
        if ($help) {
            $html .= '<div class="uk-text-meta uk-margin-small-top">' . htmlspecialchars($help) . '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    protected function renderColorPicker(string $name, string $value = '#000000', array $attributes = []): string
    {
        $id = 'color-picker-' . str_replace('_', '-', $name);
        
        $html = '<div class="uk-inline uk-width-1-1">';
        
        // Color Preview Button für Pickr
        $html .= '<div class="color-picker-wrapper" style="position: relative;">';
        $html .= '<div class="' . $id . '" style="width: 40px; height: 40px; border-radius: 4px; border: 2px solid #b0b0b0; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08); cursor: pointer; background-color: ' . rex_escape($value) . ';"></div>';
        $html .= '</div>';
        
        // Hidden Input für Form-Submit
        $html .= '<input type="hidden" id="' . $id . '-input" name="' . rex_escape($name) . '" value="' . rex_escape($value) . '">';
        
        $html .= '</div>';
        
        // CSS und JS für Pickr (statisch einmalig eingebunden)
        static $pickrAssetsIncluded = false;
        if (!$pickrAssetsIncluded) {
            $html .= $this->renderPickrAssets();
            $pickrAssetsIncluded = true;
        }
        
        // Eindeutige Picker-ID generieren
        $pickerId = 'pickr_' . uniqid();
        
        // Individuelle Picker-Initialisierung mit Standard-Swatches
        $html .= '
        <script>
        (function() {
            function init' . $pickerId . '() {
                if (typeof Pickr === "undefined") {
                    console.warn("Pickr not loaded yet, retrying...");
                    setTimeout(init' . $pickerId . ', 100);
                    return;
                }
                
                // Prüfen ob Element existiert
                const element = document.querySelector(".' . $id . '");
                if (!element) {
                    setTimeout(init' . $pickerId . ', 100);
                    return;
                }
                
                // Prüfen ob schon initialisiert
                if (element.dataset.pickrInitialized) {
                    return;
                }
                
                const ' . $pickerId . ' = Pickr.create({
                    el: ".' . $id . '",
                    theme: "classic",
                    default: "' . \rex_escape($value) . '",
                    defaultRepresentation: "RGBA",
                    
                    swatches: [
                        "transparent",  // Transparent
                        "#ffffff",      // Weiß
                        "#000000",      // Schwarz
                        "#1e87f0",      // UIKit Primary Blue
                        "#f0506e",      // UIKit Danger Red
                        "#32d296",      // UIKit Success Green
                        "#faa05a",      // UIKit Warning Orange
                        "#664dc6",      // UIKit Purple
                        "#222222",      // Dark Gray
                        "#666666",      // Medium Gray
                        "#999999",      // Light Gray
                        "#cccccc",      // Very Light Gray
                        "#e5e5e5",      // Border Gray
                        "#f8f9fa"       // Background Gray
                    ],
                    
                    components: {
                        preview: true,
                        opacity: true,
                        hue: true,
                        interaction: {
                            hex: true,
                            rgba: true,
                            hsla: true,
                            hsva: true,
                            input: true,
                            save: true
                        }
                    }
                });
                
                // Als initialisiert markieren
                element.dataset.pickrInitialized = "true";
                
                // Funktion zur Farbübernahme
                const updateColor = (color) => {
                    try {
                        const rgba = color.toRGBA();
                        let colorString;
                        // Bei voll-opaken Farben: 6-stelliges Hex (LESS-kompatibel)
                        // Bei Transparenz: rgba() (ebenfalls LESS-kompatibel)
                        if (rgba[3] >= 0.99) {
                            colorString = color.toHEXA().toString().slice(0, 7);
                        } else {
                            colorString = color.toRGBA().toString();
                        }
                        
                        // Update hidden input
                        const input = document.getElementById("' . $id . '-input");
                        if (input) {
                            input.value = colorString;
                        }
                        
                        // Update preview
                        const preview = document.querySelector(".' . $id . '");
                        if (preview) {
                            preview.style.backgroundColor = colorString;
                        }
                        
                    } catch (e) {
                        console.warn("Fehler bei Farbkonvertierung:", e);
                    }
                };
                
                // Events für verschiedene Pickr-Interaktionen
                ' . $pickerId . '.on("change", updateColor);
                ' . $pickerId . '.on("changestop", updateColor);
                ' . $pickerId . '.on("save", (color) => {
                    updateColor(color);
                    ' . $pickerId . '.hide();
                });
            }
            
            // Initialisierung starten
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", init' . $pickerId . ');
            } else {
                init' . $pickerId . '();
            }
        })();
        </script>';
        
        return $html;
    }

    /**
     * Lädt Pickr CSS und JS (einmalig) - Standard-Swatches für alle Widgets
     */
    protected function renderPickrAssets(): string
    {
        $cssUrl = \rex_url::addonAssets('uikit_theme_builder', 'pickr/classic.min.css');
        $jsUrl  = \rex_url::addonAssets('uikit_theme_builder', 'pickr/pickr.min.js');
        return '
        <link rel="stylesheet" href="' . $cssUrl . '">
        <script src="' . $jsUrl . '"></script>
        <style>
        .pickr .pcr-button {
            border: 2px solid #b0b0b0 !important;
            border-radius: 4px !important;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08) !important;
        }
        .pickr .pcr-button:hover,
        .pickr .pcr-button:focus {
            border-color: #555 !important;
        }
        </style>
        ';
    }
    
    protected function renderNumberInput(string $name, int $value = 0, array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($val) . '"';
        }
        
        return '<input type="number" name="' . htmlspecialchars($name) . '" value="' . $value . '" class="uk-input uk-form-width-small"' . $attrs . '>';
    }
    
    protected function renderTextInput(string $name, string $value = '', array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($val) . '"';
        }
        
        return '<input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" class="uk-input"' . $attrs . '>';
    }
    
    protected function renderSelectInput(string $name, array $options, string $selected = '', array $attributes = []): string
    {
        // CSS-Klassen sammeln
        $classes = ['uk-select'];
        if (isset($attributes['class'])) {
            $classes[] = $attributes['class'];
            unset($attributes['class']);
        }
        
        // Restliche Attribute
        $attrs = ' class="' . implode(' ', $classes) . '"';
        foreach ($attributes as $key => $val) {
            $attrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($val) . '"';
        }
        
        $html = '<select name="' . htmlspecialchars($name) . '"' . $attrs . '>';
        
        foreach ($options as $value => $label) {
            $selectedAttr = ($value == $selected) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '"' . $selectedAttr . '>' . htmlspecialchars($label) . '</option>';
        }
        
        $html .= '</select>';
        
        return $html;
    }
    
    protected function renderIconPicker(string $name, string $value = ''): string
    {
        // Icon Picker nutzt das JavaScript aus uikit-icon-picker.js
        // Die CSS-Klasse "uk-iconpicker" triggert die Initialisierung
        return '<input type="text" 
                       name="' . htmlspecialchars($name) . '" 
                       class="uk-input uk-iconpicker" 
                       value="' . htmlspecialchars($value) . '" 
                       placeholder="Icon auswählen...">';
    }
}
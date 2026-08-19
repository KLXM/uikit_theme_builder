<?php

/**
 * Einfacher Pickr Color Picker Widget für REDAXO Backend
 * 
 * Ein simpler Input-Replacement Color Picker basierend auf Pickr.js
 * Funktioniert wie ein normaler Input, öffnet aber Pickr beim Klick.
 * 
 * @author UIKit Theme Builder
 * @version 2.0.0
 */
class PickrColorPickerWidget
{
    /**
     * Rendert das einfache Color Picker Widget
     * 
     * @param string $fieldName Name des Input-Felds
     * @param string $currentValue Aktuell ausgewählte Farbe (hex, rgb, hsl)
     * @param array $options Zusätzliche Optionen
     * @return string HTML-Code des Widgets
     */
    public static function render($fieldName, $currentValue = '#1e87f0', $options = [])
    {
        // Default-Optionen
        $defaults = [
            'format' => 'hex', // hex, rgb, hsl
            'alpha' => false,
            'class' => 'uk-input',
            'id' => 'pickr_' . uniqid(),
            'placeholder' => 'Farbe auswählen...'
        ];
        
        $options = array_merge($defaults, $options);
        $fieldId = $options['id'];
        
        // Assets nur einmal laden
        static $assetsLoaded = false;
        $assets = '';
        
        if (!$assetsLoaded) {
            $assets = self::getAssets();
            $assetsLoaded = true;
        }
        
        // HTML für das einfache Widget (wie im Colors Widget)
        $html = $assets;
        
        // Container
        $html .= '<div class="uk-inline uk-width-1-1">';
        $html .= '<div class="color-picker-wrapper" style="position: relative;">';
        
        // Pickr Element (wird zum Button)
        $html .= '<div class="pickr-element" id="' . $fieldId . '_pickr"></div>';
        
        $html .= '</div>'; // wrapper
        
        // Hidden Input für den Wert
        $html .= '<input type="hidden" ';
        $html .= 'id="' . $fieldId . '" ';
        $html .= 'name="' . htmlspecialchars($fieldName) . '" ';
        $html .= 'value="' . htmlspecialchars($currentValue) . '" ';
        $html .= '/>';
        
        $html .= '</div>'; // inline container
        
        // Initialization Script
        $html .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            initializeSimplePickr("' . $fieldId . '", ' . json_encode($options) . ');
        });
        </script>';
        
        return $html;
    }
    
    /**
     * CSS und JavaScript Assets
     */
    private static function getAssets()
    {
        $html = '';
        
        // CSS für einfaches Pickr-Button Design
        $html .= '<style>
        .color-picker-wrapper {
            display: inline-block;
        }
        
        /* Pickr Element als sichtbarer Button */
        .pickr-element {
            display: inline-block !important;
            position: relative !important;
            left: auto !important;
            opacity: 1 !important;
            pointer-events: all !important;
            visibility: visible !important;
        }
        
        /* Pickr Button Styling */
        .pcr-button {
            width: 40px !important;
            height: 40px !important;
            border-radius: 4px !important;
            border: 1px solid #e5e5e5 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }
        
        .pcr-button:hover {
            transform: scale(1.05) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
        }
        
        /* Pickr Popup Styling */
        .pcr-app {
            z-index: 9999 !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15) !important;
            border: 1px solid #e5e5e5 !important;
            border-radius: 8px !important;
        }
        </style>';
        
        // JavaScript für einfache Pickr Integration
        $html .= '<script>
        function initializeSimplePickr(fieldId, options) {
            const hiddenInput = document.getElementById(fieldId);
            const pickerElement = document.getElementById(fieldId + "_pickr");
            const container = hiddenInput.closest(".uk-inline");
            
            if (!hiddenInput || !pickerElement) {
                console.error("Required elements not found for:", fieldId);
                return;
            }
            
            // Fallback check für Pickr
            if (typeof Pickr === "undefined") {
                console.warn("Pickr nicht verfügbar - verwende nativen Color Input");
                setupNativeColorFallback(hiddenInput, container);
                return;
            }
            
            try {
                // Einfache Pickr Konfiguration (wie im Colors Widget)
                const pickr = Pickr.create({
                    el: pickerElement,
                    theme: "nano",
                    default: hiddenInput.value || "#1e87f0",
                    
                    swatches: [
                        "#1e87f0", "#f0506e", "#32d296", "#faa05a", "#664dc6",
                        "#222222", "#666666", "#999999", "#cccccc", "#ffffff"
                    ],
                    
                    defaultRepresentation: "RGBA",
                    
                    components: {
                        preview: true,
                        opacity: options.alpha === true,
                        hue: true,
                        
                        interaction: {
                            hex: true,
                            rgba: true,
                            hsla: false,
                            input: true,
                            clear: false,
                            save: true
                        }
                    },
                    
                    strings: {
                        save: "OK",
                        cancel: "Abbrechen"
                    }
                });
                
                // Pickr Events
                pickr.on("change", (color) => {
                    updateColorValue(color, hiddenInput, options.format);
                });
                
                pickr.on("save", (color) => {
                    if (color) {
                        updateColorValue(color, hiddenInput, options.format);
                    }
                    pickr.hide();
                });
                
                // Store instance
                container.pickrInstance = pickr;
                
            } catch (error) {
                console.error("Error initializing Pickr:", error);
                setupNativeColorFallback(hiddenInput, container);
            }
        }
        
        function updateColorValue(color, hiddenInput, format) {
            let colorValue;
            const rgba = color.toRGBA();
            
            switch(format) {
                case "hsl":
                    colorValue = color.toHSLA().toString();
                    break;
                case "hex":
                    // Bei voll-opaken Farben: 6-stelliges Hex (LESS-kompatibel, kein 8-stelliges HEXA)
                    // Bei Transparenz: rgba() (ebenfalls LESS-kompatibel)
                    if (rgba[3] >= 0.99) {
                        colorValue = color.toHEXA().toString().slice(0, 7);
                    } else {
                        colorValue = color.toRGBA().toString();
                    }
                    break;
                case "rgb":
                default:
                    // Bei voll-opaken Farben: 6-stelliges Hex für bessere LESS-Kompatibilität
                    if (rgba[3] >= 0.99) {
                        colorValue = color.toHEXA().toString().slice(0, 7);
                    } else {
                        colorValue = color.toRGBA().toString();
                    }
                    break;
            }
            
            // Hidden Input aktualisieren
            hiddenInput.value = colorValue;
            
            // Change Event triggern
            hiddenInput.dispatchEvent(new Event("change"));
        }
        
        function setupNativeColorFallback(hiddenInput, container) {
            // Nativer HTML5 Color Input als Fallback
            const fallbackInput = document.createElement("input");
            fallbackInput.type = "color";
            fallbackInput.value = hiddenInput.value || "#1e87f0";
            fallbackInput.style.width = "40px";
            fallbackInput.style.height = "40px";
            fallbackInput.style.border = "1px solid #e5e5e5";
            fallbackInput.style.borderRadius = "4px";
            fallbackInput.style.cursor = "pointer";
            
            fallbackInput.addEventListener("change", function() {
                hiddenInput.value = this.value;
                hiddenInput.dispatchEvent(new Event("change"));
            });
            
            // Pickr-Element durch nativen Input ersetzen
            const pickerWrapper = container.querySelector(".color-picker-wrapper");
            if (pickerWrapper) {
                pickerWrapper.innerHTML = "";
                pickerWrapper.appendChild(fallbackInput);
            }
        }
        </script>';
        
        return $html;
    }
}
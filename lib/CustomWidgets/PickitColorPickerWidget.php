<?php

/**
 * Einfacher Color Picker Widget für REDAXO Backend
 *
 * Ein simpler Input-Replacement Color Picker basierend auf Pickit Color
 * (https://github.com/skerbis/pickit_color). Funktioniert wie ein normaler Input -
 * kein Hidden-Feld, keine manuelle Initialisierung nötig.
 *
 * @author UIKit Theme Builder
 * @version 3.0.0
 */
class PickitColorPickerWidget
{
    /**
     * Rendert das Color Picker Widget
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
            'id' => 'colorpicker_' . uniqid(),
            'placeholder' => 'Farbe auswählen...',
        ];

        $options = array_merge($defaults, $options);
        $fieldId = $options['id'];

        // Kein eigenes Asset-Include hier - Pickit Color wird bereits global für jede
        // eingeloggte Backend-Session über boot.php geladen. Ein zweites <script>-Tag würde
        // dessen Auto-Init ein zweites Mal laufen lassen und bereits initialisierte Felder
        // doppelt instanziieren.
        $html = '';

        $dataOptions = 'format:' . $options['format'] . ',compact:true,language:de';
        if ($options['alpha']) {
            $dataOptions .= ',alpha:true';
        }

        $html .= '<input type="text" ';
        $html .= 'id="' . htmlspecialchars($fieldId) . '" ';
        $html .= 'name="' . htmlspecialchars($fieldName) . '" ';
        $html .= 'value="' . htmlspecialchars($currentValue) . '" ';
        $html .= 'class="' . htmlspecialchars($options['class']) . ' tb-color-input" ';
        $html .= 'placeholder="' . htmlspecialchars($options['placeholder']) . '" ';
        $html .= 'autocomplete="off" ';
        $html .= 'data-colorpicker="' . htmlspecialchars($dataOptions) . '"';
        $html .= '/>';

        return $html;
    }
}

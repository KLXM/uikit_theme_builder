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

        // Alpha nur wenn der Aufrufer es explizit erlaubt (['alpha' => true]) - z.B.
        // ColorsWidget::generateLessVariables() entfernt den Alpha-Kanal beim Speichern
        // wieder, dort würde ein Alpha-Regler nur eine Auswahl vortäuschen, die nicht
        // ankommt. NavbarWidget behält Alpha dagegen unverändert bei.
        //
        // Format bewusst NICHT einheitlich "hex": Pickit kodiert Transparenz bei format:hex
        // als 8-stelliges Hex (#rrggbbaa). Das alte Pickr-Setup hat nie 8-stelliges Hex
        // erzeugt (Grund: wikimedia/less.php hatte damit Probleme, siehe ColorsWidget::
        // generateLessVariables(), das Alpha-Hex bis heute vorsichtshalber wieder entfernt),
        // sondern bei Transparenz stattdessen rgba() - genau das liefert format:rgb hier
        // (rgb() wenn deckend, rgba() bei Transparenz), NavbarWidget::validateFormData()
        // akzeptiert beides über "rgba?\(...\)". Bei alpha:false bleibt es bei Hex wie zuvor.
        $dataOptions = !empty($attributes['alpha'])
            ? 'format:rgb,alpha:true,compact:true,language:de'
            : 'format:hex,compact:true,language:de';

        // Kein eigenes Asset-Include hier - Pickit Color wird global für jede eingeloggte
        // Backend-Session über boot.php geladen. Ein zweites <script>-Tag würde das UMD-Bundle
        // erneut ausführen und dessen Auto-Init (initColorPickers() bei DOMContentLoaded) ein
        // zweites Mal über die Seite laufen lassen - das würde bereits initialisierte Felder
        // doppelt instanziieren. Felder initialisieren sich selbst über das data-colorpicker-
        // Attribut, siehe https://github.com/skerbis/pickit_color.
        return '<input type="text" id="' . $id . '" name="' . rex_escape($name) . '" value="' . rex_escape($value) . '" class="uk-input tb-color-input" autocomplete="off" data-colorpicker="' . $dataOptions . '">';
    }

    /**
     * Lädt Pickit Color CSS/JS explizit - nur für Kontexte nötig, die NICHT bereits über
     * boot.php versorgt werden (z.B. das Live-Theme-Editor-Widget im Frontend). Innerhalb des
     * Backends NICHT zusätzlich aufrufen, siehe Hinweis in renderColorPicker().
     */
    protected function renderColorPickerAssets(): string
    {
        $cssUrl = \rex_url::addonAssets('uikit_theme_builder', 'pickit-color/colorpicker.min.css');
        $jsUrl  = \rex_url::addonAssets('uikit_theme_builder', 'pickit-color/colorpicker.min.js');
        return '
        <link rel="stylesheet" href="' . $cssUrl . '">
        <script src="' . $jsUrl . '"></script>
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
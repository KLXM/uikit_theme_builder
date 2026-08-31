<?php

/**
 * Custom Widgets Demo Seite
 * Zeigt Live-Demos der verfügbaren Backend-Widgets
 */

// Widget-Klassen laden
require_once rex_addon::get('uikit_theme_builder')->getPath('lib/CustomWidgets/PickitColorPickerWidget.php');

$content = '';

// Header
$content .= '<header class="uk-section uk-section-small">
    <div class="uk-container uk-container-large">
        <h1 class="uk-heading-medium">
            <span uk-icon="icon: palette; ratio: 1.2" class="uk-margin-small-right"></span>
            Custom Widgets Demo
        </h1>
        <p class="uk-text-lead uk-text-muted">Live-Demos der Backend-Widgets für Entwickler</p>
        
        <div class="uk-alert uk-alert-primary">
            <span uk-icon="icon: info" class="uk-margin-small-right"></span>
            <strong>Dokumentation:</strong> Vollständige Anleitung und Code-Beispiele finden Sie in der 
            <a href="' . rex_addon::get('uikit_theme_builder')->getAssetsUrl('') . '../lib/CustomWidgets/README.md" target="_blank">
                README.md <span uk-icon="icon: external-link; ratio: 0.8"></span>
            </a>
        </div>
    </div>
</header>';

// Live Demos
$content .= '<section class="uk-section">
    <div class="uk-container uk-container-large">
        
        <!-- Pickit Color Picker Demo -->
        <div class="uk-card uk-card-default uk-card-body uk-margin-large">
            <h2 class="uk-card-title">
                🎨 Pickit Color Picker Widget
                <span class="uk-badge uk-badge-success uk-margin-small-left">v3.0</span>
            </h2>
            
            <div class="uk-grid uk-child-width-1-2@m" uk-grid>
                <div>
                    <h4>Basic Color Picker</h4>
                    ' . PickitColorPickerWidget::render('demo_color1', '#1e87f0') . '
                </div>
                <div>
                    <h4>Mit Alpha-Channel</h4>
                    ' . PickitColorPickerWidget::render('demo_color2', '#f0506e', ['alpha' => true]) . '
                </div>
            </div>
            
            <details class="uk-margin-top">
                <summary class="uk-text-primary" style="cursor: pointer;">
                    <span uk-icon="icon: code; ratio: 0.8"></span> Code-Snippet anzeigen
                </summary>
                <pre class="uk-background-muted uk-padding-small uk-margin-small-top"><code>// Basic Usage
echo PickitColorPickerWidget::render(\'field_name\', \'#1e87f0\');

// Mit Optionen  
echo PickitColorPickerWidget::render(\'field_name\', \'#f0506e\', [
    \'alpha\' => true,
    \'format\' => \'rgb\'
]);</code></pre>
            </details>
        </div>

        <!-- UIKit Icon Picker Demo -->
        <div class="uk-card uk-card-default uk-card-body uk-margin-large">
            <h2 class="uk-card-title">
                🎯 UIKit Icon Picker
                <span class="uk-badge uk-badge-success uk-margin-small-left">CSS-Klasse</span>
            </h2>
            
            <p class="uk-text-meta">
                Einfach die Klasse <code>uk-iconpicker</code> verwenden - kein PHP nötig!<br>
                Unterstützt alle UIKit Standard Icons + Custom Icons automatisch.
            </p>
            
            <div class="uk-grid uk-child-width-1-3@m uk-grid-small" uk-grid>
                <div>
                    <label class="uk-form-label">Navigation Icon:</label>
                    <input type="text" class="uk-iconpicker uk-input" name="nav_icon" value="menu" />
                </div>
                <div>
                    <label class="uk-form-label">Social Icon:</label>
                    <input type="text" class="uk-iconpicker uk-input" name="social_icon" value="github" />
                </div>
                <div>
                    <label class="uk-form-label">Custom Icon:</label>
                    <input type="text" class="uk-iconpicker uk-input" name="custom_icon" value="custom-person" />
                </div>
                <div>
                    <label class="uk-form-label">Leer (kein Vorwert):</label>
                    <input type="text" class="uk-iconpicker uk-input" name="empty_icon" value="" placeholder="Icon auswählen..." />
                </div>
                <div>
                    <label class="uk-form-label">Mit Placeholder:</label>
                    <input type="text" class="uk-iconpicker uk-input" name="placeholder_icon" value="" placeholder="Wähle ein Icon..." />
                </div>
                <div>
                    <label class="uk-form-label">Vorausgefüllt:</label>
                    <input type="text" class="uk-iconpicker uk-input" name="prefilled_icon" value="heart" />
                </div>
            </div>
            
            <details class="uk-margin-top">
                <summary class="uk-text-primary" style="cursor: pointer;">
                    <span uk-icon="icon: code; ratio: 0.8"></span> Code-Snippet anzeigen
                </summary>
                <pre class="uk-background-muted uk-padding-small uk-margin-small-top"><code>// Einfach die CSS-Klasse verwenden
&lt;input type="text" class="uk-iconpicker uk-input" name="my_icon" value="heart" /&gt;

// Mit Placeholder
&lt;input type="text" class="uk-iconpicker uk-input" name="my_icon" placeholder="Icon wählen..." /&gt;

// Custom Icons funktionieren automatisch
&lt;input type="text" class="uk-iconpicker uk-input" name="my_icon" value="custom-person" /&gt;

// Automatische Initialisierung via JavaScript
// Kein extra Code nötig - funktioniert out of the box!</code></pre>
            </details>
        </div>

        <!-- Integration Examples -->
        <div class="uk-card uk-card-primary uk-card-body uk-margin-large">
            <h2 class="uk-card-title uk-text-white">
                ⚡ Quick Integration
            </h2>
            
            <div class="uk-grid uk-child-width-1-1@s uk-child-width-1-3@m" uk-grid>
                <div>
                    <h4 class="uk-text-white">MForm</h4>
                    <pre class="uk-background-white uk-text-dark uk-padding-small"><code>$mform->addText(\'color\')
      ->setNote(PickitColorPickerWidget::render(
          \'color\', \'#1e87f0\'
      ));</code></pre>
                </div>
                <div>
                    <h4 class="uk-text-white">Direkt in Modulen</h4>
                    <pre class="uk-background-white uk-text-dark uk-padding-small"><code>echo PickitColorPickerWidget::render(
    \'brand_color\', 
    $this->getValue(\'color\')
);</code></pre>
                </div>
                <div>
                    <h4 class="uk-text-white">Icon Selection</h4>
                    <pre class="uk-background-white uk-text-dark uk-padding-small"><code>// CSS-Klasse (empfohlen)
&lt;input type="text" 
       class="uk-iconpicker uk-input" 
       name="icon" 
       value="heart" /&gt;

// In MForm/MBlock
$mform->addTextField(\'icon\')
      ->setAttributes([
          \'class\' => \'uk-iconpicker\'
      ]);</code></pre>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="uk-alert uk-alert-success">
            <span uk-icon="icon: check" class="uk-margin-small-right"></span>
            <strong>Assets automatisch geladen:</strong> Alle benötigten CSS/JS Dateien werden über boot.php global geladen.
            Einfach die Widgets verwenden - keine weitere Konfiguration nötig!
        </div>
        
    </div>
</section>';

// Navigation Breadcrumb
$fragment = new rex_fragment();
$fragment->setVar('title', 'Custom Widgets Demo');
$fragment->setVar('breadcrumb', [
    'UIKit Theme Builder' => rex_url::backendPage('uikit_theme_builder'),
    'Custom Widgets' => ''
]);

echo $fragment->parse('core/page/section.php');
echo $content;

// Extended Icons laden (enthält ALLE Icons: UIkit Standard + Custom)
$iconBuilder = new UikitThemeBuilder\CustomIconBuilder();
if ($iconBuilder->hasExtendedIcons()) {
    echo '<script src="' . $iconBuilder->getExtendedIconsUrl() . '"></script>';
}

// Icon-Liste für Icon Picker bereitstellen
$availableIcons = $iconBuilder->getAllAvailableIcons();
echo '<script>window.uikitAvailableIcons = ' . json_encode($availableIcons) . ';</script>';

// Icon Picker manuell initialisieren
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("Available Icons:", window.uikitAvailableIcons);
    if (window.UikitIconPicker) {
        window.UikitIconPicker.init();
        console.log("Icon Picker initialized");
    }
});
</script>';

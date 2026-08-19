<?php
/**
 * Debug-Seite für UIkit Theme Builder
 * Zeigt Theme-Daten, flattened Data und generierte LESS-Variablen
 */

$manager = new \UikitThemeBuilder\UikitThemeBuilderManager();

// Aktuelles Theme laden
$activeTheme = \UikitThemeBuilder\UikitThemeBuilderManager::getActiveTheme();
$themeData = null;
$themeName = rex_request('theme', 'string', $activeTheme);

if ($themeName) {
    $loadedTheme = $manager->loadTheme($themeName);
    if ($loadedTheme && isset($loadedTheme['data'])) {
        $themeData = $loadedTheme['data'];
    }
}

// Alle verfügbaren Themes für Dropdown
$allThemes = $manager->listThemes();

// Theme-Auswahl
$content = '<div class="rex-form">';
$content .= '<form method="get">';
$content .= '<input type="hidden" name="page" value="uikit_theme_builder/debug" />';
$content .= '<div class="form-group">';
$content .= '<label class="control-label">Theme auswählen:</label>';
$content .= '<select name="theme" class="form-control" onchange="this.form.submit()">';
$content .= '<option value="">-- Theme wählen --</option>';
foreach ($allThemes as $name => $info) {
    $selected = ($name === $themeName) ? ' selected' : '';
    $content .= '<option value="' . htmlspecialchars($name) . '"' . $selected . '>' . htmlspecialchars($name) . '</option>';
}
$content .= '</select>';
$content .= '</div>';
$content .= '</form>';
$content .= '</div>';

if ($themeData) {
    // Theme Data Structure
    $content .= '<h3>Theme Data Structure</h3>';
    $content .= '<pre style="background: #f5f5f5; padding: 15px; overflow-x: auto; font-size: 12px; border: 1px solid #ddd;">';
    $content .= htmlspecialchars(print_r($themeData, true));
    $content .= '</pre>';
    
    // Flattened Data (simulieren was der Manager tun würde)
    $content .= '<h3>Flattened Data (nach Widget-Verarbeitung)</h3>';
    $content .= '<p class="text-muted">Dies zeigt, wie die Daten nach der Widget-spezifischen Verarbeitung aussehen.</p>';
    
    // Widgets laden und deren generateLessVariables verwenden
    $widgets = [
        'colors' => new \UikitThemeBuilder\Widget\ColorsWidget(),
        'typography' => new \UikitThemeBuilder\Widget\TypographyWidget(),
        'breakpoints' => new \UikitThemeBuilder\Widget\BreakpointsWidget(),
        'borders' => new \UikitThemeBuilder\Widget\BorderWidget(),
        'shadows' => new \UikitThemeBuilder\Widget\ShadowWidget(),
        'container' => new \UikitThemeBuilder\Widget\ContainerWidget(),
        'navbar' => new \UikitThemeBuilder\Widget\NavbarWidget(),
        'components' => new \UikitThemeBuilder\Widget\ComponentsWidget(),
        'style_sets' => new \UikitThemeBuilder\Widget\StyleSetSelectionWidget(),
    ];
    
    $allVariables = [];
    foreach ($widgets as $widgetKey => $widget) {
        if (isset($themeData[$widgetKey])) {
            $widgetVars = $widget->generateLessVariables($themeData[$widgetKey]);
            $allVariables[$widgetKey] = $widgetVars;
        }
    }
    
    $content .= '<pre style="background: #f5f5f5; padding: 15px; overflow-x: auto; font-size: 12px; border: 1px solid #ddd;">';
    $content .= htmlspecialchars(print_r($allVariables, true));
    $content .= '</pre>';
    
    // Generierte LESS-Variablen
    $content .= '<h3>Generierte LESS-Variablen (Format)</h3>';
    $content .= '<p class="text-muted">Dies zeigt, wie die Variablen in der LESS-Datei aussehen werden.</p>';
    
    $lessOutput = "// UIKit Theme Builder - Generated Variables\n";
    $lessOutput .= "// Theme: {$themeName}\n";
    $lessOutput .= "// Generated at: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Variablen nach Kategorien gruppiert ausgeben
    foreach ($allVariables as $widgetKey => $variables) {
        if (!empty($variables)) {
            $lessOutput .= "// " . ucfirst($widgetKey) . " Widget\n";
            foreach ($variables as $varName => $value) {
                $lessOutput .= "@{$varName}: {$value};\n";
            }
            $lessOutput .= "\n";
        }
    }
    
    $content .= '<pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; overflow-x: auto; font-size: 12px; border: 1px solid #000;">';
    $content .= htmlspecialchars($lessOutput);
    $content .= '</pre>';
    
    // Typography Widget Debug - speziell für Überschriften
    $content .= '<h3>Typography Widget - Debug (Überschriften)</h3>';
    $content .= '<p class="text-muted">Dies zeigt die Typography-Daten, die an generateLess() übergeben werden.</p>';
    
    if (isset($themeData['typography'])) {
        $typoData = $themeData['typography'];
        
        // Wichtige Überschriften-Felder hervorheben
        $headingFields = [
            'base-heading-font-family',
            'base-heading-font-weight',
            'base-heading-text-transform',
            'base-heading-color',
        ];
        
        $content .= '<div class="table-responsive"><table class="table table-striped table-hover">';
        $content .= '<thead><tr><th>Feld</th><th>Wert</th><th>Status</th></tr></thead><tbody>';
        
        foreach ($headingFields as $field) {
            $value = $typoData[$field] ?? null;
            $status = '';
            
            if ($value === null) {
                $status = '<span class="label label-danger">❌ Nicht gesetzt</span>';
            } elseif ($value === 'inherit' || empty($value)) {
                $status = '<span class="label label-warning">⚠️ Inherit/Leer</span>';
            } else {
                $status = '<span class="label label-success">✅ Gesetzt</span>';
            }
            
            $content .= '<tr>';
            $content .= '<td><code>' . htmlspecialchars($field) . '</code></td>';
            $content .= '<td><code>' . htmlspecialchars(var_export($value, true)) . '</code></td>';
            $content .= '<td>' . $status . '</td>';
            $content .= '</tr>';
        }
        
        $content .= '</tbody></table></div>';
        
        // Test: generateLess() direkt aufrufen
        $typoWidget = new \UikitThemeBuilder\Widget\TypographyWidget();
        $generatedLess = $typoWidget->generateLess($typoData);
        
        $content .= '<h4>Generiertes LESS (nur Typography Widget):</h4>';
        if (!empty($generatedLess)) {
            $content .= '<pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; overflow-x: auto; font-size: 12px; border: 1px solid #000;">';
            $content .= htmlspecialchars($generatedLess);
            $content .= '</pre>';
        } else {
            $content .= '<div class="alert alert-danger">❌ generateLess() hat keinen Output erzeugt!</div>';
        }
    } else {
        $content .= '<div class="alert alert-danger">❌ Keine Typography-Daten im Theme gefunden!</div>';
    }
    
    // Custom LESS (Hooks) anzeigen
    $content .= '<h3>Generiertes Custom LESS (Hooks)</h3>';
    $content .= '<p class="text-muted">Dies zeigt die generierten Hooks und Custom LESS Styles.</p>';
    
    $customLess = $manager->generateCustomLess($themeData);
    
    if (!empty($customLess)) {
        $content .= '<pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; overflow-x: auto; font-size: 12px; border: 1px solid #000;">';
        $content .= htmlspecialchars($customLess);
        $content .= '</pre>';
    } else {
        $content .= '<div class="alert alert-warning">⚠️ Kein Custom LESS generiert!</div>';
    }
    
    // Temp LESS-Datei Vorschau (wie sie kompiliert wird)
    $content .= '<h3>Komplette LESS-Datei (Preview)</h3>';
    $content .= '<p class="text-muted">Dies zeigt die komplette LESS-Datei in der Reihenfolge, wie sie kompiliert wird.</p>';
    
    // Simuliere createTempLessFile
    $uikitLessPath = rex_path::addon('uikit_theme_builder', 'sources/uikit/less');
    $tempLessPreview = "// UIKit Theme Builder - Generated Theme\n";
    $tempLessPreview .= "// Theme: {$themeName}\n";
    $tempLessPreview .= "// Generated at: " . date('Y-m-d H:i:s') . "\n\n";
    $tempLessPreview .= "// Step 1: Import UIKit Core + Theme\n";
    $tempLessPreview .= '@import "' . $uikitLessPath . '/components/_import.less";' . "\n";
    $tempLessPreview .= '@import "' . $uikitLessPath . '/theme/_import.less";' . "\n\n";
    $tempLessPreview .= "// Step 2: Override Variables with Theme Settings\n";
    $tempLessPreview .= "// In Less werden später definierte Variablen bevorzugt\n";
    $tempLessPreview .= $lessOutput . "\n\n";
    $tempLessPreview .= "// Step 3: Import Extra Styles\n";
    $tempLessPreview .= '@import "' . rex_path::addon('uikit_theme_builder', 'sources/extra.less') . '";' . "\n\n";
    $tempLessPreview .= "// Step 4: Custom CSS Rules (Override UIKit)\n";
    $tempLessPreview .= "// These CSS rules override UIKit's default styles\n";
    if (!empty($customLess)) {
        $tempLessPreview .= $customLess . "\n";
    }
    
    $content .= '<pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px; overflow-x: auto; font-size: 12px; border: 1px solid #000; max-height: 500px;">';
    $content .= htmlspecialchars($tempLessPreview);
    $content .= '</pre>';
    
    // Potenzielle Probleme erkennen
    $content .= '<h3>Analyse & Validierung</h3>';
    
    $issues = [];
    
    // Prüfe auf Variablen mit Unterstrichen (sollten nicht mehr vorkommen)
    foreach ($allVariables as $widgetKey => $variables) {
        foreach ($variables as $varName => $value) {
            if (strpos($varName, '_') !== false) {
                $issues[] = "⚠️ Variable mit Unterstrich gefunden: <code>@{$varName}</code> (Widget: {$widgetKey})";
            }
        }
    }
    
    // Prüfe auf font-fallback Variablen (sollten nicht als LESS-Variablen auftauchen)
    foreach ($allVariables as $widgetKey => $variables) {
        foreach ($variables as $varName => $value) {
            if (strpos($varName, 'font-fallback') !== false) {
                $issues[] = "⚠️ font-fallback Variable gefunden: <code>@{$varName}</code> - Diese sollten nur intern verwendet werden!";
            }
        }
    }
    
    // Prüfe Typography Widget spezifisch
    if (isset($themeData['typography'])) {
        $typographyData = $themeData['typography'];
        if (isset($typographyData['font_fallback_sans_serif'])) {
            $issues[] = "❌ Alte Syntax gefunden: <code>font_fallback_sans_serif</code> verwendet Unterstriche statt Bindestriche!";
        }
        if (isset($typographyData['font-fallback-sans-serif'])) {
            // Prüfe ob diese in den generierten Variablen ist
            if (isset($allVariables['typography']['font-fallback-sans-serif'])) {
                $issues[] = "❌ <code>font-fallback-sans-serif</code> wird als LESS-Variable generiert - sollte nur intern sein!";
            } else {
                $issues[] = "✅ <code>font-fallback-sans-serif</code> ist nur intern und wird nicht als LESS-Variable generiert.";
            }
        }
    }
    
    // Prüfe Shadow Widget
    if (isset($themeData['shadows'])) {
        $shadowData = $themeData['shadows'];
        if (isset($shadowData['box_shadow_small'])) {
            $issues[] = "❌ Alte Syntax gefunden: <code>box_shadow_small</code> sollte <code>global-small-box-shadow</code> sein!";
        }
        if (isset($shadowData['global-small-box-shadow'])) {
            if (isset($allVariables['shadows']['global-small-box-shadow'])) {
                $issues[] = "✅ Shadow Variable <code>global-small-box-shadow</code> korrekt formatiert.";
            }
        }
    }
    
    if (empty($issues)) {
        $content .= '<div class="alert alert-success">';
        $content .= '<strong>✅ Keine Probleme gefunden!</strong> Alle Variablen sind korrekt formatiert.';
        $content .= '</div>';
    } else {
        $content .= '<div class="alert alert-info">';
        $content .= '<strong>Analyse-Ergebnisse:</strong>';
        $content .= '<ul>';
        foreach ($issues as $issue) {
            $content .= '<li>' . $issue . '</li>';
        }
        $content .= '</ul>';
        $content .= '</div>';
    }
    
    // Vergleich mit UIkit Variablen
    $content .= '<h3>UIkit Variablen-Referenz</h3>';
    $content .= '<p class="text-muted">Häufig verwendete UIkit LESS-Variablen zur Referenz:</p>';
    
    $uikitVars = [
        'Globale Farben' => [
            '@global-color', '@global-emphasis-color', '@global-muted-color',
            '@global-link-color', '@global-link-hover-color',
            '@global-primary-background', '@global-secondary-background',
            '@global-success-background', '@global-warning-background', '@global-danger-background'
        ],
        'Typografie' => [
            '@global-font-family', '@global-font-size', '@global-line-height',
            '@global-small-font-size', '@global-medium-font-size', '@global-large-font-size',
            '@global-xlarge-font-size', '@global-2xlarge-font-size',
            '@base-heading-font-family', '@base-heading-font-weight', '@base-heading-color',
            '@text-lead-font-size', '@text-lead-color', '@text-meta-font-size'
        ],
        'Schatten' => [
            '@global-small-box-shadow', '@global-medium-box-shadow',
            '@global-large-box-shadow', '@global-xlarge-box-shadow'
        ],
        'Breakpoints' => [
            '@breakpoint-small', '@breakpoint-medium', '@breakpoint-large', '@breakpoint-xlarge'
        ]
    ];
    
    $content .= '<div class="row">';
    foreach ($uikitVars as $category => $vars) {
        $content .= '<div class="col-md-6">';
        $content .= '<h4>' . $category . '</h4>';
        $content .= '<ul style="font-family: monospace; font-size: 12px;">';
        foreach ($vars as $var) {
            // Prüfe ob diese Variable in unseren generierten Variablen ist
            $varName = substr($var, 1); // @ entfernen
            $found = false;
            foreach ($allVariables as $widgetVars) {
                if (isset($widgetVars[$varName])) {
                    $found = true;
                    break;
                }
            }
            $badge = $found ? '<span class="label label-success">✓ Gesetzt</span>' : '<span class="label label-default">Standard</span>';
            $content .= '<li><code>' . $var . '</code> ' . $badge . '</li>';
        }
        $content .= '</ul>';
        $content .= '</div>';
    }
    $content .= '</div>';
    
} else {
    $content .= '<div class="alert alert-warning">';
    $content .= '<strong>Kein Theme ausgewählt.</strong> Bitte wählen Sie ein Theme aus dem Dropdown-Menü.';
    $content .= '</div>';
}

// Fragment ausgeben
$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', 'Theme Debug-Informationen', false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

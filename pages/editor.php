<?php

/**
 * UIkit Theme Builder - Theme Editor
 */

$func = rex_request('func', 'string');
$themeName = rex_request('theme', 'string', 'new_theme');
if ('' === $themeName) {
    $themeName = 'new_theme';
}
$themeManager = new UikitThemeBuilder\UikitThemeBuilderManager();
$csrfToken = rex_csrf_token::factory('uikit-theme-editor');
$content = '';

# Widgets laden
$widgets = [
    'colors' => new \UikitThemeBuilder\Widget\ColorsWidget(),
    'typography' => new \UikitThemeBuilder\Widget\TypographyWidget(),
    'form' => new \UikitThemeBuilder\Widget\FormWidget(),
    'card' => new \UikitThemeBuilder\Widget\CardWidget(),
    'search' => new \UikitThemeBuilder\Widget\SearchWidget(),
    'breakpoints' => new \UikitThemeBuilder\Widget\BreakpointsWidget(),
    'borders' => new \UikitThemeBuilder\Widget\BorderWidget(),
    'shadows' => new \UikitThemeBuilder\Widget\ShadowWidget(),
    'container' => new \UikitThemeBuilder\Widget\ContainerWidget(),
    'navbar' => new \UikitThemeBuilder\Widget\NavbarWidget(),
    'components' => new \UikitThemeBuilder\Widget\ComponentsWidget(),
    'component_selection' => new \UikitThemeBuilder\Widget\ComponentSelectionWidget(),
    'custom_styles' => new \UikitThemeBuilder\Widget\CustomStylesWidget(),
    'style_sets' => new \UikitThemeBuilder\Widget\StyleSetSelectionWidget()
];

// Bestehendes Theme? Dann ist der Theme Key fest (Umbenennen/Kopieren über die Theme-Übersicht)
$isExistingTheme = 'new_theme' !== $themeName && null !== $themeManager->loadTheme($themeName);

// Werte für ein erneutes Anzeigen nach einem Fehler (Eingaben sollen nicht verloren gehen)
$postedThemeData = null;
$postedThemeTitle = null;
$keyInputValue = 'new_theme' === $themeName ? '' : $themeName;
$savedSuccessfully = false;

# Theme speichern und kompilieren (= veröffentlichen)
if ($func === 'compile' && rex_post('compile', 'boolean')) {
    // Bei bestehenden Themes zählt der ursprüngliche Key aus der URL – ein geänderter theme_name wird ignoriert
    $newThemeKey = $isExistingTheme ? $themeName : trim(rex_post('theme_name', 'string', ''));
    $themeTitle = rex_post('theme_title', 'string', '');
    $themeData = [];

    // Widget-Daten verarbeiten
    foreach ($widgets as $widget) {
        $themeData[$widget->getKey()] = $widget->processFormData($_POST);
    }

    // Titel zu Theme-Daten hinzufügen
    $themeData['_meta'] = [
        'title' => $themeTitle,
        'key' => $newThemeKey
    ];

    if (!$csrfToken->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } elseif (!preg_match('/^[a-z0-9_]+$/', $newThemeKey)) {
        echo rex_view::error('Ungültiger Theme Key! Verwende nur Kleinbuchstaben, Zahlen und Unterstriche (z.B. "mein_theme").');
    } elseif (strtolower($newThemeKey) === 'new_theme') {
        echo rex_view::error('Der Theme Key "new_theme" ist reserviert. Bitte wähle einen anderen Namen.');
    } elseif (!$isExistingTheme && null !== $themeManager->loadTheme($newThemeKey)) {
        echo rex_view::error('Es gibt schon ein Theme mit dem Key „' . rex_escape($newThemeKey) . '“. Wähle einen anderen Key – oder öffne das vorhandene Theme in der Theme-Übersicht.');
    } else {
        // Erst Theme speichern
        try {
            $themeManager->saveTheme($newThemeKey, $themeData);

            // Ab jetzt ist es ein bestehendes Theme (auch wenn das Kompilieren gleich scheitern sollte)
            $themeName = $newThemeKey;
            $keyInputValue = $newThemeKey;
            $isExistingTheme = true;

            // Dann kompilieren – das CSS ist danach sofort auf der Website aktiv
            try {
                $result = $themeManager->compileTheme($themeName, $themeData);
                if ($result['success']) {
                    $savedSuccessfully = true;
                    $cssSize = round(strlen((string) ($result['css'] ?? '')) / 1024, 1);
                    $successMessage = 'Theme „' . rex_escape($themeName) . '“ gespeichert und veröffentlicht. '
                        . 'Das neue CSS ist jetzt auf der Website aktiv (' . rex_escape((string) $cssSize) . ' KB).';
                    if (!empty($result['debug_output'])) {
                        $successMessage .= '<details class="utb-editor-debug"><summary>Debug-Informationen anzeigen</summary>'
                            . '<div class="utb-editor-debug-body">' . $result['debug_output'] . '</div></details>';
                    }
                    echo rex_view::success($successMessage);
                } else {
                    echo rex_view::error('Theme gespeichert, aber das Kompilieren ist fehlgeschlagen – auf der Website ist noch das alte CSS aktiv: ' . rex_escape((string) ($result['message'] ?? '')));
                }
            } catch (Exception $e) {
                echo rex_view::error('Theme gespeichert, aber das Kompilieren ist fehlgeschlagen – auf der Website ist noch das alte CSS aktiv: ' . rex_escape($e->getMessage()));
            }
        } catch (\InvalidArgumentException $e) {
            // Validierungsfehler (z.B. reservierter Theme-Name)
            echo rex_view::error(rex_escape($e->getMessage()));
        } catch (Exception $e) {
            echo rex_view::error('Theme konnte nicht gespeichert werden: ' . rex_escape($e->getMessage()));
        }
    }

    if (!$isExistingTheme) {
        // Neues Theme nicht gespeichert: Eingaben wieder anzeigen
        $postedThemeData = $themeData;
        $postedThemeTitle = $themeTitle;
        $keyInputValue = $newThemeKey;
    }
}

// Theme-Daten laden (nach dem Speichern: der gespeicherte Stand)
$currentThemeData = [];
$currentThemeTitle = '';
if (null !== $postedThemeData) {
    $currentThemeData = $postedThemeData;
    $currentThemeTitle = (string) $postedThemeTitle;
} elseif ($isExistingTheme) {
    $loadedTheme = $themeManager->loadTheme($themeName);
    if ($loadedTheme) {
        $currentThemeData = $loadedTheme['data'];
        $currentThemeTitle = $currentThemeData['_meta']['title'] ?? '';
    }
}

$themeListUrl = rex_url::backendPage('uikit_theme_builder/themes');
$editorUrl = $isExistingTheme
    ? rex_url::backendPage('uikit_theme_builder/editor', ['theme' => $themeName], false)
    : rex_url::backendPage('uikit_theme_builder/editor', [], false);
// Basis-URL des Frontends (auch bei Installation in einem Unterordner); Parameter previewtheme hängt das JS an
$previewBaseUrl = rex_url::frontend();

// Assets (UIkit, Icon Picker und Extended Icons lädt bereits boot.php).
// Direkt ausgeben: rex_view::addCssFile()/addJsFile() wirken auf einer Seite zu spät, der Kopf ist dann schon fertig.
$editorAddon = rex_addon::get('uikit_theme_builder');
$editorAsset = static function (string $file) use ($editorAddon): string {
    $path = $editorAddon->getAssetsPath($file);
    $version = is_file($path) ? (string) filemtime($path) : '';
    return $editorAddon->getAssetsUrl($file) . ('' !== $version ? '?v=' . $version : '');
};
echo '<link rel="stylesheet" href="' . rex_escape($editorAsset('css/theme-editor-actions.css')) . '">';

// Icon-Liste für Icon Picker bereitstellen
$iconBuilder = new UikitThemeBuilder\CustomIconBuilder();
$availableIcons = $iconBuilder->getAllAvailableIcons();
echo '<script>window.uikitAvailableIcons = ' . json_encode($availableIcons) . ';</script>';

$content .= '<div class="utb-editor uk-container uk-container-large uk-margin-top">';

// Zurück zur Übersicht
$content .= '<p class="utb-editor-back"><a href="' . $themeListUrl . '"><span uk-icon="arrow-left" aria-hidden="true"></span> Zurück zur Theme-Übersicht</a></p>';

// Main Card
$content .= '<div class="uk-card uk-card-default utb-editor-card">';
$content .= '<div class="uk-card-header">';
$content .= '<h3 class="uk-card-title uk-margin-remove-bottom"><span uk-icon="icon: settings; ratio: 1.2" aria-hidden="true"></span> Theme Editor</h3>';
if ($isExistingTheme) {
    $content .= '<p class="uk-text-meta uk-margin-remove-top">Bearbeite Theme: <strong>' . rex_escape($currentThemeTitle !== '' ? $currentThemeTitle : $themeName) . '</strong>';
    if ($currentThemeTitle !== '') {
        $content .= ' <code>' . rex_escape($themeName) . '</code>';
    }
    $content .= '</p>';
} else {
    $content .= '<p class="uk-text-meta uk-margin-remove-top">Erstelle ein neues UIkit Theme</p>';
}
$content .= '</div>';

// Form Start (umschließt Inhalt und Aktionsleiste)
$formAction = $isExistingTheme
    ? rex_url::currentBackendPage(['theme' => $themeName])
    : rex_url::currentBackendPage();
$content .= '<form id="utb-theme-editor-form" action="' . $formAction . '" method="post" class="uk-form-horizontal"'
    . ' data-utb-theme-key="' . rex_escape($isExistingTheme ? $themeName : 'new_theme') . '"'
    . ' data-utb-editor-url="' . rex_escape($editorUrl) . '"'
    . ' data-utb-preview-url="' . rex_escape($previewBaseUrl) . '"'
    . ($savedSuccessfully ? ' data-utb-saved="1"' : '')
    . '>';
$content .= '<input type="hidden" name="func" value="compile">';
$content .= $csrfToken->getHiddenField();

$content .= '<div class="uk-card-body">';

// Theme Key und Titel
$content .= '<div class="utb-editor-meta uk-margin-medium-bottom">';

// Theme Key
$content .= '<div class="utb-editor-field uk-margin">';
$content .= '<label class="uk-form-label uk-text-bold" for="theme-name-input">Theme Key' . ($isExistingTheme ? '' : ' *') . ' <span class="uk-text-meta uk-text-normal">(technischer Name)</span></label>';
$content .= '<div class="uk-form-controls">';
if ($isExistingTheme) {
    $content .= '<input type="text" id="theme-name-input" name="theme_name" value="' . rex_escape($themeName) . '" class="uk-input uk-form-width-large" readonly aria-describedby="theme-name-help">';
    $content .= '<div id="theme-name-help" class="uk-text-small uk-text-muted uk-margin-small-top">';
    $content .= '<span uk-icon="icon: lock; ratio: 0.8" aria-hidden="true"></span> ';
    $content .= 'Der Key eines bestehenden Themes bleibt fest. Zum Umbenennen oder Kopieren: <a href="' . $themeListUrl . '">Themes</a> → Menü → Duplizieren.';
    $content .= '</div>';
} else {
    $content .= '<input type="text" id="theme-name-input" name="theme_name" value="' . rex_escape($keyInputValue) . '" class="uk-input uk-form-width-large" required pattern="[a-z0-9_]+" title="Nur Kleinbuchstaben, Zahlen und Unterstriche" placeholder="z.B. mein_theme" aria-describedby="theme-name-help">';
    $content .= '<div id="theme-name-help" class="uk-text-small uk-text-muted uk-margin-small-top">';
    $content .= '<span uk-icon="icon: info; ratio: 0.8" aria-hidden="true"></span> ';
    $content .= 'Technischer Name für Dateien und URLs. Nur Kleinbuchstaben, Zahlen und Unterstriche (z.B. <code>mein_theme</code> oder <code>firma_2024</code>). Kann nach dem Speichern nicht mehr geändert werden.';
    $content .= '</div>';
}
$content .= '</div>';
$content .= '</div>';

// Theme Titel
$content .= '<div class="utb-editor-field">';
$content .= '<label class="uk-form-label uk-text-bold" for="theme-title-input">Theme Titel <span class="uk-text-meta uk-text-normal">(optional)</span></label>';
$content .= '<div class="uk-form-controls">';
$content .= '<input type="text" id="theme-title-input" name="theme_title" value="' . rex_escape($currentThemeTitle) . '" class="uk-input uk-form-width-large" placeholder="z.B. Mein tolles Theme 2024" aria-describedby="theme-title-help">';
$content .= '<div id="theme-title-help" class="uk-text-small uk-text-muted uk-margin-small-top">';
$content .= '<span uk-icon="icon: info; ratio: 0.8" aria-hidden="true"></span> ';
$content .= 'Anzeigename für die Übersicht. Hier kannst du Großbuchstaben, Leerzeichen und Sonderzeichen verwenden.';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';

$content .= '</div>';

// Widgets in Accordion (geöffnete Bereiche merkt sich theme-editor-save-state.js je Theme)
$content .= '<ul uk-accordion="multiple: true" class="utb-editor-sections" data-utb-sections>';

foreach ($widgets as $key => $widget) {
    $content .= '<li data-utb-section="' . rex_escape($widget->getKey()) . '">';
    $content .= '<a class="uk-accordion-title" href="#">';
    $content .= '<span class="utb-section-head">';

    // Widget-spezifische Icons
    $icon = 'cog';
    switch ($widget->getKey()) {
        case 'colors': $icon = 'paint-bucket'; break;
        case 'typography': $icon = 'file-text'; break;
        case 'form': $icon = 'file-edit'; break;
        case 'card': $icon = 'credit-card'; break;
        case 'search': $icon = 'search'; break;
        case 'breakpoints': $icon = 'laptop'; break;
        case 'borders': $icon = 'minus-circle'; break;
        case 'shadows': $icon = 'copy'; break;
        case 'container': $icon = 'grid'; break;
        case 'navbar': $icon = 'menu'; break;
        case 'components': $icon = 'cog'; break;
        case 'component_selection': $icon = 'list'; break;
        case 'extra_styles': $icon = 'star'; break;
        case 'custom_styles': $icon = 'code'; break;
        case 'google_fonts': $icon = 'font'; break;
        case 'style_sets':
        case 'style_set_selection': $icon = 'thumbnails'; break;
    }

    $content .= '<span uk-icon="icon: ' . $icon . '; ratio: 1.2" class="uk-text-primary utb-section-icon" aria-hidden="true"></span>';
    $content .= '<span class="utb-section-text">';
    $content .= '<span class="utb-section-title">' . rex_escape($widget->getName()) . '</span>';
    $content .= '<span class="uk-text-meta utb-section-desc">' . rex_escape($widget->getDescription()) . '</span>';
    $content .= '</span>';
    $content .= '</span>';
    $content .= '</a>';
    $content .= '<div class="uk-accordion-content">';
    $content .= '<div class="uk-card uk-card-default uk-card-body uk-margin-small utb-section-body">';

    $widgetValues = $currentThemeData[$widget->getKey()] ?? [];
    $content .= $widget->renderForm($widgetValues);

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</li>';
}

$content .= '</ul>';

$content .= '</div>'; // card-body

// Aktionsleiste (sticky am unteren Rand)
$content .= '<div class="uk-card-footer utb-editor-actions">';
$content .= '<div class="utb-editor-actions-inner">';
$content .= '<div class="utb-editor-actions-info">';
$content .= '<div class="utb-save-status" role="status" aria-live="polite">';
$content .= '<span class="utb-save-dot" aria-hidden="true"></span>';
$content .= '<span id="utb-save-status-text" class="uk-text-small">Alle Änderungen gespeichert</span>';
$content .= '</div>';
$content .= '<div id="utb-save-hint" class="uk-text-small utb-save-hint">';
$content .= 'Speichert das gesamte Theme. Das Theme ist danach sofort auf der Website aktiv.';
$content .= '</div>';
$content .= '</div>';
$content .= '<div class="utb-action-group">';
$content .= '<button type="submit" name="compile" value="1" class="uk-button uk-button-primary utb-btn-save" data-utb-save-button="1" aria-describedby="utb-save-hint">';
$content .= '<span uk-icon="icon: check; ratio: 0.9" class="uk-margin-small-right" aria-hidden="true"></span>Speichern &amp; veröffentlichen';
$content .= '</button>';
if ($isExistingTheme) {
    $content .= '<button type="button" class="uk-button uk-button-default utb-btn-preview" data-utb-preview-theme="' . rex_escape($themeName) . '" onclick="openPreviewModal(this.getAttribute(\'data-utb-preview-theme\'))">';
    $content .= '<span uk-icon="icon: tv; ratio: 0.9" class="uk-margin-small-right" aria-hidden="true"></span>Vorschau';
    $content .= '</button>';
}
$content .= '<a href="' . $themeListUrl . '" class="uk-button uk-button-default utb-btn-cancel">Verwerfen</a>';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';

$content .= '</form>';

$content .= '</div>'; // card
$content .= '</div>'; // container

// Preview Modal
$content .= '
<!-- Preview Modal -->
<div id="preview-modal" class="utb-preview-modal" uk-modal="bg-close: true; esc-close: true" aria-labelledby="utb-preview-title">
    <div class="uk-modal-dialog uk-padding-remove utb-preview-dialog">

        <div class="utb-preview-header">
            <div class="utb-preview-titlebar">
                <h2 id="utb-preview-title" class="uk-modal-title uk-margin-remove">
                    <span uk-icon="icon: tv; ratio: 1.2" class="uk-margin-small-right" aria-hidden="true"></span>Vorschau
                    <span id="preview-theme-name" class="utb-preview-theme-name"></span>
                </h2>
                <button class="uk-button uk-button-small uk-modal-close utb-preview-close" type="button">
                    <span uk-icon="icon: close" aria-hidden="true"></span> Schließen
                </button>
            </div>
            <div class="utb-preview-toolbar">
                <div class="uk-button-group utb-preview-sizes" role="group" aria-label="Bildschirmgröße der Vorschau">
                    <button type="button" class="uk-button uk-button-small" onclick="setPreviewSize(\'desktop\')" id="btn-desktop" aria-pressed="true">
                        <span uk-icon="icon: desktop" aria-hidden="true"></span> Desktop
                    </button>
                    <button type="button" class="uk-button uk-button-small" onclick="setPreviewSize(\'tablet\')" id="btn-tablet" aria-pressed="false">
                        <span uk-icon="icon: tablet" aria-hidden="true"></span> Tablet
                    </button>
                    <button type="button" class="uk-button uk-button-small" onclick="setPreviewSize(\'mobile\')" id="btn-mobile" aria-pressed="false">
                        <span uk-icon="icon: phone" aria-hidden="true"></span> Mobile
                    </button>
                </div>
                <span class="utb-preview-note">Zeigt den zuletzt gespeicherten Stand.</span>
            </div>
        </div>

        <div class="utb-preview-body">
            <div id="preview-container" class="utb-preview-container">
                <div id="preview-frame-container" class="utb-preview-frame">
                    <iframe id="preview-iframe" src="" title="Vorschau des Themes"></iframe>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function openPreviewModal(themeName) {
    document.getElementById("preview-theme-name").textContent = themeName;

    // Vorschau-URL: Frontend-Basis aus PHP (rex_url::frontend, auch für Unterordner) + previewtheme
    const form = document.getElementById("utb-theme-editor-form");
    const base = (form && form.getAttribute("data-utb-preview-url")) || "/";
    const url = new URL(base, window.location.href);
    url.searchParams.set("previewtheme", themeName);
    document.getElementById("preview-iframe").src = url.toString();

    UIkit.modal("#preview-modal").show();
    setPreviewSize("desktop");
}

function setPreviewSize(size) {
    const container = document.getElementById("preview-frame-container");
    ["desktop", "tablet", "mobile"].forEach(function (name) {
        const btn = document.getElementById("btn-" + name);
        if (btn) {
            btn.classList.toggle("is-active", name === size);
            btn.setAttribute("aria-pressed", name === size ? "true" : "false");
        }
    });
    container.classList.remove("is-desktop", "is-tablet", "is-mobile");
    container.classList.add("is-" + size);
}

UIkit.util.on("#preview-modal", "hidden", function () {
    document.getElementById("preview-iframe").src = "";
});
</script>
';

echo $content;
echo '<script src="' . rex_escape($editorAsset('js/theme-editor-save-state.js')) . '"></script>';
?>

<script>
// Icon-Picker nach Tab-Wechsel und initial re-initialisieren
document.addEventListener('DOMContentLoaded', function() {
    // Initial-Initialisierung mit Verzögerung
    setTimeout(function() {
        if (window.UikitIconPicker) {
            console.log('Icon-Picker wird initialisiert...');
            window.UikitIconPicker.init();
        } else {
            console.error('UikitIconPicker nicht gefunden!');
        }
    }, 500);
});

UIkit.util.on(document, 'shown', '.uk-switcher', function() {
    if (window.UikitIconPicker) {
        setTimeout(function() {
            console.log('Icon-Picker re-initialisiert nach Tab-Wechsel');
            window.UikitIconPicker.init();
        }, 200);
    }
});
</script>

<script>
// Shadow Designer Widget
document.addEventListener('DOMContentLoaded', function() {
    // Shadow Designer öffnen
    document.addEventListener('click', function(e) {
        if (e.target.closest('.shadow-designer-btn')) {
            const btn = e.target.closest('.shadow-designer-btn');
            const target = btn.getAttribute('data-target');
            const modal = document.getElementById('shadow-designer-' + target);
            
            if (modal && typeof UIkit !== 'undefined') {
                // UIKit Modal öffnen
                const modalInstance = UIkit.modal(modal);
                modalInstance.show();
                
                // Extremen z-index setzen nach dem Öffnen
                setTimeout(() => {
                    modal.style.zIndex = '999999';
                    const backdrop = modal.querySelector('.uk-modal-page') || document.querySelector('.uk-modal-page');
                    if (backdrop) {
                        backdrop.style.zIndex = '999998';
                    }
                }, 10);
                
                // Aktuellen Shadow-Wert parsen und Slider setzen
                updateDesignerFromInput(target);
            }
        }
    });
    
    // Shadow Designer Controls
    document.addEventListener('input', function(e) {
        if (e.target.hasAttribute('data-control')) {
            const control = e.target.getAttribute('data-control');
            const target = e.target.getAttribute('data-target');
            const value = e.target.value;
            
            // Wert-Anzeige aktualisieren (nur für Range-Inputs)
            if (e.target.type === 'range') {
                const valueSpan = e.target.parentElement.querySelector('.shadow-value-' + control);
                if (valueSpan) {
                    const unit = control === 'opacity' ? '' : 'px';
                    valueSpan.textContent = value + unit;
                }
            }
            
            // Shadow aktualisieren
            updateShadowFromControls(target);
        }
    });
    
    // Separate Event-Handler für Checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.hasAttribute('data-control') && e.target.type === 'checkbox') {
            const target = e.target.getAttribute('data-target');
            updateShadowFromControls(target);
        }
    });
    
    // Neumorphismus Preset Handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.neumorph-preset')) {
            const btn = e.target.closest('.neumorph-preset');
            const target = btn.getAttribute('data-target');
            const preset = btn.getAttribute('data-preset');
            applyNeumorphismPreset(target, preset);
        }
    });
    
    function updateDesignerFromInput(target) {
        const input = document.querySelector('input[name="' + target + '"]');
        if (!input) return;
        
        const value = input.value;
        const modal = document.getElementById('shadow-designer-' + target);
        if (!modal) return;
        
        // Inset-Status prüfen
        const isInset = value.includes('inset');
        
        // Inset aus String entfernen für Parsing
        const cleanValue = value.replace('inset', '').trim();
        
        // Grundlegende Parsing für Shadow-Werte
        const regex = /(-?\d+)px\s+(-?\d+)px\s+(\d+)px\s+rgba?\(([^)]+)\)/;
        const matches = cleanValue.match(regex);
        
        if (matches) {
            const x = matches[1];
            const y = matches[2];
            const blur = matches[3];
            
            // Slider setzen
            const xSlider = modal.querySelector('[data-control="x"]');
            const ySlider = modal.querySelector('[data-control="y"]');
            const blurSlider = modal.querySelector('[data-control="blur"]');
            const insetCheckbox = modal.querySelector('[data-control="inset"]');
            
            if (xSlider) xSlider.value = x;
            if (ySlider) ySlider.value = y;
            if (blurSlider) blurSlider.value = blur;
            if (insetCheckbox) insetCheckbox.checked = isInset;
            
            // Wert-Anzeigen aktualisieren
            modal.querySelectorAll('[class*="shadow-value-"]').forEach(span => {
                const control = span.className.match(/shadow-value-(\w+)/)[1];
                const slider = modal.querySelector('[data-control="' + control + '"]');
                if (slider) {
                    const unit = control === 'opacity' ? '' : 'px';
                    span.textContent = slider.value + unit;
                }
            });
        }
        
        // Preview aktualisieren
        updateShadowFromControls(target);
    }
    
    function updateShadowFromControls(target) {
        const modal = document.getElementById('shadow-designer-' + target);
        if (!modal) return;
        
        const isDropShadow = target.includes('drop_shadow');
        
        // Werte aus Controls sammeln
        const x = modal.querySelector('[data-control="x"]')?.value || 0;
        const y = modal.querySelector('[data-control="y"]')?.value || 2;
        const blur = modal.querySelector('[data-control="blur"]')?.value || 8;
        const color = modal.querySelector('[data-control="color"]')?.value || '#000000';
        const opacity = modal.querySelector('[data-control="opacity"]')?.value || 0.08;
        const inset = modal.querySelector('[data-control="inset"]')?.checked || false;
        
        // HEX zu RGB konvertieren
        const rgb = hexToRgb(color);
        
        // Shadow-String erstellen
        let shadowValue;
        if (isDropShadow) {
            shadowValue = `drop-shadow(${x}px ${y}px ${blur}px rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${opacity}))`;
        } else {
            const insetText = inset ? 'inset ' : '';
            shadowValue = `${insetText}${x}px ${y}px ${blur}px rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${opacity})`;
        }
        
        // Input aktualisieren
        const input = document.querySelector('input[name="' + target + '"]');
        if (input) {
            input.value = shadowValue;
        }
        
        // CSS Output aktualisieren
        const cssOutput = modal.querySelector('.shadow-css-output');
        if (cssOutput) {
            if (isDropShadow) {
                cssOutput.textContent = `filter: ${shadowValue};`;
            } else {
                cssOutput.textContent = `box-shadow: ${shadowValue};`;
            }
        }
        
        // Preview aktualisieren
        const preview = document.querySelector('.shadow-preview[data-preview="' + target + '"]');
        const designerPreview = modal.querySelector('.shadow-designer-preview');
        
        const previewStyle = isDropShadow ? 
            'filter: ' + shadowValue + ';' : 
            'box-shadow: ' + shadowValue + ';';
            
        if (preview) {
            preview.style.cssText = 'width: 60px; height: 40px; background: white; border: 1px solid #ddd; ' + previewStyle;
        }
        if (designerPreview) {
            designerPreview.style.cssText = 'width: 80px; height: 60px; background: white; border: 1px solid #ddd; margin: 20px auto; ' + previewStyle;
        }
    }
    
    function hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : {r: 0, g: 0, b: 0};
    }
    
    function applyNeumorphismPreset(target, preset) {
        const modal = document.getElementById('shadow-designer-' + target);
        if (!modal) return;
        
        // Neumorphismus-Presets definieren
        const presets = {
            raised: {
                x: 8,
                y: 8,
                blur: 16,
                color: '#ffffff',
                opacity: 0.6,
                inset: false,
                secondary: {
                    x: -8,
                    y: -8,
                    blur: 16,
                    color: '#000000',
                    opacity: 0.1
                }
            },
            pressed: {
                x: 4,
                y: 4,
                blur: 8,
                color: '#000000',
                opacity: 0.15,
                inset: true,
                secondary: {
                    x: -4,
                    y: -4,
                    blur: 8,
                    color: '#ffffff',
                    opacity: 0.8
                }
            },
            flat: {
                x: 0,
                y: 0,
                blur: 0,
                color: '#000000',
                opacity: 0,
                inset: false
            },
            floating: {
                x: 0,
                y: 16,
                blur: 32,
                color: '#000000',
                opacity: 0.15,
                inset: false
            }
        };
        
        const config = presets[preset];
        if (!config) return;
        
        // Controls setzen
        const xSlider = modal.querySelector('[data-control="x"]');
        const ySlider = modal.querySelector('[data-control="y"]');
        const blurSlider = modal.querySelector('[data-control="blur"]');
        const colorInput = modal.querySelector('[data-control="color"]');
        const opacitySlider = modal.querySelector('[data-control="opacity"]');
        const insetCheckbox = modal.querySelector('[data-control="inset"]');
        
        if (xSlider) xSlider.value = config.x;
        if (ySlider) ySlider.value = config.y;
        if (blurSlider) blurSlider.value = config.blur;
        if (colorInput) colorInput.value = config.color;
        if (opacitySlider) opacitySlider.value = config.opacity;
        if (insetCheckbox) insetCheckbox.checked = config.inset;
        
        // Wert-Anzeigen aktualisieren
        modal.querySelectorAll('[class*="shadow-value-"]').forEach(span => {
            const control = span.className.match(/shadow-value-(\w+)/)[1];
            const slider = modal.querySelector('[data-control="' + control + '"]');
            if (slider) {
                const unit = control === 'opacity' ? '' : 'px';
                span.textContent = slider.value + unit;
            }
        });
        
        // Für erweiterte Neumorphismus-Effekte (Dual-Shadow)
        // Erstmal nur primary Shadow generieren
        updateShadowFromControls(target);
        
        // Hinweis für Benutzer bei Dual-Shadow Presets
        if (config.secondary && (preset === 'raised' || preset === 'pressed')) {
            // Optional: Benachrichtigung, dass für vollständigen Neumorphismus zwei Shadow-Felder kombiniert werden sollten
            console.log('Neumorphismus-Tipp: Für perfekten ' + preset + '-Effekt verwenden Sie ein zweites Shadow-Feld mit:', config.secondary);
        }
    }
});
</script>

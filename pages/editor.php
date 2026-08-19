<?php

/**
 * UIKit Theme Builder - Theme Editor
 */

$func = rex_request('func', 'string');
$themeName = rex_request('theme', 'string', 'new_theme');
$themeManager = new UikitThemeBuilder\UikitThemeBuilderManager();
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
    'custom_styles' => new \UikitThemeBuilder\Widget\CustomStylesWidget(),
    'style_sets' => new \UikitThemeBuilder\Widget\StyleSetSelectionWidget()
];

# Theme kompilieren und speichern
if ($func === 'compile' && rex_post('compile', 'boolean')) {
    $themeName = rex_post('theme_name', 'string');
    $themeTitle = rex_post('theme_title', 'string', '');
    $themeData = [];
    
    // Theme Key Validierung
    if (!preg_match('/^[a-z0-9_]+$/', $themeName)) {
        echo rex_view::error('Ungültiger Theme Key! Verwende nur Kleinbuchstaben, Zahlen und Unterstriche (z.B. "mein_theme").');
    } elseif (strtolower($themeName) === 'new_theme') {
        echo rex_view::error('Der Theme Key "new_theme" ist reserviert. Bitte wähle einen anderen Namen.');
    } elseif ($themeName) {
        // Widget-Daten verarbeiten
        foreach ($widgets as $widget) {
            $widgetData = $widget->processFormData($_POST);
            $themeData[$widget->getKey()] = $widgetData;
        }
        
        // Titel zu Theme-Daten hinzufügen
        $themeData['_meta'] = [
            'title' => $themeTitle,
            'key' => $themeName
        ];
        
        // Erst Theme speichern
        try {
            $themeManager->saveTheme($themeName, $themeData);
            
            // Dann kompilieren
            try {
                $result = $themeManager->compileTheme($themeName, $themeData);
                if ($result['success']) {
                    // Erfolgs-Seite anzeigen mit UIKit Animation
                    $content .= '<div class="uk-container uk-container-large uk-margin-top" uk-scrollspy="cls: uk-animation-slide-bottom-medium; delay: 200">';
                    $content .= '<div class="uk-card uk-card-default uk-card-body uk-text-center" uk-scrollspy="cls: uk-animation-scale-up; delay: 400">';
                    
                    // Animiertes Success Icon
                    $content .= '<div uk-scrollspy="cls: uk-animation-fade uk-animation-scale-up; delay: 600">';
                    $content .= '<span uk-icon="icon: check; ratio: 4" class="uk-text-success"></span>';
                    $content .= '</div>';
                    
                    // Titel mit Animation
                    $content .= '<h2 class="uk-card-title uk-text-success uk-margin-top" uk-scrollspy="cls: uk-animation-slide-top-small; delay: 800">Theme erfolgreich kompiliert!</h2>';
                    
                    // Informationen mit gestaffelter Animation
                    $content .= '<div uk-scrollspy="target: > *; cls: uk-animation-fade; delay: 200">';
                    $content .= '<p class="uk-text-lead"><strong>Theme:</strong> ' . rex_escape($themeName) . '</p>';
                    $content .= '<p class="uk-text-large uk-text-success"><strong>CSS-Größe:</strong> ' . round(strlen($result['css']) / 1024, 1) . ' KB</p>';
                    $content .= '</div>';
                    
                    // Buttons mit Animation
                    $content .= '<div class="uk-margin-top" uk-scrollspy="target: > *; cls: uk-animation-slide-bottom-small; delay: 300">';
                    $content .= '<a href="' . rex_url::currentBackendPage(['page' => 'uikit_theme_builder/editor', 'theme' => $themeName]) . '" class="uk-button uk-button-primary uk-margin-right uk-button-large">Weiter bearbeiten</a>';
                    $content .= '<a href="' . rex_url::currentBackendPage(['page' => 'uikit_theme_builder/themes']) . '" class="uk-button uk-button-default uk-button-large">Zur Theme-Übersicht</a>';
                    $content .= '</div>';
                    $content .= '</div>';
                    
                    // Debug-Ausgabe anzeigen wenn verfügbar
                    if (!empty($result['debug_output'])) {
                        $content .= '<div class="uk-margin-top" uk-scrollspy="cls: uk-animation-fade; delay: 1200">';
                        $content .= '<details class="uk-margin-top">';
                        $content .= '<summary class="uk-text-small uk-text-muted uk-button uk-button-text">Debug-Informationen anzeigen</summary>';
                        $content .= '<div class="uk-margin-top uk-padding-small uk-background-muted uk-border-rounded">';
                        $content .= $result['debug_output'];
                        $content .= '</div>';
                        $content .= '</details>';
                        $content .= '</div>';
                    }
                    
                    $content .= '</div>';
                    
                    // Zusätzliche Hintergrund-Animation
                    $content .= '<style>
                    .uk-card-default {
                        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                        box-shadow: 0 14px 25px rgba(0,0,0,0.16);
                        border: none;
                    }
                    .uk-text-success {
                        color: #32d296 !important;
                    }
                    @keyframes pulse-success {
                        0%, 100% { transform: scale(1); }
                        50% { transform: scale(1.05); }
                    }
                    .uk-card:hover {
                        animation: pulse-success 2s ease-in-out infinite;
                    }
                    </style>';
                    
                    echo $content;
                    return; // Formular nicht anzeigen
                    
                } else {
                    echo rex_view::error('Fehler beim Kompilieren: ' . $result['message']);
                }
            } catch (Exception $e) {
                echo rex_view::error('Fehler beim Kompilieren: ' . rex_escape($e->getMessage()));
            }
        } catch (\InvalidArgumentException $e) {
            // Validierungsfehler (z.B. reservierter Theme-Name)
            echo rex_view::error($e->getMessage());
        } catch (Exception $e) {
            echo rex_view::error('Theme konnte nicht gespeichert werden: ' . rex_escape($e->getMessage()));
        }
    }
}

// Existierendes Theme laden
$currentThemeData = [];
$currentThemeTitle = '';
if ($themeName !== 'new_theme') {
    $loadedTheme = $themeManager->loadTheme($themeName);
    if ($loadedTheme) {
        $currentThemeData = $loadedTheme['data'];
        $currentThemeTitle = $currentThemeData['_meta']['title'] ?? '';
    }
}

// Content mit UIKit Styling
$content = '';

// UIKit CSS einbinden für Backend
$content .= '<link rel="stylesheet" href="' . rex_url::assets('uikit/css/uikit.min.css') . '">';
$content .= '<script src="' . rex_url::assets('uikit/js/uikit.min.js') . '"></script>';
rex_view::addJsFile($this->getAddon()->getAssetsUrl('js/uikit-icon-picker.js'));
rex_view::addCssFile($this->getAddon()->getAssetsUrl('css/theme-editor-actions.css'));
rex_view::addJsFile($this->getAddon()->getAssetsUrl('js/theme-editor-save-state.js'));
// Extended Icons laden (enthält ALLE Icons: UIkit Standard + Custom)
$iconBuilder = new UikitThemeBuilder\CustomIconBuilder();
if ($iconBuilder->hasExtendedIcons()) {
    echo '<script src="' . $iconBuilder->getExtendedIconsUrl() . '"></script>';
}

// Icon-Liste für Icon Picker bereitstellen
$availableIcons = $iconBuilder->getAllAvailableIcons();
echo '<script>window.uikitAvailableIcons = ' . json_encode($availableIcons) . ';</script>';

rex_view::addJsFile($this->getAddon()->getAssetsUrl('js/uikit-icon-picker.js'));

echo '<style>
    .pcr-button {
        border: 1px solid rgba(0, 0, 0, 0.15) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12) !important;
    }
</style>';

$content .= '<div class="uk-container uk-container-large uk-margin-top">';

// Header mit Breadcrumb
$content .= '<nav class="uk-margin-bottom" uk-navbar>';
$content .= '<div class="uk-navbar-left">';
$content .= '<ul class="uk-navbar-nav">';
$content .= '<li><a href="' . rex_url::currentBackendPage(['page' => 'uikit_theme_builder/themes']) . '" class="uk-text-muted"><span uk-icon="arrow-left"></span> Zurück zu Themes</a></li>';
$content .= '</ul>';
$content .= '</div>';
$content .= '<div class="uk-navbar-right">';
$content .= '<div class="uk-navbar-item">';
$content .= '<span class="uk-badge uk-badge-success">UIKit Theme Builder</span>';
$content .= '</div>';
$content .= '</div>';
$content .= '</nav>';

// Main Card
$content .= '<div class="uk-card uk-card-default uk-card-large">';
$content .= '<div class="uk-card-header">';
$content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
$content .= '<div class="uk-width-expand">';
$content .= '<h3 class="uk-card-title uk-margin-remove-bottom"><span uk-icon="icon: settings; ratio: 1.2"></span> Theme Editor</h3>';
if ($themeName !== 'new_theme') {
    $content .= '<p class="uk-text-meta uk-margin-remove-top">Bearbeite Theme: <strong>' . rex_escape($themeName) . '</strong></p>';
} else {
    $content .= '<p class="uk-text-meta uk-margin-remove-top">Erstelle ein neues UIKit Theme</p>';
}
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';

$content .= '<div class="uk-card-body">';

// Form Start
$content .= '<form id="utb-theme-editor-form" action="' . rex_url::currentBackendPage() . '" method="post" class="uk-form-horizontal">';
$content .= '<input type="hidden" name="func" value="compile">';

// Notice für existierendes Theme
if ($themeName !== 'new_theme') {
    $content .= '<div class="uk-alert-primary" uk-alert>';
    $content .= '<a class="uk-alert-close" uk-close></a>';
    $content .= '<h4>Hinweis</h4>';
    $content .= '<p>Du bearbeitest ein existierendes Theme. ';
    $content .= '<a href="' . rex_url::currentBackendPage(['page' => 'uikit_theme_builder/editor']) . '" class="uk-button uk-button-small uk-button-secondary uk-margin-small-left">Neues Theme erstellen</a></p>';
    $content .= '</div>';
}

// Theme Key und Titel in schöner Box
$content .= '<div class="uk-card uk-card-muted uk-margin-medium-bottom">';
$content .= '<div class="uk-card-body uk-padding-small">';

// Theme Key
$content .= '<div class="uk-grid-small uk-flex-middle uk-margin" uk-grid>';
$content .= '<div class="uk-width-auto">';
$content .= '<span uk-icon="icon: code; ratio: 1.5" class="uk-text-primary"></span>';
$content .= '</div>';
$content .= '<div class="uk-width-expand">';
$content .= '<label class="uk-form-label uk-text-bold" for="theme-name-input">Theme Key * <span class="uk-text-meta uk-text-normal">(technischer Name)</span></label>';
$content .= '<input type="text" id="theme-name-input" name="theme_name" value="' . rex_escape($themeName) . '" class="uk-input uk-form-width-large" required pattern="[a-z0-9_]+" title="Nur Kleinbuchstaben, Zahlen und Unterstriche">';
$content .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">';
$content .= '<span uk-icon="icon: info; ratio: 0.8"></span> ';
$content .= 'Technischer Name für Dateien und URLs. Nur Kleinbuchstaben, Zahlen und Unterstriche (z.B. <code>mein_theme</code> oder <code>firma_2024</code>).';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';

// Theme Titel
$content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
$content .= '<div class="uk-width-auto">';
$content .= '<span uk-icon="icon: tag; ratio: 1.5" class="uk-text-primary"></span>';
$content .= '</div>';
$content .= '<div class="uk-width-expand">';
$content .= '<label class="uk-form-label uk-text-bold" for="theme-title-input">Theme Titel <span class="uk-text-meta uk-text-normal">(optional)</span></label>';
$content .= '<input type="text" id="theme-title-input" name="theme_title" value="' . rex_escape($currentThemeTitle) . '" class="uk-input uk-form-width-large" placeholder="z.B. Mein tolles Theme 2024">';
$content .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">';
$content .= '<span uk-icon="icon: info; ratio: 0.8"></span> ';
$content .= 'Anzeigename für die Übersicht. Hier kannst du Großbuchstaben, Leerzeichen und Sonderzeichen verwenden.';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';

$content .= '</div>';
$content .= '</div>';

// Widgets in Accordion
$content .= '<ul uk-accordion="multiple: true">';

$widgetIndex = 0;
foreach ($widgets as $key => $widget) {
    // Kein Widget automatisch öffnen - Benutzer soll selbst entscheiden
    $isOpen = '';
    $content .= '<li ' . $isOpen . '>';
    $content .= '<a class="uk-accordion-title" href="#">';
    $content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
    $content .= '<div class="uk-width-auto">';
    
    // Widget-spezifische Icons
    $icon = 'cog'; // Default Icon geändert von 'settings' zu 'cog'
    switch($widget->getKey()) {
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
        case 'extra_styles': $icon = 'star'; break;
        case 'custom_styles': $icon = 'code'; break;
        case 'google_fonts': $icon = 'font'; break;
        case 'style_set_selection': $icon = 'thumbnails'; break;
    }
    
    $content .= '<span uk-icon="icon: ' . $icon . '; ratio: 1.2" class="uk-text-primary"></span>';
    $content .= '</div>';
    $content .= '<div class="uk-width-expand">';
    $content .= '<h4 class="uk-margin-remove">' . rex_escape($widget->getName()) . '</h4>';
    $content .= '<p class="uk-text-meta uk-margin-remove-top">' . rex_escape($widget->getDescription()) . '</p>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</a>';
    $content .= '<div class="uk-accordion-content">';
    $content .= '<div class="uk-card uk-card-default uk-card-body uk-margin-small">';
    
    $widgetValues = $currentThemeData[$widget->getKey()] ?? [];
    $content .= $widget->renderForm($widgetValues);
    
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</li>';
    $widgetIndex++;
}

$content .= '</ul>';

$content .= '</div>'; // card-body

// Sticky Footer mit Actions
$content .= '<div class="uk-card-footer uk-background-muted utb-editor-actions">';
$content .= '<div class="uk-grid-small uk-flex-middle uk-flex-between" uk-grid>';
$content .= '<div class="uk-width-auto">';
$content .= '<div class="uk-flex uk-flex-middle utb-save-status">';
$content .= '<span class="utb-save-dot" aria-hidden="true"></span>';
$content .= '<span id="utb-save-status-text" class="uk-text-small uk-text-muted">Alle Änderungen gespeichert</span>';
$content .= '</div>';
$content .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">';
$content .= '<span uk-icon="icon: info; ratio: 0.8" class="uk-margin-small-right"></span>';
$content .= 'Du speicherst immer das gesamte Theme, nicht nur einen einzelnen Bereich.';
$content .= '</div>';
$content .= '</div>';
$content .= '<div class="uk-width-auto">';
$content .= '<div class="uk-button-group utb-action-group">';
$content .= '<button type="submit" name="compile" value="1" class="uk-button uk-button-primary uk-button-large utb-btn-save" data-utb-save-button="1">';
$content .= '<span uk-icon="icon: check; ratio: 0.9" class="uk-margin-small-right"></span>Theme speichern';
$content .= '</button>';
if ($themeName !== 'new_theme') {
    $content .= '<button type="button" class="uk-button uk-button-default uk-button-large" onclick="openPreviewModal(\'' . rex_escape($themeName) . '\')">';
    $content .= '<span uk-icon="icon: tv; ratio: 0.9" class="uk-margin-small-right"></span>Vorschau';
    $content .= '</button>';
}
$content .= '<a href="' . rex_url::currentBackendPage(['page' => 'uikit_theme_builder/themes']) . '" class="uk-button uk-button-default uk-button-large utb-btn-cancel">Verwerfen</a>';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';

$content .= '</form>';

$content .= '</div>'; // card
$content .= '</div>'; // container

// Preview Modal mit besserer z-index Behandlung
$content .= '
<!-- Preview Modal -->
<div id="preview-modal" uk-modal="bg-close: true; esc-close: true" style="z-index: 99999 !important;">
    <div class="uk-modal-dialog uk-modal-body uk-padding-remove" style="width: 90vw; height: 90vh; max-width: none; max-height: none; margin: 5vh auto; z-index: 100000 !important;">
        
        <!-- Modal Header -->
        <div class="uk-modal-header" style="background: linear-gradient(135deg, #324050 0%, #1e2832 100%); color: white; position: relative; z-index: 100001 !important; padding: 20px 30px;">
            <div class="uk-grid-small uk-flex-middle" uk-grid>
                <div class="uk-width-auto">
                    <h2 class="uk-modal-title uk-margin-remove" style="color: white; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                        <span uk-icon="icon: tv; ratio: 1.2" class="uk-margin-small-right" style="color: white;"></span>
                        Theme Preview
                    </h2>
                </div>
                <div class="uk-width-expand">
                    <span id="preview-theme-name" class="uk-text-large" style="color: rgba(255,255,255,0.9);"></span>
                </div>
            </div>
            
            <!-- Responsive Controls in eigener Zeile -->
            <div class="uk-grid-small uk-flex-middle uk-flex-between uk-margin-top" uk-grid>
                <div class="uk-width-auto">
                    <div class="uk-button-group">
                        <button class="uk-button uk-button-small" onclick="setPreviewSize(\'desktop\')" id="btn-desktop" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
                            <span uk-icon="icon: desktop"></span> Desktop
                        </button>
                        <button class="uk-button uk-button-small" onclick="setPreviewSize(\'tablet\')" id="btn-tablet" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
                            <span uk-icon="icon: tablet"></span> Tablet  
                        </button>
                        <button class="uk-button uk-button-small" onclick="setPreviewSize(\'mobile\')" id="btn-mobile" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
                            <span uk-icon="icon: phone"></span> Mobile
                        </button>
                    </div>
                </div>
                <div class="uk-width-auto">
                    <button class="uk-modal-close-default uk-button uk-button-small" type="button" style="background: rgba(255,255,255,0.9); color: #333; border: none;">
                        <span uk-icon="icon: close"></span> Schließen
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Modal Body -->
        <div class="uk-modal-body uk-padding-remove uk-background-muted" style="height: calc(90vh - 80px); overflow: hidden; position: relative; z-index: 100001 !important;">
            <div id="preview-container" class="uk-width-1-1 uk-height-1-1 uk-flex uk-flex-center uk-flex-middle" style="transition: all 0.3s ease;">
                <div id="preview-frame-container" style="background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.15); border-radius: 8px; overflow: hidden;">
                    <iframe id="preview-iframe" 
                            src="" 
                            style="border: none; background: white; display: block;">
                    </iframe>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- Modal Overlay Style -->
<style>
#preview-modal .uk-modal-dialog {
    z-index: 100000 !important;
}

#preview-modal {
    z-index: 99999 !important;
}

.uk-modal-page {
    z-index: 99998 !important;
}

/* REDAXO Backend Override */
body.rex-backend #preview-modal {
    z-index: 99999 !important;
}

body.rex-backend #preview-modal .uk-modal-dialog {
    z-index: 100000 !important;  
}
</style>

<script>
function openPreviewModal(themeName) {
    // Modal-Titel setzen
    document.getElementById("preview-theme-name").textContent = themeName;
    
    // iframe URL setzen - previewtheme Parameter wie in themes.php
    const previewUrl = "/?previewtheme=" + encodeURIComponent(themeName);
    document.getElementById("preview-iframe").src = previewUrl;
    
    // Modal öffnen
    UIkit.modal("#preview-modal").show();
    
    // Desktop als Standard setzen
    setTimeout(() => setPreviewSize("desktop"), 500);
}

function setPreviewSize(size) {
    const container = document.getElementById("preview-frame-container");
    const iframe = document.getElementById("preview-iframe");
    const buttons = ["desktop", "tablet", "mobile"];
    
    // Button-States zurücksetzen
    buttons.forEach(btn => {
        const element = document.getElementById("btn-" + btn);
        element.style.background = "rgba(255,255,255,0.2)";
        element.style.border = "1px solid rgba(255,255,255,0.3)";
        element.style.color = "white";
    });
    
    // Aktiven Button setzen
    const activeBtn = document.getElementById("btn-" + size);
    activeBtn.style.background = "rgba(255,255,255,0.9)";
    activeBtn.style.border = "1px solid white";
    activeBtn.style.color = "#333";
    
    // Container und iframe-Größe anpassen
    switch(size) {
        case "desktop":
            container.style.width = "95%";
            container.style.height = "calc(90vh - 120px)";
            iframe.style.width = "100%";
            iframe.style.height = "100%";
            break;
        case "tablet":
            container.style.width = "768px";
            container.style.height = "1024px";
            iframe.style.width = "768px";
            iframe.style.height = "1024px";
            break;
        case "mobile":
            container.style.width = "375px";
            container.style.height = "812px";
            iframe.style.width = "375px";
            iframe.style.height = "812px";
            break;
    }
}

// Modal schließen Event
UIkit.util.on("#preview-modal", "hidden", function() {
    document.getElementById("preview-iframe").src = "";
});

// Modal nach Laden korrekt positionieren
UIkit.util.on("#preview-modal", "shown", function() {
    // Sicherstellen dass Modal im Vordergrund ist
    const modal = document.getElementById("preview-modal");
    modal.style.zIndex = "99999";
    const dialog = modal.querySelector(".uk-modal-dialog");
    if (dialog) {
        dialog.style.zIndex = "100000";
    }
});
</script>
';

echo $content;
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

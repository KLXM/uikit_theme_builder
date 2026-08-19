<?php

/**
 * Extra Styles - Formular für Style-Set bearbeiten/erstellen
 */

$styleSetManager = new UikitThemeBuilder\StyleSetManager();
$styleSet = null;
$formTitle = 'Neues Style-Set erstellen';

if ($styleSetId > 0) {
    $styleSet = $styleSetManager->getStyleSetById($styleSetId);
    if ($styleSet) {
        $formTitle = 'Style-Set "' . rex_escape($styleSet['name']) . '" bearbeiten';
    }
}

// Form-Handler
if (rex_post('save', 'boolean')) {
    $name = rex_post('name', 'string');
    $description = rex_post('description', 'string');
    $styles = rex_post('extra_styles', 'array', []);
    
    // Styles-Daten verarbeiten (genau wie im Widget)
    $processedStyles = [];
    if (isset($styles['styles'])) {
        foreach ($styles['styles'] as $index => $style) {
            $enabled = isset($style['enabled']) && $style['enabled'] === '1';
            $hasSlug = !empty($style['slug']);
            
            if ($hasSlug && ($enabled || !empty($style['background_color']))) {
                $style['slug'] = preg_replace('/[^a-z0-9-]/', '', strtolower($style['slug']));
                $style['enabled'] = $enabled;
                
                // Defaults setzen
                $style['name'] = $style['name'] ?? '';
                $style['type'] = $style['type'] ?? 'background';
                $style['background_color'] = $style['background_color'] ?? '#ffffff';
                $style['text_color'] = $style['text_color'] ?? '';
                $style['link_color'] = $style['link_color'] ?? '';
                $style['border_color'] = $style['border_color'] ?? '#e5e5e5';
                $style['border_width'] = $style['border_width'] ?? '0';
                $style['border_radius'] = $style['border_radius'] ?? '0';
                $style['backdrop_blur'] = $style['backdrop_blur'] ?? '0';
                
                $processedStyles[] = $style;
            }
        }
    }
    
    if (empty($name)) {
        echo rex_view::error('Bitte geben Sie einen Namen für das Style-Set ein.');
    } else {
        try {
            if ($styleSetId > 0) {
                // Update
                $result = $styleSetManager->updateStyleSet($styleSetId, [
                    'name'        => $name,
                    'description' => $description,
                    'styles_data' => $processedStyles,
                ]);
                if ($result) {
                    echo rex_view::success('Style-Set wurde erfolgreich aktualisiert.');
                    // Daten neu laden
                    $styleSet = $styleSetManager->getStyleSetById($styleSetId);
                } else {
                    echo rex_view::error('Fehler beim Aktualisieren des Style-Sets.');
                }
            } else {
                // Create
                $slug = preg_replace('/[^a-z0-9-]/', '-', strtolower($name));
                $slug = trim(preg_replace('/-+/', '-', $slug), '-');
                $result = $styleSetManager->createStyleSet([
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => $description,
                    'styles_data' => $processedStyles,
                ]);
                if ($result) {
                    echo rex_view::success('Style-Set wurde erfolgreich erstellt.');
                    // Zurück zur Übersicht
                    rex_response::sendRedirect(rex_url::currentBackendPage());
                } else {
                    echo rex_view::error('Fehler beim Erstellen des Style-Sets.');
                }
            }
        } catch (Exception $e) {
            echo rex_view::error('Fehler: ' . $e->getMessage());
        }
    }
}

// Bestehende Styles für Bearbeitung vorbereiten
$existingStyles = [];
if ($styleSet && !empty($styleSet['styles_data'])) {
    $existingStyles = $styleSet['styles_data'];
}

$content = '';

$content .= '<div class="uk-margin">';

$content .= '<form action="" method="post" id="style-set-form">';

// Basis-Informationen Karte
$content .= '<div class="uk-card uk-card-default uk-margin">';
$content .= '<div class="uk-card-header">';
$content .= '<h3 class="uk-card-title"><span uk-icon="icon: info"></span> Allgemeine Informationen</h3>';
$content .= '<p class="uk-text-muted uk-margin-remove">Grundlegende Metadaten für das Style-Set</p>';
$content .= '</div>';
$content .= '<div class="uk-card-body">';

// Name
$content .= '<div class="uk-margin">';
$content .= '<label class="uk-form-label" for="name">Name des Style-Sets *</label>';
$content .= '<input type="text" id="name" name="name" value="' . rex_escape($styleSet['name'] ?? '') . '" 
             class="uk-input" required placeholder="z.B. Hotel Wellings Brand Colors">';
$content .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">Eindeutiger Name zur Identifikation des Style-Sets</div>';
$content .= '</div>';

// Beschreibung
$content .= '<div class="uk-margin">';
$content .= '<label class="uk-form-label" for="description">Beschreibung</label>';
$content .= '<textarea id="description" name="description" class="uk-textarea" rows="3" 
             placeholder="Optionale Beschreibung des Style-Sets">' . rex_escape($styleSet['description'] ?? '') . '</textarea>';
$content .= '</div>';

// Style-Set Information (bei Bearbeitung)
if ($styleSet) {
    $content .= '<div class="uk-margin">';
    $content .= '<div class="uk-alert uk-alert-primary">';
    $content .= '<h4>Style-Set Information</h4>';
    $content .= '<ul class="uk-list">';
    $content .= '<li><strong>Erstellt:</strong> ';
    if (!empty($styleSet['created_date'])) {
        $content .= date('d.m.Y H:i', strtotime($styleSet['created_date'])) . ' Uhr';
    } else {
        $content .= '<span class="uk-text-muted">Unbekannt</span>';
    }
    $content .= '</li>';
    if (!empty($styleSet['slug'])) {
        $content .= '<li><strong>Slug:</strong> <code>' . rex_escape($styleSet['slug']) . '</code></li>';
    }
    $content .= '<li><strong>Styles:</strong> ' . count($existingStyles) . ' Definitionen</li>';
    $content .= '</ul>';
    $content .= '</div>';
    $content .= '</div>';
}

$content .= '</div>';
$content .= '</div>';

// Extra Styles Widget Karte
$content .= '<div class="uk-card uk-card-default uk-margin">';
$content .= '<div class="uk-card-header">';
$content .= '<div class="uk-flex uk-flex-between uk-flex-middle">';
$content .= '<div>';
$content .= '<h3 class="uk-card-title uk-margin-remove"><span uk-icon="icon: palette"></span> Style-Definitionen</h3>';
$content .= '<p class="uk-text-muted uk-margin-remove">Farben, Hintergründe und weitere CSS-Styles konfigurieren</p>';
$content .= '</div>';
$content .= '<div>';
$content .= '<span class="uk-badge uk-badge-primary">' . count($existingStyles) . ' Style' . (count($existingStyles) !== 1 ? 's' : '') . '</span>';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';
$content .= '<div class="uk-card-body">';

// Hilfetext
$content .= '<div class="uk-alert uk-alert-primary uk-margin">';
$content .= '<h4><span uk-icon="icon: info"></span> Hinweise zur Bedienung</h4>';
$content .= '<ul class="uk-list uk-list-bullet">';
$content .= '<li><strong>Extra Style hinzufügen:</strong> Neuen Style zur Liste hinzufügen</li>';
$content .= '<li><strong>Aktiviert-Checkbox:</strong> Style in Theme einbinden (nur aktive Styles werden kompiliert)</li>';
$content .= '<li><strong>Slug:</strong> URL-freundlicher Name für CSS-Klassen (.uk-[type]-[slug])</li>';
$content .= '<li><strong>Live-Vorschau:</strong> Änderungen werden sofort in der Vorschau angezeigt</li>';
$content .= '</ul>';
$content .= '</div>';

// Extra Styles Widget einbinden
$extraStylesWidget = new UikitThemeBuilder\Widget\ExtraStylesWidget();
$widgetValues = ['styles' => $existingStyles];
$content .= $extraStylesWidget->renderForm($widgetValues);

$content .= '</div>';
$content .= '</div>';

$content .= '</form>';
$content .= '</div>';

// Sticky Footer für Aktionen (außerhalb des Formulars)
$content .= '<div class="form-actions-sticky">';
$content .= '<div class="uk-container uk-container-large">';
$content .= '<div class="uk-flex uk-flex-center uk-flex-middle" style="min-height: 60px;">';
$content .= '<div class="uk-button-group">';
$content .= '<button type="submit" form="style-set-form" name="save" value="1" class="uk-button uk-button-primary">';
$content .= '<span uk-icon="icon: check"></span> ' . ($styleSetId > 0 ? 'Aktualisieren' : 'Erstellen');
$content .= '</button>';
$content .= '<a href="' . rex_url::currentBackendPage() . '" class="uk-button uk-button-default">';
$content .= '<span uk-icon="icon: close"></span> Abbrechen';
$content .= '</a>';
if ($styleSetId > 0) {
    $content .= '<a href="' . rex_url::currentBackendPage(['func' => 'duplicate', 'id' => $styleSetId]) . '" 
                 class="uk-button uk-button-secondary">';
    $content .= '<span uk-icon="icon: copy"></span> Duplizieren';
    $content .= '</a>';
}
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';

echo $content;

?>
<style>
/* Sticky Footer für Form-Aktionen */
.form-actions-sticky {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-top: 1px solid #e5e5e5;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

/* REDAXO Backend Überschreibungen */
@media (min-width: 992px) {
    body, .rex-page {
        height: auto !important;
        min-height: 100% !important;
    }
}

/* Body Padding für Sticky Footer */
body.rex-page {
    padding-bottom: 80px !important;
}

.rex-page-main {
    padding-bottom: 20px;
}

/* Widget Z-Index Anpassungen */
.extra-styles-widget {
    position: relative;
    z-index: 1;
}

.extra-styles-widget .repeater-item {
    position: relative;
    z-index: 2;
    background: white;
}

/* Übergangseffekte */
.form-actions-sticky {
    transition: all 0.3s ease;
}
</style>

<script>
// Widget Initialisierung
document.addEventListener('DOMContentLoaded', function() {
    // Widget initialisieren falls noch nicht geschehen
    if (typeof initializeExtraStyles === 'function') {
        initializeExtraStyles();
    }
    
    // Auto-save Warnung bei Verlassen
    let formChanged = false;
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('change', () => formChanged = true);
        form.addEventListener('input', () => formChanged = true);
        
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
                return 'Sie haben ungespeicherte Änderungen. Möchten Sie die Seite wirklich verlassen?';
            }
        });
        
        form.addEventListener('submit', () => formChanged = false);
    }
});
</script>

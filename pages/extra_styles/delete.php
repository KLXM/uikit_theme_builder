<?php

/**
 * Extra Styles - Style-Set löschen
 */

$styleSetManager = new UikitThemeBuilder\StyleSetManager();

if ($styleSetId <= 0) {
    echo rex_view::error('Ungültige Style-Set ID.');
    return;
}

$styleSet = $styleSetManager->getStyleSetById($styleSetId);

if (!$styleSet) {
    echo rex_view::error('Style-Set nicht gefunden.');
    return;
}

// Löschung bestätigen
if (rex_post('delete', 'boolean')) {
    try {
        $result = $styleSetManager->deleteStyleSet($styleSetId);
        if ($result) {
            echo rex_view::success('Style-Set wurde erfolgreich gelöscht.');
            // Zurück zur Übersicht
            rex_response::sendRedirect(rex_url::currentBackendPage());
        } else {
            echo rex_view::error('Fehler beim Löschen des Style-Sets.');
        }
    } catch (Exception $e) {
        echo rex_view::error('Fehler: ' . $e->getMessage());
    }
}

$content = '';

$content .= '<div class="uk-margin">';

// Hauptkarte
$content .= '<div class="uk-card uk-card-default uk-card-large">';
$content .= '<div class="uk-card-header uk-text-center">';
$content .= '<div uk-icon="icon: warning; ratio: 3" class="uk-text-danger uk-margin"></div>';
$content .= '<h1 class="uk-card-title uk-text-danger uk-margin-remove">Style-Set löschen</h1>';
$content .= '<p class="uk-text-muted">Unwiderrufliche Entfernung bestätigen</p>';
$content .= '</div>';

$content .= '<div class="uk-card-body">';

// Warnung
$content .= '<div class="uk-alert uk-alert-warning">';
$content .= '<h4><span uk-icon="icon: warning"></span> Achtung! Unwiderrufliche Aktion</h4>';
$content .= '<p>Sie sind dabei, das Style-Set <strong>"' . rex_escape($styleSet['name']) . '"</strong> zu löschen.</p>';
$content .= '</div>';

// Style-Set Details
$content .= '<div class="uk-margin">';
$content .= '<h3 class="uk-margin-remove">' . rex_escape($styleSet['name']) . '</h3>';
if (!empty($styleSet['description'])) {
    $content .= '<p class="uk-text-muted">' . rex_escape($styleSet['description']) . '</p>';
}

$content .= '<div class="uk-grid-small uk-text-small uk-margin-small-top" uk-grid>';
$content .= '<div class="uk-width-auto">';
$content .= '<span class="uk-text-muted">Erstellt:</span> ';
if (!empty($styleSet['created_date'])) {
    $content .= date('d.m.Y H:i', strtotime($styleSet['created_date']));
} else {
    $content .= '<span class="uk-text-muted">Unbekannt</span>';
}
$content .= '</div>';
if (!empty($styleSet['slug'])) {
    $content .= '<div class="uk-width-auto">';
    $content .= '<span class="uk-text-muted">Slug:</span> <code>' . rex_escape($styleSet['slug']) . '</code>';
    $content .= '</div>';
}
$content .= '</div>';
$content .= '</div>';

// Betroffene Styles
// styles_data kann bereits ein Array sein oder ein JSON-String
$stylesData = is_string($styleSet['styles_data']) 
    ? json_decode($styleSet['styles_data'] ?: '[]', true) 
    : ($styleSet['styles_data'] ?: []);
$activeStyles = array_filter($stylesData, function($style) {
    return !empty($style['enabled']) && !empty($style['slug']);
});

if (!empty($activeStyles)) {
    $content .= '<div class="uk-alert uk-alert-primary uk-margin">';
    $content .= '<h5><span uk-icon="icon: info"></span> Betroffene Style-Definitionen (' . count($activeStyles) . ')</h5>';
    $content .= '<div class="uk-grid-small uk-child-width-auto uk-margin-small-top" uk-grid>';
    
    foreach ($activeStyles as $style) {
        $bgColor = rex_escape($style['background_color'] ?? '#f8f8f8');
        $textColor = rex_escape($style['text_color'] ?? '#333');
        
        $content .= '<div>';
        $content .= '<div class="uk-card uk-card-small uk-border-rounded uk-padding-small" 
                     style="background-color: ' . $bgColor . '; color: ' . $textColor . ';">';
        $content .= '<div class="uk-text-bold uk-text-small">' . rex_escape($style['name'] ?? 'Unnamed') . '</div>';
        $content .= '<div class="uk-text-meta uk-text-small">.uk-' . rex_escape($style['type'] ?? 'card') . '-' . rex_escape($style['slug']) . '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }
    
    $content .= '</div>';
    $content .= '</div>';
}

// Finale Warnung
$content .= '<div class="uk-alert uk-alert-danger uk-margin">';
$content .= '<h5><span uk-icon="icon: ban"></span> Diese Aktion kann nicht rückgängig gemacht werden!</h5>';
$content .= '<ul class="uk-margin-small-top uk-margin-remove-bottom">';
$content .= '<li>Das Style-Set wird permanent aus der Datenbank entfernt</li>';
$content .= '<li>Alle ' . count($stylesData ?: []) . ' Style-Definitionen gehen verloren</li>';
$content .= '<li>Themes, die dieses Style-Set verwenden, funktionieren möglicherweise nicht mehr korrekt</li>';
$content .= '</ul>';
$content .= '</div>';

$content .= '</div>';

// Card Footer mit Aktionen
$content .= '<div class="uk-card-footer">';
$content .= '<form action="" method="post" class="uk-margin-remove">';
$content .= '<div class="uk-grid-small uk-flex-center" uk-grid>';
$content .= '<div class="uk-width-auto">';
$content .= '<button type="submit" name="delete" value="1" 
             class="uk-button uk-button-danger uk-button-large"
             onclick="return confirm(\'Sind Sie sich wirklich sicher? Diese Aktion kann nicht rückgängig gemacht werden!\')">';
$content .= '<span uk-icon="icon: trash"></span> Endgültig löschen';
$content .= '</button>';
$content .= '</div>';
$content .= '<div class="uk-width-auto">';
$content .= '<a href="' . rex_url::currentBackendPage() . '" class="uk-button uk-button-default uk-button-large">';
$content .= '<span uk-icon="icon: close"></span> Abbrechen';
$content .= '</a>';
$content .= '</div>';
$content .= '</div>';
$content .= '</form>';
$content .= '</div>';

$content .= '</div>';

// Tipp
$content .= '<div class="uk-card uk-card-muted uk-margin">';
$content .= '<div class="uk-card-body uk-text-center">';
$content .= '<p class="uk-text-small uk-text-muted uk-margin-remove">';
$content .= '<span uk-icon="icon: info"></span> Tipp: Verwenden Sie die "Duplizieren" Funktion, um eine Backup-Kopie zu erstellen, bevor Sie ein Style-Set löschen.';
$content .= '</p>';
$content .= '</div>';
$content .= '</div>';

$content .= '</div>';

echo $content;

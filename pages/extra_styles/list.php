<?php

/**
 * Extra Styles - Übersicht aller Style-Sets
 */

$styleSetManager = new UikitThemeBuilder\StyleSetManager();

// Action Handling
$message = '';
if (rex_get('action') === 'toggle' && rex_get('id', 'int') > 0) {
    $id = rex_get('id', 'int');
    $active = rex_get('active', 'boolean');
    
    if ($styleSetManager->toggleStyleSetStatus($id, $active)) {
        $message = rex_view::success('Status wurde erfolgreich geändert.');
    } else {
        $message = rex_view::error('Fehler beim Ändern des Status.');
    }
}

echo $message;

// Alle Style-Sets laden
$styleSets = $styleSetManager->getAllStyleSets();

$content = '';

$content .= '<div class="uk-margin">';

if (empty($styleSets)) {
    $content .= '<div class="uk-card uk-card-default uk-card-body uk-text-center">
        <div uk-icon="icon: palette; ratio: 3" class="uk-text-muted uk-margin"></div>
        <h3>Keine Style-Sets vorhanden</h3>
        <p class="uk-text-muted">Erstellen Sie Ihr erstes Style-Set, um loszulegen.</p>
        <a href="' . rex_url::currentBackendPage(['func' => 'add']) . '" class="uk-button uk-button-primary">
            <span uk-icon="icon: plus"></span> Erstes Style-Set erstellen
        </a>
    </div>';
} else {
    $content .= '<div class="uk-grid-match uk-child-width-1-3@l uk-child-width-1-2@m" uk-grid>';
    
    foreach ($styleSets as $styleSet) {
        // styles_data kann bereits ein Array sein oder ein JSON-String
        $stylesData = is_string($styleSet['styles_data']) 
            ? json_decode($styleSet['styles_data'] ?: '[]', true) 
            : ($styleSet['styles_data'] ?: []);
        $stylesCount = count($stylesData);
        $isActive = !empty($styleSet['is_active']);
        $createdDate = new DateTime($styleSet['created_date'] ?? 'now');
        
        $content .= '<div>';
        $content .= '<div class="uk-card uk-card-default uk-card-hover' . (!$isActive ? ' uk-card-muted' : '') . '">';
        
        // Card Header
        $content .= '<div class="uk-card-header">';
        $content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
        $content .= '<div class="uk-width-expand">';
        $content .= '<h3 class="uk-card-title uk-margin-remove">' . rex_escape($styleSet['name']) . '</h3>';
        if (!empty($styleSet['description'])) {
            $content .= '<p class="uk-text-muted uk-margin-remove-top uk-text-small">' . rex_escape($styleSet['description']) . '</p>';
        }
        $content .= '</div>';
        $content .= '<div class="uk-width-auto">';
        $content .= '<span class="uk-badge' . ($isActive ? ' uk-badge-success' : ' uk-badge-default') . '">';
        $content .= $stylesCount . ' Style' . ($stylesCount !== 1 ? 's' : '');
        $content .= '</span>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        
        // Card Body mit Style-Preview
        if ($stylesCount > 0) {
            $content .= '<div class="uk-card-body uk-padding-small">';
            $content .= '<div class="uk-text-small uk-text-muted uk-margin-small-bottom">Style-Vorschau:</div>';
            $content .= '<div class="uk-grid-small uk-child-width-auto" uk-grid>';
            
            $activeStyles = array_filter($stylesData, function($style) {
                return !empty($style['enabled']) && !empty($style['slug']);
            });
            
            foreach (array_slice($activeStyles, 0, 6) as $style) {
                $bgColor = rex_escape($style['background_color'] ?? '#f8f8f8');
                $textColor = rex_escape($style['text_color'] ?? '#333');
                
                $content .= '<div>';
                $content .= '<div class="uk-card uk-card-small uk-border-rounded uk-padding-small" 
                             style="background-color: ' . $bgColor . '; color: ' . $textColor . '; min-width: 60px; text-align: center;">';
                $content .= '<div class="uk-text-small">' . rex_escape($style['name'] ?? 'Style') . '</div>';
                $content .= '</div>';
                $content .= '</div>';
            }
            
            if (count($activeStyles) > 6) {
                $content .= '<div class="uk-flex uk-flex-middle">';
                $content .= '<span class="uk-text-muted uk-text-small">+' . (count($activeStyles) - 6) . ' weitere</span>';
                $content .= '</div>';
            }
            
            $content .= '</div>';
            $content .= '</div>';
        }
        
        // Card Footer mit Aktionen
        $content .= '<div class="uk-card-footer uk-padding-small">';
        $content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
        $content .= '<div class="uk-width-expand uk-text-small uk-text-muted">';
        $content .= 'Erstellt: ' . $createdDate->format('d.m.Y H:i');
        $content .= '</div>';
        $content .= '<div class="uk-width-auto">';
        $content .= '<div class="uk-button-group">';
        $content .= '<a href="' . rex_url::currentBackendPage(['func' => 'edit', 'id' => $styleSet['id']]) . '" 
                     class="uk-button uk-button-small uk-button-primary" uk-tooltip="Bearbeiten">
                     <span uk-icon="icon: pencil"></span></a>';
        $content .= '<button class="uk-button uk-button-small uk-button-default" type="button" uk-tooltip="Weitere Aktionen">
                     <span uk-icon="icon: more"></span></button>';
        $content .= '<div uk-dropdown="pos: bottom-right">';
        $content .= '<ul class="uk-nav uk-dropdown-nav">';
        $content .= '<li><a href="' . rex_url::currentBackendPage(['action' => 'toggle', 'id' => $styleSet['id'], 'active' => !$isActive]) . '">
                     <span uk-icon="icon: ' . ($isActive ? 'ban' : 'check') . '"></span> ' . ($isActive ? 'Deaktivieren' : 'Aktivieren') . '</a></li>';
        $content .= '<li><a href="' . rex_url::currentBackendPage(['func' => 'duplicate', 'id' => $styleSet['id']]) . '">
                     <span uk-icon="icon: copy"></span> Duplizieren</a></li>';
        $content .= '<li class="uk-nav-divider"></li>';
        $content .= '<li><a href="' . rex_url::currentBackendPage(['func' => 'delete', 'id' => $styleSet['id']]) . '" class="uk-text-danger">
                     <span uk-icon="icon: trash"></span> Löschen</a></li>';
        $content .= '</ul>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        
        $content .= '</div>';
        $content .= '</div>';
    }
    
    $content .= '</div>';
}

$content .= '</div>';

echo $content;

?>
<script>
function previewStyleSet(id) {
    UIkit.modal.alert('Vorschau wird implementiert...');
}

function exportStyleSet(id) {
    window.location.href = '<?= rex_url::currentBackendPage() ?>&func=export&id=' + id;
}

function exportAllStyleSets() {
    window.location.href = '<?= rex_url::currentBackendPage() ?>&func=export_all';
}

function importStyleSets() {
    UIkit.modal.alert('Import wird implementiert...');
}
</script>

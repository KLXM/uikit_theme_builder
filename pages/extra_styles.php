<?php

/**
 * UIKit Theme Builder - Extra Styles Verwaltung
 * 
 * Moderne UIKit-gestylte Seite für die Verwaltung wiederverwendbarer Style-Sets.
 * Style-Sets können erstellt, bearbeitet, dupliziert und gelöscht werden.
 */

$func = rex_request('func', 'string');
$styleSetId = rex_request('id', 'int');
$styleSetManager = new UikitThemeBuilder\StyleSetManager();

// Duplizierung handling
if ($func === 'duplicate' && $styleSetId > 0) {
    $originalStyleSet = $styleSetManager->getStyleSetById($styleSetId);
    if ($originalStyleSet) {
        $newName = $originalStyleSet['name'] . ' (Kopie)';
        $counter = 1;
        
        // Eindeutigen Namen finden
        while ($styleSetManager->getStyleSetByName($newName)) {
            $counter++;
            $newName = $originalStyleSet['name'] . ' (Kopie ' . $counter . ')';
        }
        
        if ($styleSetManager->duplicateStyleSet($styleSetId, $newName)) {
            echo rex_view::success('<strong>Erfolgreich dupliziert!</strong> Das Style-Set wurde als "' . rex_escape($newName) . '" kopiert.');
        } else {
            echo rex_view::error('<strong>Fehler beim Duplizieren!</strong> Das Style-Set konnte nicht kopiert werden.');
        }
    }
    
    // Zurück zur Liste
    $func = '';
}

// Export-Funktionalität
if ($func === 'export' && $styleSetId > 0) {
    $styleSet = $styleSetManager->getStyleSetById($styleSetId);
    if ($styleSet) {
        $exportData = [
            'name' => $styleSet['name'] . ' (Import)',
            'description' => $styleSet['description'],
            'styles_data' => is_string($styleSet['styles_data']) 
                ? json_decode($styleSet['styles_data'], true) 
                : $styleSet['styles_data']
        ];
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="styleset-' . ($styleSet['slug'] ?: 'styleset') . '.json"');
        echo json_encode($exportData, JSON_PRETTY_PRINT);
        exit;
    }
}

// Router für verschiedene Funktionen
switch ($func) {
    case 'add':
    case 'edit':
        include __DIR__ . '/extra_styles/form.php';
        return;
    
    case 'delete':
        include __DIR__ . '/extra_styles/delete.php';
        return;
        
    default:
        include __DIR__ . '/extra_styles/list.php';
        return;
}

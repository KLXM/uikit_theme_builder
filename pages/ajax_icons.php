<?php

/**
 * AJAX Endpunkt für UIKit Icon Loader
 */

// Nur AJAX-Requests erlauben
if (!rex_request('action', 'string')) {
    http_response_code(400);
    exit('Bad Request');
}

$action = rex_request('action', 'string');

if ($action === 'load_icons') {
    // Icon Loader laden
    if (!class_exists('UikitIconLoader')) {
        require_once rex_addon::get('uikit_theme_builder')->getPath('lib/CustomWidgets/UikitIconLoader.php');
    }
    
    try {
        $icons = UikitIconLoader::loadAvailableIcons();
        
        // JSON Response senden
        header('Content-Type: application/json');
        echo json_encode($icons);
        
    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Fehler beim Laden der Icons: ' . $e->getMessage()
        ]);
    }
    
} else {
    http_response_code(404);
    echo 'Not Found';
}

exit;
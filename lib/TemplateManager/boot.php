<?php

/**
 * UIKit Theme Builder Integration für Template Manager
 * Registriert Custom Widgets für Theme-Auswahl und Icon-Picker
 */

// Nur wenn beide AddOns aktiv sind
if (rex_addon::get('template_manager')->isAvailable() && rex_addon::get('uikit_theme_builder')->isAvailable()) {
    
    // Autoload für Integration-Klassen
    rex_autoload::addDirectory(__DIR__ . '/lib/TemplateManager/');
    
    // Widgets beim Template Manager registrieren
    rex_extension::register('TEMPLATE_MANAGER_WIDGETS_REGISTERED', function() {
        
        // Theme Selection Widget registrieren
        \FriendsOfRedaxo\TemplateManager\WidgetRegistry::register(
            new \UikitThemeBuilder\TemplateManager\ThemeSelectionWidget()
        );
        
        // UIKit Icon + Link Repeater Widget registrieren
        \FriendsOfRedaxo\TemplateManager\WidgetRegistry::register(
            new \UikitThemeBuilder\TemplateManager\UikitIconLinkWidget()
        );
        
    });
}

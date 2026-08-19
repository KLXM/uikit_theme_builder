<?php

/**
 * Update-Script für UIKit Theme Builder
 * Erweitert die Datenbank um die neuen Extra Styles Felder
 */

// Prüfen ob die neuen Spalten bereits existieren
$sql = rex_sql::factory();

try {
    // Prüfe ob 'type' Spalte existiert
    $columns = $sql->getArray("SHOW COLUMNS FROM `" . rex::getTable('theme_builder_extra') . "` LIKE 'type'");
    
    if (empty($columns)) {
        // Neue Spalten hinzufügen
        $alterQueries = [
            "ALTER TABLE `" . rex::getTable('theme_builder_extra') . "` 
             ADD COLUMN `type` enum('card','section','background','border') DEFAULT 'background' COMMENT 'Extra Style Typ' AFTER `module`",
            
            "ALTER TABLE `" . rex::getTable('theme_builder_extra') . "` 
             ADD COLUMN `color` varchar(50) DEFAULT '#ffffff' COMMENT 'Hauptfarbe' AFTER `type`",
            
            "ALTER TABLE `" . rex::getTable('theme_builder_extra') . "` 
             ADD COLUMN `backdrop_blur` int(3) DEFAULT 0 COMMENT 'Backdrop Blur in Pixel' AFTER `color`",
            
            "ALTER TABLE `" . rex::getTable('theme_builder_extra') . "` 
             ADD COLUMN `text_color` varchar(50) DEFAULT '#333333' COMMENT 'Textfarbe' AFTER `backdrop_blur`",
            
            "ALTER TABLE `" . rex::getTable('theme_builder_extra') . "` 
             ADD COLUMN `link_color` varchar(50) DEFAULT '#0066cc' COMMENT 'Linkfarbe' AFTER `text_color`",
            
            "ALTER TABLE `" . rex::getTable('theme_builder_extra') . "` 
             ADD COLUMN `border_color` varchar(50) DEFAULT '#e5e5e5' COMMENT 'Rahmenfarbe' AFTER `link_color`",
            
            "ALTER TABLE `" . rex::getTable('theme_builder_extra') . "` 
             ADD COLUMN `border_width` varchar(20) DEFAULT '0' COMMENT 'Rahmendicke' AFTER `border_color`",
            
            "ALTER TABLE `" . rex::getTable('theme_builder_extra') . "` 
             ADD COLUMN `border_radius` varchar(20) DEFAULT '0' COMMENT 'Ecken-Radius' AFTER `border_width`",
            
            "ALTER TABLE `" . rex::getTable('theme_builder_extra') . "` 
             ADD COLUMN `is_light` tinyint(1) DEFAULT 0 COMMENT '1 = weiße Schrift, 0 = dunkle Schrift' AFTER `border_radius`",
            
            "ALTER TABLE `" . rex::getTable('theme_builder_extra') . "` 
             ADD COLUMN `compile_themes` text DEFAULT NULL COMMENT 'JSON Array der Theme-Slugs für Kompilierung' AFTER `is_light`",
            
            "ALTER TABLE `" . rex::getTable('theme_builder_extra') . "` 
             ADD KEY `type` (`type`)"
        ];
        
        foreach ($alterQueries as $query) {
            try {
                $sql->setQuery($query);
            } catch (rex_sql_exception $e) {
                // Ignoriere Fehler wenn Spalte bereits existiert
                if (!strpos($e->getMessage(), 'Duplicate column name')) {
                    throw $e;
                }
            }
        }
        
        rex_logger::factory()->info('UIKit Theme Builder: Extra Styles Tabelle erfolgreich erweitert');
    }
    
} catch (rex_sql_exception $e) {
    rex_logger::factory()->error('UIKit Theme Builder Update Fehler: ' . $e->getMessage());
    throw new rex_functional_exception('Update fehlgeschlagen: ' . $e->getMessage());
}

// Template Manager Integration - Stellen Sie sicher, dass Templates verfügbar sind
if (rex_addon::exists('template_manager')) {
    rex_logger::factory()->info('UIKit Theme Builder: Template Manager Addon erkannt');
}
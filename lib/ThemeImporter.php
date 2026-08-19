<?php

namespace UikitThemeBuilder;

/**
 * ThemeImporter
 * Importiert Themes und installiert Dependencies (Google Fonts, StyleSets)
 */
class ThemeImporter
{
    private UikitThemeBuilderManager $themeManager;
    private StyleSetManager $styleSetManager;
    private GoogleFontsManager $fontsManager;
    
    private array $importLog = [];
    private array $warnings = [];
    private array $errors = [];

    public function __construct()
    {
        $this->themeManager = new UikitThemeBuilderManager();
        $this->styleSetManager = new StyleSetManager();
        $this->fontsManager = new GoogleFontsManager();
    }

    /**
     * Theme aus JSON importieren
     * 
     * @param string $json JSON-String des Exports
     * @param array $options Import-Optionen
     * @return array Ergebnis mit success, theme_name, log, warnings, errors
     */
    public function importTheme(string $json, array $options = []): array
    {
        $this->importLog = [];
        $this->warnings = [];
        $this->errors = [];
        
        // Default-Optionen
        $options = array_merge([
            'download_fonts' => true,
            'import_style_sets' => true,
            'overwrite_style_sets' => false,
            'auto_rename' => true,
        ], $options);

        try {
            // JSON parsen
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Ungültiges JSON-Format: ' . json_last_error_msg());
            }

            // Validierung
            $this->validateImportData($data);

            // Theme-Name vorbereiten
            $themeName = $data['theme']['name'];
            if ($options['auto_rename']) {
                $themeName = $this->ensureUniqueThemeName($themeName);
            }

            $this->log("Starte Import von Theme: {$themeName}");

            // Dependencies installieren
            $styleSetMapping = [];
            
            if ($options['import_style_sets'] && !empty($data['dependencies']['style_sets'])) {
                $styleSetMapping = $this->importStyleSets(
                    $data['dependencies']['style_sets'],
                    $options['overwrite_style_sets']
                );
            }

            if ($options['download_fonts'] && !empty($data['dependencies']['google_fonts'])) {
                $this->importGoogleFonts($data['dependencies']['google_fonts']);
            }

            // Theme-Daten anpassen (StyleSet-IDs neu mappen)
            $themeData = $data['theme']['theme_data'];
            if (!empty($styleSetMapping) && isset($themeData['style_sets']['selected_style_sets'])) {
                $themeData['style_sets']['selected_style_sets'] = $this->remapStyleSetIds(
                    $themeData['style_sets']['selected_style_sets'],
                    $styleSetMapping
                );
            }

            // Theme speichern
            $saved = $this->themeManager->saveTheme($themeName, $themeData);
            
            if (!$saved) {
                throw new \Exception('Fehler beim Speichern des Themes');
            }

            $this->log("Theme erfolgreich importiert: {$themeName}");

            // Theme kompilieren
            $compileResult = $this->themeManager->compileTheme($themeName, $themeData);
            if ($compileResult['success']) {
                $this->log("Theme erfolgreich kompiliert");
            } else {
                $this->warning("Theme konnte nicht kompiliert werden: " . $compileResult['message']);
            }

            return [
                'success' => true,
                'theme_name' => $themeName,
                'log' => $this->importLog,
                'warnings' => $this->warnings,
                'errors' => $this->errors,
                'style_set_mapping' => $styleSetMapping,
            ];

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            
            return [
                'success' => false,
                'theme_name' => null,
                'log' => $this->importLog,
                'warnings' => $this->warnings,
                'errors' => $this->errors,
            ];
        }
    }

    /**
     * Import-Daten validieren
     */
    private function validateImportData(array $data): void
    {
        if (!isset($data['version'])) {
            throw new \Exception('Export-Version fehlt');
        }

        if (!isset($data['theme']['name'])) {
            throw new \Exception('Theme-Name fehlt');
        }

        if (!isset($data['theme']['theme_data'])) {
            throw new \Exception('Theme-Daten fehlen');
        }

        // Version-Check (optional)
        if ($data['version'] !== '1.0') {
            $this->warning("Export-Version {$data['version']} könnte inkompatibel sein");
        }
    }

    /**
     * StyleSets importieren
     */
    private function importStyleSets(array $styleSets, bool $overwrite = false): array
    {
        $mapping = []; // alt_id => neue_id
        
        foreach ($styleSets as $styleSetData) {
            $oldId = $styleSetData['id'];
            $slug = $styleSetData['slug'];
            
            // Prüfen ob StyleSet bereits existiert
            $existing = $this->styleSetManager->getStyleSetBySlug($slug);
            
            if ($existing) {
                if ($overwrite) {
                    // Aktualisieren
                    $updated = $this->styleSetManager->updateStyleSet($existing['id'], [
                        'name' => $styleSetData['name'],
                        'description' => $styleSetData['description'],
                        'styles_data' => $styleSetData['styles_data'],
                        'is_active' => $styleSetData['is_active'],
                    ]);
                    
                    if ($updated) {
                        $mapping[$oldId] = $existing['id'];
                        $this->log("StyleSet aktualisiert: {$styleSetData['name']} (Slug: {$slug})");
                    } else {
                        $this->warning("StyleSet konnte nicht aktualisiert werden: {$styleSetData['name']}");
                    }
                } else {
                    // Bestehende ID verwenden
                    $mapping[$oldId] = $existing['id'];
                    $this->log("StyleSet bereits vorhanden: {$styleSetData['name']} (Slug: {$slug})");
                }
            } else {
                // Neu erstellen
                $newId = $this->styleSetManager->createStyleSet([
                    'slug' => $slug,
                    'name' => $styleSetData['name'],
                    'description' => $styleSetData['description'],
                    'styles_data' => $styleSetData['styles_data'],
                    'is_active' => $styleSetData['is_active'],
                ]);
                
                if ($newId) {
                    $mapping[$oldId] = $newId;
                    $this->log("StyleSet erstellt: {$styleSetData['name']} (Slug: {$slug}, ID: {$newId})");
                } else {
                    $this->error("StyleSet konnte nicht erstellt werden: {$styleSetData['name']}");
                }
            }
        }
        
        return $mapping;
    }

    /**
     * Google Fonts importieren/herunterladen
     */
    private function importGoogleFonts(array $fonts): void
    {
        foreach ($fonts as $fontData) {
            $family = $fontData['family'];
            $variants = $fontData['variants'] ?? ['400'];
            
            // Prüfen ob Font bereits heruntergeladen ist
            $downloadedFonts = $this->fontsManager->getDownloadedFonts();
            
            if (isset($downloadedFonts[$family])) {
                $this->log("Google Font bereits vorhanden: {$family}");
            } else {
                // Font herunterladen
                $result = $this->fontsManager->downloadFont($family, $variants);
                
                if ($result['success']) {
                    $this->log("Google Font heruntergeladen: {$family}");
                } else {
                    $this->warning("Google Font konnte nicht heruntergeladen werden: {$family} - {$result['message']}");
                }
            }
        }
    }

    /**
     * StyleSet-IDs neu mappen
     */
    private function remapStyleSetIds(array $oldIds, array $mapping): array
    {
        $newIds = [];
        
        foreach ($oldIds as $oldId) {
            if (isset($mapping[$oldId])) {
                $newIds[] = $mapping[$oldId];
            } else {
                $this->warning("StyleSet-ID {$oldId} konnte nicht gemappt werden");
            }
        }
        
        return $newIds;
    }

    /**
     * Eindeutigen Theme-Namen sicherstellen
     */
    private function ensureUniqueThemeName(string $baseName): string
    {
        $themes = $this->themeManager->listThemes();
        
        if (!isset($themes[$baseName])) {
            return $baseName;
        }

        // Name mit Suffix
        $counter = 2;
        while (isset($themes["{$baseName}_{$counter}"])) {
            $counter++;
        }

        $newName = "{$baseName}_{$counter}";
        $this->log("Theme-Name angepasst: {$baseName} → {$newName}");
        
        return $newName;
    }

    /**
     * Log-Nachricht hinzufügen
     */
    private function log(string $message): void
    {
        $this->importLog[] = [
            'type' => 'info',
            'message' => $message,
            'time' => date('H:i:s')
        ];
    }

    /**
     * Warning hinzufügen
     */
    private function warning(string $message): void
    {
        $this->warnings[] = $message;
        $this->importLog[] = [
            'type' => 'warning',
            'message' => $message,
            'time' => date('H:i:s')
        ];
    }

    /**
     * Error hinzufügen
     */
    private function error(string $message): void
    {
        $this->errors[] = $message;
        $this->importLog[] = [
            'type' => 'error',
            'message' => $message,
            'time' => date('H:i:s')
        ];
    }

    /**
     * Theme aus Datei-Upload importieren
     */
    public function importThemeFromUpload(array $file, array $options = []): array
    {
        // Validierung
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Upload-Fehler: ' . $file['error']);
        }

        if ($file['type'] !== 'application/json') {
            throw new \Exception('Nur JSON-Dateien erlaubt');
        }

        // Datei lesen
        $json = file_get_contents($file['tmp_name']);
        
        if ($json === false) {
            throw new \Exception('Datei konnte nicht gelesen werden');
        }

        return $this->importTheme($json, $options);
    }
}

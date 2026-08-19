<?php

namespace UikitThemeBuilder;

/**
 * IconImporter
 * Importiert Custom Icons aus ZIP oder mehrere SVG-Dateien (Massen-Import)
 */
class IconImporter
{
    private CustomIconManager $iconManager;
    private string $iconsDir;
    private string $metaFile;
    
    private array $importLog = [];
    private array $warnings = [];
    private array $errors = [];

    public function __construct()
    {
        $this->iconManager = new CustomIconManager();
        $this->iconsDir = \rex_path::addonData('uikit_theme_builder', 'icons/');
        $this->metaFile = \rex_path::addonData('uikit_theme_builder', 'custom-icons.json');
    }

    /**
     * Icons aus ZIP importieren
     */
    public function importFromZip(string $zipPath): array
    {
        $this->importLog = [];
        $this->warnings = [];
        $this->errors = [];

        $zip = new \ZipArchive();
        
        if ($zip->open($zipPath) !== true) {
            return [
                'success' => false,
                'message' => 'ZIP-Archiv konnte nicht geöffnet werden'
            ];
        }

        // Temp-Verzeichnis erstellen
        $tempDir = \rex_path::addonCache('uikit_theme_builder', 'icon-import-' . time() . '/');
        \rex_dir::create($tempDir);

        // ZIP entpacken
        $zip->extractTo($tempDir);
        $zip->close();

        // Icons importieren
        $iconsDir = $tempDir . 'icons/';
        $importedCount = 0;

        if (is_dir($iconsDir)) {
            $files = scandir($iconsDir);
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                
                $filePath = $iconsDir . $file;
                
                if (is_file($filePath) && pathinfo($file, PATHINFO_EXTENSION) === 'svg') {
                    $result = $this->importSingleIcon($filePath, pathinfo($file, PATHINFO_FILENAME));
                    
                    if ($result['success']) {
                        $importedCount++;
                    }
                }
            }
        }

        // Temp-Verzeichnis löschen
        \rex_dir::delete($tempDir);

        $message = "Import abgeschlossen: {$importedCount} Icons importiert";
        if (!empty($this->warnings)) {
            $message .= ', ' . count($this->warnings) . ' Warnungen';
        }
        if (!empty($this->errors)) {
            $message .= ', ' . count($this->errors) . ' Fehler';
        }

        return [
            'success' => true,
            'imported_count' => $importedCount,
            'message' => $message,
            'log' => $this->importLog,
            'warnings' => $this->warnings,
            'errors' => $this->errors
        ];
    }

    /**
     * Mehrere SVG-Dateien importieren (Massen-Import)
     */
    public function importMultipleFiles(array $files): array
    {
        $this->importLog = [];
        $this->warnings = [];
        $this->errors = [];

        $importedCount = 0;

        foreach ($files as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->error('Upload-Fehler für ' . $file['name']);
                continue;
            }

            if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'svg') {
                $this->warning($file['name'] . ' übersprungen (keine SVG-Datei)');
                continue;
            }

            $iconName = pathinfo($file['name'], PATHINFO_FILENAME);
            $result = $this->importSingleIcon($file['tmp_name'], $iconName);

            if ($result['success']) {
                $importedCount++;
            }
        }

        $message = "Massen-Import abgeschlossen: {$importedCount} Icons importiert";
        if (!empty($this->warnings)) {
            $message .= ', ' . count($this->warnings) . ' Warnungen';
        }
        if (!empty($this->errors)) {
            $message .= ', ' . count($this->errors) . ' Fehler';
        }

        return [
            'success' => true,
            'imported_count' => $importedCount,
            'message' => $message,
            'log' => $this->importLog,
            'warnings' => $this->warnings,
            'errors' => $this->errors
        ];
    }

    /**
     * Einzelnes Icon importieren
     */
    private function importSingleIcon(string $filePath, string $iconName): array
    {
        $iconName = $this->iconManager->sanitizeIconName($iconName);

        if (empty($iconName)) {
            $this->error("Ungültiger Icon-Name");
            return ['success' => false];
        }

        // Prüfen ob Icon bereits existiert
        if ($this->iconManager->iconExists($iconName)) {
            // Eindeutigen Namen finden
            $counter = 2;
            $originalName = $iconName;
            while ($this->iconManager->iconExists($iconName)) {
                $iconName = $originalName . '-' . $counter;
                $counter++;
            }
            $this->warning("Icon '{$originalName}' existiert bereits, umbenannt zu '{$iconName}'");
        }

        // SVG-Inhalt lesen
        $svgContent = file_get_contents($filePath);

        // Über CustomIconManager hochladen
        $file = [
            'tmp_name' => $filePath,
            'name' => $iconName . '.svg',
            'size' => strlen($svgContent),
            'error' => UPLOAD_ERR_OK
        ];

        $result = $this->iconManager->uploadIcon($file, $iconName, 'imported', ['imported']);

        if ($result['success']) {
            $this->log("Icon '{$iconName}' erfolgreich importiert");
            return ['success' => true];
        } else {
            $this->error("Icon '{$iconName}' konnte nicht importiert werden: " . $result['message']);
            return ['success' => false];
        }
    }

    /**
     * Log-Nachricht
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
     * Warning
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
     * Error
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
}

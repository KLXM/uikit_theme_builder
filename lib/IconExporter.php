<?php

namespace UikitThemeBuilder;

/**
 * IconExporter
 * Exportiert Custom Icons als ZIP
 */
class IconExporter
{
    private CustomIconManager $iconManager;
    private string $metaFile;

    public function __construct()
    {
        $this->iconManager = new CustomIconManager();
        $this->metaFile = \rex_path::addonData('uikit_theme_builder', 'custom-icons.json');
    }

    /**
     * Alle Custom Icons als ZIP exportieren
     */
    public function exportAllIcons(): array
    {
        $icons = $this->iconManager->getCustomIcons();
        
        if (empty($icons)) {
            return [
                'success' => false,
                'message' => 'Keine Custom Icons zum Exportieren vorhanden'
            ];
        }

        // Temp ZIP erstellen - Cache-Verzeichnis sicherstellen
        $cacheDir = \rex_path::addonCache('uikit_theme_builder');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $tempZip = $cacheDir . 'icons-export-' . time() . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return [
                'success' => false,
                'message' => 'ZIP-Archiv konnte nicht erstellt werden. Prüfe Schreibrechte in: ' . $cacheDir
            ];
        }

        // Icons hinzufügen
        $addedCount = 0;
        $errors = [];
        foreach ($icons as $icon) {
            // Icon-Pfad direkt aus Array verwenden (enthält bereits vollständigen Pfad)
            $iconPath = $icon['file'] ?? null;
            $iconName = $icon['name'] ?? null;
            
            if (!$iconPath || !$iconName) {
                $errors[] = 'Icon ohne Dateiname oder Pfad gefunden: ' . json_encode($icon);
                continue;
            }
            
            // Sicherstellen dass .svg Extension im Namen vorhanden ist
            $iconFilename = str_ends_with($iconName, '.svg') ? $iconName : $iconName . '.svg';
            
            // Prüfen ob es eine Datei ist (nicht Verzeichnis)
            if (!file_exists($iconPath)) {
                $errors[] = 'Icon-Datei nicht gefunden: ' . $iconPath;
                continue;
            }
            
            if (!is_file($iconPath)) {
                $errors[] = 'Pfad ist kein File: ' . $iconPath;
                continue;
            }
            
            if ($zip->addFile($iconPath, 'icons/' . $iconFilename)) {
                $addedCount++;
            } else {
                $errors[] = 'Konnte Icon nicht hinzufügen: ' . $iconFilename;
            }
        }
        
        if ($addedCount === 0) {
            $zip->close();
            @unlink($tempZip);
            return [
                'success' => false,
                'message' => 'Keine Icons konnten hinzugefügt werden. ' . implode(', ', $errors)
            ];
        }

        // Metadaten hinzufügen
        if (file_exists($this->metaFile)) {
            $zip->addFile($this->metaFile, 'custom-icons.json');
        }

        // Export-Info hinzufügen
        $exportInfo = [
            'version' => '1.0',
            'exported_at' => date('Y-m-d H:i:s'),
            'icon_count' => $addedCount,
            'addon_version' => \rex_addon::get('uikit_theme_builder')->getVersion(),
        ];
        $zip->addFromString('export-info.json', json_encode($exportInfo, JSON_PRETTY_PRINT));

        // ZIP schließen und Status prüfen
        $closeStatus = $zip->close();
        
        if (!$closeStatus) {
            return [
                'success' => false,
                'message' => 'Fehler beim Schließen der ZIP-Datei. Status: ' . $zip->getStatusString()
            ];
        }
        
        // Prüfen ob Datei wirklich existiert
        if (!file_exists($tempZip)) {
            return [
                'success' => false,
                'message' => 'ZIP-Datei wurde nicht erstellt: ' . $tempZip . ' (close status: ' . ($closeStatus ? 'true' : 'false') . ')'
            ];
        }
        
        // Prüfen ob Datei eine gültige Größe hat
        $fileSize = filesize($tempZip);
        if ($fileSize === 0) {
            @unlink($tempZip);
            return [
                'success' => false,
                'message' => 'ZIP-Datei ist leer (0 Bytes)'
            ];
        }

        return [
            'success' => true,
            'zip_file' => $tempZip,
            'icon_count' => $addedCount
        ];
    }

    /**
     * Icons als Download bereitstellen
     */
    public function exportIconsAsDownload(): void
    {
        $result = $this->exportAllIcons();

        if (!$result['success']) {
            throw new \Exception($result['message']);
        }

        $filename = 'uikit-custom-icons-' . date('Y-m-d') . '.zip';
        $zipFile = $result['zip_file'];

        // ALLE Output Buffer leeren und Auto-Flush aktivieren
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Sicherstellen dass nichts mehr gebuffert wird
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($zipFile));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        header('Pragma: public');

        // Datei ausgeben
        readfile($zipFile);
        
        // Temp-Datei löschen
        unlink($zipFile);

        exit;
    }
}

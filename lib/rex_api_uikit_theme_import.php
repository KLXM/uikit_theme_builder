<?php

/**
 * API Endpoint für Theme-Import
 */
class rex_api_uikit_theme_import extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        try {
            // Datei-Upload prüfen
            if (!isset($_FILES['theme_file'])) {
                throw new Exception('Keine Datei hochgeladen');
            }

            $file = $_FILES['theme_file'];

            // Import-Optionen
            $options = [
                'download_fonts' => rex_request('download_fonts', 'bool', true),
                'import_style_sets' => rex_request('import_style_sets', 'bool', true),
                'overwrite_style_sets' => rex_request('overwrite_style_sets', 'bool', false),
                'auto_rename' => rex_request('auto_rename', 'bool', true),
            ];

            $importer = new UikitThemeBuilder\ThemeImporter();
            $result = $importer->importThemeFromUpload($file, $options);

            rex_response::cleanOutputBuffers();
            rex_response::sendJson($result);

        } catch (Exception $e) {
            rex_response::cleanOutputBuffers();
            rex_response::setStatus(rex_response::HTTP_INTERNAL_ERROR);
            rex_response::sendJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}

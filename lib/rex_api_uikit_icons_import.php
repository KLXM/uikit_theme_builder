<?php

/**
 * API Endpoint für Icon Import (ZIP oder Massen-Import)
 */
class rex_api_uikit_icons_import extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        try {
            $importer = new UikitThemeBuilder\IconImporter();
            $result = null;

            // ZIP-Import
            if (isset($_FILES['icon_zip']) && $_FILES['icon_zip']['error'] === UPLOAD_ERR_OK) {
                $result = $importer->importFromZip($_FILES['icon_zip']['tmp_name']);
            }
            // Massen-Import (mehrere SVG-Dateien)
            elseif (isset($_FILES['icon_files']) && is_array($_FILES['icon_files']['name'])) {
                // Restrukturiere $_FILES Array
                $files = [];
                $fileCount = count($_FILES['icon_files']['name']);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    $files[] = [
                        'name' => $_FILES['icon_files']['name'][$i],
                        'tmp_name' => $_FILES['icon_files']['tmp_name'][$i],
                        'size' => $_FILES['icon_files']['size'][$i],
                        'error' => $_FILES['icon_files']['error'][$i],
                    ];
                }
                
                $result = $importer->importMultipleFiles($files);
            }
            else {
                throw new Exception('Keine Dateien hochgeladen');
            }

            rex_response::cleanOutputBuffers();
            rex_response::sendJson($result);
            exit;

        } catch (Exception $e) {
            rex_response::cleanOutputBuffers();
            rex_response::setStatus(rex_response::HTTP_INTERNAL_ERROR);
            rex_response::sendJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }
}

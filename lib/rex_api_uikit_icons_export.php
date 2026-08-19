<?php

/**
 * API Endpoint für Icon Export
 */
class rex_api_uikit_icons_export extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        try {
            $exporter = new UikitThemeBuilder\IconExporter();
            $exporter->exportIconsAsDownload();
            // exportIconsAsDownload macht exit

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

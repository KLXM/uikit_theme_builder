<?php

/**
 * API Endpoint für Theme-Export
 */
class rex_api_uikit_theme_export extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        try {
            $themeName = rex_request('theme', 'string');
            $includeStyleSets = rex_request('include_style_sets', 'bool', true);

            if (empty($themeName)) {
                throw new Exception('Theme-Name fehlt');
            }

            $exporter = new UikitThemeBuilder\ThemeExporter();
            $exporter->exportThemeAsDownload($themeName, $includeStyleSets);
            // exportThemeAsDownload macht exit, daher wird dieser Code nicht erreicht

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

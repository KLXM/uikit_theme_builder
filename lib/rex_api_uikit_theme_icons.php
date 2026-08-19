<?php

/**
 * API: Verfuegbare UIKit-Icons als JSON liefern (UIkit + Custom).
 */
class rex_api_uikit_theme_icons extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        rex_response::cleanOutputBuffers();

        try {
            $iconBuilder = new UikitThemeBuilder\CustomIconBuilder();
            $icons = $iconBuilder->getAllAvailableIcons();

            rex_response::sendJson([
                'success' => true,
                'data' => $icons,
            ]);
        } catch (Throwable $e) {
            rex_response::sendJson([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
            ]);
        }

        exit;
    }
}

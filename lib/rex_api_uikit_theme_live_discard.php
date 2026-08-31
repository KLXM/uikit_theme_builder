<?php

/**
 * API Endpoint für den Live Theme Editor: eigenen Entwurf verwerfen ("Verwerfen").
 */
class rex_api_uikit_theme_live_discard extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        try {
            $user = UikitThemeBuilder\LiveThemeState::requireUser();

            $theme = rex_post('theme', 'string', '');
            UikitThemeBuilder\LiveThemeState::validateTheme($theme);

            UikitThemeBuilder\LiveThemeState::deleteIfExists(
                UikitThemeBuilder\LiveThemeState::draftPath($theme, $user->getId())
            );

            rex_response::cleanOutputBuffers();
            rex_response::sendJson(['success' => true]);
            exit;
        } catch (Exception $e) {
            rex_response::cleanOutputBuffers();
            rex_response::setStatus(rex_response::HTTP_FORBIDDEN);
            rex_response::sendJson(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

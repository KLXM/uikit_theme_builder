<?php

/**
 * API Endpoint für den Live Theme Editor: laufende Live-Session beenden ("Live-Session
 * beenden"). Entfernt das Aktiv-Flag (boot.php injiziert den Public-Listener danach nicht
 * mehr) und die Public-Datei. Nur Admins.
 */
class rex_api_uikit_theme_live_stop extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        try {
            UikitThemeBuilder\LiveThemeState::requireAdmin();

            $theme = rex_post('theme', 'string', '');
            UikitThemeBuilder\LiveThemeState::validateTheme($theme);

            UikitThemeBuilder\LiveThemeState::deleteIfExists(UikitThemeBuilder\LiveThemeState::flagPath($theme));
            UikitThemeBuilder\LiveThemeState::deleteIfExists(UikitThemeBuilder\LiveThemeState::publicPath($theme));

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

<?php

/**
 * API Endpoint für den Live Theme Editor: eigenen Draft für ALLE Besucher sichtbar machen
 * ("Live schalten"). Nur Admins - das betrifft die öffentliche Website.
 */
class rex_api_uikit_theme_live_golive extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        try {
            $user = UikitThemeBuilder\LiveThemeState::requireAdmin();

            $theme = rex_post('theme', 'string', '');
            UikitThemeBuilder\LiveThemeState::validateTheme($theme);

            $draft = UikitThemeBuilder\LiveThemeState::readJson(
                UikitThemeBuilder\LiveThemeState::draftPath($theme, $user->getId())
            );

            UikitThemeBuilder\LiveThemeState::writeJson(
                UikitThemeBuilder\LiveThemeState::publicPath($theme),
                $draft
            );
            touch(UikitThemeBuilder\LiveThemeState::flagPath($theme));

            rex_response::cleanOutputBuffers();
            rex_response::sendJson(['success' => true, 'values' => $draft]);
            exit;
        } catch (Exception $e) {
            rex_response::cleanOutputBuffers();
            rex_response::setStatus(rex_response::HTTP_FORBIDDEN);
            rex_response::sendJson(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

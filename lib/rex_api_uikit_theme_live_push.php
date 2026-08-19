<?php

/**
 * API Endpoint für den Live Theme Editor: aktuellen Entwurf (nur für die eigene Session
 * sichtbar) schreiben. published=true, weil vom Frontend-Overlay aufgerufen -
 * Berechtigung wird explizit in LiveThemeState::requireUser() geprüft, nicht über REDAXO.
 */
class rex_api_uikit_theme_live_push extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        try {
            $user = UikitThemeBuilder\LiveThemeState::requireUser();

            $theme = rex_post('theme', 'string', '');
            UikitThemeBuilder\LiveThemeState::validateTheme($theme);

            $raw = json_decode((string) rex_post('values', 'string', '{}'), true);
            $values = UikitThemeBuilder\LiveThemeState::sanitizeValues(is_array($raw) ? $raw : []);

            UikitThemeBuilder\LiveThemeState::writeJson(
                UikitThemeBuilder\LiveThemeState::draftPath($theme, $user->getId()),
                $values
            );

            rex_response::cleanOutputBuffers();
            rex_response::sendJson(['success' => true, 'values' => $values]);
            exit;
        } catch (Exception $e) {
            rex_response::cleanOutputBuffers();
            rex_response::setStatus(rex_response::HTTP_FORBIDDEN);
            rex_response::sendJson(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

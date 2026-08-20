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

            // Feingranulare Rechte: '_switch_theme' und die eigentlichen Style-Felder sind
            // getrennt erlaubbar (siehe LiveThemeState::canStyle()/canSwitchTheme()) - hier
            // serverseitig durchsetzen, nicht nur die UI ausblenden.
            if (isset($values['_switch_theme'])) {
                $allowedThemes = UikitThemeBuilder\DomainContext::getLiveEditorAvailableThemes($theme);
                if (!UikitThemeBuilder\LiveThemeState::canSwitchTheme($user) || !isset($allowedThemes[$values['_switch_theme']])) {
                    unset($values['_switch_theme']);
                }
            }
            if (!UikitThemeBuilder\LiveThemeState::canStyle($user)) {
                $values = isset($values['_switch_theme']) ? ['_switch_theme' => $values['_switch_theme']] : [];
            }

            UikitThemeBuilder\LiveThemeState::writeJson(
                UikitThemeBuilder\LiveThemeState::draftPath($theme, $user->getId()),
                $values
            );

            // Solange "Live schalten" für dieses Theme aktiv ist UND genau dieser Nutzer sie
            // ausgelöst hat (nicht irgendein anderer Redakteur mit einem parallelen privaten
            // Draft), jeden weiteren Push zusätzlich live spiegeln - sonst friert der öffentliche
            // Broadcast beim Stand vom Klick auf "Live schalten" ein, obwohl im Editor
            // munter weiterbearbeitet wird ("es passiert nicht live").
            if (
                UikitThemeBuilder\DomainContext::isLiveBroadcastEnabled()
                && UikitThemeBuilder\LiveThemeState::liveBroadcastOwnerId($theme) === $user->getId()
            ) {
                UikitThemeBuilder\LiveThemeState::writeJson(
                    UikitThemeBuilder\LiveThemeState::publicPath($theme),
                    $values
                );
            }

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

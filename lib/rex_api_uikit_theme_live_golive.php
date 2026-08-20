<?php

/**
 * API Endpoint für den Live Theme Editor: eigenen Draft für ALLE Besucher sichtbar machen
 * ("Live schalten"-Checkbox im Editor). Nur Admins - das betrifft die öffentliche Website.
 * Explizites Opt-in pro Aktion, kein automatischer Broadcast.
 */
class rex_api_uikit_theme_live_golive extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        try {
            $user = UikitThemeBuilder\LiveThemeState::requireAdmin();

            // Globaler Schalter aus den Addon-Einstellungen - Admin-Recht allein reicht nicht,
            // die UI blendet die Checkbox dafür zwar bereits aus, aber das ist kein Schutz gegen
            // einen direkten POST auf diesen Endpoint.
            if (!UikitThemeBuilder\DomainContext::isLiveBroadcastEnabled()) {
                throw new Exception('Live-Schaltung ist in den Addon-Einstellungen nicht freigegeben.');
            }

            $theme = rex_post('theme', 'string', '');
            UikitThemeBuilder\LiveThemeState::validateTheme($theme);

            $draft = UikitThemeBuilder\LiveThemeState::readJson(
                UikitThemeBuilder\LiveThemeState::draftPath($theme, $user->getId())
            );

            UikitThemeBuilder\LiveThemeState::writeJson(
                UikitThemeBuilder\LiveThemeState::publicPath($theme),
                $draft
            );
            // Owner im Flag hinterlegen (nicht nur touch()): push.php muss später wissen, WESSEN
            // laufende Bearbeitung live gespiegelt werden darf - siehe liveBroadcastOwnerId().
            UikitThemeBuilder\LiveThemeState::writeJson(
                UikitThemeBuilder\LiveThemeState::flagPath($theme),
                ['userId' => $user->getId(), 'startedAt' => time()]
            );

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

<?php

/**
 * API Endpoint für den Live Theme Editor: Domain dauerhaft auf ein anderes Theme umstellen
 * ("Theme übernehmen"). Anders als rex_api_uikit_theme_live_save (kompiliert Variablen-
 * Änderungen INS aktuelle Theme) - hier wird stattdessen die Domain->Theme-Zuordnung selbst
 * geändert (uikit_theme_domains), das aktuelle Theme bleibt unverändert. Nur Admins.
 */
class rex_api_uikit_theme_live_switch_theme extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        try {
            UikitThemeBuilder\LiveThemeState::requireAdmin();

            $theme = rex_post('theme', 'string', '');
            UikitThemeBuilder\LiveThemeState::validateTheme($theme);

            $newTheme = rex_post('new_theme', 'string', '');
            UikitThemeBuilder\LiveThemeState::validateTheme($newTheme);

            $domainId = UikitThemeBuilder\DomainContext::getCurrentDomainId();
            if (!$domainId) {
                throw new Exception('Keine Domain im aktuellen Kontext gefunden.');
            }

            $sql = rex_sql::factory();
            $sql->setTable(rex::getTable('uikit_theme_domains'));
            $sql->setWhere(['domain_id' => $domainId]);
            $sql->setValue('theme_name', $newTheme);
            $sql->update();

            if (0 === $sql->getRows()) {
                throw new Exception('Domain-Zuordnung nicht gefunden - Theme-Wechsel nur für bereits zugeordnete Domains möglich.');
            }

            UikitThemeBuilder\DomainContext::resetContext();

            // Live-Overlay-Zustand für das ALTE Theme aufräumen - ab jetzt zeigt die Domain
            // direkt das neue, kompilierte Theme, kein Overlay mehr nötig.
            foreach (UikitThemeBuilder\LiveThemeState::allDraftPaths($theme) as $draftPath) {
                UikitThemeBuilder\LiveThemeState::deleteIfExists($draftPath);
            }

            rex_response::cleanOutputBuffers();
            rex_response::sendJson(['success' => true, 'theme' => $newTheme]);
            exit;
        } catch (Exception $e) {
            rex_response::cleanOutputBuffers();
            rex_response::setStatus(rex_response::HTTP_FORBIDDEN);
            rex_response::sendJson(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

<?php

/**
 * API Endpoint für den Live Theme Editor: eigenen Entwurf dauerhaft ins Theme übernehmen
 * ("Speichern"). Nutzt die bestehende Persistenz-/Compile-Pipeline
 * (UikitThemeBuilderManager::saveTheme()/compileTheme()) unverändert weiter - der Live-Editor
 * ist nur eine temporäre Vorschau-Schicht davor. Nur Admins.
 */
class rex_api_uikit_theme_live_save extends rex_api_function
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

            if (empty($draft)) {
                throw new Exception('Kein Entwurf zum Speichern vorhanden.');
            }

            $manager = new UikitThemeBuilder\UikitThemeBuilderManager();
            $themeRecord = $manager->loadTheme($theme);

            if (!$themeRecord || empty($themeRecord['data'])) {
                throw new Exception("Theme '{$theme}' konnte nicht geladen werden.");
            }

            $themeData = $themeRecord['data'];

            foreach ($draft as $fieldKey => $value) {
                if (!isset(UikitThemeBuilder\LiveThemeState::FIELDS[$fieldKey])) {
                    continue;
                }
                $field = UikitThemeBuilder\LiveThemeState::FIELDS[$fieldKey];
                $themeData[$field['group']][$field['less']] = $value;
            }

            $manager->saveTheme($theme, $themeData);
            $compileResult = $manager->compileTheme($theme, $themeData);

            // Live-Overlay-Zustand für dieses Theme aufräumen - ab jetzt ist es Teil des echten Themes
            foreach (UikitThemeBuilder\LiveThemeState::allDraftPaths($theme) as $draftPath) {
                UikitThemeBuilder\LiveThemeState::deleteIfExists($draftPath);
            }
            UikitThemeBuilder\LiveThemeState::deleteIfExists(UikitThemeBuilder\LiveThemeState::publicPath($theme));
            UikitThemeBuilder\LiveThemeState::deleteIfExists(UikitThemeBuilder\LiveThemeState::flagPath($theme));

            rex_response::cleanOutputBuffers();
            rex_response::sendJson([
                'success' => true,
                'compiled' => $compileResult['success'] ?? false,
            ]);
            exit;
        } catch (Exception $e) {
            rex_response::cleanOutputBuffers();
            rex_response::setStatus(rex_response::HTTP_FORBIDDEN);
            rex_response::sendJson(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

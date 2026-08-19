<?php

/**
 * API Endpoint für Theme-Copy (Duplizieren)
 */
class rex_api_uikit_theme_copy extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        try {
            $themeName = rex_request('theme', 'string');

            if (empty($themeName)) {
                throw new Exception('Theme-Name fehlt');
            }

            $themeManager = new UikitThemeBuilder\UikitThemeBuilderManager();
            
            // Theme laden
            $themeData = $themeManager->loadTheme($themeName);
            
            if (!$themeData) {
                throw new Exception("Theme '{$themeName}' nicht gefunden");
            }

            // Neuen Namen finden
            $newName = $this->findUniqueCopyName($themeName, $themeManager);

            // Theme kopieren
            $saved = $themeManager->saveTheme($newName, $themeData['data']);

            if (!$saved) {
                throw new Exception('Theme konnte nicht kopiert werden');
            }

            // Kompilieren
            $compileResult = $themeManager->compileTheme($newName, $themeData['data']);

            rex_response::cleanOutputBuffers();
            rex_response::sendJson([
                'success' => true,
                'theme_name' => $newName,
                'message' => "Theme '{$themeName}' wurde als '{$newName}' kopiert",
                'compiled' => $compileResult['success']
            ]);
            exit;

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

    /**
     * Eindeutigen Kopie-Namen finden
     */
    private function findUniqueCopyName(string $baseName, $themeManager): string
    {
        $themes = $themeManager->listThemes();
        
        // Prüfe ob "_copy" oder "_copy_X" bereits im Namen ist
        if (preg_match('/^(.+)_copy(_\d+)?$/', $baseName, $matches)) {
            $baseName = trim($matches[1]);
        }

        // Ersten verfügbaren Namen finden
        $newName = "{$baseName}_copy";
        if (!isset($themes[$newName])) {
            return $newName;
        }

        $counter = 2;
        while (isset($themes["{$baseName}_copy_{$counter}"])) {
            $counter++;
        }

        return "{$baseName}_copy_{$counter}";
    }
}

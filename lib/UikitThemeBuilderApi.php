<?php

namespace UikitThemeBuilder;

/**
 * UIKit Theme Builder API
 * REX API für AJAX-Operationen
 */
class UikitThemeBuilderApi extends \rex_api_function
{
    protected $published = true;
    
    public function execute()
    {
        $action = \rex_request('action', 'string');
        $themeManager = new UikitThemeBuilderManager();
        
        try {
            switch ($action) {
                case 'compile':
                    return $this->compileTheme($themeManager);
                    
                case 'save':
                    return $this->saveTheme($themeManager);
                    
                case 'delete':
                    return $this->deleteTheme($themeManager);
                    
                case 'list':
                    return $this->listThemes($themeManager);

                case 'icons':
                    return $this->listIcons();
                    
                default:
                    throw new \rex_api_exception('Unknown action: ' . $action);
            }
        } catch (\Exception $e) {
            return new \rex_api_result(false, $e->getMessage());
        }
    }
    
    /**
     * Theme kompilieren
     */
    private function compileTheme(UikitThemeBuilderManager $manager): \rex_api_result
    {
        $themeName = \rex_request('theme', 'string');
        $themeData = \rex_request('data', 'array');
        
        if (!$themeName) {
            throw new \rex_api_exception('Theme name is required');
        }
        
        if (!$themeData) {
            throw new \rex_api_exception('Theme data is required');
        }
        
        $result = $manager->compileTheme($themeName, $themeData);
        
        return new \rex_api_result(true, 'Theme compiled successfully', [
            'css_size' => strlen($result['css']),
            'compilation_time' => $result['compilation_info']['compilation_time'],
            'variables_count' => $result['compilation_info']['variables_count']
        ]);
    }
    
    /**
     * Theme speichern
     */
    private function saveTheme(UikitThemeBuilderManager $manager): \rex_api_result
    {
        $themeName = \rex_request('theme', 'string');
        $themeData = \rex_request('data', 'array');
        
        if (!$themeName) {
            throw new \rex_api_exception('Theme name is required');
        }
        
        if (!$themeData) {
            throw new \rex_api_exception('Theme data is required');
        }
        
        $success = $manager->saveTheme($themeName, $themeData);
        
        if ($success) {
            return new \rex_api_result(true, 'Theme saved successfully');
        } else {
            throw new \rex_api_exception('Failed to save theme');
        }
    }
    
    /**
     * Theme löschen
     */
    private function deleteTheme(UikitThemeBuilderManager $manager): \rex_api_result
    {
        $themeName = \rex_request('theme', 'string');
        
        if (!$themeName) {
            throw new \rex_api_exception('Theme name is required');
        }
        
        $success = $manager->deleteTheme($themeName);
        
        if ($success) {
            return new \rex_api_result(true, 'Theme deleted successfully');
        } else {
            throw new \rex_api_exception('Failed to delete theme');
        }
    }
    
    /**
     * Themes auflisten
     */
    private function listThemes(UikitThemeBuilderManager $manager): \rex_api_result
    {
        $themes = $manager->listThemes();
        
        return new \rex_api_result(true, 'Themes retrieved successfully', $themes);
    }

    /**
     * Verfuegbare Icons (UIkit + Custom) auflisten
     */
    private function listIcons(): \rex_api_result
    {
        $iconBuilder = new CustomIconBuilder();
        $icons = $iconBuilder->getAllAvailableIcons();

        return new \rex_api_result(true, 'Icons retrieved successfully', $icons);
    }
}
<?php

/**
 * UIKit Theme Builder - Themes Overview
 */

$func = rex_request('func', 'string');
$themeManager = new UikitThemeBuilder\UikitThemeBuilderManager();
$previewThemeFromRequest = rex_request('preview_theme', 'string', '');

$quickstartFontsManager = new \UikitThemeBuilder\GoogleFontsManager();
$quickstartAvailableFonts = $quickstartFontsManager->getAllAvailableFonts();

$quickstartFontMap = [];
$quickstartGoogleFonts = [];
$quickstartSystemFonts = [];

foreach ($quickstartAvailableFonts as $font) {
    $family = trim((string) ($font['family'] ?? ''));
    if ('' === $family) {
        continue;
    }

    $category = (string) ($font['category'] ?? 'sans-serif');
    $source = (string) ($font['source'] ?? 'system');

    $quickstartFontMap[$family] = [
        'category' => $category,
        'source' => $source,
    ];

    if ('google' === $source) {
        $quickstartGoogleFonts[$family] = [
            'family' => $family,
            'category' => $category,
            'source' => $source,
        ];
    } else {
        $quickstartSystemFonts[$family] = [
            'family' => $family,
            'category' => $category,
            'source' => $source,
        ];
    }
}

ksort($quickstartGoogleFonts);
ksort($quickstartSystemFonts);

$pickPreferredFont = static function (array $preferredFamilies, array $fontMap, string $fallback): string {
    foreach ($preferredFamilies as $family) {
        if (isset($fontMap[$family])) {
            return $family;
        }
    }

    return $fallback;
};

$firstAvailableFont = '';
if ([] !== $quickstartGoogleFonts) {
    $firstAvailableFont = (string) array_key_first($quickstartGoogleFonts);
} elseif ([] !== $quickstartSystemFonts) {
    $firstAvailableFont = (string) array_key_first($quickstartSystemFonts);
}

if ('' === $firstAvailableFont) {
    $firstAvailableFont = 'Arial';
    $quickstartFontMap[$firstAvailableFont] = [
        'category' => 'sans-serif',
        'source' => 'system',
    ];
    $quickstartSystemFonts[$firstAvailableFont] = [
        'family' => $firstAvailableFont,
        'category' => 'sans-serif',
        'source' => 'system',
    ];
}

$defaultBodyFont = $pickPreferredFont(['Roboto', 'Open Sans', 'Lato', 'Arial', 'Helvetica'], $quickstartFontMap, $firstAvailableFont);
$defaultHeadingFont = $pickPreferredFont(['Playfair Display', 'Merriweather', 'Lora', 'PT Serif', 'Georgia', 'Times New Roman'], $quickstartFontMap, $defaultBodyFont);

$fontPresetValues = [
    'modern' => [
        'body' => $defaultBodyFont,
        'heading' => $defaultHeadingFont,
    ],
    'editorial' => [
        'body' => $pickPreferredFont(['Source Sans Pro', 'Lato', 'Open Sans', 'Arial'], $quickstartFontMap, $defaultBodyFont),
        'heading' => $pickPreferredFont(['Merriweather', 'Lora', 'PT Serif', 'Georgia'], $quickstartFontMap, $defaultHeadingFont),
    ],
    'friendly' => [
        'body' => $pickPreferredFont(['Nunito', 'Poppins', 'Verdana', 'Trebuchet MS'], $quickstartFontMap, $defaultBodyFont),
        'heading' => $pickPreferredFont(['Montserrat', 'Poppins', 'Arial Black', $defaultHeadingFont], $quickstartFontMap, $defaultHeadingFont),
    ],
];

$renderFontOptions = static function (array $googleFonts, array $systemFonts, string $selected): string {
    $html = '';

    if ([] !== $googleFonts) {
        $html .= '<optgroup label="Heruntergeladene Google Fonts">';
        foreach ($googleFonts as $font) {
            $family = (string) $font['family'];
            $label = $family . ' (Google)';
            $isSelected = $family === $selected ? ' selected' : '';
            $html .= '<option value="' . rex_escape($family) . '"' . $isSelected . '>' . rex_escape($label) . '</option>';
        }
        $html .= '</optgroup>';
    }

    if ([] !== $systemFonts) {
        $html .= '<optgroup label="Systemschriften">';
        foreach ($systemFonts as $font) {
            $family = (string) $font['family'];
            $label = $family . ' (System)';
            $isSelected = $family === $selected ? ' selected' : '';
            $html .= '<option value="' . rex_escape($family) . '"' . $isSelected . '>' . rex_escape($label) . '</option>';
        }
        $html .= '</optgroup>';
    }

    return $html;
};

$quickstartBodyFontOptionsHtml = $renderFontOptions($quickstartGoogleFonts, $quickstartSystemFonts, $defaultBodyFont);
$quickstartHeadingFontOptionsHtml = $renderFontOptions($quickstartGoogleFonts, $quickstartSystemFonts, $defaultHeadingFont);

$quickstartCreatedTheme = rex_request('quickstart_created', 'string', '');
if ('' !== $quickstartCreatedTheme) {
    echo rex_view::success('Quickstart erfolgreich: Theme "' . rex_escape($quickstartCreatedTheme) . '" wurde erstellt und kompiliert.');
}

// Theme Quickstart (Einsteiger-Flow)
if ($func === 'quickstart' && rex_request::post('quickstart_create', 'bool') && rex_csrf_token::factory('uikit-theme-quickstart')->isValid()) {
    $themeKeyInput = rex_request::post('qs_theme_key', 'string', '');
    $themeTitle = trim(rex_request::post('qs_theme_title', 'string', ''));
    $palettePresetKey = rex_request::post('qs_palette_preset', 'string', 'ocean');
    $fontPresetKey = rex_request::post('qs_font_preset', 'string', 'modern');
    $primaryColorInput = trim(rex_request::post('qs_color_primary', 'string', '#1e87f0'));
    $secondaryColorInput = trim(rex_request::post('qs_color_secondary', 'string', '#324050'));
    $accentColorInput = trim(rex_request::post('qs_color_accent', 'string', '#1e87f0'));
    $bodyFontInput = trim(rex_request::post('qs_font_body', 'string', $defaultBodyFont));
    $headingFontInput = trim(rex_request::post('qs_font_heading', 'string', $defaultHeadingFont));
    $openMode = rex_request::post('qs_open_mode', 'string', 'details');

    $sanitizeHexColor = static function (string $color, string $fallback): string {
        $color = trim($color);
        if (1 === preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            return strtolower($color);
        }

        return $fallback;
    };

    $sanitizeFontFamily = static function (string $font, string $fallback, array $availableFontMap): string {
        $font = trim($font);
        $font = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $font);
        $font = preg_replace('/\s+/', ' ', (string) $font);
        $font = trim((string) $font);

        if ('' === $font) {
            return $fallback;
        }

        if (isset($availableFontMap[$font])) {
            return $font;
        }

        return $fallback;
    };

    $palettePresets = [
        'ocean' => [
            'primary' => '#1e87f0',
            'secondary' => '#324050',
            'accent' => '#1e87f0',
        ],
        'forest' => [
            'primary' => '#2f8f66',
            'secondary' => '#2f4a3f',
            'accent' => '#2f8f66',
        ],
        'sunset' => [
            'primary' => '#d86a36',
            'secondary' => '#4b3a34',
            'accent' => '#d86a36',
        ],
    ];

    $fontPresets = $fontPresetValues;

    $selectedPalettePreset = $palettePresets[$palettePresetKey] ?? $palettePresets['ocean'];
    $selectedFontPreset = $fontPresets[$fontPresetKey] ?? $fontPresets['modern'];

    $primaryColor = $sanitizeHexColor($primaryColorInput, $selectedPalettePreset['primary']);
    $secondaryColor = $sanitizeHexColor($secondaryColorInput, $selectedPalettePreset['secondary']);
    $accentColor = $sanitizeHexColor($accentColorInput, $selectedPalettePreset['accent']);
    $bodyFont = $sanitizeFontFamily($bodyFontInput, $selectedFontPreset['body'], $quickstartFontMap);
    $headingFont = $sanitizeFontFamily($headingFontInput, $selectedFontPreset['heading'], $quickstartFontMap);

    $themeKey = strtolower(trim($themeKeyInput));
    $themeKey = preg_replace('/[^a-z0-9_]/', '_', $themeKey);
    $themeKey = preg_replace('/_+/', '_', (string) $themeKey);
    $themeKey = trim((string) $themeKey, '_');

    if ('' === $themeKey) {
        echo rex_view::error('Bitte gib einen gueltigen Theme-Key an (nur Kleinbuchstaben, Zahlen, Unterstriche).');
    } elseif ('new_theme' === $themeKey) {
        echo rex_view::error('Der Theme-Key "new_theme" ist reserviert. Bitte waehle einen anderen Namen.');
    } elseif (null !== $themeManager->loadTheme($themeKey)) {
        echo rex_view::error('Ein Theme mit dem Key "' . rex_escape($themeKey) . '" existiert bereits.');
    } else {
        $widgets = [
            'colors' => new \UikitThemeBuilder\Widget\ColorsWidget(),
            'typography' => new \UikitThemeBuilder\Widget\TypographyWidget(),
            'form' => new \UikitThemeBuilder\Widget\FormWidget(),
            'card' => new \UikitThemeBuilder\Widget\CardWidget(),
            'search' => new \UikitThemeBuilder\Widget\SearchWidget(),
            'breakpoints' => new \UikitThemeBuilder\Widget\BreakpointsWidget(),
            'borders' => new \UikitThemeBuilder\Widget\BorderWidget(),
            'shadows' => new \UikitThemeBuilder\Widget\ShadowWidget(),
            'container' => new \UikitThemeBuilder\Widget\ContainerWidget(),
            'navbar' => new \UikitThemeBuilder\Widget\NavbarWidget(),
            'components' => new \UikitThemeBuilder\Widget\ComponentsWidget(),
            'custom_styles' => new \UikitThemeBuilder\Widget\CustomStylesWidget(),
            'style_sets' => new \UikitThemeBuilder\Widget\StyleSetSelectionWidget(),
        ];

        $themeData = [];
        foreach ($widgets as $widget) {
            $themeData[$widget->getKey()] = $widget->getDefaultValues();
        }

        $themeData['colors'] = array_merge($themeData['colors'], [
            'global-primary-background' => $primaryColor,
            'global-secondary-background' => $secondaryColor,
            'global-link-color' => $accentColor,
            'global-link-hover-color' => $accentColor,
        ]);

        $bodyCategory = (string) ($quickstartFontMap[$bodyFont]['category'] ?? 'sans-serif');
        $headingCategory = (string) ($quickstartFontMap[$headingFont]['category'] ?? 'sans-serif');

        $themeData['typography']['global-font-family'] = '"' . $bodyFont . '", ' . $bodyCategory;
        $themeData['typography']['base-heading-font-family'] = '"' . $headingFont . '", ' . $headingCategory;

        $selectedFonts = [];

        if (($quickstartFontMap[$bodyFont]['source'] ?? '') === 'google') {
            $selectedFonts[$bodyFont] = [
                'family' => $bodyFont,
                'category' => $bodyCategory,
            ];
        }

        if (($quickstartFontMap[$headingFont]['source'] ?? '') === 'google') {
            $selectedFonts[$headingFont] = [
                'family' => $headingFont,
                'category' => $headingCategory,
            ];
        }

        $themeData['google_fonts']['selected_fonts'] = array_values($selectedFonts);
        $themeData['google_fonts']['font_variables'] = [
            'global-font-family' => $bodyFont,
            'base-heading-font-family' => $headingFont,
        ];

        $themeData['_meta'] = [
            'title' => $themeTitle,
            'key' => $themeKey,
            'created_via' => 'quickstart',
            'quickstart_palette_preset' => $palettePresetKey,
            'quickstart_font_preset' => $fontPresetKey,
        ];

        try {
            $themeManager->saveTheme($themeKey, $themeData);
            $themeManager->compileTheme($themeKey, $themeData);

            if ($openMode === 'preview') {
                rex_response::sendRedirect(rex_url::currentBackendPage([
                    'page' => 'uikit_theme_builder/themes',
                    'quickstart_created' => $themeKey,
                    'preview_theme' => $themeKey,
                ]));
            }

            rex_response::sendRedirect(rex_url::currentBackendPage([
                'page' => 'uikit_theme_builder/editor',
                'theme' => $themeKey,
                'quickstart_created' => '1',
            ]));
        } catch (Exception $e) {
            echo rex_view::error('Quickstart konnte nicht abgeschlossen werden: ' . rex_escape($e->getMessage()));
        }
    }
}

// Theme löschen
if ($func === 'delete') {
    $themeName = rex_request('theme', 'string');
    if ($themeName) {
        $result = $themeManager->deleteTheme($themeName);
        if ($result) {
            echo rex_view::success('Theme "' . rex_escape($themeName) . '" wurde gelöscht.');
        } else {
            echo rex_view::error('Theme "' . rex_escape($themeName) . '" konnte nicht gelöscht werden. Prüfe die Dateiberechtigungen.');
        }
    } else {
        echo rex_view::error('Kein Theme-Name angegeben.');
    }
}

// Theme importieren
if ($func === 'import' && isset($_FILES['theme_file'])) {
    try {
        $importer = new UikitThemeBuilder\ThemeImporter();
        $result = $importer->importThemeFromUpload($_FILES['theme_file'], [
            'download_fonts' => rex_request('download_fonts', 'bool', true),
            'import_style_sets' => rex_request('import_style_sets', 'bool', true),
            'overwrite_style_sets' => rex_request('overwrite_style_sets', 'bool', false),
            'auto_rename' => true,
        ]);
        
        if ($result['success']) {
            $message = 'Theme "' . rex_escape($result['theme_name']) . '" wurde erfolgreich importiert.';
            if (!empty($result['warnings'])) {
                $message .= '<br><strong>Warnungen:</strong><ul>';
                foreach ($result['warnings'] as $warning) {
                    $message .= '<li>' . rex_escape($warning) . '</li>';
                }
                $message .= '</ul>';
            }
            echo rex_view::success($message);
        } else {
            $message = 'Fehler beim Importieren des Themes.';
            if (!empty($result['errors'])) {
                $message .= '<ul>';
                foreach ($result['errors'] as $error) {
                    $message .= '<li>' . rex_escape($error) . '</li>';
                }
                $message .= '</ul>';
            }
            echo rex_view::error($message);
        }
    } catch (Exception $e) {
        echo rex_view::error('Import-Fehler: ' . rex_escape($e->getMessage()));
    }
}

// Theme kompilieren
if ($func === 'compile') {
    $themeName = rex_request('theme', 'string');
    if ($themeName) {
        $themeData = $themeManager->loadTheme($themeName);
        if ($themeData) {
            try {
                $result = $themeManager->compileTheme($themeName, $themeData['data']);
                if ($result['success']) {
                    echo rex_view::success('Theme "' . rex_escape($themeName) . '" wurde kompiliert. (' . round($result['compilation_info']['compilation_time'], 3) . 's)');
                    
                    // Debug-Ausgabe anzeigen wenn verfügbar
                    if (!empty($result['debug_output'])) {
                        echo $result['debug_output'];
                    }
                } else {
                    echo rex_view::error('Fehler beim Kompilieren: ' . $result['message']);
                }
            } catch (Exception $e) {
                echo rex_view::error('Fehler beim Kompilieren: ' . rex_escape($e->getMessage()));
            }
        }
    }
}

// Themes laden
$themes = $themeManager->listThemes();

$content = '';

$content .= '<div class="uk-container uk-container-large uk-margin-top">';

// Header mit Hero-Bereich (vereinfacht)
$content .= '<div class="uk-card uk-card-default uk-card-body uk-margin-bottom" style="background: linear-gradient(135deg, #324050 0%, #1e2832 100%); color: white;">';
$content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
$content .= '<div class="uk-width-auto">';
$content .= '<span uk-icon="icon: paint-bucket; ratio: 2.5" style="color: white;"></span>';
$content .= '</div>';
$content .= '<div class="uk-width-expand">';
$content .= '<h1 class="uk-card-title uk-margin-remove" style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">UIKit Theme Builder</h1>';
$content .= '<p class="uk-text-large uk-margin-remove-top" style="color: rgba(255,255,255,0.95); text-shadow: 0 1px 3px rgba(0,0,0,0.3);">Erstelle und verwalte deine individuellen UIKit Themes</p>';
$content .= '</div>';
$content .= '<div class="uk-width-auto">';
$content .= '<div class="uk-button-group">';
    $content .= '<button type="button" class="uk-button uk-button-primary uk-button-large" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; backdrop-filter: blur(10px);" onclick="UIkit.modal(\'#quickstart-modal\').show()">';
$content .= '<span uk-icon="icon: plus; ratio: 0.9" class="uk-margin-small-right"></span>Neues Theme';
    $content .= '</button>';
$content .= '<button class="uk-button uk-button-primary uk-button-large" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; backdrop-filter: blur(10px);" onclick="UIkit.modal(\'#import-modal\').show()">';
$content .= '<span uk-icon="icon: cloud-upload; ratio: 0.9" class="uk-margin-small-right"></span>Theme importieren';
$content .= '</button>';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';

if (empty($themes)) {
    // Empty State
    $content .= '<div class="uk-card uk-card-default uk-card-large uk-text-center">';
    $content .= '<div class="uk-card-body">';
    $content .= '<span uk-icon="icon: image; ratio: 4" class="uk-text-muted uk-margin-bottom"></span>';
    $content .= '<h3 class="uk-text-muted">Noch keine Themes vorhanden</h3>';
    $content .= '<p class="uk-text-large uk-text-muted">Erstelle dein erstes UIKit Theme um loszulegen.</p>';
    $content .= '<button type="button" class="uk-button uk-button-primary uk-button-large uk-margin-top" onclick="UIkit.modal(\'#quickstart-modal\').show()">';
    $content .= '<span uk-icon="icon: plus; ratio: 0.9" class="uk-margin-small-right"></span>Erstes Theme erstellen';
    $content .= '</button>';
    $content .= '</div>';
    $content .= '</div>';
} else {
    // Themes Grid
    $content .= '<div class="uk-grid-match uk-child-width-1-1@s uk-child-width-1-2@m uk-child-width-1-3@l" uk-grid>';
    
    foreach ($themes as $themeName => $themeInfo) {
        // Theme-Farben laden
        $themeColors = UikitThemeBuilder\ThemeHelper::getThemeColors($themeManager, $themeName);
        
        // Theme-Titel extrahieren (falls vorhanden)
        $themeTitle = $themeInfo['data']['_meta']['title'] ?? '';
        $displayName = !empty($themeTitle) ? $themeTitle : $themeName;
        
        $content .= '<div>';
        $content .= '<div class="uk-card uk-card-default uk-card-hover uk-height-1-1">';
        
        // Hauptfarbe des Themes laden
        $primaryColor = $themeColors['primary'] ?? '#1e87f0';
        
        // Card Header mit Werkzeugleiste (Theme-Hauptfarbe als Hintergrund)
        $content .= '<div class="uk-card-header uk-padding-small" style="background: ' . rex_escape($primaryColor) . '; border-bottom: 1px solid rgba(0,0,0,0.1);">';
        $content .= '<div class="uk-flex uk-flex-between uk-flex-middle">';
        
        // Icon links
        $content .= '<div>';
        $content .= '<span uk-icon="icon: palette; ratio: 1.2" style="color: rgba(255,255,255,0.9);"></span>';
        $content .= '</div>';
        
        // Quick-Actions und Menü rechts
        $content .= '<div>';
        $content .= '<div class="uk-button-group">';
        $content .= '<a href="' . rex_url::currentBackendPage(['page' => 'uikit_theme_builder/editor', 'theme' => $themeName]) . '" class="uk-button uk-button-default uk-button-small" uk-tooltip="title: Bearbeiten" style="background: rgba(255,255,255,0.9); border: none;">';
        $content .= '<span uk-icon="icon: pencil; ratio: 0.8"></span>';
        $content .= '</a>';
        $content .= '<button class="uk-button uk-button-default uk-button-small" type="button" uk-tooltip="title: Vorschau" onclick="openPreviewModal(' . htmlspecialchars(json_encode($themeName), ENT_QUOTES) . ')" style="background: rgba(255,255,255,0.9); border: none;">';
        $content .= '<span uk-icon="icon: desktop; ratio: 0.8"></span>';
        $content .= '</button>';
        $content .= '<a href="' . rex_url::currentBackendPage(['func' => 'compile', 'theme' => $themeName]) . '" class="uk-button uk-button-default uk-button-small" uk-tooltip="title: Kompilieren" style="background: rgba(255,255,255,0.9); border: none;">';
        $content .= '<span uk-icon="icon: refresh; ratio: 0.8"></span>';
        $content .= '</a>';
        $content .= '<div>';
        $content .= '<button class="uk-button uk-button-default uk-button-small" type="button" uk-tooltip="title: Weitere Aktionen" style="background: rgba(255,255,255,0.9); border: none;">';
        $content .= '<span uk-icon="icon: more-vertical; ratio: 0.8"></span>';
        $content .= '</button>';
        $content .= '<div uk-dropdown="mode: click; pos: bottom-right">';
        $content .= '<ul class="uk-nav uk-dropdown-nav">';
        
        // Duplizieren
        $content .= '<li><a href="#" onclick="copyTheme(' . htmlspecialchars(json_encode($themeName), ENT_QUOTES) . '); return false;">';
        $content .= '<span uk-icon="icon: copy" class="uk-margin-small-right"></span>Duplizieren</a></li>';
        
        // Exportieren
        $content .= '<li><a href="' . rex_url::backendController(['rex-api-call' => 'uikit_theme_export', 'theme' => $themeName]) . '">';
        $content .= '<span uk-icon="icon: cloud-download" class="uk-margin-small-right"></span>Exportieren</a></li>';
        
        $content .= '<li class="uk-nav-divider"></li>';
        
        // Löschen
        $content .= '<li><a href="' . rex_url::currentBackendPage(['func' => 'delete', 'theme' => $themeName]) . '" class="uk-text-danger" onclick="return confirm(' . htmlspecialchars(json_encode('Theme "' . $themeName . '" wirklich löschen?'), ENT_QUOTES) . ')">';
        $content .= '<span uk-icon="icon: trash" class="uk-margin-small-right"></span>Löschen</a></li>';
        
        $content .= '</ul>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        
        $content .= '</div>';
        $content .= '</div>';
        
        // Theme-Name/Titel als separater Titel-Bereich
        $content .= '<div class="uk-card-body uk-padding-small" style="border-bottom: 1px solid #e5e5e5;">';
        if (!empty($themeTitle)) {
            // Wenn Titel vorhanden: Titel groß, Key klein drunter
            $content .= '<h3 class="uk-margin-remove uk-text-truncate" title="' . rex_escape($displayName) . '">' . rex_escape($displayName) . '</h3>';
            $content .= '<p class="uk-text-meta uk-margin-remove-top uk-margin-small-top"><code>' . rex_escape($themeName) . '</code></p>';
        } else {
            // Wenn kein Titel: nur Key anzeigen
            $content .= '<h3 class="uk-margin-remove uk-text-truncate" title="' . rex_escape($themeName) . '">' . rex_escape($themeName) . '</h3>';
        }
        $content .= '</div>';
        
        // Card Body mit Farbvorschau und Meta-Info
        $content .= '<div class="uk-card-body">';
        
        // Farbpalette
        $content .= '<div class="uk-margin-bottom">';
        $content .= '<h4 class="uk-text-small uk-text-muted uk-margin-small-bottom">Farbpalette</h4>';
        $content .= '<div class="uk-grid-small uk-flex-center" uk-grid>';
        
        foreach ($themeColors as $colorName => $colorValue) {
            $content .= '<div class="uk-width-auto">';
            $content .= '<div class="uk-border-rounded" style="width: 28px; height: 28px; background-color: ' . rex_escape($colorValue) . '; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.2s ease; cursor: pointer;" uk-tooltip="title: ' . ucfirst($colorName) . ': ' . rex_escape($colorValue) . '" onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'scale(1)\'"></div>';
            $content .= '</div>';
        }
        
        $content .= '</div>';
        $content .= '</div>';
        
        $content .= '<div class="uk-grid-small uk-child-width-1-2" uk-grid>';
        $content .= '<div>';
        $content .= '<dt class="uk-text-small uk-text-muted">Erstellt</dt>';
        $content .= '<dd class="uk-text-meta uk-margin-remove">' . rex_escape($themeInfo['created']) . '</dd>';
        $content .= '</div>';
        $content .= '<div>';
        $content .= '<dt class="uk-text-small uk-text-muted">Bearbeitet</dt>';
        $content .= '<dd class="uk-text-meta uk-margin-remove">' . date('d.m.Y H:i', $themeInfo['modified']) . '</dd>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        
        $content .= '</div>'; // card
        $content .= '</div>'; // grid item
    }
    
    $content .= '</div>'; // grid
}

$content .= '</div>'; // container

// JavaScript Config für Copy-Funktion
$content .= '<script>';
$content .= 'const BACKEND_URL = "' . rex_url::backendController() . '";';
$content .= '</script>';

// Quickstart Modal
$content .= '
<div id="quickstart-modal" uk-modal>
    <div class="uk-modal-dialog uk-modal-body">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <h2 class="uk-modal-title">Theme Schnellstart</h2>
        <p class="uk-text-meta">In 1 Minute zu einem startklaren Theme. Du kannst danach jederzeit alles im Detail anpassen.</p>

        <form method="post" action="' . rex_url::currentBackendPage(['func' => 'quickstart']) . '">
            ' . rex_csrf_token::factory('uikit-theme-quickstart')->getHiddenField() . '
            <input type="hidden" name="quickstart_create" value="1">

            <div class="uk-margin">
                <label class="uk-form-label" for="qs_theme_key">Theme-Key *</label>
                <input class="uk-input" id="qs_theme_key" name="qs_theme_key" type="text" required pattern="[a-z0-9_]+" placeholder="z.B. kunden_theme_2026">
                <div class="uk-text-meta">Nur Kleinbuchstaben, Zahlen und Unterstriche.</div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="qs_theme_title">Titel (optional)</label>
                <input class="uk-input" id="qs_theme_title" name="qs_theme_title" type="text" placeholder="z.B. Firmenauftritt Sommer 2026">
            </div>

            <div class="uk-margin">
                <label class="uk-form-label">Schnellwahl Preset (optional)</label>
                <div class="uk-grid-small uk-child-width-1-1 uk-child-width-1-3@s" uk-grid>
                    <div>
                        <label class="qs-preset-card" data-qs-group="palette-preset">
                            <input class="uk-hidden" type="radio" name="qs_palette_preset" value="ocean" checked>
                            <div class="qs-preset-title">Ocean Blue</div>
                            <div class="qs-preset-meta">neutral, modern</div>
                            <div class="qs-preset-swatches">
                                <span style="background:#1e87f0"></span>
                                <span style="background:#324050"></span>
                                <span style="background:#1e87f0"></span>
                            </div>
                        </label>
                    </div>
                    <div>
                        <label class="qs-preset-card" data-qs-group="palette-preset">
                            <input class="uk-hidden" type="radio" name="qs_palette_preset" value="forest">
                            <div class="qs-preset-title">Forest Green</div>
                            <div class="qs-preset-meta">ruhig, natuerlich</div>
                            <div class="qs-preset-swatches">
                                <span style="background:#2f8f66"></span>
                                <span style="background:#2f4a3f"></span>
                                <span style="background:#2f8f66"></span>
                            </div>
                        </label>
                    </div>
                    <div>
                        <label class="qs-preset-card" data-qs-group="palette-preset">
                            <input class="uk-hidden" type="radio" name="qs_palette_preset" value="sunset">
                            <div class="qs-preset-title">Sunset Orange</div>
                            <div class="qs-preset-meta">warm, aufmerksamkeitsstark</div>
                            <div class="qs-preset-swatches">
                                <span style="background:#d86a36"></span>
                                <span style="background:#4b3a34"></span>
                                <span style="background:#d86a36"></span>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="uk-grid-small uk-child-width-1-1 uk-child-width-1-3@s uk-margin-small-top" uk-grid>
                    <div>
                        <label class="qs-preset-card" data-qs-group="font-preset">
                            <input class="uk-hidden" type="radio" name="qs_font_preset" value="modern" checked>
                            <div class="qs-preset-title">Modern Pair</div>
                            <div class="qs-preset-meta">' . rex_escape($fontPresetValues['modern']['body']) . ' + ' . rex_escape($fontPresetValues['modern']['heading']) . '</div>
                        </label>
                    </div>
                    <div>
                        <label class="qs-preset-card" data-qs-group="font-preset">
                            <input class="uk-hidden" type="radio" name="qs_font_preset" value="editorial">
                            <div class="qs-preset-title">Editorial Pair</div>
                            <div class="qs-preset-meta">' . rex_escape($fontPresetValues['editorial']['body']) . ' + ' . rex_escape($fontPresetValues['editorial']['heading']) . '</div>
                        </label>
                    </div>
                    <div>
                        <label class="qs-preset-card" data-qs-group="font-preset">
                            <input class="uk-hidden" type="radio" name="qs_font_preset" value="friendly">
                            <div class="qs-preset-title">Friendly Pair</div>
                            <div class="qs-preset-meta">' . rex_escape($fontPresetValues['friendly']['body']) . ' + ' . rex_escape($fontPresetValues['friendly']['heading']) . '</div>
                        </label>
                    </div>
                </div>
                <div class="uk-text-meta">Tipp: Preset anklicken und darunter bei Bedarf manuell anpassen.</div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label">Grundfarben</label>
                <div class="uk-grid-small uk-child-width-1-1 uk-child-width-1-3@s" uk-grid>
                    <div>
                        <label class="uk-form-label" for="qs_color_primary_text">Primaerfarbe</label>
                        <div class="qs-color-row">
                            <div class="pickr-el qs-pickr" id="qs-color-primary-pickr"></div>
                            <input class="uk-input qs-color-input" id="qs_color_primary_text" type="text" value="#1e87f0" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
                            <input type="hidden" name="qs_color_primary" id="qs_color_primary" value="#1e87f0">
                        </div>
                        <div class="uk-text-meta">Hauptfarbe fuer Buttons und Highlights.</div>
                    </div>
                    <div>
                        <label class="uk-form-label" for="qs_color_secondary_text">Sekundaerfarbe</label>
                        <div class="qs-color-row">
                            <div class="pickr-el qs-pickr" id="qs-color-secondary-pickr"></div>
                            <input class="uk-input qs-color-input" id="qs_color_secondary_text" type="text" value="#324050" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
                            <input type="hidden" name="qs_color_secondary" id="qs_color_secondary" value="#324050">
                        </div>
                        <div class="uk-text-meta">Fuer Flaechen, Header und Kontraste.</div>
                    </div>
                    <div>
                        <label class="uk-form-label" for="qs_color_accent_text">Akzentfarbe</label>
                        <div class="qs-color-row">
                            <div class="pickr-el qs-pickr" id="qs-color-accent-pickr"></div>
                            <input class="uk-input qs-color-input" id="qs_color_accent_text" type="text" value="#1e87f0" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
                            <input type="hidden" name="qs_color_accent" id="qs_color_accent" value="#1e87f0">
                        </div>
                        <div class="uk-text-meta">Fuer Links und besondere Hinweise.</div>
                    </div>
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label">Google Fonts</label>
                <div class="uk-grid-small uk-child-width-1-1 uk-child-width-1-2@s" uk-grid>
                    <div>
                        <label class="uk-form-label" for="qs_font_body">Allgemeine Schrift</label>
                        <select class="uk-select" id="qs_font_body" name="qs_font_body">
                            ' . $quickstartBodyFontOptionsHtml . '
                        </select>
                    </div>
                    <div>
                        <label class="uk-form-label" for="qs_font_heading">Ueberschriften</label>
                        <select class="uk-select" id="qs_font_heading" name="qs_font_heading">
                            ' . $quickstartHeadingFontOptionsHtml . '
                        </select>
                    </div>
                </div>
                <div class="uk-text-meta">Quelle: heruntergeladene Google Fonts + Systemschriften. Nur echte Google Fonts werden als Import hinterlegt.</div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="qs_open_mode">Nach dem Erstellen</label>
                <select class="uk-select" id="qs_open_mode" name="qs_open_mode">
                    <option value="details" selected>In die Details springen (Editor)</option>
                    <option value="preview">Direkt Vorschau oeffnen</option>
                </select>
            </div>

            <div class="uk-grid-small uk-child-width-1-2" uk-grid>
                <div>
                    <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                        <span uk-icon="icon: bolt"></span> Schnellstart erstellen
                    </button>
                </div>
                <div>
                    <a href="' . rex_url::currentBackendPage(['page' => 'uikit_theme_builder/editor']) . '" class="uk-button uk-button-default uk-width-1-1">
                        Direkt in Details
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
';

$content .= '
<style>
.qs-preset-card {
    display: block;
    border: 1px solid #d7dee8;
    border-radius: 8px;
    padding: 10px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
}

.qs-preset-card:hover {
    border-color: #6ea9df;
    box-shadow: 0 3px 10px rgba(33, 85, 130, 0.14);
}

.qs-preset-card.is-selected {
    border-color: #1e87f0;
    box-shadow: 0 0 0 2px rgba(30, 135, 240, 0.2);
    background: #f6fbff;
}

.qs-preset-title {
    font-weight: 600;
    line-height: 1.2;
    margin-bottom: 4px;
}

.qs-preset-meta {
    font-size: 12px;
    color: #7c8694;
    line-height: 1.3;
}

.qs-preset-swatches {
    display: flex;
    gap: 6px;
    margin-top: 10px;
}

.qs-preset-swatches span {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 1px solid rgba(0,0,0,0.12);
    display: inline-block;
}

.qs-color-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.qs-pickr {
    flex: 0 0 auto;
}

.qs-color-input {
    flex: 1 1 auto;
    min-width: 120px;
}

@media (max-width: 639px) {
    .qs-color-row {
        gap: 6px;
    }
}
</style>
';

// Import Modal
$content .= '
<div id="import-modal" uk-modal>
    <div class="uk-modal-dialog">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <div class="uk-modal-header">
            <h2 class="uk-modal-title">Theme importieren</h2>
        </div>
        <div class="uk-modal-body">
            <form method="post" enctype="multipart/form-data" action="' . rex_url::currentBackendPage(['func' => 'import']) . '">
                <div class="uk-margin">
                    <label class="uk-form-label" for="theme_file">Theme-Datei (JSON)</label>
                    <div uk-form-custom>
                        <input type="file" name="theme_file" id="theme_file" accept=".json" required>
                        <button class="uk-button uk-button-default uk-width-1-1" type="button" tabindex="-1">
                            <span uk-icon="icon: cloud-upload"></span> Datei auswählen
                        </button>
                    </div>
                </div>
                
                <div class="uk-margin">
                    <label><input class="uk-checkbox" type="checkbox" name="download_fonts" value="1" checked> Google Fonts automatisch herunterladen</label>
                </div>
                
                <div class="uk-margin">
                    <label><input class="uk-checkbox" type="checkbox" name="import_style_sets" value="1" checked> Extra Styles (StyleSets) importieren</label>
                </div>
                
                <div class="uk-margin">
                    <label><input class="uk-checkbox" type="checkbox" name="overwrite_style_sets" value="1"> Bestehende StyleSets überschreiben</label>
                </div>
                
                <div class="uk-margin">
                    <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                        <span uk-icon="icon: upload"></span> Importieren
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
';

// Preview Modal
$content .= '
<!-- Preview Modal -->
<div id="preview-modal" uk-modal="bg-close: true; esc-close: true" style="z-index: 99999 !important;">
    <div class="uk-modal-dialog uk-modal-body uk-padding-remove" style="width: 90vw; height: 90vh; max-width: none; max-height: none; margin: 5vh auto; z-index: 100000 !important;">
        
        <!-- Modal Header -->
        <div class="uk-modal-header" style="background: linear-gradient(135deg, #324050 0%, #1e2832 100%); color: white; position: relative; z-index: 100001 !important; padding: 20px 30px;">
            <div class="uk-grid-small uk-flex-middle" uk-grid>
                <div class="uk-width-auto">
                    <h2 class="uk-modal-title uk-margin-remove" style="color: white; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                        <span uk-icon="icon: tv; ratio: 1.2" class="uk-margin-small-right" style="color: white;"></span>
                        Theme Preview
                    </h2>
                </div>
                <div class="uk-width-expand">
                    <span id="preview-theme-name" class="uk-text-large" style="color: rgba(255,255,255,0.9);"></span>
                </div>
            </div>
            
            <!-- Responsive Controls in eigener Zeile -->
            <div class="uk-grid-small uk-flex-middle uk-flex-between uk-margin-top" uk-grid>
                <div class="uk-width-auto">
                    <div class="uk-button-group">
                        <button class="uk-button uk-button-small" onclick="setPreviewSize(\'desktop\')" id="btn-desktop" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
                            <span uk-icon="icon: desktop"></span> Desktop
                        </button>
                        <button class="uk-button uk-button-small" onclick="setPreviewSize(\'tablet\')" id="btn-tablet" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
                            <span uk-icon="icon: tablet"></span> Tablet  
                        </button>
                        <button class="uk-button uk-button-small" onclick="setPreviewSize(\'mobile\')" id="btn-mobile" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
                            <span uk-icon="icon: phone"></span> Mobile
                        </button>
                    </div>
                </div>
                <div class="uk-width-auto">
                    <button class="uk-modal-close-default uk-button uk-button-small" type="button" style="background: rgba(255,255,255,0.9); color: #333; border: none;">
                        <span uk-icon="icon: close"></span> Schließen
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Modal Body -->
        <div class="uk-modal-body uk-padding-remove uk-background-muted" style="height: calc(90vh - 80px); overflow: hidden; position: relative; z-index: 100001 !important;">
            <div id="preview-container" class="uk-width-1-1 uk-height-1-1 uk-flex uk-flex-center uk-flex-middle" style="transition: all 0.3s ease;">
                <div id="preview-frame-container" style="background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.15); border-radius: 8px; overflow: hidden;">
                    <iframe id="preview-iframe" 
                            src="" 
                            style="border: none; background: white; display: block;">
                    </iframe>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- Modal Overlay Style -->
<style>
#preview-modal .uk-modal-dialog {
    z-index: 100000 !important;
}

#preview-modal {
    z-index: 99999 !important;
}

.uk-modal-page {
    z-index: 99998 !important;
}

/* REDAXO Backend Override */
body.rex-backend #preview-modal {
    z-index: 99999 !important;
}

body.rex-backend #preview-modal .uk-modal-dialog {
    z-index: 100000 !important;  
}
</style>

<script>
let currentTheme = "";
const QUICKSTART_PREVIEW_THEME = ' . json_encode($previewThemeFromRequest) . ';
const QUICKSTART_FONT_PRESETS = ' . json_encode($fontPresetValues) . ';
const QUICKSTART_PICKR_INSTANCES = {};

// Theme kopieren
function copyTheme(themeName) {
    if (!confirm("Theme \\"" + themeName + "\\" wirklich duplizieren?")) {
        return;
    }
    
    UIkit.notification("Theme wird kopiert...", {status: "primary"});
    
    fetch(BACKEND_URL + "?rex-api-call=uikit_theme_copy&theme=" + encodeURIComponent(themeName))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                UIkit.notification("Theme \\"" + data.theme_name + "\\" wurde erstellt", {status: "success"});
                setTimeout(() => location.reload(), 1000);
            } else {
                UIkit.notification("Fehler: " + data.error, {status: "danger"});
            }
        })
        .catch(error => {
            UIkit.notification("Fehler beim Kopieren", {status: "danger"});
            console.error(error);
        });
}

function openPreviewModal(themeName) {
    currentTheme = themeName;
    
    // Modal-Titel setzen
    document.getElementById("preview-theme-name").textContent = themeName;
    
    // iframe URL setzen - einfache URL mit Output Buffer
    const previewUrl = "/?previewtheme=" + encodeURIComponent(themeName);
    document.getElementById("preview-iframe").src = previewUrl;
    
    // Modal öffnen
    UIkit.modal("#preview-modal").show();
    
    // Desktop als Standard setzen
    setTimeout(() => setPreviewSize("desktop"), 500);
}

function normalizeQuickstartHex(value, fallbackValue) {
    const trimmed = (value || "").trim();
    if (/^#[0-9a-fA-F]{6}$/.test(trimmed)) {
        return trimmed.toLowerCase();
    }

    return fallbackValue;
}

function initQuickstartPresetCards() {
    const cards = document.querySelectorAll(".qs-preset-card");
    if (!cards.length) {
        return;
    }

    const refreshGroup = (groupName) => {
        const groupCards = document.querySelectorAll(".qs-preset-card[data-qs-group=\"" + groupName + "\"]");
        groupCards.forEach((card) => {
            const input = card.querySelector("input[type=\"radio\"]");
            if (input && input.checked) {
                card.classList.add("is-selected");
            } else {
                card.classList.remove("is-selected");
            }
        });
    };

    cards.forEach((card) => {
        const input = card.querySelector("input[type=\"radio\"]");
        if (!input) {
            return;
        }

        card.addEventListener("click", () => {
            input.checked = true;
            const group = card.getAttribute("data-qs-group");
            if (group) {
                refreshGroup(group);
            }
            input.dispatchEvent(new Event("change"));
        });
    });

    refreshGroup("palette-preset");
    refreshGroup("font-preset");
}

function syncQuickstartPickrColor(hiddenId, colorValue) {
    const pickr = QUICKSTART_PICKR_INSTANCES[hiddenId];
    if (!pickr || typeof pickr.setColor !== "function") {
        return;
    }

    try {
        pickr.setColor(colorValue);
        if (typeof pickr.applyColor === "function") {
            pickr.applyColor(true);
        }
    } catch (error) {
        console.warn("Quickstart Pickr konnte nicht synchronisiert werden:", error);
    }
}

function setQuickstartColorFields(hiddenId, textId, value) {
    const hiddenInput = document.getElementById(hiddenId);
    const textInput = document.getElementById(textId);
    if (!hiddenInput || !textInput) {
        return;
    }

    const normalized = normalizeQuickstartHex(value, hiddenInput.value || "#1e87f0");
    hiddenInput.value = normalized;
    textInput.value = normalized;
    syncQuickstartPickrColor(hiddenId, normalized);
}

function applyQuickstartPalettePreset(presetKey) {
    const presets = {
        ocean: { primary: "#1e87f0", secondary: "#324050", accent: "#1e87f0" },
        forest: { primary: "#2f8f66", secondary: "#2f4a3f", accent: "#2f8f66" },
        sunset: { primary: "#d86a36", secondary: "#4b3a34", accent: "#d86a36" }
    };

    const preset = presets[presetKey];
    if (!preset) {
        return;
    }

    setQuickstartColorFields("qs_color_primary", "qs_color_primary_text", preset.primary);
    setQuickstartColorFields("qs_color_secondary", "qs_color_secondary_text", preset.secondary);
    setQuickstartColorFields("qs_color_accent", "qs_color_accent_text", preset.accent);
}

function applyQuickstartFontPreset(presetKey) {
    const preset = QUICKSTART_FONT_PRESETS[presetKey];
    if (!preset) {
        return;
    }

    const bodySelect = document.getElementById("qs_font_body");
    const headingSelect = document.getElementById("qs_font_heading");
    if (bodySelect) {
        bodySelect.value = preset.body;
    }
    if (headingSelect) {
        headingSelect.value = preset.heading;
    }
}

function initQuickstartPickr(pickrId, hiddenId, textId, fallbackColor) {
    const pickrElement = document.getElementById(pickrId);
    const hiddenInput = document.getElementById(hiddenId);
    const textInput = document.getElementById(textId);

    if (!pickrElement || !hiddenInput || !textInput) {
        return;
    }

    const applyColor = (value) => {
        const normalized = normalizeQuickstartHex(value, fallbackColor);
        hiddenInput.value = normalized;
        textInput.value = normalized;
    };

    applyColor(hiddenInput.value || fallbackColor);

    textInput.addEventListener("change", () => {
        applyColor(textInput.value);
    });

    if (typeof Pickr === "undefined") {
        return;
    }

    const pickr = Pickr.create({
        el: pickrElement,
        theme: "nano",
        default: hiddenInput.value || fallbackColor,
        swatches: [
            "#1e87f0", "#324050", "#d86a36", "#2f8f66", "#f0506e",
            "#222222", "#666666", "#999999", "#cccccc", "#ffffff"
        ],
        components: {
            preview: true,
            opacity: false,
            hue: true,
            interaction: {
                hex: true,
                rgba: false,
                hsla: false,
                input: true,
                clear: false,
                save: true
            }
        },
        strings: {
            save: "OK",
            cancel: "Abbrechen"
        }
    });

    const updateFromPickr = (color) => {
        if (!color) {
            return;
        }

        const value = color.toHEXA().toString().slice(0, 7).toLowerCase();
        applyColor(value);
    };

    pickr.on("change", updateFromPickr);
    pickr.on("save", (color) => {
        updateFromPickr(color);
        pickr.hide();
    });

    QUICKSTART_PICKR_INSTANCES[hiddenId] = pickr;
}

function setPreviewSize(size) {
    const container = document.getElementById("preview-frame-container");
    const iframe = document.getElementById("preview-iframe");
    const buttons = ["desktop", "tablet", "mobile"];
    
    // Button-States zurücksetzen
    buttons.forEach(btn => {
        const element = document.getElementById("btn-" + btn);
        element.style.background = "rgba(255,255,255,0.2)";
        element.style.border = "1px solid rgba(255,255,255,0.3)";
        element.style.color = "white";
    });
    
    // Aktiven Button setzen
    const activeBtn = document.getElementById("btn-" + size);
    activeBtn.style.background = "rgba(255,255,255,0.9)";
    activeBtn.style.border = "1px solid white";
    activeBtn.style.color = "#333";
    
    // Container und iframe-Größe anpassen
    switch(size) {
        case "desktop":
            container.style.width = "95%";
            container.style.height = "calc(90vh - 120px)";
            iframe.style.width = "100%";
            iframe.style.height = "100%";
            break;
        case "tablet":
            container.style.width = "768px";
            container.style.height = "1024px";
            iframe.style.width = "768px";
            iframe.style.height = "1024px";
            break;
        case "mobile":
            container.style.width = "375px";
            container.style.height = "812px";
            iframe.style.width = "375px";
            iframe.style.height = "812px";
            break;
    }
}

// Modal schließen Event
UIkit.util.on("#preview-modal", "hidden", function() {
    document.getElementById("preview-iframe").src = "";
});

// Modal nach Laden korrekt positionieren
UIkit.util.on("#preview-modal", "shown", function() {
    // Sicherstellen dass Modal im Vordergrund ist
    const modal = document.getElementById("preview-modal");
    modal.style.zIndex = "99999";
    const dialog = modal.querySelector(".uk-modal-dialog");
    if (dialog) {
        dialog.style.zIndex = "100000";
    }
});

document.addEventListener("DOMContentLoaded", function() {
    initQuickstartPresetCards();
    initQuickstartPickr("qs-color-primary-pickr", "qs_color_primary", "qs_color_primary_text", "#1e87f0");
    initQuickstartPickr("qs-color-secondary-pickr", "qs_color_secondary", "qs_color_secondary_text", "#324050");
    initQuickstartPickr("qs-color-accent-pickr", "qs_color_accent", "qs_color_accent_text", "#1e87f0");

    const palettePresetInputs = document.querySelectorAll("input[name=\"qs_palette_preset\"]");
    palettePresetInputs.forEach((input) => {
        input.addEventListener("change", () => {
            if (input.checked) {
                applyQuickstartPalettePreset(input.value);
            }
        });
    });

    const fontPresetInputs = document.querySelectorAll("input[name=\"qs_font_preset\"]");
    fontPresetInputs.forEach((input) => {
        input.addEventListener("change", () => {
            if (input.checked) {
                applyQuickstartFontPreset(input.value);
            }
        });
    });

    const initialPalettePreset = document.querySelector("input[name=\"qs_palette_preset\"]:checked");
    if (initialPalettePreset) {
        applyQuickstartPalettePreset(initialPalettePreset.value);
    }

    const initialFontPreset = document.querySelector("input[name=\"qs_font_preset\"]:checked");
    if (initialFontPreset) {
        applyQuickstartFontPreset(initialFontPreset.value);
    }

    if (QUICKSTART_PREVIEW_THEME) {
        openPreviewModal(QUICKSTART_PREVIEW_THEME);
    }
});
</script>
';

echo $content;

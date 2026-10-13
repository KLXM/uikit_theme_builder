<?php

/**
 * Custom Icons - Verwaltung von eigenen SVG-Icons
 */

use UikitThemeBuilder\CustomIconManager;
use UikitThemeBuilder\CustomIconBuilder;
use UikitThemeBuilder\IconImporter;

$iconManager = new CustomIconManager();
$iconBuilder = new CustomIconBuilder();

$content = '';
$message = '';

// Seiten-Stile (hell/dunkel, mobil) – direkt eingebunden, da der Seitenkopf bereits ausgegeben ist
$utbPageCss = rex_path::addonAssets('uikit_theme_builder', 'css/backend-pages.css');
echo '<link rel="stylesheet" href="' . rex_url::addonAssets('uikit_theme_builder', 'css/backend-pages.css') . (is_file($utbPageCss) ? '?v=' . filemtime($utbPageCss) : '') . '">';

// CSRF-Schutz für das Löschen von Icons (Link trägt den Token)
$iconActionToken = rex_csrf_token::factory('uikit-icon-actions');

// Action Handling
$action = rex_request('action', 'string');

// Import Icons
if ($action === 'import' && rex_post('submit_import', 'boolean')) {
    if (isset($_FILES['icon_import_file']) && $_FILES['icon_import_file']['error'] === UPLOAD_ERR_OK) {
        $importer = new IconImporter();
        
        // Prüfen ob ZIP oder SVG
        $fileType = $_FILES['icon_import_file']['type'];
        $fileName = $_FILES['icon_import_file']['name'];
        
        if (strpos($fileType, 'zip') !== false || pathinfo($fileName, PATHINFO_EXTENSION) === 'zip') {
            // ZIP Import
            $result = $importer->importFromZip($_FILES['icon_import_file']['tmp_name']);
        } else {
            // Einzelne SVG-Datei
            $result = $importer->importMultipleFiles([$_FILES['icon_import_file']]);
        }
        
        if ($result['success']) {
            // Icons neu kompilieren nach Import
            $iconBuilder->rebuild();
            
            $msg = "Import erfolgreich: {$result['imported_count']} Icons importiert";
            if (!empty($result['warnings'])) {
                $msg .= '<br><strong>Warnungen:</strong><ul>';
                foreach ($result['warnings'] as $warning) {
                    $msg .= '<li>' . rex_escape($warning) . '</li>';
                }
                $msg .= '</ul>';
            }
            $message = rex_view::success($msg);
        } else {
            $msg = 'Fehler beim Import';
            if (!empty($result['errors'])) {
                $msg .= ':<ul>';
                foreach ($result['errors'] as $error) {
                    $msg .= '<li>' . rex_escape($error) . '</li>';
                }
                $msg .= '</ul>';
            }
            $message = rex_view::error($msg);
        }
    } else {
        $message = rex_view::error('Bitte wähle eine Datei aus (ZIP oder SVG).');
    }
}

// Upload Icon via Textarea (SVG Code)
if ($action === 'upload_code' && rex_post('submit_code', 'boolean')) {
    $name = rex_post('icon_name_code', 'string');
    $category = rex_post('icon_category_code', 'string', 'custom');
    $tags = array_filter(explode(',', rex_post('icon_tags_code', 'string')));
    $svgCode = rex_post('svg_code', 'string');
    
    if (!empty($svgCode) && !empty($name)) {
        // Temporäre Datei erstellen
        $tmpFile = rex_path::addonCache('uikit_theme_builder', 'tmp_svg_' . uniqid() . '.svg');
        file_put_contents($tmpFile, $svgCode);
        
        // Als Upload-Array strukturieren
        $fileArray = [
            'name' => $name . '.svg',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => strlen($svgCode)
        ];
        
        $result = $iconManager->uploadIcon($fileArray, $name, $category, $tags);
        
        // Temp-Datei löschen
        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }
        
        if ($result['success']) {
            $message = rex_view::success($result['message']);
        } else {
            $message = rex_view::error($result['message']);
        }
    } else {
        $message = rex_view::error('Bitte gib SVG-Code und Icon-Namen ein.');
    }
}

// Upload Icon via File
if ($action === 'upload' && rex_post('submit', 'boolean')) {
    $name = rex_post('icon_name', 'string');
    $category = rex_post('icon_category', 'string', 'custom');
    $tags = array_filter(explode(',', rex_post('icon_tags', 'string')));
    
    if (isset($_FILES['icon_file']) && $_FILES['icon_file']['error'] === UPLOAD_ERR_OK) {
        $result = $iconManager->uploadIcon($_FILES['icon_file'], $name, $category, $tags);
        
        if ($result['success']) {
            $message = rex_view::success($result['message']);
        } else {
            $message = rex_view::error($result['message']);
        }
    } else {
        $message = rex_view::error('Bitte wähle eine SVG-Datei aus.');
    }
}

// Delete Icon
if ($action === 'delete' && !$iconActionToken->isValid()) {
    $message = rex_view::error('Das Icon wurde nicht gelöscht: Sicherheitstoken ungültig oder abgelaufen. Bitte lade die Seite neu und versuche es noch einmal.');
} elseif ($action === 'delete') {
    $name = rex_request('icon', 'string');
    if (!empty($name)) {
        $result = $iconManager->deleteIcon($name);
        if ($result['success']) {
            $message = rex_view::success($result['message']);
        } else {
            $message = rex_view::error($result['message']);
        }
    }
}

// Rebuild Icons
if ($action === 'rebuild') {
    $result = $iconBuilder->rebuild();
    if ($result['success']) {
        $message = rex_view::success(
            "Icons neu kompiliert!<br>" .
            "UIkit Icons: {$result['uikit_count']}<br>" .
            "Custom Icons: {$result['custom_count']}<br>" .
            "Gesamt: {$result['total_count']}"
        );
    } else {
        $message = rex_view::error($result['message']);
    }
}

// Re-Normalize Icons (alle Icons neu normalisieren)
if ($action === 'renormalize') {
    $customIcons = $iconManager->getCustomIcons();
    $count = 0;
    $errors = [];
    
    foreach ($customIcons as $icon) {
        // Icon neu normalisieren (überschreibt Datei)
        $result = $iconManager->renormalizeIcon($icon['name']);
        
        if ($result['success']) {
            $count++;
        } else {
            $errors[] = $icon['name'] . ': ' . $result['message'];
        }
    }
    
    // Nach erfolgreichem Re-Normalize auch neu kompilieren
    if ($count > 0) {
        $iconBuilder->rebuild();
    }
    
    if (empty($errors)) {
        $message = rex_view::success("$count Icons erfolgreich neu normalisiert und kompiliert");
    } else {
        $message = rex_view::warning("$count Icons normalisiert, aber mit Fehlern:<br>" . implode('<br>', $errors));
    }
}

echo $message;

// Statistiken
$stats = $iconManager->getStats();
$customIcons = $iconManager->getCustomIcons();

// Header
$content .= '<div class="utb-page utb-icons-page">';
$content .= '<header class="utb-icons-header uk-section uk-section-small">
    <div class="uk-container uk-container-large">
        <div class="utb-icons-header-inner">
            <div>
                <h1 class="utb-icons-title">
                    <span uk-icon="icon: star; ratio: 1.2" class="uk-margin-small-right utb-accent" aria-hidden="true"></span>
                    Custom Icons
                </h1>
                <p class="utb-icons-lead">Eigene SVG-Icons für UIkit</p>
            </div>
            <div class="utb-stats">
                <div>
                    <div class="utb-stat-value">' . (int) $stats['total'] . '</div>
                    <div class="utb-stat-label">Custom Icons</div>
                </div>
                <div>
                    <div class="utb-stat-value">' . round($stats['total_size'] / 1024, 1) . ' KB</div>
                    <div class="utb-stat-label">Gesamt</div>
                </div>
            </div>
        </div>
    </div>
</header>';

// Info-Box
$content .= '<section class="uk-section uk-section-small">
    <div class="uk-container uk-container-large">
        <div class="utb-info uk-alert">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-1 uk-width-expand@m">
                    <h2 class="utb-info-title uk-margin-remove"><span uk-icon="info" aria-hidden="true"></span> So funktioniert es</h2>
                    <ol class="uk-margin-small-top">
                        <li>SVG-Icon hochladen (max. 50KB)</li>
                        <li>Icon wird automatisch normalisiert (width/height 20, originale viewBox bleibt erhalten)</li>
                        <li>Icons werden zu UIkit hinzugefügt</li>
                        <li>Verwendung: <code class="utb-code">&lt;span uk-icon="custom-mein-icon"&gt;&lt;/span&gt;</code></li>
                    </ol>
                </div>
                <div class="uk-width-1-1 uk-width-auto@m">
                    <div class="utb-icon-actions">
                        <!-- Hauptfunktionen -->
                        <div class="uk-button-group">
                            <a href="' . rex_url::currentBackendPage(['action' => 'rebuild']) . '" class="utb-btn-primary uk-button uk-button-primary">
                                <span uk-icon="refresh" aria-hidden="true"></span> Kompilieren
                            </a>
                            <a href="' . rex_url::currentBackendPage(['action' => 'renormalize']) . '" class="utb-btn-primary uk-button uk-button-primary">
                                <span uk-icon="cog" aria-hidden="true"></span> Normalisieren
                            </a>
                        </div>
                        
                        <!-- Import/Export -->
                        <div class="uk-button-group">
                            <button class="utb-btn-default uk-button uk-button-default uk-button-small" type="button" onclick="UIkit.modal(\'#import-modal\').show()">
                                <span uk-icon="cloud-upload"></span> Importieren
                            </button>
                            <a href="' . rex_url::backendController(['rex-api-call' => 'uikit_icons_export']) . '" class="utb-btn-default uk-button uk-button-default uk-button-small">
                                <span uk-icon="cloud-download"></span> Exportieren
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>';

// Upload-Formular
$content .= '<section class="uk-section uk-section-small">
    <div class="uk-container uk-container-large">
        
        <ul class="utb-tabs" uk-tab>
            <li><a href="#"><span uk-icon="upload"></span> Datei hochladen</a></li>
            <li><a href="#"><span uk-icon="code"></span> SVG-Code einfügen</a></li>
            <li><a href="#"><span uk-icon="world"></span> Icon-Quellen</a></li>
        </ul>

        <div class="uk-switcher">
            <!-- Tab 1: File Upload -->
            <div>
                <div class="utb-card uk-card uk-card-default">
                    <div class="uk-card-header">
                        <h2 class="uk-card-title utb-card-heading">
                            <span uk-icon="upload"></span> Icon-Datei hochladen
                        </h2>
                    </div>
                    <div class="uk-card-body">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload" />
                            
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-width-1-1 uk-width-1-3@m">
                                    <label class="uk-form-label" for="utb-icon-file">Icon-Datei (SVG)</label>
                                    <div uk-form-custom>
                                        <input type="file" name="icon_file" id="utb-icon-file" accept=".svg" required />
                                        <button class="uk-button uk-button-default uk-width-1-1" type="button" tabindex="-1">
                                            <span uk-icon="cloud-upload"></span> Datei wählen
                                        </button>
                                    </div>
                                    <div class="uk-text-meta uk-margin-small-top">Max. 50KB, nur SVG</div>
                                </div>
                                
                                <div class="uk-width-1-1 uk-width-1-3@m">
                                    <label class="uk-form-label" for="utb-icon-name">Icon-Name</label>
                                    <input type="text" name="icon_name" id="utb-icon-name" class="uk-input" placeholder="z.B. my-logo" required 
                                           pattern="[a-z0-9-]+" title="Nur Kleinbuchstaben, Zahlen und Bindestriche" />
                                    <div class="uk-text-meta uk-margin-small-top">Nur a-z, 0-9, -</div>
                                </div>
                                
                                <div class="uk-width-1-2@s uk-width-1-6@m">
                                    <label class="uk-form-label" for="utb-icon-category">Kategorie</label>
                                    <select name="icon_category" id="utb-icon-category" class="uk-select">
                                        <option value="custom">Custom</option>
                                        <option value="brands">Brands</option>
                                        <option value="interface">Interface</option>
                                        <option value="navigation">Navigation</option>
                                    </select>
                                </div>
                                
                                <div class="uk-width-1-2@s uk-width-1-6@m">
                                    <label class="uk-form-label" for="utb-icon-tags">Tags (kommagetrennt)</label>
                                    <input type="text" name="icon_tags" id="utb-icon-tags" class="uk-input" placeholder="logo,brand" />
                                </div>
                            </div>
                            
                            <div class="uk-margin-top">
                                <button type="submit" name="submit" value="1" class="uk-button uk-button-primary">
                                    <span uk-icon="check"></span> Icon hochladen
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Tab 2: SVG Code -->
            <div>
                <div class="utb-card uk-card uk-card-default">
                    <div class="uk-card-header">
                        <h2 class="uk-card-title utb-card-heading">
                            <span uk-icon="code"></span> SVG-Code einfügen
                        </h2>
                    </div>
                    <div class="uk-card-body">
                        <form method="post">
                            <input type="hidden" name="action" value="upload_code" />
                            
                            <div class="uk-margin">
                                <label class="uk-form-label" for="utb-svg-code">SVG-Code</label>
                                <textarea name="svg_code" id="utb-svg-code" class="uk-textarea" rows="8" required 
                                          placeholder="<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; viewBox=&quot;0 0 20 20&quot;>...</svg>"></textarea>
                                <div class="uk-text-meta uk-margin-small-top">
                                    <span uk-icon="icon: info; ratio: 0.7"></span>
                                    Füge hier deinen kompletten SVG-Code ein (z.B. von FontAwesome, Heroicons, etc.)
                                </div>
                            </div>
                            
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-width-1-1 uk-width-1-2@m">
                                    <label class="uk-form-label" for="utb-icon-name-code">Icon-Name</label>
                                    <input type="text" name="icon_name_code" id="utb-icon-name-code" class="uk-input" placeholder="z.B. my-logo" required 
                                           pattern="[a-z0-9-]+" title="Nur Kleinbuchstaben, Zahlen und Bindestriche" />
                                    <div class="uk-text-meta uk-margin-small-top">Nur a-z, 0-9, -</div>
                                </div>
                                
                                <div class="uk-width-1-2@s uk-width-1-4@m">
                                    <label class="uk-form-label" for="utb-icon-category-code">Kategorie</label>
                                    <select name="icon_category_code" id="utb-icon-category-code" class="uk-select">
                                        <option value="custom">Custom</option>
                                        <option value="brands">Brands</option>
                                        <option value="interface">Interface</option>
                                        <option value="navigation">Navigation</option>
                                    </select>
                                </div>
                                
                                <div class="uk-width-1-2@s uk-width-1-4@m">
                                    <label class="uk-form-label" for="utb-icon-tags-code">Tags (kommagetrennt)</label>
                                    <input type="text" name="icon_tags_code" id="utb-icon-tags-code" class="uk-input" placeholder="logo,brand" />
                                </div>
                            </div>
                            
                            <div class="uk-margin-top">
                                <button type="submit" name="submit_code" value="1" class="uk-button uk-button-primary">
                                    <span uk-icon="check"></span> SVG-Code hochladen
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Tab 3: Icon-Quellen -->
            <div>
                <div class="utb-card uk-card uk-card-default">
                    <div class="uk-card-header">
                        <h2 class="uk-card-title utb-card-heading">
                            <span uk-icon="world"></span> Kostenlose Icon-Bibliotheken
                        </h2>
                    </div>
                    <div class="uk-card-body">
                        <p class="utb-muted">Kostenlose SVG-Icon-Bibliotheken zum Download:</p>
                        <div class="uk-grid-small uk-child-width-1-2@s uk-child-width-1-3@m" uk-grid>
                            <div>
                                <a href="https://fontawesome.com/search?o=r&m=free" target="_blank" rel="noopener" class="uk-link-reset">
                                    <div class="utb-card uk-card uk-card-small uk-card-default uk-card-hover uk-card-body">
                                        <h3 class="utb-lib-title uk-margin-small-bottom">Font Awesome</h3>
                                        <p class="utb-muted uk-text-small uk-margin-remove">2.000+ Icons (Free)</p>
                                        <p class="uk-text-small uk-margin-small-top">SVG-Download via Icon-Detail-Seite</p>
                                    </div>
                                </a>
                            </div>
                            <div>
                                <a href="https://heroicons.com" target="_blank" rel="noopener" class="uk-link-reset">
                                    <div class="utb-card uk-card uk-card-small uk-card-default uk-card-hover uk-card-body">
                                        <h3 class="utb-lib-title uk-margin-small-bottom">Heroicons</h3>
                                        <p class="utb-muted uk-text-small uk-margin-remove">292 Icons (MIT)</p>
                                        <p class="uk-text-small uk-margin-small-top">Copy/Paste SVG direkt verfügbar</p>
                                    </div>
                                </a>
                            </div>
                            <div>
                                <a href="https://feathericons.com" target="_blank" rel="noopener" class="uk-link-reset">
                                    <div class="utb-card uk-card uk-card-small uk-card-default uk-card-hover uk-card-body">
                                        <h3 class="utb-lib-title uk-margin-small-bottom">Feather Icons</h3>
                                        <p class="utb-muted uk-text-small uk-margin-remove">287 Icons (MIT)</p>
                                        <p class="uk-text-small uk-margin-small-top">Minimalistisches Icon-Set</p>
                                    </div>
                                </a>
                            </div>
                            <div>
                                <a href="https://remixicon.com" target="_blank" rel="noopener" class="uk-link-reset">
                                    <div class="utb-card uk-card uk-card-small uk-card-default uk-card-hover uk-card-body">
                                        <h3 class="utb-lib-title uk-margin-small-bottom">Remix Icon</h3>
                                        <p class="utb-muted uk-text-small uk-margin-remove">2.800+ Icons (Apache)</p>
                                        <p class="uk-text-small uk-margin-small-top">Open-Source-Bibliothek</p>
                                    </div>
                                </a>
                            </div>
                            <div>
                                <a href="https://phosphoricons.com" target="_blank" rel="noopener" class="uk-link-reset">
                                    <div class="utb-card uk-card uk-card-small uk-card-default uk-card-hover uk-card-body">
                                        <h3 class="utb-lib-title uk-margin-small-bottom">Phosphor Icons</h3>
                                        <p class="utb-muted uk-text-small uk-margin-remove">1.200+ Icons (MIT)</p>
                                        <p class="uk-text-small uk-margin-small-top">6 Gewichtsvarianten</p>
                                    </div>
                                </a>
                            </div>
                            <div>
                                <a href="https://lucide.dev" target="_blank" rel="noopener" class="uk-link-reset">
                                    <div class="utb-card uk-card uk-card-small uk-card-default uk-card-hover uk-card-body">
                                        <h3 class="utb-lib-title uk-margin-small-bottom">Lucide</h3>
                                        <p class="utb-muted uk-text-small uk-margin-remove">1.400+ Icons (ISC)</p>
                                        <p class="uk-text-small uk-margin-small-top">Feather Icons Fork</p>
                                    </div>
                                </a>
                            </div>
                            <div>
                                <a href="https://tabler.io/icons" target="_blank" rel="noopener" class="uk-link-reset">
                                    <div class="utb-card uk-card uk-card-small uk-card-default uk-card-hover uk-card-body">
                                        <h3 class="utb-lib-title uk-margin-small-bottom">Tabler Icons</h3>
                                        <p class="utb-muted uk-text-small uk-margin-remove">5.200+ Icons (MIT)</p>
                                        <p class="uk-text-small uk-margin-small-top">Umfangreichste Sammlung</p>
                                    </div>
                                </a>
                            </div>
                            <div>
                                <a href="https://icons.getbootstrap.com" target="_blank" rel="noopener" class="uk-link-reset">
                                    <div class="utb-card uk-card uk-card-small uk-card-default uk-card-hover uk-card-body">
                                        <h3 class="utb-lib-title uk-margin-small-bottom">Bootstrap Icons</h3>
                                        <p class="utb-muted uk-text-small uk-margin-remove">2.000+ Icons (MIT)</p>
                                        <p class="uk-text-small uk-margin-small-top">Framework-unabhängig</p>
                                    </div>
                                </a>
                            </div>
                            <div>
                                <a href="https://iconoir.com" target="_blank" rel="noopener" class="uk-link-reset">
                                    <div class="utb-card uk-card uk-card-small uk-card-default uk-card-hover uk-card-body">
                                        <h3 class="utb-lib-title uk-margin-small-bottom">Iconoir</h3>
                                        <p class="utb-muted uk-text-small uk-margin-remove">1.500+ Icons (MIT)</p>
                                        <p class="uk-text-small uk-margin-small-top">Elegantes Design</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>';

// Icon-Grid
$content .= '<section class="uk-section">
    <div class="uk-container uk-container-large">
        <div class="utb-card uk-card uk-card-default">
            <div class="uk-card-header">
                <h2 class="uk-card-title utb-card-heading">
                    <span uk-icon="grid"></span> Custom Icons (' . count($customIcons) . ')
                </h2>
            </div>
            <div class="uk-card-body">';

if (empty($customIcons)) {
    $content .= '<div class="uk-alert uk-alert-warning">
                    <span uk-icon="warning" aria-hidden="true"></span> Noch keine Custom Icons hochgeladen.
                 </div>';
} else {
    $content .= '<div class="uk-grid-small uk-child-width-1-2 uk-child-width-1-3@s uk-child-width-1-4@m uk-child-width-1-5@l uk-child-width-1-6@xl" uk-grid>';
    
    foreach ($customIcons as $icon) {
        $deleteUrl = rex_url::currentBackendPage(['action' => 'delete', 'icon' => $icon['name']] + $iconActionToken->getUrlParams());
        $deleteLabel = 'Icon „' . rex_escape($icon['name']) . '“ löschen';
        
        $content .= '<div>
            <div class="utb-card uk-card uk-card-default uk-card-small uk-card-hover">
                <div class="uk-card-body uk-text-center">
                    <div class="uk-inline uk-width-1-1">
                        <span uk-icon="icon: ' . rex_escape($icon['full_name']) . '; ratio: 2.5" aria-hidden="true"></span>
                        <div class="uk-position-top-right uk-padding-small">
                            <a href="' . $deleteUrl . '" class="uk-icon-button utb-icon-delete"
                               uk-icon="trash" uk-tooltip="title: Löschen" aria-label="' . $deleteLabel . '"
                               onclick="return confirm(' . htmlspecialchars(json_encode('Icon "' . $icon['name'] . '" wirklich löschen?'), ENT_QUOTES) . ')"></a>
                        </div>
                    </div>
                    <div class="uk-margin-small-top uk-text-small uk-text-bold">' . rex_escape($icon['name']) . '</div>
                    <div class="utb-muted uk-text-small">' . rex_escape($icon['category']) . '</div>
                    <div class="utb-muted uk-text-small">' . round($icon['size'] / 1024, 1) . ' KB</div>
                </div>
                <div class="uk-card-footer uk-padding-small">
                    <code class="utb-code utb-code-small">custom-' . rex_escape($icon['name']) . '</code>
                </div>
            </div>
        </div>';
    }
    
    $content .= '</div>';
}

$content .= '    </div>
        </div>
    </div>
</section>';
$content .= '</div>'; // utb-page

// Import Modal
$content .= '
<div id="import-modal" class="utb-modal" uk-modal>
    <div class="uk-modal-dialog">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <div class="uk-modal-header">
            <h2 class="uk-modal-title">Icons importieren</h2>
        </div>
        <div class="uk-modal-body">
            <form method="post" enctype="multipart/form-data" action="' . rex_url::currentBackendPage(['action' => 'import']) . '">
                <div class="uk-margin">
                    <label class="uk-form-label" for="icon_import_file">Icon-Datei (ZIP oder SVG)</label>
                    <div uk-form-custom>
                        <input type="file" name="icon_import_file" id="icon_import_file" accept=".zip,.svg" required>
                        <button class="uk-button uk-button-default uk-width-1-1" type="button" tabindex="-1">
                            <span uk-icon="icon: cloud-upload"></span> Datei auswählen
                        </button>
                    </div>
                    <div class="uk-text-meta uk-margin-small-top">
                        <strong>ZIP-Datei:</strong> Exportierte Icons (komplettes Icon-Set)<br>
                        <strong>SVG-Datei:</strong> Einzelnes Icon zum Hinzufügen
                    </div>
                </div>
                
                <div class="uk-alert uk-alert-primary">
                    <span uk-icon="info"></span>
                    <strong>Hinweis:</strong> Bei doppelten Namen werden Icons automatisch umbenannt (icon-2, icon-3, etc.)
                </div>
                
                <div class="uk-margin">
                    <button class="uk-button uk-button-primary uk-width-1-1" type="submit" name="submit_import" value="1">
                        <span uk-icon="icon: upload"></span> Importieren
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
';

// Fragment ausgeben
$fragment = new rex_fragment();
$fragment->setVar('title', 'Custom Icons');
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

// Extended Icons laden (enthält ALLE Icons: UIkit Standard + Custom)
$iconBuilder = new UikitThemeBuilder\CustomIconBuilder();
if ($iconBuilder->hasExtendedIcons()) {
    echo '<script src="' . $iconBuilder->getExtendedIconsUrl() . '"></script>';
}

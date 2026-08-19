<?php
/**
 * UIKit Topbar Template
 *
 * @name UIKit Topbar Template
 * @description Template mit separater Top-Leiste für Logo, Social Media und CTA. Navigation darunter.
 *
 * DOMAIN_SETTINGS:
 *
 * --- Layout & Struktur [fa-solid fa-layer-group] ---
 * tm_topbar_color: select|Top-Leiste Hintergrund|:Keine Farbe,uk-background-muted:Hell,uk-background-default:Standard,uk-background-primary uk-light:Primärfarbe,uk-background-secondary uk-light:Sekundärfarbe|Hintergrundfarbe der Top-Leiste
 * tm_topbar_container: select|Top-Leiste Breite|uk-container-large:Weit,uk-container:Standard,uk-container-small:Eng,uk-container-xlarge:Extra Weit,uk-container-expand:Volle Breite|Container-Breite für die Top-Leiste
 * tm_navbar_container: select|Navbar Breite|uk-container-large:Weit,uk-container:Standard,uk-container-small:Eng,uk-container-xlarge:Extra Weit,uk-container-expand:Volle Breite|Container-Breite für die Navbar
 * tm_navbar_style: select|Navbar Stil|uk-navbar-container:Standard (mit Schatten),uk-navbar-transparent:Transparent (ohne Schatten)|Stil der Navigationsleiste
 * tm_navbar_color: select|Navbar Hintergrund|uk-background-default:Standard,uk-background-muted:Hell,uk-background-primary uk-light:Primärfarbe,uk-background-secondary uk-light:Sekundärfarbe,:Transparent|Hintergrundfarbe der Navbar
 * tm_navbar_sticky: select|Navbar Sticky|default:Immer sichtbar,show-on-up:Nur beim Hochscrollen,:Nicht fixiert|Soll die Navigation beim Scrollen fixiert werden?
 *
 * --- Barrierefreiheit [fa-solid fa-universal-access] [admin,developer] ---
 * tm_sa11y_enabled: checkbox|Sa11y Accessibility Checker||Aktiviert den Sa11y Accessibility Checker im Frontend
 *
 * --- Branding & Logo [fa-solid fa-palette] ---
 * tm_logo: media|Logo|logo.svg|Logo für Header und Footer
 * tm_logo_text: text|Logo Alt-Text|Meine Firma|Alternativer Text für das Logo
 * tm_logo_white: media|Logo Weiß|logo_white.svg|Weißes Logo für dunkle Hintergründe
 * tm_mobile_logo_enabled: checkbox|Separates Mobillogo|1|Soll ein separates Logo für mobile Geräte verwendet werden?
 * tm_mobile_logo_variant: select|Mobile Logo Variante|standard:Standard Logo,white:Weißes Logo,custom:Eigenes Mobillogo|Welches Logo soll auf mobilen Geräten verwendet werden?
 * tm_mobile_logo: media|Eigenes Mobillogo|logo.svg|Eigenes Logo für mobile Geräte (nur bei Variante "Eigenes Mobillogo")
 * tm_firma: text|Firmenname|Meine Firma|Firmenname für Copyright
 * tm_slogan: text|Slogan||Slogan im Footer
 *
 * --- Responsive Breakpoints [fa-solid fa-mobile-screen] ---
 * tm_logo_breakpoint: select|Desktop-Logo Breakpoint|s:Small (640px),m:Medium (960px),l:Large (1200px),xl:Extra Large (1600px)|Ab welcher Bildschirmbreite soll das Desktop-Logo angezeigt werden?
 * tm_nav_breakpoint: select|Navigation Breakpoint|s:Small (640px),m:Medium (960px),l:Large (1200px),xl:Extra Large (1600px)|Ab welcher Bildschirmbreite soll die Desktop-Navigation angezeigt werden?
 * tm_toggle_breakpoint: select|Mobile-Toggle Breakpoint|s:Small (640px),m:Medium (960px),l:Large (1200px),xl:Extra Large (1600px)|Bis zu welcher Bildschirmbreite soll der Mobile-Toggle angezeigt werden?
 * tm_search_breakpoint: select|Suchbutton Breakpoint|s:Small (640px),m:Medium (960px),l:Large (1200px),xl:Extra Large (1600px)|Ab welcher Bildschirmbreite soll der Suchbutton angezeigt werden?
 *
 * --- Navigation [fa-solid fa-bars] [admin,developer] ---
 * tm_nav_style: select|Navigationsstil|default:Links,centered:Zentriert,right:Rechts|Wähle den Stil der Hauptnavigation
 * tm_mobile_nav_icon: text|Mobile Nav Toggle Icon|plus|UIKit Icon für Mobile Navigation Toggle (plus, chevron-down, triangle-down)
 * tm_breadcrumb_enabled: checkbox|Breadcrumb Navigation|1|Soll die Breadcrumb-Navigation angezeigt werden?
 * tm_dropdown_rounded: checkbox|Dropdown Ecken abrunden|1|Soll das Dropdown-Menü abgerundete Ecken haben?
 * tm_dropdown_mode: select|Dropdown Modus|auto:Automatisch (basierend auf Anzahl),manual:Manuelle Einstellung|Soll die Dropdown-Breite automatisch oder manuell gesteuert werden?
 * tm_dropdown_width: select|Dropdown Breite|4:Breit (4),2:Schmal (2),3:Mittel (3),5:Extra Breit (5)|Breite des Dropdown-Menüs (uk-navbar-dropdown-width-X)
 * tm_dropdown_columns: select|Dropdown Spalten|2:2 Spalten,1:1 Spalte,3:3 Spalten|Anzahl der Spalten im Dropdown-Menü
 * tm_dropdown_auto_threshold: text|Auto-Schwellenwert|2|Ab wie vielen Einträgen wird im Auto-Modus 2-spaltig gewechselt?
 *
 * --- CTA Button [fa-solid fa-bullhorn] ---
 * tm_cta_enabled: checkbox|CTA Button anzeigen|1|Soll der Call-to-Action Button angezeigt werden?
 * tm_cta_text: text|CTA Button Text|Kontakt|Text des CTA Buttons
 * tm_cta_link: text|CTA Button Link|#kontakt|Link-Ziel des CTA Buttons
 * tm_cta_icon: text|CTA Button Icon|mail|UIKit Icon-Name (ohne "icon:" Präfix)
 * tm_cta_button_style: select|CTA Button Stil|uk-button-primary:Primär,uk-button-secondary:Sekundär,uk-button-default:Standard,uk-button-text:Textlink|Uikit-Button-Stile
 *
 * --- Suche [fa-solid fa-magnifying-glass] [admin,developer] ---
 * tm_search_enabled: checkbox|Suchfeld anzeigen|0|Soll ein Suchfeld in der Navbar angezeigt werden?
 * tm_search_article: article|Suchergebnisseite||Artikel-ID der Search It Ergebnisseite
 * tm_search_placeholder: text|Suchfeld Platzhaltertext|Suchen...|Platzhaltertext für das Suchfeld
 *
 * --- Social Media [fa-brands fa-facebook] ---
 * tm_social_links: social_links|Social Media Links|uk|Social-Media-Profile (UIKit Icons)
 *
 * --- Footer [fa-solid fa-sitemap] ---
 * tm_footer_color: select|Footer-Farbe|uk-background-default:Standard,uk-background-primary uk-light:Sekundärfarbe,uk-background-secondary uk-light:Primärfarbe,uk-background-muted:Hell|Uikit-Farben
 * tm_footer_logo_enabled: checkbox|Footer Logo anzeigen|1|Soll das Logo im Footer angezeigt werden?
 * tm_footer_mobile_logo: media|Mobillogo für Footer|logo.svg|Alternatives Logo für mobile Geräte im Footer
 * tm_footer_alternative_layout: checkbox|Alternative Footer-Layout||Nur Impressum, Datenschutz und Kontakt zentriert ausgeben
 * tm_footer_impressum_link: article|Impressum Artikel||Artikel-ID für Impressum
 * tm_footer_datenschutz_link: article|Datenschutz Artikel||Artikel-ID für Datenschutzerklärung
 * tm_footer_kontakt_link: article|Kontakt Artikel||Artikel-ID für Kontakt
 * tm_footer_show_privacy_settings: checkbox|Datenschutzeinstellungen Link||Link zu Cookie-Einstellungen im Footer anzeigen
 * tm_footer_inner_shadow: checkbox|Footer innerer Schatten||Inneren Schatten am oberen Rand des Footers anzeigen
 * tm_footer_section1_title: text|Footer Spalte 1 Titel|Unternehmen|Überschrift erste Footer-Spalte
 * tm_footer_section1_links: linklist|Footer Spalte 1 Links||Artikel-Links für erste Footer-Spalte
 * tm_footer_section2_title: text|Footer Spalte 2 Titel|Service|Überschrift zweite Footer-Spalte
 * tm_footer_section2_links: linklist|Footer Spalte 2 Links||Artikel-Links für zweite Footer-Spalte
 * tm_footer_section3_title: text|Footer Spalte 3 Titel|Kontakt|Überschrift dritte Footer-Spalte
 * tm_footer_section3_text: cke5|Footer Spalte 3 Text||Kontakt-Informationen
 */

use FriendsOfRedaxo\NavigationArray\BuildArray;
use FriendsOfRedaxo\Sa11y\Sa11y;
use FriendsOfRedaxo\TemplateManager\TemplateManager;
use UikitThemeBuilder\DomainContext;
use UikitThemeBuilder\TemplateHelper;

setlocale(LC_TIME, 'de_DE.UTF8');
$ctype1 = 'REX_ARTICLE[ctype=1]';

if (array_key_exists('Modal', apache_request_headers()) && 'on' == apache_request_headers()['Modal']) {
    echo rex_extension::registerPoint(new rex_extension_point('OUTPUT_FILTER', $ctype1 . '<script>UIkit.scroll().scrollTo("#ajax-dialog");</script>'));
    exit;
}

// Hilfsfunktion zum Zählen aller Kinder (inkl. Enkel)
if (!function_exists('countAllChildren')) {
    function countAllChildren($categories) {
        $count = 0;
        foreach ($categories as $child) {
            $count++;
            if (!empty($child['children'])) {
                $count += countAllChildren($child['children']);
            }
        }
        return $count;
    }
}

// Navigation-Funktion für Desktop - UIKit Navbar kompatibel mit erweitertem Dropdown
if (!function_exists('buildNavigation')) {
    function buildNavigation($categories, $level = 0, $config = [])
    {
        // Config mit Defaults
        $defaults = [
            'rounded' => true,
            'mode' => 'auto',
            'width' => '4',
            'columns' => '2',
            'autoThreshold' => 2
        ];
        $config = array_merge($defaults, is_array($config) ? $config : ['rounded' => $config]);
        
        $output = [];

        foreach ($categories as $cat) {
            $liClass = '';
            $subnavi = '';
            $parentIcon = '';
            $aClass = '';

            // Styling für verschiedene Levels
            if ($level == 1) {
                $aClass = ' class="uk-text-bolder uk-margin-small-left"';
            } elseif ($level >= 2) {
                $aClass = ' class="uk-margin-small-left"';
            }

            // Parent-Klasse für Dropdown-Menüs - NUR auf Level 0
            if (!empty($cat['children']) && $level === 0) {
                $liClass = 'uk-parent';
                $parentIcon = '<span uk-navbar-parent-icon></span>';

                // Dropdown-Breite und Spalten bestimmen
                if ($config['mode'] === 'auto') {
                    // Automatischer Modus: Basierend auf Anzahl der Kinder
                    $totalChildren = countAllChildren($cat['children']);
                    
                    if ($totalChildren > $config['autoThreshold']) {
                        $columns = '2';
                        $width = '4';
                    } else {
                        $columns = '1';
                        $width = '2';
                    }
                } else {
                    // Manueller Modus: Feste Werte aus Config
                    $columns = $config['columns'];
                    $width = $config['width'];
                }
                
                // Grid und Dropdown-Klassen zusammenbauen
                $roundedClass = $config['rounded'] ? ' uk-border-rounded' : '';
                $dividerClass = $columns > 1 ? 'uk-grid-divider ' : '';
                $ulClass = $dividerClass . 'uk-grid-small uk-nav uk-navbar-dropdown-nav uk-child-width-1-' . $columns;
                // uk-background-default entfernt damit Customizer-Farben greifen
                $dropdownClass = 'uk-navbar-dropdown uk-navbar-dropdown-width-' . $width . $roundedClass . ' uk-margin-remove uk-padding-small';
                
                $gridAttr = ' uk-grid="masonry: true"';
                $scrollspyAttr = ' uk-scrollspy="target: > .uk-active; cls: uk-animation-fade; delay: 50; repeat: true"';

                // Dropdown-Menü erstellen
                $subnavi .= '<div tabindex="0" class="' . $dropdownClass . '">';
                $subnavi .= '<ul class="' . $ulClass . '"' . $gridAttr . $scrollspyAttr . '>';
                $subnavi .= buildNavigation($cat['children'], $level + 1, $config);
                $subnavi .= '</ul>';
                $subnavi .= '</div>';
            } elseif (!empty($cat['children']) && $level >= 1) {
                // Ab Level 1: Kinder als uk-nav-sub darunter anzeigen
                $subnavi .= '<ul class="uk-nav-sub">';
                $subnavi .= buildNavigation($cat['children'], $level + 1, $config);
                $subnavi .= '</ul>';
            }

            // Aktiver Punkt
            if ($cat['current']) {
                $liClass .= ($liClass ? ' ' : '') . 'uk-active';
            }

            // Link erstellen
            $link = '<a' . $aClass . ' href="' . $cat['url'] . '">' . htmlspecialchars($cat['catName']) . $parentIcon . '</a>';

            // Li-Element zusammenbauen
            $li = '<li' . ($liClass ? ' class="' . $liClass . '"' : '') . '>';
            $li .= $link . $subnavi;
            $li .= '</li>';

            $output[] = $li;
        }

        return implode("\n", $output);
    }
}

// Funktion zum Generieren von Link-Listen aus komma-getrennten Artikel-IDs
if (!function_exists('generateFooterSection')) {
    function generateFooterSection($linkString)
    {
        if (empty($linkString)) {
            return '';
        }

        $output = '';
        $linkIds = array_map('trim', explode(',', $linkString));

        foreach ($linkIds as $linkId) {
            if (empty($linkId) || !is_numeric($linkId)) {
                continue;
            }

            $article = rex_article::get($linkId);
            if ($article && $article->isOnline()) {
                $articleName = $article->getValue('name');
                $output .= '<li><a href="' . rex_getUrl($linkId) . '">' . htmlspecialchars($articleName) . '</a></li>';
            }
        }

        return $output;
    }
}

// Mobile Navigation-Funktion - UIKit Nav mit separatem Toggle-Button
if (!function_exists('buildMobileNavigation')) {
    function buildMobileNavigation($categories, $level = 0, $toggleIcon = 'plus')
    {
        $output = [];

        foreach ($categories as $cat) {
            $liClass = 'level-' . $level;
            $subnavi = '';
            $toggleBtn = '';
            $isOpen = '';

            // Parent-Klasse mit Sub-Navigation
            if (!empty($cat['children'])) {
                $liClass .= ' uk-parent';

                // Wenn aktiv, uk-open hinzufügen
                if ($cat['current']) {
                    $isOpen = ' uk-open';
                }

                // Toggle-Button mit Icon
                $toggleBtn = '<a href="#" tabindex="0" class="nav-toggle-btn"><span uk-icon="icon: ' . htmlspecialchars($toggleIcon) . '; ratio: 1"></span></a>';

                // Sub-Navigation erstellen
                $subnavi .= '<ul class="uk-nav-sub">';
                $subnavi .= buildMobileNavigation($cat['children'], $level + 1, $toggleIcon);
                $subnavi .= '</ul>';
            }

            // Aktiver Punkt
            if ($cat['current']) {
                $liClass .= ' uk-active';
            }

            // Link erstellen - immer mit URL
            $catname = htmlspecialchars($cat['catName']);
            if ($cat['current']) {
                $catname = '<strong>' . $catname . '</strong>';
            }
            $link = '<a href="' . $cat['url'] . '">' . $catname . '</a>';

            // Li-Element zusammenbauen: Toggle-Button DANN Link (barrierefreie DOM-Reihenfolge, visuell rechts durch float)
            $li = '<li class="' . $liClass . '"' . $isOpen . '>';
            $li .= $toggleBtn . $link . $subnavi;
            $li .= '</li>';

            $output[] = $li;
        }

        return implode("\n", $output);
    }
}

// Breadcrumb-Funktion mit navigation_array
if (!function_exists('buildBreadcrumb')) {
    function buildBreadcrumb()
    {
        // Nicht auf Startseite anzeigen
        if (rex_article::getCurrentId() == rex_article::getSiteStartArticleId()) {
            return '';
        }

        $navarray = BuildArray::create()->setDepth(10);
        $breadcrumbItems = [];

        $navarray->walk(static function ($item, $level) use (&$breadcrumbItems) {
            if ($item['active']) {
                // Aktueller Artikel wird als aktiv markiert aber nicht verlinkt
                if (rex_article::getCurrentId() == $item['catId']) {
                    $breadcrumbItems[] = '<li class="uk-disabled"><span>' . htmlspecialchars($item['catName']) . '</span></li>';
                } else {
                    $breadcrumbItems[] = '<li><a href="' . $item['url'] . '">' . htmlspecialchars($item['catName']) . '</a></li>';
                }
            }
        });

        if (empty($breadcrumbItems)) {
            return '';
        }

        $output = '<nav aria-label="Breadcrumb" class="uk-margin-medium-bottom">';
        $output .= '<ul class="uk-breadcrumb">';
        $output .= '<li><a href="/" title="Startseite"><span uk-icon="home"></span></a></li>';
        $output .= implode("\n", $breadcrumbItems);
        $output .= '</ul>';
        $output .= '</nav>';

        return $output;
    }
}

// Navigation generieren
$nav_start = rex_yrewrite::getDomainByArticleId(rex_article::getCurrentId(), rex_clang::getCurrentId())->getMountId();

// Mobile Navigation mit Toggle-Button (Icon aus Template Manager, default: plus)
$mobileToggleIcon = TemplateManager::get('tm_mobile_nav_icon', 'plus');

// Template Manager Settings laden
$topbarColor = TemplateManager::get('tm_topbar_color', 'uk-background-muted');
$topbarContainer = TemplateManager::get('tm_topbar_container', 'uk-container-large');
$navbarContainer = TemplateManager::get('tm_navbar_container', 'uk-container-large');
$navbarStyle = TemplateManager::get('tm_navbar_style', 'uk-navbar-container');
$navbarColor = TemplateManager::get('tm_navbar_color', 'uk-background-default');
$sa11yEnabled = TemplateManager::get('tm_sa11y_enabled', '0');
$logoMedia = TemplateManager::get('tm_logo', 'logo.svg');
$logoWhite = TemplateManager::get('tm_logo_white', 'logo_white.svg');
$logoAltText = TemplateManager::get('tm_logo_text', 'Meine Firma');
$slogan = TemplateManager::get('tm_slogan', '');
$firma = TemplateManager::get('tm_firma', 'Meine Firma');
$navbarSticky = TemplateManager::get('tm_navbar_sticky', 'default');
$footerLogoEnabled = TemplateManager::get('tm_footer_logo_enabled', '1');
$mobileLogoEnabled = TemplateManager::get('tm_mobile_logo_enabled', '1');
$mobileLogoVariant = TemplateManager::get('tm_mobile_logo_variant', 'standard');
$mobileLogo = TemplateManager::get('tm_mobile_logo', $logoMedia);

// Responsive Breakpoints für einzelne Elemente
$logoBreakpoint = TemplateManager::get('tm_logo_breakpoint', 'l');
$navBreakpoint = TemplateManager::get('tm_nav_breakpoint', 'l');
$toggleBreakpoint = TemplateManager::get('tm_toggle_breakpoint', 'l');
$searchBreakpoint = TemplateManager::get('tm_search_breakpoint', 'l');

// CTA Button Einstellungen
$ctaEnabled = TemplateManager::get('tm_cta_enabled', '1');
$ctaText = TemplateManager::get('tm_cta_text', 'Kontakt');
$ctaLink = TemplateManager::get('tm_cta_link', '#kontakt');
$ctaIcon = TemplateManager::get('tm_cta_icon', 'mail');
$ctaButtonStyle = TemplateManager::get('tm_cta_button_style', 'uk-button-primary');

// Breadcrumb Einstellung
$breadcrumbEnabled = TemplateManager::get('tm_breadcrumb_enabled', '1');

// Dropdown Einstellungen
$dropdownRounded = TemplateManager::get('tm_dropdown_rounded', '1');
$dropdownMode = TemplateManager::get('tm_dropdown_mode', 'auto');
$dropdownWidth = TemplateManager::get('tm_dropdown_width', '4');
$dropdownColumns = TemplateManager::get('tm_dropdown_columns', '2');
$dropdownAutoThreshold = TemplateManager::get('tm_dropdown_auto_threshold', '2');

// Suchfeld Einstellungen
$searchEnabled = TemplateManager::get('tm_search_enabled', '0');
$searchArticle = TemplateManager::get('tm_search_article', '');
$searchPlaceholder = TemplateManager::get('tm_search_placeholder', 'Suchen...');

// Navigation Stil
$navStyle = TemplateManager::get('tm_nav_style', 'default');

// UIKit Theme aus Settings
$uikitTheme = TemplateManager::get('tm_uikit_theme', 'default');

// Navigation Array erstellen
$navigationArray = BuildArray::create()
    ->setStart(-1)
    ->setDepth(2)
    ->setIgnore(true)
    ->generate();

// Dropdown-Konfiguration zusammenstellen
$dropdownConfig = [
    'rounded' => '1' == $dropdownRounded,
    'mode' => $dropdownMode,
    'width' => $dropdownWidth,
    'columns' => $dropdownColumns,
    'autoThreshold' => (int)$dropdownAutoThreshold
];

$navigation = buildNavigation($navigationArray, 0, $dropdownConfig);
$mobileNavigation = buildMobileNavigation($navigationArray, 0, $mobileToggleIcon);

// Footer-Settings aus Template Manager laden
$footerColor = TemplateManager::get('tm_footer_color', 'uk-background-default');
$footerMobileLogo = TemplateManager::get('tm_footer_mobile_logo', $logoMedia);
$footerAlternativeLayout = TemplateManager::get('tm_footer_alternative_layout', '0');
$footerImpressumLink = TemplateManager::get('tm_footer_impressum_link', '');
$footerDatenschutzLink = TemplateManager::get('tm_footer_datenschutz_link', '');
$footerKontaktLink = TemplateManager::get('tm_footer_kontakt_link', '');
$footerShowPrivacySettings = TemplateManager::get('tm_footer_show_privacy_settings', '0');
$footerInnerShadow = TemplateManager::get('tm_footer_inner_shadow', '0');
$section1Title = TemplateManager::get('tm_footer_section1_title', 'Unternehmen');
$section1Links = generateFooterSection(TemplateManager::get('tm_footer_section1_links', ''));

$section2Title = TemplateManager::get('tm_footer_section2_title', 'Service');
$section2Links = generateFooterSection(TemplateManager::get('tm_footer_section2_links', ''));

$section3Title = TemplateManager::get('tm_footer_section3_title', 'Kontakt');
$section3Text = TemplateManager::get('tm_footer_section3_text', '');
?>
<!DOCTYPE html>
<html lang="<?= rex_clang::getCurrent()->getCode() ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php
        $seo = new rex_yrewrite_seo();
        echo $seo->getTags();
    ?>
    
    <!-- UIKit Theme Builder - CSS und Fonts -->
    <?php
    // Theme aus DomainContext oder Template Manager
    $themeContext = DomainContext::getContext();
    $themeName = $themeContext['theme'] ?? $uikitTheme;
    echo TemplateHelper::includeAllStyles($themeContext['theme']);
    ?>
    <?php
        echo '<link rel="stylesheet" href="' . rex_url::addonAssets('vidstack', 'vidstack.css') . '">';
        echo '<link rel="stylesheet" href="' . rex_url::addonAssets('vidstack', 'vidstack_helper.css') . '">';
        echo '<link rel="stylesheet" href="' . rex_url::addonAssets('uikit_theme_builder', 'templates.css') . '">';
    ?>
    
    <!-- Accessibility -->
    <?php if ('1' == $sa11yEnabled): ?>
    <?= Sa11y::get()?>
    <?php endif ?>
    
    <style>
        /* Focus visible for better keyboard navigation */
        *:focus-visible {
            outline: 2px solid var(--global-primary-background, #1e87f0);
            outline-offset: 2px;
        }
    </style>
   </head>
<body>

<!-- Skip to main content link for keyboard users -->
<a href="#main-content" class="uk-position-top-left uk-position-fixed uk-position-z-index uk-transition-slide-top-small" style="transform: translateY(-100%);" onfocus="this.style.transform='translateY(0)'" onblur="this.style.transform='translateY(-100%)'" tabindex="0">
    <span class="uk-button uk-button-primary">Zum Hauptinhalt springen</span>
</a>

<!-- Header mit Navigation -->
<header role="banner">
    <!-- Top-Leiste mit Logo, Social und CTA -->
    <div class="uk-section uk-section-xsmall <?= htmlspecialchars($topbarColor) ?>">
        <div class="uk-container uk-container-large <?= htmlspecialchars($topbarContainer) ?>">
            <div class="uk-grid uk-grid-small uk-flex-middle" uk-grid>
                <!-- Logo -->
                <div class="uk-width-auto">
                    <a href="/" aria-label="Zur Startseite">
                        <!-- Desktop Logo -->
                        <img src="<?= rex_url::media($logoMedia) ?>" 
                             alt="<?= htmlspecialchars($logoAltText) ?>" 
                             height="40" 
                             style="height: 40px;" 
                             class="uk-visible@<?= htmlspecialchars($logoBreakpoint) ?>" />
                        <!-- Mobile Logo -->
                        <?php
                        if ('1' == $mobileLogoEnabled) {
                            if ('custom' === $mobileLogoVariant) {
                                $topbarMobileLogo = $mobileLogo;
                            } elseif ('white' === $mobileLogoVariant) {
                                $topbarMobileLogo = $logoWhite;
                            } else {
                                $topbarMobileLogo = $logoMedia;
                            }
                        } else {
                            $topbarMobileLogo = $logoMedia;
                        }
                        ?>
                        <img src="<?= rex_url::media($topbarMobileLogo) ?>" 
                             alt="<?= htmlspecialchars($logoAltText) ?>" 
                             height="40" 
                             style="height: 40px;" 
                             class="uk-hidden@<?= htmlspecialchars($logoBreakpoint) ?>" />
                    </a>
                </div>
                
                <!-- Spacer -->
                <div class="uk-width-expand"></div>
                
                <!-- Suchfeld -->
                <?php if ('1' == $searchEnabled && !empty($searchArticle)): ?>
                <div class="uk-width-auto uk-visible@<?= htmlspecialchars($searchBreakpoint) ?>">
                    <form class="uk-search uk-search-default" method="get" action="<?= rex_getUrl($searchArticle) ?>">
                        <input class="uk-search-input" type="search" name="query" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" aria-label="<?= htmlspecialchars($searchPlaceholder) ?>">
                        <button type="submit" uk-search-icon aria-label="Suche absenden"></button>
                    </form>
                </div>
                <?php endif ?>
                
                <!-- Social Media Icons -->
                <?php
                $socialLinksJson = TemplateManager::get('tm_social_links');
                $socialLinks = $socialLinksJson ? json_decode($socialLinksJson, true) : [];
                if (!empty($socialLinks)):
                ?>
                <div class="uk-width-auto uk-visible@<?= htmlspecialchars($logoBreakpoint) ?>">
                    <ul class="uk-iconnav uk-margin-remove">
                        <?php foreach ($socialLinks as $link):
                            if (empty($link['url'])) {
                            continue;
                            }
                            $icon = $link['icon'] ?? '';
                            $url = $link['url'];
                            $label = $link['label'] ?? '';

                            // UIKit Icon extrahieren
                            $ukIcon = str_replace(['uk-icon-', 'fa-'], '', $icon);
                        ?>
                        <li>
                            <a href="<?= rex_escape($url) ?>" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               uk-icon="icon: <?= rex_escape($ukIcon) ?>; ratio: 0.9"
                               <?= $label ? 'title="' . rex_escape($label) . '" uk-tooltip' : '' ?>>
                            </a>
                        </li>
                        <?php endforeach ?>
                    </ul>
                </div>
                <?php endif ?>
                
                <!-- CTA Button -->
                <?php if ('1' == $ctaEnabled): ?>
                <div class="uk-width-auto uk-visible@<?= htmlspecialchars($logoBreakpoint) ?>">
                    <a href="<?= htmlspecialchars($ctaLink) ?>" 
                       class="uk-button <?= htmlspecialchars($ctaButtonStyle) ?> uk-button-small" 
                       title="<?= htmlspecialchars($ctaText) ?>">
                        <?php if (!empty($ctaIcon)): ?>
                        <span uk-icon="<?= htmlspecialchars($ctaIcon) ?>" aria-hidden="true"></span>
                        <?php endif ?>
                        <?= htmlspecialchars($ctaText) ?>
                    </a>
                </div>
                <?php endif ?>
            </div>
        </div>
    </div>
    
    <!-- Navbar mit optionalem Sticky -->
    <?php if (!empty($navbarSticky) && '' !== $navbarSticky): ?>
        <?php
        $stickyAttr = 'sel-target: .uk-navbar-container; cls-active: uk-navbar-sticky';
        if ('show-on-up' === $navbarSticky) {
            $stickyAttr = 'show-on-up: true; animation: uk-animation-slide-top; sel-target: .uk-navbar-container; cls-active: uk-navbar-sticky';
        }
        ?>
    <div uk-sticky="<?= htmlspecialchars($stickyAttr) ?>">
    <?php endif ?>
        <nav class="<?= htmlspecialchars($navbarStyle) ?> <?= htmlspecialchars($navbarColor) ?>" aria-label="Hauptnavigation">
            <div class="uk-container uk-container-large <?= htmlspecialchars($navbarContainer) ?>">
                <div uk-navbar>
                    
                <!-- Navigation mit Flexbox ausgerichtet -->
                <div class="uk-navbar-left uk-width-1-1">
                    <div class="uk-flex uk-flex-middle uk-width-1-1 uk-visible@<?= htmlspecialchars($navBreakpoint) ?>" style="justify-content: <?= 'centered' === $navStyle ? 'center' : ('right' === $navStyle ? 'flex-end' : 'flex-start') ?>;">
                        <ul class="uk-navbar-nav">
                            <?= $navigation ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Mobile-Toggle -->
                <div class="uk-navbar-right uk-hidden@<?= htmlspecialchars($toggleBreakpoint) ?>">
                    <a class="uk-navbar-toggle uk-margin-right" 
                       uk-toggle="target: #mobile-nav"
                       uk-navbar-toggle-icon
                       aria-label="Mobile Navigation öffnen"
                       aria-expanded="false"
                       aria-controls="mobile-nav">
                    </a>
                </div>
            
                </div>
            </div>
        </nav>
    <?php if (!empty($navbarSticky) && '' !== $navbarSticky): ?>
    </div>
    <?php endif ?>
</header>

<!-- Mobile Navigation Offcanvas -->
<div id="mobile-nav" 
     uk-offcanvas="mode: slide; overlay: true; flip: true;"
     aria-labelledby="mobile-nav-title">
    <div class="uk-offcanvas-bar" role="dialog" aria-modal="true">
        
        <!-- Close Button -->
        <button class="uk-offcanvas-close" 
                type="button" 
                uk-close
                aria-label="Mobile Navigation schließen"></button>
        
        <!-- Mobile Logo -->
        <?php if ('1' == $mobileLogoEnabled): ?>
            <?php
            if ('custom' === $mobileLogoVariant) {
                $offcanvasMobileLogo = $mobileLogo;
            } elseif ('white' === $mobileLogoVariant) {
                $offcanvasMobileLogo = $logoWhite;
            } else {
                $offcanvasMobileLogo = $logoMedia;
            }
            ?>
        <div class="uk-text-center uk-margin-medium-top">
            <img src="<?= rex_url::media($offcanvasMobileLogo) ?>" 
                 alt="<?= htmlspecialchars($logoAltText) ?>" 
                 class="uk-responsive-height"
                 height="50" />
        </div>
        <?php endif ?>
        
        <!-- Mobile Navigation Menu -->
        <nav aria-label="Mobile Navigation">
            <h2 id="mobile-nav-title" class="uk-hidden-visually">Mobile Navigation</h2>
            <ul class="uk-nav-default" data-uk-nav="toggle:>.nav-toggle-btn">
                <?= $mobileNavigation ?>
            </ul>
        </nav>
        
        <!-- Mobile Suchfeld -->
        <?php if ('1' == $searchEnabled && !empty($searchArticle)): ?>
        <hr class="uk-margin-medium">
        <div class="uk-margin-medium">
            <form class="uk-search uk-search-default uk-width-1-1" method="get" action="<?= rex_getUrl($searchArticle) ?>">
                <input class="uk-search-input uk-width-1-1" type="search" name="query" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" aria-label="<?= htmlspecialchars($searchPlaceholder) ?>">
                <button type="submit" uk-search-icon aria-label="Suche absenden"></button>
            </form>
        </div>
        <?php endif ?>
        
        <!-- Mobile CTA Button mit konfigurierbarem Stil -->
        <?php if ('1' == $ctaEnabled): ?>
        <div class="uk-margin-large-top uk-text-center">
            <a href="<?= htmlspecialchars($ctaLink) ?>" class="uk-button <?= htmlspecialchars($ctaButtonStyle) ?> uk-width-1-1" title="<?= htmlspecialchars($ctaText) ?>">
                <?php if (!empty($ctaIcon)): ?>
                <span uk-icon="<?= htmlspecialchars($ctaIcon) ?>" aria-hidden="true"></span>
                <?php endif ?>
                <?= htmlspecialchars($ctaText) ?>
            </a>
        </div>
        <?php endif ?>
    </div>
</div>

<!-- Main Content -->
<main id="main-content" role="main" tabindex="-1">
    <article class="ck-content">
        <?php if ('1' == $breadcrumbEnabled): ?>
        <div class="uk-container uk-container-large uk-margin-top">
            <?= buildBreadcrumb() ?>
        </div>
        <?php endif ?>
        
        <div class="uk-section uk-margin-remove uk-padding-remove" uk-height-viewport="expand: true">
            <?= $ctype1?>
        </div>
    </article>
</main>

<!-- Footer identisch mit default Template -->
<footer class="uk-footer uk-section <?= $footerColor?> uk-margin-large-top<?= '1' == $footerInnerShadow ? ' uk-box-shadow-top' : '' ?>" role="contentinfo" style="<?= '1' == $footerInnerShadow ? 'box-shadow: inset 0 10px 20px -10px rgba(0,0,0,0.1);' : '' ?>">
    <div class="uk-container uk-container-large">
        <?php if ('1' == $footerAlternativeLayout): ?>
            <!-- Alternatives Footer-Layout: Nur Impressum, Datenschutz, Kontakt zentriert -->
            <nav class="uk-text-center uk-margin-medium-bottom" aria-label="Footer Navigation">
                <ul class="uk-subnav uk-subnav-divider uk-flex-center">
                    <?php if (!empty($footerImpressumLink)): ?>
                        <li><a href="<?= rex_getUrl($footerImpressumLink) ?>"><?= htmlspecialchars(rex_article::get($footerImpressumLink)->getName() ?? 'Impressum') ?></a></li>
                    <?php endif ?>
                    <?php if (!empty($footerDatenschutzLink)): ?>
                        <li><a href="<?= rex_getUrl($footerDatenschutzLink) ?>"><?= htmlspecialchars(rex_article::get($footerDatenschutzLink)->getName() ?? 'Datenschutz') ?></a></li>
                    <?php endif ?>
                    <?php if (!empty($footerKontaktLink)): ?>
                        <li><a href="<?= rex_getUrl($footerKontaktLink) ?>"><?= htmlspecialchars(rex_article::get($footerKontaktLink)->getName() ?? 'Kontakt') ?></a></li>
                    <?php endif ?>
                    <?php if ('1' == $footerShowPrivacySettings): ?>
                        <li><a href="#" class="consent_manager-show-box">Datenschutzeinstellungen</a></li>
                    <?php endif ?>
                </ul>
            </nav>
        <?php else: ?>
            <!-- Standard Footer-Layout: 3-Spalten -->
            <nav class="uk-grid-medium uk-child-width-1-3@m uk-grid" uk-grid="" aria-label="Footer Navigation">
                
                <?php if (!empty($section1Title) || !empty($section1Links)): ?>
                <section class="uk-first-column">
                    <?php if (!empty($section1Title)): ?>
                        <h2 class="uk-h4"><?= htmlspecialchars($section1Title) ?></h2>
                    <?php endif ?>
                    
                    <?php if (!empty($section1Links)): ?>
                        <ul class="uk-list">
                            <?= $section1Links ?>
                        </ul>
                    <?php endif ?>
                    
                    <?php if ('1' == $footerShowPrivacySettings): ?>
                        <ul class="uk-list">
                            <li><a href="#" class="consent_manager-show-box">Datenschutzeinstellungen</a></li>
                        </ul>
                    <?php endif ?>
                </section>
                <?php endif ?>
                
                <?php if (!empty($section2Title) || !empty($section2Links)): ?>
                <section>
                    <?php if (!empty($section2Title)): ?>
                        <h2 class="uk-h4"><?= htmlspecialchars($section2Title) ?></h2>
                    <?php endif ?>
                    
                    <?php if (!empty($section2Links)): ?>
                        <ul class="uk-list">
                            <?= $section2Links ?>
                        </ul>
                    <?php endif ?>
                </section>
                <?php endif ?>
                
                <?php if (!empty($section3Title) || !empty($section3Text)): ?>
                <section>
                    <?php if (!empty($section3Title)): ?>
                        <h2 class="uk-h4"><?= htmlspecialchars($section3Title) ?></h2>
                    <?php endif ?>
                    
                    <?php if (!empty($section3Text)): ?>
                        <address class="uk-text-small uk-not-italic">
                            <?= $section3Text ?>
                        </address>
                    <?php endif ?>
                </section>
                <?php endif ?>
                
            </nav>
        <?php endif ?>
        
        <hr class="uk-margin-medium">
        
        <div class="uk-text-center">
            <?php if (!empty($slogan)): ?>
                <p class="uk-margin-small">
                    <?= htmlspecialchars($slogan) ?>
                </p>
            <?php endif ?>
            
            <!-- Social Media Links -->
            <?php
            $socialLinksJson = TemplateManager::get('tm_social_links');
            $socialLinks = $socialLinksJson ? json_decode($socialLinksJson, true) : [];
            if (!empty($socialLinks)):
            ?>
            <div class="uk-margin-medium">
                <ul class="uk-iconnav uk-flex-center">
                    <?php foreach ($socialLinks as $link):
                        if (empty($link['url'])) {
                        continue;
                        }
                        $icon = $link['icon'] ?? '';
                        $url = $link['url'];
                        $label = $link['label'] ?? '';

                        // UIKit Icon extrahieren (uk-icon-facebook -> facebook)
                        $ukIcon = str_replace('uk-icon-', '', $icon);
                        // Falls Font Awesome Icon (fa-facebook -> facebook)
                        if (str_starts_with($icon, 'fa-')) {
                            $ukIcon = str_replace('fa-', '', $icon);
                        }
                    ?>
                    <li>
                        <a href="<?= rex_escape($url) ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           uk-icon="icon: <?= rex_escape($ukIcon) ?>; ratio: 1.2"
                           <?= $label ? 'title="' . rex_escape($label) . '" uk-tooltip' : '' ?>>
                        </a>
                    </li>
                    <?php endforeach ?>
                </ul>
            </div>
            <?php endif ?>
            
            <!-- Footer Logo (wiederverwendet aus Header) -->
            <?php if ('1' == $footerLogoEnabled): ?>
            <div class="uk-margin-medium">
                <!-- Desktop Logo -->
                <img src="<?= rex_url::media($logoMedia) ?>" 
                     alt="<?= htmlspecialchars($logoAltText) ?>" 
                     class="uk-responsive-height uk-opacity-medium uk-visible@<?= htmlspecialchars($logoBreakpoint) ?>"
                     height="60" style="height: 130px; width: auto;"/>
                <!-- Mobile Logo -->
                <img src="<?= rex_url::media($footerMobileLogo) ?>" 
                     alt="<?= htmlspecialchars($logoAltText) ?>" 
                     class="uk-responsive-height uk-opacity-medium uk-hidden@<?= htmlspecialchars($logoBreakpoint) ?>"
                     height="60" style="height: 80px; width: auto;"/>
            </div>
            <?php endif ?>
            
            <p class="uk-text-small uk-text-muted">
                © <?= date('Y') ?> <?= htmlspecialchars($firma) ?> - Alle Rechte vorbehalten
            </p>
        </div>
        
    </div>
</footer>

<!-- UIKit Theme Builder - JavaScript -->
<?= TemplateHelper::includeAllJS() ?>
<?php
// JavaScript einbinden
echo '<script src="' . rex_url::addonAssets('vidstack', 'vidstack.js') . '"></script>';
echo '<script src="' . rex_url::addonAssets('vidstack', 'vidstack_helper.js') . '"></script>';
?>                     
</body>
</html>

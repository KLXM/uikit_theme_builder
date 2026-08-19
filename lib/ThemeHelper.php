<?php

namespace UikitThemeBuilder;

/**
 * Theme Helper Utilities
 * Gemeinsame Funktionen für Theme-Verwaltung und UIKit-Integration
 */
class ThemeHelper
{
    private static $outputFilterRegistered = false;

    /**
     * Automatische Textfarben-Bestimmung basierend auf Hintergrundfarbe
     * 
     * Berechnet die Helligkeit einer Farbe und gibt die passende Textfarbe zurück
     * 
     * @param string $bgColor Hintergrundfarbe (#RRGGBB oder #RGB oder Farb-Klasse)
     * @return string 'uk-light' für dunkle Hintergründe, '' für helle
     */
    public static function getTextColorForBackground($bgColor)
    {
        // Bekannte dunkle UIKit-Klassen
        $darkClasses = [
            'uk-card-primary',
            'uk-background-primary',
            'uk-background-secondary',
        ];
        
        // Wenn es eine Klasse ist, prüfen ob dunkel
        if (strpos($bgColor, 'uk-') === 0) {
            return in_array($bgColor, $darkClasses) ? 'uk-light' : '';
        }
        
        // Hex-Farbe normalisieren
        $bgColor = trim($bgColor);
        if (strpos($bgColor, '#') !== 0) {
            return ''; // Fallback zu dunkel (Standard)
        }
        
        $hex = str_replace('#', '', $bgColor);
        
        // Shorthand (#RGB) zu Longhand (#RRGGBB) konvertieren
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        if (strlen($hex) !== 6) {
            return ''; // Fallback
        }
        
        // RGB-Werte extrahieren
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Relative Luminanz berechnen (WCAG 2.0 Formel)
        // https://www.w3.org/TR/WCAG20-TECHS/G17.html
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
        // Bei dunklem Hintergrund (< 0.5) hellen Text verwenden
        return $luminance < 0.5 ? 'uk-light' : '';
    }
    
    /**
     * Debug-Ausgabe für Backend
     * 
     * @param mixed $data Daten zum Ausgeben
     * @param string $label Optional: Label für die Ausgabe
     * @return string HTML für Backend-Debug-Ausgabe
     */
    public static function debugOutput($data, string $label = 'Debug'): string
    {
        if (!\rex::isBackend() || !\rex::isDebugMode()) {
            return '';
        }
        
        return '<div class="uk-alert uk-alert-primary" style="margin: 1rem 0;">
            <h5>' . htmlspecialchars($label) . '</h5>
            <pre style="background: white; padding: 1rem; overflow: auto;">' 
            . htmlspecialchars(print_r($data, true)) . 
            '</pre>
        </div>';
    }
    
    /**
     * Extrahiert die Hauptfarben aus Theme-Daten
     * 
     * @param UikitThemeBuilderManager $themeManager Theme-Manager Instanz
     * @param string $themeName Theme-Name
     * @return array Assoziatives Array mit Haupt-Farben
     */
    public static function getThemeColors(UikitThemeBuilderManager $themeManager, string $themeName): array
    {
        $theme = $themeManager->loadTheme($themeName);
        if (!$theme || !isset($theme['data']['colors'])) {
            return [
                'primary' => '#1e87f0',
                'secondary' => '#222',
                'success' => '#32d296',
                'warning' => '#faa05a',
                'danger' => '#f0506e'
            ];
        }
        
        $colors = $theme['data']['colors'];
        return [
            'primary' => $colors['global-primary-background'] ?? '#1e87f0',
            'secondary' => $colors['global-secondary-background'] ?? '#222',
            'success' => $colors['global-success-background'] ?? '#32d296',
            'warning' => $colors['global-warning-background'] ?? '#faa05a',
            'danger' => $colors['global-danger-background'] ?? '#f0506e'
        ];
    }
    
    /**
     * Custom Divider rendern
     * 
     * @param string $style Divider-Style (dots, wave, diamond)
     * @param string $colorClass Optional: Farb-Klasse (default: uk-text-primary)
     * @return string HTML für Divider
     */
    public static function renderCustomDivider(string $style = 'dots', string $colorClass = 'uk-text-primary'): string
    {
        $dividerClass = "uk-divider-{$style}";
        return "<hr class=\"{$dividerClass} {$colorClass}\" />";
    }
    
    /**
     * Backend UIKit Scope Wrapper
     * Gibt uk-scope Wrapper für Backend zurück, leer für Frontend
     * 
     * @param bool $opening true für öffnenden Tag, false für schließenden
     * @return string HTML-Wrapper oder leer
     */
    public static function backendWrapper(bool $opening = true): string
    {
        if (\rex::isBackend()) {
            return $opening ? '<div class="uk-scope">' : '</div>';
        }
        return '';
    }
    
    /**
     * Medien-Content-Rendering für verschiedene Display-Modi
     * Rendert Bilder/Videos in verschiedenen Layouts (Masonry, Grid, Slideshow)
     * 
     * @param string $mediaList Komma-getrennte Medien-Liste
     * @param string $multiMediaDisplay Display-Modus (masonry, grid, slideshow)
     * @param string $caption Untertitel
     * @param string $badgeText Badge-Text
     * @param bool $enableLightbox Lightbox aktivieren
     * @param string $textColorClass Textfarbe-Klasse (uk-light oder leer)
     * @param string $layout Layout-Typ für Badge-Position
     * @return string HTML für Medien-Content
     */
    public static function renderMediaContent(
        string $mediaList, 
        string $multiMediaDisplay, 
        string $caption = '', 
        string $badgeText = '', 
        bool $enableLightbox = true, 
        string $textColorClass = '', 
        string $layout = 'text-left-media-right',
        array $slideshowOptions = []
    ): string
    {
        if (empty($mediaList)) {
            return '';
        }
        
        $mediaFiles = array_filter(array_map('trim', explode(',', $mediaList)));
        if (empty($mediaFiles)) {
            return '';
        }
        
        $output = '<div class="uk-position-relative">';
        
        // Badge falls vorhanden
        if (!empty($badgeText)) {
            $badgePosition = ($layout === 'media-left-text-right') ? 'left' : 'right';
            $positionClass = $badgePosition === 'left' ? 'uk-position-top-left' : 'uk-position-top-right';
            $transform = $badgePosition === 'left' ? 
                'rotate(-12deg) translate(-8px, -8px)' : 
                'rotate(12deg) translate(8px, -8px)';
            
            $output .= '<div class="uk-position-absolute ' . $positionClass . '" style="z-index: 10;">
                <div class="uk-text-handwriting uk-padding-small uk-text-bold" 
                     style="background-color: rgba(245, 240, 232, 0.9); 
                            color: #333; 
                            transform: ' . $transform . '; 
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                            border-radius: 4px;
                            font-size: 1.1rem;">
                    ' . htmlspecialchars($badgeText) . '
                </div>
            </div>';
        }
        
        if (count($mediaFiles) === 1) {
            // Einzelmedium
            $mediaFile = $mediaFiles[0];
            $media = \rex_media::get($mediaFile);
            $altText = $media ? $media->getValue('title') : '';
            $isVideo = self::isVideoFile($mediaFile);
            
            if ($enableLightbox) {
                $output .= '<div uk-lightbox>';
            }
            
            if ($isVideo) {
                $videoUrl = \rex_url::media($mediaFile);
                $output .= '<a href="' . $videoUrl . '" data-type="video" class="uk-inline uk-transition-toggle" tabindex="0">
                    <video class="uk-width-1-1 uk-border-rounded uk-transition-scale-up uk-transition-opaque" muted playsinline>
                        <source src="' . $videoUrl . '" type="video/' . pathinfo($mediaFile, PATHINFO_EXTENSION) . '">
                    </video>
                    <div class="uk-position-center uk-transition-fade uk-light">
                        <div class="uk-background-primary uk-border-circle uk-padding-small">
                            <span uk-icon="icon: play; ratio: 2"></span>
                        </div>
                    </div>
                </a>';
            } else {
                $imageUrl = \rex_media_manager::getUrl('image_single', $mediaFile);
                $fullUrl = \rex_media_manager::getUrl('image_full', $mediaFile);
                $output .= '<a href="' . $fullUrl . '" class="uk-inline uk-transition-toggle" tabindex="0">
                    <img src="' . $imageUrl . '" alt="' . htmlspecialchars($altText) . '" 
                         class="uk-width-1-1 uk-border-rounded uk-transition-scale-up uk-transition-opaque">
                    <div class="uk-position-center uk-transition-fade uk-light">
                        <div class="uk-background-primary uk-border-circle uk-padding-small">
                            <span uk-icon="icon: search; ratio: 1.5"></span>
                        </div>
                    </div>
                </a>';
            }
            
            if ($enableLightbox) {
                $output .= '</div>';
            }
        } else {
            // Mehrere Medien - Grid/Masonry/Slideshow
            $output .= self::renderMultiMedia($mediaFiles, $multiMediaDisplay, $layout, $slideshowOptions);
        }
        
        $output .= '</div>';
        
        // Caption
        if (!empty($caption)) {
            $captionClass = $textColorClass === 'uk-light' ? 'uk-light' : 'uk-text-muted';
            $output .= '<p class="uk-text-default ' . $captionClass . ' uk-margin-small-top uk-text-center">' 
                     . htmlspecialchars($caption) . '</p>';
        }
        
        return $output;
    }
    
    /**
     * Prüft ob Datei ein Video ist
     * 
     * @param string $filename Dateiname
     * @return bool True wenn Video
     */
    private static function isVideoFile(string $filename): bool
    {
        $videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $videoExtensions);
    }
    
    /**
     * Rendert mehrere Medien in verschiedenen Layouts
     * 
     * @param array $mediaFiles Array mit Medien-Dateinamen
     * @param string $displayMode Display-Modus (masonry, grid, slideshow, etc.)
     * @param string $layout Layout für Slideshow-Höhe
     * @param array $options Zusätzliche Optionen (autoplay, animation, ratio)
     * @return string HTML
     */
    private static function renderMultiMedia(array $mediaFiles, string $displayMode, string $layout, array $options = []): string
    {
        switch ($displayMode) {
            case 'grid':
                return self::renderGrid($mediaFiles);
            case 'slideshow':
                return self::renderSlideshow($mediaFiles, $layout, $options);
            case 'slideshow-gallery':
                $options['variant'] = 0;
                return self::renderSlideshowGallery($mediaFiles, $layout, $options);
            case 'slideshow-gallery-left':
                $options['variant'] = 1;
                return self::renderSlideshowGallery($mediaFiles, $layout, $options);
            case 'slideshow-gallery-right':
                $options['variant'] = 2;
                return self::renderSlideshowGallery($mediaFiles, $layout, $options);
            case 'slideshow-gallery-bottom-scroll':
                $options['variant'] = 3;
                return self::renderSlideshowGallery($mediaFiles, $layout, $options);
            case 'masonry':
            default:
                return self::renderMasonry($mediaFiles);
        }
    }
    
    /**
     * Rendert Masonry-Galerie
     */
    private static function renderMasonry(array $mediaFiles): string
    {
        $output = '<div class="uk-child-width-1-2@s uk-child-width-1-3@m" uk-grid="masonry: true" uk-lightbox="animation: slide">';
        
        foreach ($mediaFiles as $mediaFile) {
            $media = \rex_media::get($mediaFile);
            $altText = $media ? $media->getValue('title') : '';
            $isVideo = self::isVideoFile($mediaFile);
            
            if ($isVideo) {
                $videoUrl = \rex_url::media($mediaFile);
                $output .= '<div><a href="' . $videoUrl . '" data-type="video">
                    <video class="uk-width-1-1 uk-border-rounded" autoplay muted loop playsinline>
                        <source src="' . $videoUrl . '" type="video/' . pathinfo($mediaFile, PATHINFO_EXTENSION) . '">
                    </video>
                </a></div>';
            } else {
                $output .= '<div><a href="' . \rex_media_manager::getUrl('image_full', $mediaFile) . '">
                    <img src="' . \rex_media_manager::getUrl('image_masonry', $mediaFile) . '" 
                         alt="' . htmlspecialchars($altText) . '" class="uk-width-1-1 uk-border-rounded">
                </a></div>';
            }
        }
        
        $output .= '</div>';
        return $output;
    }
    
    /**
     * Rendert Grid-Galerie
     */
    private static function renderGrid(array $mediaFiles): string
    {
        $output = '<div class="uk-child-width-1-3@s uk-child-width-1-4@m uk-grid-small" uk-grid uk-lightbox="animation: slide">';
        
        foreach ($mediaFiles as $mediaFile) {
            $media = \rex_media::get($mediaFile);
            $altText = $media ? $media->getValue('title') : '';
            $isVideo = self::isVideoFile($mediaFile);
            
            if ($isVideo) {
                $videoUrl = \rex_url::media($mediaFile);
                $output .= '<div><a href="' . $videoUrl . '" data-type="video" class="uk-display-block">
                    <video class="uk-width-1-1 uk-border-rounded" autoplay muted loop playsinline>
                        <source src="' . $videoUrl . '" type="video/' . pathinfo($mediaFile, PATHINFO_EXTENSION) . '">
                    </video>
                </a></div>';
            } else {
                $output .= '<div><a href="' . \rex_media_manager::getUrl('image_full', $mediaFile) . '" class="uk-display-block">
                    <img src="' . \rex_media_manager::getUrl('image_thumb', $mediaFile) . '" 
                         alt="' . htmlspecialchars($altText) . '" class="uk-width-1-1 uk-border-rounded">
                </a></div>';
            }
        }
        
        $output .= '</div>';
        return $output;
    }
    
    /**
     * Helper um Slideshow-Einstellungen zu generieren
     */
    private static function getSlideshowAttributes(array $options, string $layout): string
    {
        $settings = [];
        
        // Animation
        $animation = $options['animation'] ?? 'slide'; // slide, fade, scale, pull, push
        $settings[] = 'animation: ' . $animation;
        
        // Autoplay
        if (!empty($options['autoplay']) && $options['autoplay'] === true) {
            $interval = $options['autoplay_interval'] ?? '6000';
            $settings[] = 'autoplay: true';
            $settings[] = 'autoplay-interval: ' . (int)$interval;
            $settings[] = 'pause-on-hover: true';
        }
        
        // Ratio/Height
        if (isset($options['ratio']) && !empty($options['ratio'])) {
            $settings[] = 'ratio: ' . $options['ratio'];
        } elseif ($layout !== 'media-only') {
            $settings[] = 'min-height: 300';
            $settings[] = 'max-height: 600';
        }
        
        return implode('; ', $settings);
    }
    
    /**
     * Rendert Standard-Slideshow
     */
    private static function renderSlideshow(array $mediaFiles, string $layout, array $options = []): string
    {
        $attributes = self::getSlideshowAttributes($options, $layout);
        
        $output = '<div uk-slideshow="' . $attributes . '" uk-lightbox="animation: slide">';
        $output .= '<div class="uk-position-relative uk-visible-toggle" tabindex="-1">';
        $output .= '<ul class="uk-slideshow-items">';
        
        foreach ($mediaFiles as $mediaFile) {
            $output .= self::renderSlideshowItem($mediaFile, $layout);
        }
        
        $output .= '</ul>';
        $output .= '<a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#" uk-slidenav-previous uk-slideshow-item="previous"></a>';
        $output .= '<a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#" uk-slidenav-next uk-slideshow-item="next"></a>';
        $output .= '</div>';
        
        if (count($mediaFiles) > 1) {
            $output .= '<ul class="uk-slideshow-nav uk-dotnav uk-flex-center uk-margin"></ul>';
        }
        
        $output .= '</div>';
        return $output;
    }




    




    private static function getGalleryCss(): string
    {
        return '<style>
        :root { --thumb-width: 120px; --thumb-height: 80px; }
        
        /* Wrapper: Light transparency & Shadow */
        .slideshow-wrapper { 
            position: relative; 
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.5); 
            padding: 15px; 
            border-radius: 12px; 
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        /* Core Slideshow Styles */
        .uk-slideshow-items { min-height: 300px; border-radius: 8px; overflow: hidden; background: rgba(255, 255, 255, 0.25); }
        .uk-slideshow-items video { width: 100%; height: 100%; object-fit: cover; }
        .uk-slidenav { background: rgba(0,0,0,0.4); color: white; padding: 15px; border-radius: 4px; }
        .uk-slidenav:hover { background: rgba(0,0,0,0.7); color: white; }
        
        /* Scrollbar */
        .thumb-nav::-webkit-scrollbar { width: 6px; height: 6px; }
        .thumb-nav::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
        .thumb-nav::-webkit-scrollbar-thumb { background: #888; border-radius: 3px; }
        .thumb-nav::-webkit-scrollbar-thumb:hover { background: #555; }

        /* Layouts */
        .slideshow-thumbs-left, .slideshow-thumbs-right { display: flex; gap: 15px; align-items: flex-start; }
        .slideshow-thumbs-left { flex-direction: row; }
        .slideshow-thumbs-right { flex-direction: row-reverse; }
        .slideshow-thumbs-left .thumb-nav, .slideshow-thumbs-right .thumb-nav { width: var(--thumb-width); flex-shrink: 0; overflow-y: auto; overflow-x: hidden; scroll-behavior: smooth; }
        .slideshow-thumbs-left .slideshow-main, .slideshow-thumbs-right .slideshow-main { flex-grow: 1; min-width: 0; }
        .slideshow-thumbs-left .thumb-nav .thumb-item, .slideshow-thumbs-right .thumb-nav .thumb-item { margin-bottom: 10px; }
        .slideshow-thumbs-left .thumb-nav .thumb-item:last-child, .slideshow-thumbs-right .thumb-nav .thumb-item:last-child { margin-bottom: 0; }
        
        .slideshow-thumbs-bottom-scroll .thumb-nav { overflow-x: auto; overflow-y: hidden; white-space: nowrap; scroll-behavior: smooth; padding: 15px 0; }
        .slideshow-thumbs-bottom-scroll .thumb-nav .thumb-list { display: inline-flex; gap: 10px; }
        .slideshow-thumbs-bottom .thumb-nav { margin-top: 15px; }
        .slideshow-thumbs-bottom .thumb-list { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
        
        /* Thumb Item */
        .thumb-item { cursor: pointer; opacity: 0.6; transition: all 0.3s ease; border: 3px solid transparent; overflow: hidden; border-radius: 4px; position: relative; padding: 0; }
        .thumb-item:hover { opacity: 0.9; transform: scale(1.05); }
        .thumb-item.uk-active { opacity: 1; border-color: #1e87f0; box-shadow: 0 0 15px rgba(30, 135, 240, 0.4); }
        .thumb-item img { display: block; width: 100%; height: var(--thumb-height); object-fit: cover; margin: 0; }
        .thumb-item.thumb-video::before { content: ""; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 30px; height: 30px; background: rgba(0,0,0,0.6); border-radius: 50%; z-index: 1; }
        .thumb-item.thumb-video::after { content: ""; position: absolute; top: 50%; left: 50%; transform: translate(-45%, -50%); width: 0; height: 0; border-style: solid; border-width: 8px 0 8px 12px; border-color: transparent transparent transparent white; z-index: 2; }
        
        @media (max-width: 768px) {
            /* Mobile: Full width slideshow */
            .slideshow-wrapper { 
                padding: 15px; 
                margin-bottom: 30px;
                margin-left: auto;
                margin-right: auto;
                /* Background and Shadow are inherited from base style */
                border-radius: 8px;
            }
            .uk-slideshow-items { 
                border-radius: 4px; 
                max-height: none !important;
            }
            
            /* Adjust image sizing on mobile */
            .uk-slideshow-items img,
            .uk-slideshow-items video { 
                height: auto !important;
                max-height: 400px !important;
                max-width: 100% !important;
                object-fit: contain !important;
            }
            
            /* Thumbs need some breathing room */
            .thumb-nav { padding-left: 0; padding-right: 0; }
            
            :root { --thumb-width: 100px; --thumb-height: 66px; }
            .slideshow-thumbs-left, .slideshow-thumbs-right { flex-direction: column; }
            .slideshow-thumbs-left .thumb-nav, .slideshow-thumbs-right .thumb-nav { width: 100%; height: auto !important; max-height: none !important; overflow-x: auto; overflow-y: hidden; order: 2; margin-top: 15px; display: flex; gap: 10px; padding-bottom: 10px; }
            .slideshow-thumbs-left .slideshow-main, .slideshow-thumbs-right .slideshow-main { order: 1; width: 100% !important; }
            .slideshow-thumbs-left .thumb-nav .thumb-item, .slideshow-thumbs-right .thumb-nav .thumb-item { margin-bottom: 0; flex-shrink: 0; width: var(--thumb-width); }
            
            /* Ensure slideshow-main takes full width in all gallery variants */
            .slideshow-thumbs-bottom .slideshow-main,
            .slideshow-thumbs-bottom-scroll .slideshow-main { width: 100% !important; }
        }
        
        @media (max-width: 480px) {
            :root { --thumb-width: 80px; --thumb-height: 53px; }
            .thumb-item.thumb-video::before { width: 24px; height: 24px; }
            .thumb-item.thumb-video::after { border-width: 6px 0 6px 10px; }
        }
        </style>';
    }

    private static function getGalleryScript(): string
    {
        return '<script>
        (function() {
            function initSlideshows() {
                if (typeof UIkit === "undefined") { setTimeout(initSlideshows, 100); return; }
                var wrappers = document.querySelectorAll(".slideshow-wrapper");
                wrappers.forEach(function(wrapper) {
                    if (wrapper.getAttribute("data-initialized")) return;
                    wrapper.setAttribute("data-initialized", "true");
                    
                    var variant = parseInt(wrapper.getAttribute("data-variant") || "0", 10);
                    var slideshowEl = wrapper.querySelector("[uk-slideshow]");
                    if (!slideshowEl) return;
                    
                    var thumbItems = wrapper.querySelectorAll(".thumb-item");
                    var thumbNav = wrapper.querySelector(".thumb-nav");
                    
                    function syncThumbNavHeight() {
                        if ((variant === 1 || variant === 2) && window.innerWidth > 768) {
                            var slideshowItems = slideshowEl.querySelector(".uk-slideshow-items");
                            if (slideshowItems && thumbNav) {
                                var height = slideshowItems.offsetHeight;
                                if (height > 0) {
                                    thumbNav.style.height = height + "px";
                                    thumbNav.style.maxHeight = height + "px";
                                }
                            }
                        } else if (thumbNav) {
                            thumbNav.style.height = "";
                            thumbNav.style.maxHeight = "";
                        }
                    }
                    
                    setTimeout(syncThumbNavHeight, 300);
                    window.addEventListener("resize", syncThumbNavHeight);
                    UIkit.util.on(slideshowEl, "itemshown", syncThumbNavHeight);

                    var videoElements = slideshowEl.querySelectorAll("video");
                    function pauseAllVideos() {
                        videoElements.forEach(function(vid) { vid.pause(); });
                    }

                    UIkit.util.on(slideshowEl, "itemshow", function(e) {
                         var index = UIkit.slideshow(slideshowEl).index;
                         thumbItems.forEach(function(item, i) {
                             item.classList.toggle("uk-active", i === index);
                             if (i === index && (variant === 3 || variant === 1 || variant === 2)) {
                                 try {
                                     // Scroll within thumb nav without affecting page scroll
                                     if (thumbNav) {
                                         var itemLeft = item.offsetLeft;
                                         var itemWidth = item.offsetWidth;
                                         var navWidth = thumbNav.offsetWidth;
                                         var navScrollLeft = thumbNav.scrollLeft;
                                         
                                         // Scroll if item is not fully visible
                                         if (itemLeft < navScrollLeft) {
                                             thumbNav.scrollLeft = itemLeft - 10; // 10px padding
                                         } else if (itemLeft + itemWidth > navScrollLeft + navWidth) {
                                             thumbNav.scrollLeft = itemLeft + itemWidth - navWidth + 10;
                                         }
                                     }
                                 } catch(e){}
                             }
                         });
                         
                         var slide = e.target;
                         var video = slide.querySelector("video");
                         if (video) {
                             video.currentTime = 0;
                             video.play().catch(function(){}); 
                         }
                    });
                    
                    UIkit.util.on(slideshowEl, "itemhide", function(e) {
                        var slide = e.target;
                        var video = slide.querySelector("video");
                        if (video) video.pause();
                    });

                    thumbItems.forEach(function(thumb) {
                        var index = parseInt(thumb.getAttribute("data-slide-index") || "0", 10);
                        thumb.addEventListener("click", function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            pauseAllVideos();
                            UIkit.slideshow(slideshowEl).show(index);
                        });
                        thumb.addEventListener("keydown", function(e) {
                            if (e.key === "Enter" || e.key === " ") {
                                e.preventDefault();
                                pauseAllVideos();
                                UIkit.slideshow(slideshowEl).show(index);
                            }
                        });
                    });
                });
            }
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", initSlideshows);
            } else {
                initSlideshows();
            }
        })();
        </script>';
    }

    /**
     * Gallery View - All Variants
     */
    private static function renderSlideshowGallery(array $mediaFiles, string $layout, array $options = []): string
    {
        $variant = (int)($options['variant'] ?? 0);
        $slideshowId = 'slideshow-' . uniqid();
        
        // Inject JS/CSS via Output Filter if NOT registered
        if (!self::$outputFilterRegistered) {
            $css = self::getGalleryCss();
            $js = self::getGalleryScript();
            
            \rex_extension::register('OUTPUT_FILTER', function(\rex_extension_point $ep) use ($css, $js) {
                $content = $ep->getSubject();
                // Inject CSS before </head> or body
                $content = str_replace('</head>', $css . '</head>', $content);
                // Inject JS before </body>
                $content = str_replace('</body>', $js . '</body>', $content);
                return $content;
            });
            self::$outputFilterRegistered = true;
        }

        $wrapperClasses = [
            0 => 'slideshow-thumbs-bottom',
            1 => 'slideshow-thumbs-left',
            2 => 'slideshow-thumbs-right',
            3 => 'slideshow-thumbs-bottom-scroll'
        ];
        $wrapperClass = $wrapperClasses[$variant] ?? 'slideshow-thumbs-bottom';

        $output = '<div class="slideshow-wrapper ' . $wrapperClass . '" id="' . $slideshowId . '-wrapper" data-variant="' . $variant . '">';
        
        // Thumbs HTML generation
        $thumbsContent = '';
        foreach ($mediaFiles as $i => $mediaFile) {
            $media = \rex_media::get($mediaFile);
            $thumbUrl = \rex_media_manager::getUrl('image_thumb', $mediaFile);
            $title = $media ? $media->getValue('title') : '';
            $isVideo = self::isVideoFile($mediaFile);
            $videoClass = $isVideo ? ' thumb-video' : '';
            
            $tooltipAttr = !empty($title) ? ' title="' . htmlspecialchars($title) . '" uk-tooltip' : '';
            $thumbsContent .= '<div class="thumb-item' . $videoClass . ($i === 0 ? ' uk-active' : '') . '" data-slide-index="' . $i . '" tabindex="0" role="button"' . $tooltipAttr . '>';
            $thumbsContent .= '<img src="' . $thumbUrl . '" alt="' . htmlspecialchars($title) . '">';
            $thumbsContent .= '</div>';
        }

        if ($variant === 0 || $variant === 3) {
            $thumbsHtml = '<nav class="thumb-nav"><div class="thumb-list">' . $thumbsContent . '</div></nav>';
        } else {
            $thumbsHtml = '<nav class="thumb-nav">' . $thumbsContent . '</nav>';
        }

        // Slideshow HTML
        $slideAttributes = self::getSlideshowAttributes($options, $layout);
        $slideshowHtml = '<div class="slideshow-main">';
        $slideshowHtml .= '<div class="uk-position-relative uk-visible-toggle" tabindex="-1" uk-slideshow="' . $slideAttributes . '" uk-lightbox="animation: slide">';
        $slideshowHtml .= '<ul class="uk-slideshow-items">';
        
        foreach ($mediaFiles as $mediaFile) {
            $slideshowHtml .= self::renderSlideshowItem($mediaFile, $layout);
        }
        
        $slideshowHtml .= '</ul>';
        $slideshowHtml .= '<a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#" uk-slidenav-previous uk-slideshow-item="previous"></a>';
        $slideshowHtml .= '<a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#" uk-slidenav-next uk-slideshow-item="next"></a>';
        $slideshowHtml .= '</div></div>';

        // Order
        if ($variant === 1 || $variant === 2) {
            // Left/Right: Nav then Main
            $output .= $thumbsHtml;
            $output .= $slideshowHtml;
        } else {
            // Bottom: Main then Nav
            $output .= $slideshowHtml;
            $output .= $thumbsHtml;
        }

        $output .= '</div>'; // End Wrapper
        
        return $output;
    }

    /**
     * Helper to render a single slideshow item
     */
    private static function renderSlideshowItem(string $mediaFile, string $layout, string $customStyle = ''): string
    {
        $media = \rex_media::get($mediaFile);
        $altText = $media ? $media->getValue('title') : '';
        $isVideo = self::isVideoFile($mediaFile);
        $mediaStyle = $customStyle;
        
        if (empty($mediaStyle)) {
             $mediaStyle = $layout === 'media-only' ? 
                'width: 100%; height: auto; object-fit: contain;' : 
                'max-height: 600px; object-fit: contain;';
        }
        
        $output = '';
        $captionAttr = !empty($altText) ? ' data-caption="' . htmlspecialchars($altText) . '"' : '';
        $captionOverlay = !empty($altText) ? '<div class="uk-overlay uk-overlay-primary uk-position-bottom uk-text-center uk-transition-slide-bottom uk-padding-small">
            <p class="uk-margin-remove">' . htmlspecialchars($altText) . '</p>
        </div>' : '';

        if ($isVideo) {
            $videoUrl = \rex_url::media($mediaFile);
            $output .= '<li><a href="' . $videoUrl . '" data-type="video" class="uk-inline uk-transition-toggle uk-width-1-1 uk-height-1-1" tabindex="0"' . $captionAttr . '>
                <video class="uk-width-1-1 uk-height-1-1" 
                       autoplay muted loop playsinline style="' . $mediaStyle . '">
                    <source src="' . $videoUrl . '" type="video/' . pathinfo($mediaFile, PATHINFO_EXTENSION) . '">
                </video>
                <div class="uk-position-center uk-transition-fade uk-light">
                    <div class="uk-background-primary uk-border-circle uk-padding-small">
                        <span uk-icon="icon: play; ratio: 2"></span>
                    </div>
                </div>
                ' . $captionOverlay . '
            </a></li>';
        } else {
             $output .= '<li><a href="' . \rex_media_manager::getUrl('image_full', $mediaFile) . '" 
                          class="uk-inline uk-transition-toggle uk-visible-toggle uk-width-1-1 uk-height-1-1" tabindex="0"' . $captionAttr . '>
                <img src="' . \rex_media_manager::getUrl('image_single', $mediaFile) . '" 
                     alt="' . htmlspecialchars($altText) . '" 
                     class="uk-width-1-1 uk-border-rounded" 
                     style="' . $mediaStyle . '" uk-cover>
                <div class="uk-position-top-right uk-position-small uk-hidden-hover">
                    <div class="uk-background-default uk-border-circle uk-flex uk-flex-center uk-flex-middle uk-box-shadow-small" style="width: 40px; height: 40px; color: #333;">
                        <span uk-icon="icon: expand; ratio: 1"></span>
                    </div>
                </div>
                ' . $captionOverlay . '
            </a></li>';
        }
        return $output;
    }

}


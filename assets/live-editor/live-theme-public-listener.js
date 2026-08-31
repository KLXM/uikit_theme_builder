/**
 * Live Theme Editor - Listener für normale (nicht eingeloggte) Besucher.
 *
 * Wird von boot.php NUR inline in Seiten eingebunden, solange eine Live-Session aktiv ist
 * (siehe LiveThemeState::flagPath()) UND der Redakteur die "Live schalten"-Checkbox im Editor
 * explizit gesetzt hat (Opt-in, kein automatischer Broadcast). Öffnet den öffentlichen SSE-
 * Stream und wendet empfangene Werte als CSS-Custom-Properties an - die eigentliche visuelle
 * Wirkung kommt von live-theme-editor-bridge.css, die für diesen Besucher dafür ebenfalls
 * (nur währenddessen) mit ausgeliefert wird.
 */
(function () {
  "use strict";

  var script = document.currentScript;
  if (!script) {
    return;
  }

  var streamUrl = script.getAttribute("data-stream-url");
  var themeCssUrlTemplate = script.getAttribute("data-theme-css-template");
  var fontsBaseUrl = script.getAttribute("data-fonts-base-url");
  if (!streamUrl || typeof EventSource === "undefined") {
    return;
  }

  // Muss mit UikitThemeBuilder\LiveThemeState::FIELDS synchron gehalten werden (dort die
  // Quelle der Wahrheit) - hier nur die Kurzschlüssel -> CSS-Custom-Property Zuordnung.
  var FIELD_CSS = {
    primary: "--tb-live-primary",
    secondary: "--tb-live-secondary",
    success: "--tb-live-success",
    warning: "--tb-live-warning",
    danger: "--tb-live-danger",
    background: "--tb-live-background",
    color: "--tb-live-color",
    emphasis: "--tb-live-emphasis",
    muted: "--tb-live-muted",
    link: "--tb-live-link",
    link_hover: "--tb-live-link-hover",
    inverse: "--tb-live-inverse",
    border: "--tb-live-border",
    muted_background: "--tb-live-muted-background",
    heading_color: "--tb-live-heading-color",
    font_size: "--tb-live-font-size",
    line_height: "--tb-live-line-height",
    heading_line_height: "--tb-live-heading-line-height",
    h1_size: "--tb-live-h1-size",
    h2_size: "--tb-live-h2-size",
    h3_size: "--tb-live-h3-size",
    h4_size: "--tb-live-h4-size",
    margin: "--tb-live-margin",
    gutter: "--tb-live-gutter",
    gutter_small: "--tb-live-gutter-small",
    gutter_large: "--tb-live-gutter-large",
    container_width: "--tb-live-container-width",
    container_width_small: "--tb-live-container-width-small",
    container_width_large: "--tb-live-container-width-large",
    font_family: "--tb-live-font-family",
    heading_font_family: "--tb-live-heading-font-family",
    navbar_background: "--tb-live-navbar-background",
    navbar_item_color: "--tb-live-navbar-item-color",
    navbar_item_hover_color: "--tb-live-navbar-item-hover-color",
    navbar_item_height: "--tb-live-navbar-item-height",
    navbar_dropdown_background: "--tb-live-navbar-dropdown-background",
    navbar_dropdown_color: "--tb-live-navbar-dropdown-color",
    navbar_dropdown_hover_color: "--tb-live-navbar-dropdown-hover-color",
    navbar_dropdown_width: "--tb-live-navbar-dropdown-width"
  };

  var SYSTEM_FONTS = [
    "Arial", "Verdana", "Helvetica", "Tahoma", "Trebuchet MS",
    "Times New Roman", "Times", "Georgia", "Garamond",
    "Courier New", "Courier", "Monaco", "Consolas",
    "system-ui", "BlinkMacSystemFont", "-apple-system", "Segoe UI",
    "Roboto", "Ubuntu", "Cantarell", "Helvetica Neue",
    "Palatino", "Bookman", "Comic Sans MS", "Arial Black", "Impact",
    "sans-serif", "serif", "monospace", "cursive", "fantasy"
  ];
  var loadedGoogleFonts = {};

  function sanitizeFontName(fontName) {
    return fontName.replace(/[^a-zA-Z0-9_-]/g, "_");
  }

  // NIE live fonts.googleapis.com kontaktieren - echte Website-Besucher ohne Einwilligung
  // dürfen keine Verbindung zu Drittanbietern bekommen (siehe live-theme-editor.js). Nur lokal
  // bereits heruntergeladene Fonts (GoogleFontsManager); fehlt die Datei, bleibt es beim
  // Fallback-Stack.
  function ensureFontLoaded(fontStack) {
    if (!fontStack || "inherit" === fontStack || !fontsBaseUrl) {
      return;
    }
    var firstFont = fontStack.replace(/['"]/g, "").trim().split(",")[0].trim();
    if (!firstFont || loadedGoogleFonts[firstFont]) {
      return;
    }
    var isSystem = SYSTEM_FONTS.some(function (sf) {
      return sf.toLowerCase() === firstFont.toLowerCase();
    });
    if (isSystem) {
      return;
    }
    loadedGoogleFonts[firstFont] = true;
    var link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = fontsBaseUrl + sanitizeFontName(firstFont) + ".css";
    document.head.appendChild(link);
  }

  function applyThemeSwitch(themeName) {
    if (!themeName || !themeCssUrlTemplate) {
      return;
    }
    var link = document.querySelector('link[href*="themes/compiled/"]');
    if (link) {
      link.href = themeCssUrlTemplate.replace("__THEME__", themeName);
    }
  }

  function apply(values) {
    if (!values || typeof values !== "object") {
      return;
    }
    if (values._switch_theme) {
      applyThemeSwitch(values._switch_theme);
    }
    Object.keys(FIELD_CSS).forEach(function (key) {
      if (Object.prototype.hasOwnProperty.call(values, key)) {
        document.documentElement.style.setProperty(FIELD_CSS[key], values[key]);
        if ("font_family" === key || "heading_font_family" === key) {
          ensureFontLoaded(values[key]);
        }
      }
    });
  }

  var es = new EventSource(streamUrl);

  es.onmessage = function (event) {
    try {
      apply(JSON.parse(event.data));
    } catch (e) {
      /* ignore malformed payload */
    }
  };

  // Server signalisiert per "stop"-Event, dass die Live-Session beendet wurde -
  // danach nicht mehr automatisch neu verbinden (EventSource würde das sonst tun).
  es.addEventListener("stop", function () {
    es.close();
  });
})();

/**
 * Live Theme Editor - Listener für normale (nicht eingeloggte) Besucher.
 *
 * Wird von boot.php NUR inline in Seiten eingebunden, solange eine Live-Session aktiv ist
 * (siehe LiveThemeState::flagPath()). Öffnet den öffentlichen SSE-Stream und wendet
 * empfangene Werte als CSS-Custom-Properties an - die eigentliche visuelle Wirkung kommt
 * von live-theme-editor-bridge.css, die auf jeder Frontend-Seite geladen ist.
 */
(function () {
  "use strict";

  var script = document.currentScript;
  if (!script) {
    return;
  }

  var streamUrl = script.getAttribute("data-stream-url");
  if (!streamUrl || typeof EventSource === "undefined") {
    return;
  }

  var FIELD_CSS = {
    primary: "--tb-live-primary",
    secondary: "--tb-live-secondary",
    background: "--tb-live-background",
    color: "--tb-live-color",
    link: "--tb-live-link",
    font_size: "--tb-live-font-size",
    h1_size: "--tb-live-h1-size",
    h2_size: "--tb-live-h2-size",
    h3_size: "--tb-live-h3-size",
    margin: "--tb-live-margin",
    gutter: "--tb-live-gutter",
    font_family: "--tb-live-font-family",
    heading_font_family: "--tb-live-heading-font-family"
  };

  // Gleiche Technik wie live-theme-editor.js/TypographyWidget: fehlt der Font lokal,
  // per Google Fonts CSS2-API nachladen, sonst bleibt die Property wirkungslos.
  var SYSTEM_FONTS = [
    "Arial", "Verdana", "Helvetica", "Tahoma", "Trebuchet MS",
    "Times New Roman", "Times", "Georgia", "Garamond",
    "Courier New", "Courier", "Monaco", "Consolas",
    "system-ui", "BlinkMacSystemFont", "-apple-system", "Segoe UI",
    "Roboto", "Ubuntu", "Cantarell", "Helvetica Neue", "sans-serif", "serif", "monospace"
  ];
  var loadedGoogleFonts = {};

  function ensureFontLoaded(fontStack) {
    if (!fontStack || "inherit" === fontStack) {
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
    link.href = "https://fonts.googleapis.com/css2?family=" + encodeURIComponent(firstFont) + "&display=swap";
    document.head.appendChild(link);
  }

  function apply(values) {
    if (!values || typeof values !== "object") {
      return;
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

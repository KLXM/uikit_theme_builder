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
    h3_size: "--tb-live-h3-size"
  };

  function apply(values) {
    if (!values || typeof values !== "object") {
      return;
    }
    Object.keys(FIELD_CSS).forEach(function (key) {
      if (Object.prototype.hasOwnProperty.call(values, key)) {
        document.documentElement.style.setProperty(FIELD_CSS[key], values[key]);
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

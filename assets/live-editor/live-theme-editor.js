/**
 * Live Theme Editor - Overlay-UI für den eingeloggten Redakteur (siehe
 * LiveThemeEditorWidget::render()). Änderungen werden sofort lokal als CSS-Custom-Property
 * gesetzt (Wirkung über live-theme-editor-bridge.css) und gedebounced an den Draft-Endpoint
 * gepusht. Ein eigener SSE-Stream hält die eigenen weiteren Tabs/Geräte synchron.
 */
(function () {
  "use strict";

  var SIZE_RANGES = {
    font_size: { min: 12, max: 22, step: 0.5 },
    h1_size: { min: 20, max: 72, step: 1 },
    h2_size: { min: 18, max: 56, step: 1 },
    h3_size: { min: 16, max: 40, step: 1 }
  };

  var LABELS = {
    primary: "Primary",
    secondary: "Secondary",
    background: "Hintergrund",
    color: "Text",
    link: "Link",
    font_size: "Basis-Schriftgröße",
    h1_size: "H1-Größe",
    h2_size: "H2-Größe",
    h3_size: "H3-Größe"
  };

  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  function parseSize(value) {
    var match = /^(\d+(?:\.\d+)?)(px|rem|em|%)$/.exec(value || "");
    if (!match) {
      return null;
    }
    return { number: parseFloat(match[1]), unit: match[2] };
  }

  function debounce(fn, delay) {
    var timer = null;
    return function () {
      var args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function () {
        fn.apply(null, args);
      }, delay);
    };
  }

  ready(function () {
    var root = document.getElementById("tb-live-editor-root");
    if (!root) {
      return;
    }

    var config;
    try {
      config = JSON.parse(root.getAttribute("data-tb-live-config"));
    } catch (e) {
      return;
    }

    var state = Object.assign({}, config.values || {});

    // --- DOM aufbauen ---------------------------------------------------
    var wrap = document.createElement("div");
    wrap.id = "tb-live-editor";

    var toggle = document.createElement("button");
    toggle.type = "button";
    toggle.className = "tb-live-toggle";
    toggle.setAttribute("aria-label", "Live Theme Editor öffnen");
    toggle.textContent = "🎨";
    wrap.appendChild(toggle);

    var panel = document.createElement("div");
    panel.className = "tb-live-panel";
    wrap.appendChild(panel);

    var heading = document.createElement("h4");
    heading.textContent = "Live Theme: " + config.theme;
    panel.appendChild(heading);

    var colorInputs = {};
    var sizeInputs = {};

    ["primary", "secondary", "background", "color", "link"].forEach(function (key) {
      var field = config.fields[key];
      if (!field) {
        return;
      }

      var row = document.createElement("div");
      row.className = "tb-live-row";

      var label = document.createElement("label");
      label.textContent = LABELS[key] || key;
      row.appendChild(label);

      var swatch = document.createElement("div");
      swatch.className = "tb-live-swatch";
      swatch.style.backgroundColor = state[key] || "#cccccc";
      row.appendChild(swatch);

      panel.appendChild(row);
      colorInputs[key] = swatch;

      initPickr(swatch, state[key] || "#cccccc", function (color) {
        state[key] = color;
        applyLocal(key, color);
        scheduleSync();
      });
    });

    ["font_size", "h1_size", "h2_size", "h3_size"].forEach(function (key) {
      var field = config.fields[key];
      if (!field) {
        return;
      }
      var range = SIZE_RANGES[key];
      var parsed = parseSize(state[key]) || { number: range.min, unit: "px" };

      var row = document.createElement("div");
      row.className = "tb-live-row";

      var label = document.createElement("label");
      label.textContent = LABELS[key] || key;
      row.appendChild(label);

      var input = document.createElement("input");
      input.type = "range";
      input.min = String(range.min);
      input.max = String(range.max);
      input.step = String(range.step);
      input.value = String(parsed.number);
      row.appendChild(input);

      var valueLabel = document.createElement("span");
      valueLabel.className = "tb-live-size-value";
      valueLabel.textContent = parsed.number + parsed.unit;
      row.appendChild(valueLabel);

      panel.appendChild(row);
      sizeInputs[key] = { input: input, valueLabel: valueLabel, unit: parsed.unit };

      input.addEventListener("input", function () {
        var newValue = input.value + parsed.unit;
        valueLabel.textContent = newValue;
        state[key] = newValue;
        applyLocal(key, newValue);
        scheduleSync();
      });
    });

    var actions = document.createElement("div");
    actions.className = "tb-live-actions";
    panel.appendChild(actions);

    var status = document.createElement("div");
    status.className = "tb-live-status";
    status.textContent = "Nur du siehst diese Änderungen.";
    panel.appendChild(status);

    var discardBtn = makeButton("Verwerfen", "tb-live-btn", function () {
      callApi(config.discardUrl, {}).then(function () {
        location.reload();
      });
    });

    var saveBtn = null;
    var goLiveBtn = null;
    var stopBtn = null;

    if (config.isAdmin) {
      goLiveBtn = makeButton("Live schalten", "tb-live-btn tb-live-btn-primary", function () {
        callApi(config.goLiveUrl, {}).then(function (res) {
          if (res && res.success) {
            status.textContent = "Live für alle Besucher sichtbar.";
            status.classList.add("tb-live-status-active");
          }
        });
      });

      stopBtn = makeButton("Live-Session beenden", "tb-live-btn tb-live-btn-danger", function () {
        callApi(config.stopUrl, {}).then(function (res) {
          if (res && res.success) {
            status.textContent = "Nur du siehst diese Änderungen.";
            status.classList.remove("tb-live-status-active");
          }
        });
      });

      saveBtn = makeButton("Speichern", "tb-live-btn tb-live-btn-primary", function () {
        callApi(config.saveUrl, {}).then(function (res) {
          if (res && res.success) {
            status.textContent = "Gespeichert - Theme neu kompiliert.";
            status.classList.remove("tb-live-status-active");
          } else if (res && res.error) {
            status.textContent = "Fehler: " + res.error;
          }
        });
      });
    }

    [goLiveBtn, stopBtn, saveBtn, discardBtn].forEach(function (btn) {
      if (btn) {
        actions.appendChild(btn);
      }
    });

    document.body.appendChild(wrap);

    toggle.addEventListener("click", function () {
      wrap.classList.toggle("tb-live-open");
    });

    // --- Hilfsfunktionen --------------------------------------------------

    function makeButton(text, className, onClick) {
      var btn = document.createElement("button");
      btn.type = "button";
      btn.className = className;
      btn.textContent = text;
      btn.addEventListener("click", onClick);
      return btn;
    }

    function initPickr(el, defaultColor, onChange) {
      function start() {
        if (typeof Pickr === "undefined") {
          setTimeout(start, 100);
          return;
        }

        var pickr = Pickr.create({
          el: el,
          theme: "classic",
          default: defaultColor,
          defaultRepresentation: "HEXA",
          components: {
            preview: true,
            opacity: false,
            hue: true,
            interaction: { hex: true, rgba: false, hsla: false, input: true, save: true }
          }
        });

        pickr.on("change", function (color) {
          var hex = color.toHEXA().toString().slice(0, 7);
          el.style.backgroundColor = hex;
          onChange(hex);
        });
        pickr.on("save", function () {
          pickr.hide();
        });
      }

      start();
    }

    function applyLocal(key, value) {
      var field = config.fields[key];
      if (field) {
        document.documentElement.style.setProperty(field.css, value);
      }
    }

    // Aktuelle Werte beim Laden anwenden (z.B. nach Reload eines noch offenen Drafts)
    Object.keys(state).forEach(function (key) {
      applyLocal(key, state[key]);
    });

    var syncScheduled = debounce(function () {
      callApi(config.pushUrl, { values: JSON.stringify(state) });
    }, 150);

    function scheduleSync() {
      syncScheduled();
    }

    function callApi(url, extraParams) {
      var params = Object.assign({ theme: config.theme }, extraParams || {});
      var body = Object.keys(params)
        .map(function (key) {
          return encodeURIComponent(key) + "=" + encodeURIComponent(params[key]);
        })
        .join("&");

      return fetch(url, {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: body
      })
        .then(function (res) {
          return res.json();
        })
        .catch(function () {
          return null;
        });
    }

    // --- Eigener Draft-Stream (mehrere eigene Tabs/Geräte synchron halten) --
    if (typeof EventSource !== "undefined" && config.streamUrl) {
      var es = new EventSource(config.streamUrl);
      es.onmessage = function (event) {
        try {
          var values = JSON.parse(event.data);
          Object.keys(values).forEach(function (key) {
            state[key] = values[key];
            applyLocal(key, values[key]);
            if (colorInputs[key]) {
              colorInputs[key].style.backgroundColor = values[key];
            }
            if (sizeInputs[key]) {
              var parsed = parseSize(values[key]);
              if (parsed) {
                sizeInputs[key].input.value = String(parsed.number);
                sizeInputs[key].valueLabel.textContent = values[key];
              }
            }
          });
        } catch (e) {
          /* ignore malformed payload */
        }
      };
    }
  });
})();

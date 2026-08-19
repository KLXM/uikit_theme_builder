/**
 * Live Theme Editor - Inhalt des gleichnamigen Info-Center-Widgets (siehe
 * LiveThemeEditorWidget::render()). Änderungen werden sofort lokal als CSS-Custom-Property
 * gesetzt (Wirkung über live-theme-editor-bridge.css) und gedebounced an den Draft-Endpoint
 * gepusht. Ein eigener SSE-Stream hält die eigenen weiteren Tabs/Geräte synchron.
 */
(function () {
  "use strict";

  // Feste px-Bereiche passen nicht, sobald ein Theme rem/em statt px verwendet (z.B. 2.625rem
  // liegt weit unter einem für px sinnvollen Minimum von 20 - der Slider-Thumb würde am linken
  // Rand hängen bleiben, obwohl der Wert korrekt ist). Bereich daher relativ zum tatsächlichen
  // Startwert berechnen, unabhängig von der Einheit.
  function computeSizeRange(currentNumber) {
    var min = 0;
    var max = Math.max(currentNumber * 3, currentNumber + 1);
    var step = currentNumber < 6 ? 0.05 : 1;
    return { min: min, max: Math.round(max * 100) / 100, step: step };
  }

  var COLOR_FIELDS = ["primary", "secondary", "background", "color", "link"];
  var SIZE_FIELDS = ["font_size", "h1_size", "h2_size", "h3_size", "margin", "gutter"];

  var LABELS = {
    primary: "Primary",
    secondary: "Secondary",
    background: "Hintergrund",
    color: "Text",
    link: "Link",
    font_size: "Basis-Schriftgröße",
    h1_size: "H1-Größe",
    h2_size: "H2-Größe",
    h3_size: "H3-Größe",
    margin: "Standard-Abstand",
    gutter: "Container-Padding"
  };

  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  // input[type=color] akzeptiert nur exakt #rrggbb - andere Formate (rgba(), 8-stelliges Hex
  // mit Alpha, ...) würden vom Browser stillschweigend auf schwarz zurückgesetzt.
  function toHexColor(value) {
    if (/^#[0-9a-fA-F]{6}$/.test(value || "")) {
      return value;
    }
    return "#000000";
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

    // --- DOM aufbauen (direkt in den Info-Center-Widget-Content) --------
    root.innerHTML = "";

    var themeLabel = document.createElement("div");
    themeLabel.className = "tb-live-row";
    themeLabel.innerHTML = '<label style="opacity:.6;font-size:11px;">Theme: ' + config.theme + "</label>";
    root.appendChild(themeLabel);

    var colorInputs = {};
    var sizeInputs = {};

    COLOR_FIELDS.forEach(function (key) {
      var field = config.fields[key];
      if (!field) {
        return;
      }

      var row = document.createElement("div");
      row.className = "tb-live-row";

      var label = document.createElement("label");
      label.textContent = LABELS[key] || key;
      row.appendChild(label);

      var colorInput = document.createElement("input");
      colorInput.type = "color";
      colorInput.value = toHexColor(state[key]);
      row.appendChild(colorInput);

      root.appendChild(row);
      colorInputs[key] = colorInput;

      colorInput.addEventListener("input", function () {
        state[key] = colorInput.value;
        applyLocal(key, colorInput.value);
        scheduleSync();
      });
    });

    SIZE_FIELDS.forEach(function (key) {
      var field = config.fields[key];
      if (!field) {
        return;
      }
      var parsed = parseSize(state[key]) || { number: 16, unit: "px" };
      var range = computeSizeRange(parsed.number);

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

      root.appendChild(row);
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
    root.appendChild(actions);

    var status = document.createElement("div");
    status.className = "tb-live-status";
    status.textContent = "Nur du siehst diese Änderungen.";
    root.appendChild(status);

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

    // --- Hilfsfunktionen --------------------------------------------------

    function makeButton(text, className, onClick) {
      var btn = document.createElement("button");
      btn.type = "button";
      btn.className = className;
      btn.textContent = text;
      btn.addEventListener("click", onClick);
      return btn;
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
              colorInputs[key].value = toHexColor(values[key]);
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

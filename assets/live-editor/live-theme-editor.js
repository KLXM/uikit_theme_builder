/**
 * Live Theme Editor - Inhalt des gleichnamigen Info-Center-Widgets (siehe
 * LiveThemeEditorWidget::render()). Änderungen werden sofort lokal als CSS-Custom-Property
 * gesetzt (Wirkung über live-theme-editor-bridge.css) und gedebounced an den Draft-Endpoint
 * gepusht. Ein eigener SSE-Stream hält die eigenen weiteren Tabs/Geräte synchron.
 */
(function () {
  "use strict";

  var SECTIONS = [
    { key: "typography", label: "Typografie", icon: "pencil" },
    { key: "colors", label: "Farben", icon: "paint-bucket" },
    { key: "spacing", label: "Maße", icon: "expand" },
    { key: "navigation", label: "Navigation", icon: "menu" }
  ];

  var LABELS = {
    primary: "Primary",
    secondary: "Secondary",
    success: "Success",
    warning: "Warning",
    danger: "Danger",
    color: "Text",
    emphasis: "Emphasis",
    muted: "Muted",
    link: "Link",
    link_hover: "Link Hover",
    inverse: "Inverse",
    border: "Border",
    background: "Hintergrund",
    muted_background: "Muted Background",
    heading_color: "Überschriften-Farbe",
    font_family: "Primärschrift",
    heading_font_family: "Überschriften-Schrift",
    font_size: "Basis-Schriftgröße",
    line_height: "Zeilenhöhe (Fließtext)",
    heading_line_height: "Zeilenhöhe (Überschriften)",
    h1_size: "H1-Größe",
    h2_size: "H2-Größe",
    h3_size: "H3-Größe",
    h4_size: "H4-Größe",
    margin: "Standard-Abstand",
    gutter: "Padding (Medium/Standard) & Container-Gutter",
    gutter_small: "Padding (Small)",
    gutter_large: "Padding (Large)",
    container_width: "Container-Breite (Standard)",
    container_width_small: "Container-Breite (Small)",
    container_width_large: "Container-Breite (Large)",
    navbar_background: "Navbar-Hintergrund",
    navbar_item_color: "Navigation-Text",
    navbar_item_hover_color: "Navigation-Text (Hover)",
    navbar_item_height: "Navigation-Höhe",
    navbar_dropdown_background: "Dropdown-Hintergrund",
    navbar_dropdown_color: "Dropdown-Text",
    navbar_dropdown_hover_color: "Dropdown-Text (Hover)",
    navbar_dropdown_width: "Dropdown-Breite"
  };

  // A11y-Untergrenze: Basis-Schriftgröße darf laut gängigen Empfehlungen nie unter 16px
  // (bzw. dem äquivalenten rem/em/%-Wert bei unverändertem Root-Font-Size) fallen, sonst
  // leidet die Lesbarkeit. Überschriften dürfen nicht kleiner als die Basisschrift werden,
  // sonst bricht die visuelle Hierarchie - daher ebenfalls mit (etwas höherer) Untergrenze.
  var A11Y_MIN_PX = { font_size: 16, h4_size: 17, h3_size: 18, h2_size: 20, h1_size: 24 };

  function unitAwareFloor(unit, pxFloor) {
    if ("rem" === unit || "em" === unit) {
      return Math.round((pxFloor / 16) * 100) / 100;
    }
    if ("%" === unit) {
      return Math.round((pxFloor / 16) * 100);
    }
    return pxFloor;
  }

  // Feste px-Bereiche passen nicht, sobald ein Theme rem/em statt px verwendet (z.B. 2.625rem
  // liegt weit unter einem für px sinnvollen Minimum von 20 - der Slider-Thumb würde am linken
  // Rand hängen bleiben, obwohl der Wert korrekt ist). Bereich daher relativ zum tatsächlichen
  // Startwert berechnen, unabhängig von der Einheit - nur die a11y-Untergrenze ist fest.
  function computeSizeRange(key, currentNumber, unit) {
    var min = A11Y_MIN_PX[key] ? unitAwareFloor(unit, A11Y_MIN_PX[key]) : 0;
    var max = Math.max(currentNumber * 3, currentNumber + 1, min + 1);
    var step = currentNumber < 6 ? 0.05 : 1;
    return { min: min, max: Math.round(max * 100) / 100, step: step };
  }

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

  // Bekannte Betriebssystem-/Web-safe-Fonts, die kein <link> zu Google Fonts brauchen.
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

  function extractFirstFont(fontStack) {
    if (!fontStack || "inherit" === fontStack) {
      return "";
    }
    return fontStack.replace(/['"]/g, "").trim().split(",")[0].trim();
  }

  function isSystemFont(fontName) {
    return SYSTEM_FONTS.some(function (sf) {
      return sf.toLowerCase() === fontName.toLowerCase();
    });
  }

  // NIE live fonts.googleapis.com kontaktieren - das lief hier bisher genauso wie im normalen
  // Theme-Editor, war aber ein Bug: "Arial Black" fehlte in SYSTEM_FONTS, wurde also fälschlich
  // live bei Google angefragt (403, da gar kein Google Font). Ausdrücklicher Auftrag: Google
  // Fonts kommen ausschließlich aus der eigenen, bereits heruntergeladenen Fontverwaltung
  // (siehe GoogleFontsManager/TemplateHelper::includeGoogleFonts()) - fehlt die lokale Datei
  // (Font nie heruntergeladen), bleibt es beim Fallback-Stack, statt extern nachzuladen.
  function sanitizeFontName(fontName) {
    return fontName.replace(/[^a-zA-Z0-9_-]/g, "_");
  }

  function ensureFontLoaded(fontStack, fontsBaseUrl) {
    var firstFont = extractFirstFont(fontStack);
    if (!firstFont || isSystemFont(firstFont) || loadedGoogleFonts[firstFont] || !fontsBaseUrl) {
      return;
    }
    loadedGoogleFonts[firstFont] = true;
    var link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = fontsBaseUrl + sanitizeFontName(firstFont) + ".css";
    document.head.appendChild(link);
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
    var colorInputs = {};
    var sizeInputs = {};
    var fontInputs = {};
    // Siehe es.onmessage weiter unten: verhindert eine Echo-Schleife, wenn ein per SSE
    // empfangener Wert programmatisch in die Eingabefelder geschrieben wird (z.B.
    // picker.setColor(...) - Pickit Color ruft dabei synchron seinen eigenen onChange auf,
    // der sonst scheduleSync() erneut auslösen und den Wert postwendend zurückpushen würde).
    var applyingRemoteUpdate = false;

    // Muss vor dem Aufbau der Felder stehen: Pickit Color ruft onChange bereits synchron
    // während der Konstruktion auf (initiale Anzeige), also bevor der Rest dieser Funktion
    // (weiter unten) syncScheduled sonst zuweisen würde - "var" wird zwar gehoisted, ist aber
    // bis zur Zuweisung noch undefined, ein Aufruf davor wirft "not a function" und bricht die
    // gesamte Render-Funktion ab (Symptom: nur der erste Abschnitt bis zum ersten Farbfeld
    // erscheint, alles danach - weitere Abschnitte, Speichern/Live-schalten-Buttons - fehlt).
    function pushNow() {
      return callApi(config.pushUrl, { values: JSON.stringify(state) });
    }

    var syncScheduled = debounce(pushNow, 150);

    function scheduleSync() {
      syncScheduled();
    }

    root.innerHTML = "";

    var currentTheme = config.theme;

    var themeBadge = document.createElement("span");
    themeBadge.className = "tb-live-theme-badge";
    themeBadge.textContent = currentTheme;
    root.appendChild(themeBadge);

    var inspectRow = document.createElement("div");
    inspectRow.className = "tb-live-row";
    var inspectLabel = document.createElement("label");
    inspectLabel.textContent = "Bearbeitbare Elemente markieren";
    inspectRow.appendChild(inspectLabel);
    var inspectToggle = makeButton("Labels anzeigen", "tb-live-btn", function () {
      setInspectActive(!inspectActive);
    }, "tag");
    inspectRow.appendChild(inspectToggle);
    root.appendChild(inspectRow);

    var themeSwitchSelect = null;
    if (config.canSwitchTheme && config.availableThemes && Object.keys(config.availableThemes).length > 1) {
      var switchRow = document.createElement("div");
      switchRow.className = "tb-live-row";

      var switchLabel = document.createElement("label");
      switchLabel.textContent = "Theme wechseln";
      switchRow.appendChild(switchLabel);

      themeSwitchSelect = document.createElement("select");
      Object.keys(config.availableThemes).forEach(function (value) {
        var option = document.createElement("option");
        option.value = value;
        option.textContent = config.availableThemes[value];
        themeSwitchSelect.appendChild(option);
      });
      themeSwitchSelect.value = currentTheme;
      switchRow.appendChild(themeSwitchSelect);
      root.appendChild(switchRow);

      themeSwitchSelect.addEventListener("change", function () {
        state._switch_theme = themeSwitchSelect.value;
        applyThemeSwitch(themeSwitchSelect.value);
        scheduleSync();
      });

      if (config.isAdmin) {
        var switchActions = document.createElement("div");
        switchActions.className = "tb-live-actions";
        var applyThemeBtn = makeButton("Theme übernehmen", "tb-live-btn tb-live-btn-primary", function () {
          if (themeSwitchSelect.value === currentTheme) {
            return;
          }
          callApi(config.switchThemeUrl, { new_theme: themeSwitchSelect.value }).then(function (res) {
            if (res && res.success) {
              location.reload();
            } else if (res && res.error) {
              status.textContent = "Fehler: " + res.error;
            }
          });
        }, "forward");
        switchActions.appendChild(applyThemeBtn);
        root.appendChild(switchActions);
      }
    }

    function applyThemeSwitch(themeName) {
      var link = document.querySelector('link[href*="themes/compiled/"]');
      if (link && config.themeCssUrlTemplate) {
        link.href = config.themeCssUrlTemplate.replace("__THEME__", themeName);
      }
      themeBadge.textContent = themeName;
    }

    // --- "Bearbeitbare Elemente markieren" -------------------------------
    // Reihenfolge wichtig: spezifischere Klassen zuerst, damit z.B. ein Element mit sowohl
    // .uk-container als auch .uk-container-large (kommt in UIkit nicht vor, aber sicher ist
    // sicher) das treffendere Label bekommt. Bewusst auf Container/Sections beschränkt (nicht
    // jede uk-card/uk-badge/etc.), das wäre zu viel Rauschen auf einer echten Seite.
    var INSPECT_LABELS = [
      { selector: ".uk-container-small", label: "Container: Small" },
      { selector: ".uk-container-large", label: "Container: Large" },
      { selector: ".uk-container", label: "Container: Standard" },
      { selector: ".uk-section-primary", label: "Section: Primary" },
      { selector: ".uk-section-secondary", label: "Section: Secondary" },
      { selector: ".uk-section-muted", label: "Section: Muted" },
      { selector: ".uk-section-default", label: "Section: Default" }
    ];

    var inspectActive = false;
    var inspectOverlay = null;
    var inspectResizeHandler = null;

    function setInspectActive(active) {
      inspectActive = active;
      inspectToggle._tbTextNode.textContent = active ? "Labels ausblenden" : "Labels anzeigen";
      if (active) {
        renderInspectLabels();
        inspectResizeHandler = debounce(renderInspectLabels, 200);
        window.addEventListener("resize", inspectResizeHandler);
      } else {
        clearInspectLabels();
        if (inspectResizeHandler) {
          window.removeEventListener("resize", inspectResizeHandler);
          inspectResizeHandler = null;
        }
      }
    }

    function clearInspectLabels() {
      if (inspectOverlay) {
        inspectOverlay.remove();
        inspectOverlay = null;
      }
    }

    function renderInspectLabels() {
      clearInspectLabels();
      inspectOverlay = document.createElement("div");
      inspectOverlay.id = "tb-live-inspect-overlay";
      document.body.appendChild(inspectOverlay);

      var labeled = [];
      INSPECT_LABELS.forEach(function (entry) {
        document.querySelectorAll(entry.selector).forEach(function (el) {
          if (el.closest(".info-center-container") || labeled.indexOf(el) !== -1) {
            return;
          }
          labeled.push(el);

          var rect = el.getBoundingClientRect();
          if (!rect.width && !rect.height) {
            return;
          }
          var badge = document.createElement("div");
          badge.className = "tb-live-inspect-badge";
          badge.textContent = entry.label;
          badge.style.top = rect.top + window.scrollY + "px";
          badge.style.left = rect.left + window.scrollX + "px";
          inspectOverlay.appendChild(badge);
        });
      });
    }

    if (config.canStyle) {
    SECTIONS.forEach(function (section) {
      var keysInSection = Object.keys(config.fields).filter(function (key) {
        return config.fields[key].section === section.key;
      });
      if (!keysInSection.length) {
        return;
      }

      var details = document.createElement("details");
      details.className = "tb-live-section";
      // Natives Accordion-Verhalten (Chrome 109+/Firefox 118+/Safari 17+): gleicher "name"
      // sorgt dafür, dass beim Öffnen eines Abschnitts alle anderen automatisch zuklappen,
      // ganz ohne eigenes JS.
      details.name = "tb-live-accordion";

      var summary = document.createElement("summary");
      if (section.icon) {
        var summaryIcon = document.createElement("span");
        summaryIcon.className = "tb-live-section-icon";
        summaryIcon.setAttribute("uk-icon", "icon: " + section.icon + "; ratio: 0.8");
        summary.appendChild(summaryIcon);
      }
      summary.appendChild(document.createTextNode(section.label));
      details.appendChild(summary);

      var body = document.createElement("div");
      body.className = "tb-live-section-body";
      details.appendChild(body);

      keysInSection.forEach(function (key) {
        var field = config.fields[key];
        var row = document.createElement("div");
        row.className = "tb-live-row";

        var label = document.createElement("label");
        label.textContent = LABELS[key] || key;
        row.appendChild(label);

        if ("color" === field.type) {
          var colorInput = document.createElement("input");
          colorInput.type = "text";
          colorInput.className = "tb-color-input";
          colorInput.autocomplete = "off";
          colorInput.value = state[key] || "";
          row.appendChild(colorInput);

          var picker = null;
          if (window.colorpicker && window.colorpicker.ColorPicker) {
            // navbar_* Felder landen unverändert (kein Alpha-Stripping) in NavbarWidget::
            // generateLessVariables() - dort war 8-stelliges Hex nie erprobt (die alte Pickr-
            // Logik hat bei Transparenz immer rgba() erzeugt, nie Hex mit Alpha, siehe
            // AbstractWidget::renderColorPicker()). format:"rgb" hält das bei -
            // rgb()/rgba() statt #rrggbbaa.
            picker = new window.colorpicker.ColorPicker(colorInput, {
              format: "navbar" === field.group ? "rgb" : "hex",
              showAlpha: true,
              compact: true,
              language: "de",
              onChange: function (value) {
                if (applyingRemoteUpdate) {
                  return;
                }
                state[key] = value;
                applyLocal(key, value);
                scheduleSync();
              }
            });
          }
          colorInputs[key] = { input: colorInput, picker: picker };
        } else if ("font" === field.type) {
          var select = document.createElement("select");
          if ("heading_font_family" === key) {
            var inheritOption = document.createElement("option");
            inheritOption.value = "inherit";
            inheritOption.textContent = "Von Primärschrift erben";
            select.appendChild(inheritOption);
          }
          Object.keys(config.fontOptions || {}).forEach(function (value) {
            var option = document.createElement("option");
            option.value = value;
            option.textContent = config.fontOptions[value];
            select.appendChild(option);
          });
          select.value = state[key] || "inherit";
          row.className += " tb-live-row-font";
          row.appendChild(select);
          fontInputs[key] = select;
          select.addEventListener("change", function () {
            if (applyingRemoteUpdate) {
              return;
            }
            state[key] = select.value;
            applyLocal(key, select.value);
            ensureFontLoaded(select.value, config.fontsBaseUrl);
            scheduleSync();
          });
          ensureFontLoaded(select.value, config.fontsBaseUrl);
        } else if ("number" === field.type) {
          var numValue = parseFloat(state[key]) || 1.5;
          var numInput = document.createElement("input");
          numInput.type = "range";
          numInput.min = "1";
          numInput.max = "2.2";
          numInput.step = "0.05";
          numInput.value = String(numValue);
          row.appendChild(numInput);
          var numLabel = document.createElement("span");
          numLabel.className = "tb-live-size-value";
          numLabel.textContent = String(numValue);
          row.appendChild(numLabel);
          sizeInputs[key] = { input: numInput, valueLabel: numLabel, unit: "" };
          numInput.addEventListener("input", function () {
            if (applyingRemoteUpdate) {
              return;
            }
            numLabel.textContent = numInput.value;
            state[key] = numInput.value;
            applyLocal(key, numInput.value);
            scheduleSync();
          });
        } else {
          // size
          var parsed = parseSize(state[key]) || { number: 16, unit: "px" };
          var range = computeSizeRange(key, parsed.number, parsed.unit);
          if (parsed.number < range.min) {
            parsed = { number: range.min, unit: parsed.unit };
            state[key] = String(range.min) + parsed.unit;
          }

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

          sizeInputs[key] = { input: input, valueLabel: valueLabel, unit: parsed.unit };
          input.addEventListener("input", function () {
            if (applyingRemoteUpdate) {
              return;
            }
            var newValue = input.value + parsed.unit;
            valueLabel.textContent = newValue;
            state[key] = newValue;
            applyLocal(key, newValue);
            scheduleSync();
          });
        }

        body.appendChild(row);
      });

      root.appendChild(details);
    });
    }

    var actions = document.createElement("div");
    actions.className = "tb-live-actions";
    root.appendChild(actions);

    var DEFAULT_STATUS_TEXT = "Nur du siehst diese Änderungen.";

    var status = document.createElement("div");
    status.className = "tb-live-status";
    status.textContent = DEFAULT_STATUS_TEXT;
    root.appendChild(status);

    var statusResetTimer = null;

    // autoHideMs gesetzt: Meldung (Erfolg/Fehler) verschwindet danach wieder zum Standardtext -
    // sonst bliebe z.B. "Gespeichert" dauerhaft stehen, obwohl die Aktion längst vorbei ist.
    function setStatus(text, autoHideMs) {
      status.textContent = text;
      if (statusResetTimer) {
        clearTimeout(statusResetTimer);
        statusResetTimer = null;
      }
      if (autoHideMs) {
        statusResetTimer = setTimeout(function () {
          status.textContent = DEFAULT_STATUS_TEXT;
          statusResetTimer = null;
        }, autoHideMs);
      }
    }

    var discardBtn = makeButton("Verwerfen", "tb-live-btn", function () {
      callApi(config.discardUrl, {}).then(function () {
        location.reload();
      });
    }, "trash");

    var saveBtn = null;

    if (config.isAdmin) {
      saveBtn = makeButton("Speichern", "tb-live-btn tb-live-btn-primary", function () {
        saveBtn.disabled = true;
        setStatus("Speichert …");

        // Erst den aktuellen Stand SOFORT pushen (nicht auf den gedebouncten Push warten) -
        // sonst kann "Speichern" schneller sein als der letzte Feldwechsel, der Server liest
        // dann noch den alten (oder gar keinen) Entwurf: "Kein Entwurf zum Speichern vorhanden."
        pushNow().then(function () {
          return callApi(config.saveUrl, {});
        }).then(function (res) {
          if (res && res.success) {
            setStatus("Gespeichert - Theme neu kompiliert.", 4000);
          } else if (res && res.error) {
            setStatus("Fehler: " + res.error, 6000);
          } else {
            setStatus("Fehler: Keine Verbindung zum Server.", 6000);
          }
        }).finally(function () {
          saveBtn.disabled = false;
        });
      }, "check");
    }

    [saveBtn, discardBtn].forEach(function (btn) {
      if (btn) {
        actions.appendChild(btn);
      }
    });

    function makeButton(text, className, onClick, icon) {
      var btn = document.createElement("button");
      btn.type = "button";
      btn.className = className;
      if (icon) {
        var iconSpan = document.createElement("span");
        iconSpan.setAttribute("uk-icon", "icon: " + icon + "; ratio: 0.8");
        btn.appendChild(iconSpan);
        btn.appendChild(document.createTextNode(" "));
      }
      var textNode = document.createTextNode(text);
      btn.appendChild(textNode);
      btn._tbTextNode = textNode;
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

    function callApi(url, extraParams) {
      // currentTheme statt config.theme (nur der Wert bei Seitenaufruf): nach einer
      // Theme-Wechsel-Vorschau (Dropdown, ohne "Theme übernehmen"/Reload) wich das sonst
      // auseinander - alle Aktionen (Push/Speichern/...) liefen dann noch gegen den
      // ursprünglichen Theme-Namen, nicht den gerade vorgeschauten.
      var params = Object.assign({ theme: currentTheme }, extraParams || {});
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
          if (values._switch_theme) {
            currentTheme = values._switch_theme;
            applyThemeSwitch(currentTheme);
            if (themeSwitchSelect) {
              themeSwitchSelect.value = currentTheme;
            }
          }
          applyingRemoteUpdate = true;
          try {
            Object.keys(values).forEach(function (key) {
              if ("_switch_theme" === key) {
                return;
              }
              state[key] = values[key];
              applyLocal(key, values[key]);
              if (colorInputs[key]) {
                colorInputs[key].input.value = values[key];
                if (colorInputs[key].picker) {
                  colorInputs[key].picker.setColor(values[key]);
                }
              }
              if (sizeInputs[key]) {
                var parsedVal = parseFloat(values[key]);
                var displayValue = isNaN(parsedVal) ? values[key] : parsedVal + (sizeInputs[key].unit || "");
                sizeInputs[key].input.value = String(parsedVal);
                sizeInputs[key].valueLabel.textContent = displayValue;
              }
              if (fontInputs[key]) {
                fontInputs[key].value = values[key];
                ensureFontLoaded(values[key], config.fontsBaseUrl);
              }
            });
          } finally {
            applyingRemoteUpdate = false;
          }
        } catch (e) {
          /* ignore malformed payload */
        }
      };
    }
  });
})();

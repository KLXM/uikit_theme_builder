(function () {
    'use strict';

    var STORAGE_PREFIX = 'utb-editor-open-sections:';

    function storageGet(key) {
        try {
            return window.localStorage.getItem(key);
        } catch (e) {
            return null;
        }
    }

    function storageSet(key, value) {
        try {
            window.localStorage.setItem(key, value);
        } catch (e) {
            // localStorage nicht verfügbar (privates Fenster o. ä.) – dann eben ohne Merken
        }
    }

    /**
     * Geöffnete Accordion-Bereiche je Theme merken und beim Laden wiederherstellen.
     */
    function initOpenSections(form) {
        var list = form.querySelector('[data-utb-sections]');
        if (!list) {
            return;
        }

        var themeKey = form.getAttribute('data-utb-theme-key') || 'new_theme';
        var items = Array.prototype.slice.call(list.querySelectorAll(':scope > li[data-utb-section]'));

        function openKeys() {
            return items.filter(function (li) {
                return li.classList.contains('uk-open');
            }).map(function (li) {
                return li.getAttribute('data-utb-section');
            });
        }

        function persist(key) {
            storageSet(STORAGE_PREFIX + (key || themeKey), JSON.stringify(openKeys()));
        }

        // Wiederherstellen
        var saved = [];
        try {
            saved = JSON.parse(storageGet(STORAGE_PREFIX + themeKey) || '[]') || [];
        } catch (e) {
            saved = [];
        }

        if (saved.length && window.UIkit && typeof window.UIkit.accordion === 'function') {
            var accordion = window.UIkit.accordion(list);
            items.forEach(function (li, index) {
                if (saved.indexOf(li.getAttribute('data-utb-section')) !== -1 && !li.classList.contains('uk-open')) {
                    accordion.toggle(index, false);
                }
            });
        }

        // Änderungen merken (UIkit setzt/entfernt die Klasse uk-open am li)
        if (typeof MutationObserver === 'function') {
            var observer = new MutationObserver(function () {
                persist();
            });
            items.forEach(function (li) {
                observer.observe(li, { attributes: true, attributeFilter: ['class'] });
            });
        }

        // Neues Theme: nach dem Speichern heißt es so wie im Feld Theme Key
        form.addEventListener('submit', function () {
            persist();
            var keyInput = form.querySelector('[name="theme_name"]');
            if (keyInput && keyInput.value && keyInput.value !== themeKey) {
                persist(keyInput.value);
            }
        });
    }

    /**
     * Nach dem Speichern die Adresse auf den Editor des Themes setzen,
     * damit Neuladen nicht erneut absendet und der Link stimmt.
     */
    function initSavedUrl(form) {
        if (form.getAttribute('data-utb-saved') !== '1') {
            return;
        }
        var url = form.getAttribute('data-utb-editor-url');
        if (url && window.history && typeof window.history.replaceState === 'function') {
            try {
                window.history.replaceState(null, '', url);
            } catch (e) {
                // ignorieren
            }
        }
    }

    /**
     * Barrierefreiheit: Schaltflächen nur mit Icon bekommen einen Namen (title oder data-tooltip),
     * Formularfelder ohne Label einen aria-label aus Platzhalter oder Name.
     */
    function initAccessibleNames(root) {
        var buttons = root.querySelectorAll('button, a.uk-button, a.uk-icon-button, a[uk-icon], .uk-icon-button');
        Array.prototype.forEach.call(buttons, function (el) {
            if (el.hasAttribute('aria-label') || el.hasAttribute('aria-labelledby')) {
                return;
            }
            if ((el.textContent || '').trim() !== '') {
                return;
            }
            var name = el.getAttribute('title') || el.getAttribute('data-title') || el.getAttribute('data-tooltip');
            if (!name && el.hasAttribute('uk-tooltip')) {
                var tooltip = el.getAttribute('uk-tooltip') || '';
                var match = tooltip.match(/title:\s*([^;]+)/);
                name = match ? match[1] : tooltip;
            }
            if (!name) {
                var iconHost = el.matches('[uk-icon]') ? el : el.querySelector('[uk-icon]');
                var icon = iconHost ? (iconHost.getAttribute('uk-icon') || '') : '';
                var iconMatch = icon.match(/icon:\s*([\w-]+)/) || icon.match(/^([\w-]+)$/);
                var iconNames = {
                    close: 'Schließen', trash: 'Löschen', plus: 'Hinzufügen', minus: 'Entfernen',
                    pencil: 'Bearbeiten', refresh: 'Zurücksetzen', copy: 'Kopieren', search: 'Suchen',
                    'paint-bucket': 'Farbe wählen', settings: 'Einstellungen', cog: 'Einstellungen',
                    info: 'Info', question: 'Hilfe', eye: 'Anzeigen', move: 'Verschieben', image: 'Bild wählen'
                };
                if (iconMatch && iconNames[iconMatch[1]]) {
                    name = iconNames[iconMatch[1]];
                }
            }
            if (name) {
                el.setAttribute('aria-label', name.trim());
            }
        });

        var controls = root.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), select, textarea');
        Array.prototype.forEach.call(controls, function (el) {
            if (el.hasAttribute('aria-label') || el.hasAttribute('aria-labelledby')) {
                return;
            }
            if (el.labels && el.labels.length) {
                return;
            }
            if (el.closest('label')) {
                return;
            }
            // 1. Label im selben Feldblock (z. B. uk-form-label ohne for) – nur wenn es eindeutig ist
            var name = '';
            var node = el.parentElement;
            for (var depth = 0; node && depth < 3 && node !== root; depth++, node = node.parentElement) {
                var labels = node.querySelectorAll('.uk-form-label, label');
                if (labels.length === 0) {
                    continue;
                }
                if (labels.length === 1 && (labels[0].textContent || '').trim() !== '') {
                    name = labels[0].textContent.replace(/\s+/g, ' ').trim();
                }
                break;
            }
            // 2. title, 3. Platzhalter
            if (!name) {
                name = el.getAttribute('title') || el.getAttribute('placeholder') || '';
            }
            if (!name && el.name) {
                name = el.name.replace(/[_\[\]-]+/g, ' ').trim();
            }
            if (name) {
                el.setAttribute('aria-label', name);
            }
        });
    }

    function initEditorSaveState() {
        var form = document.getElementById('utb-theme-editor-form');
        if (!form) {
            return;
        }

        var footer = document.querySelector('.utb-editor-actions');
        var statusText = document.getElementById('utb-save-status-text');
        var saveButton = form.querySelector('[data-utb-save-button]');
        var dirty = false;
        var saving = false;

        function setState(nextDirty, nextSaving) {
            dirty = nextDirty;
            saving = nextSaving;

            if (footer) {
                footer.classList.toggle('is-dirty', dirty);
                footer.classList.toggle('is-saving', saving);
            }

            if (!statusText) {
                return;
            }

            if (saving) {
                statusText.textContent = 'Wird gespeichert und veröffentlicht …';
                return;
            }

            if (dirty) {
                statusText.textContent = 'Ungespeicherte Änderungen';
                return;
            }

            statusText.textContent = 'Alle Änderungen gespeichert';
        }

        function markDirty() {
            if (!dirty) {
                setState(true, false);
            }
        }

        form.addEventListener('input', function (event) {
            if (event.target && event.target.name !== 'compile') {
                markDirty();
            }
        });

        form.addEventListener('change', function (event) {
            if (event.target && event.target.name !== 'compile') {
                markDirty();
            }
        });

        form.addEventListener('submit', function () {
            setState(false, true);
            if (saveButton) {
                // Erst nach dem Absenden sperren – ein gesperrter Button würde sonst nicht mitgeschickt (compile=1)
                window.setTimeout(function () {
                    saveButton.setAttribute('disabled', 'disabled');
                }, 0);
            }
        });

        window.addEventListener('beforeunload', function (event) {
            if (!dirty || saving) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });

        setState(false, false);
        initSavedUrl(form);
        initOpenSections(form);

        // Widgets rendern teils nachträglich (Icon Picker, Colorpicker) – Namen danach ergänzen
        initAccessibleNames(form);
        window.setTimeout(function () {
            initAccessibleNames(form);
        }, 1200);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditorSaveState);
    } else {
        initEditorSaveState();
    }
})();

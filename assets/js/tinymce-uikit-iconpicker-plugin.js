/**
 * TinyMCE UIKit Icon Picker Plugin
 *
 * Eigener Floating-Picker (wie for_chars_symbols), damit keine Modal-Interaktionsprobleme auftreten.
 */
(function () {
    'use strict';

    var cachedIcons = null;
    var cssInjected = false;
    var panelsByEditor = new WeakMap();
    var RECENTLY_USED_KEY = 'utbip_recently_used';
    var RECENTLY_USED_LIMIT = 8;

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeIcons(rawIcons) {
        if (!Array.isArray(rawIcons)) {
            return [];
        }

        return rawIcons
            .map(function (item) {
                if (!item || typeof item.name !== 'string') {
                    return null;
                }

                return {
                    name: item.name,
                    category: typeof item.category === 'string' && item.category !== '' ? item.category : 'other',
                    source: item.source === 'custom' ? 'custom' : 'uikit',
                    svg: typeof item.svg === 'string' ? item.svg : ''
                };
            })
            .filter(function (item) {
                return item !== null;
            })
            .sort(function (a, b) {
                return a.name.localeCompare(b.name);
            });
    }

    function sanitizeInlineSvg(svg) {
        if (typeof svg !== 'string') {
            return '';
        }

        var normalized = svg.trim();
        if (normalized.indexOf('<svg') !== 0 || normalized.indexOf('</svg>') === -1) {
            return '';
        }

        normalized = normalized.replace(/\son[a-z-]+\s*=\s*"[^"]*"/gi, '');
        normalized = normalized.replace(/\son[a-z-]+\s*=\s*'[^']*'/gi, '');

        if (normalized.indexOf('aria-hidden=') === -1) {
            normalized = normalized.replace('<svg', '<svg aria-hidden="true"');
        }
        if (normalized.indexOf('focusable=') === -1) {
            normalized = normalized.replace('<svg', '<svg focusable="false"');
        }
        if (normalized.indexOf('class=') === -1) {
            normalized = normalized.replace('<svg', '<svg class="uikit-inline-icon"');
        }
        if (normalized.indexOf('style=') === -1) {
            normalized = normalized.replace('<svg', '<svg style="display:block;width:1em;height:1em;"');
        }

        return normalized;
    }

    function filterIcons(icons, category, search) {
        var normalizedSearch = (search || '').toLowerCase().trim();

        return icons.filter(function (icon) {
            var matchesCategory = category === 'all' || icon.category === category;
            var matchesSearch = normalizedSearch === '' || icon.name.toLowerCase().indexOf(normalizedSearch) !== -1;
            return matchesCategory && matchesSearch;
        });
    }

    function getRecentlyUsed() {
        try {
            var stored = localStorage.getItem(RECENTLY_USED_KEY);
            return stored ? JSON.parse(stored) : [];
        } catch (e) {
            return [];
        }
    }

    function addToRecentlyUsed(iconName) {
        try {
            var list = getRecentlyUsed();
            var idx = list.indexOf(iconName);
            if (idx !== -1) {
                list.splice(idx, 1);
            }
            list.unshift(iconName);
            list = list.slice(0, RECENTLY_USED_LIMIT);
            localStorage.setItem(RECENTLY_USED_KEY, JSON.stringify(list));
        } catch (e) {
            // ignore localStorage errors
        }
    }

    function buildCategoryOptionsHtml(icons) {
        var counts = {};
        icons.forEach(function (icon) {
            counts[icon.category] = (counts[icon.category] || 0) + 1;
        });

        var html = '<option value="all">Alle (' + icons.length + ')</option>';
        Object.keys(counts).sort().forEach(function (category) {
            var label = category.charAt(0).toUpperCase() + category.slice(1);
            html += '<option value="' + escapeHtml(category) + '">' + escapeHtml(label) + ' (' + counts[category] + ')</option>';
        });

        return html;
    }

    function renderIconPreview(icon, ratio) {
        var svgMarkup = sanitizeInlineSvg(icon && icon.svg ? icon.svg : '');
        if (svgMarkup) {
            return '<span class="utbip-icon-preview" style="font-size:' + ratio + 'em;">' + svgMarkup + '</span>';
        }

        return '<span class="utbip-icon-preview" style="font-size:' + ratio + 'em;">&#9675;</span>';
    }

    function ensureCss() {
        if (cssInjected) {
            return;
        }

        var css = ''
            + '.utbip-panel{position:fixed;z-index:100120;width:520px;max-width:95vw;max-height:82vh;display:flex;flex-direction:column;border:1px solid #d0d6e1;border-radius:10px;background:#fff;color:#243040;box-shadow:0 16px 42px rgba(16,24,40,.24)}'
            + '.utbip-panel[hidden]{display:none!important}'
            + '.utbip-head{display:flex;align-items:center;gap:8px;padding:8px 12px;border-bottom:1px solid #e4e8ef;cursor:move;user-select:none}'
            + '.utbip-title{flex:1;font-size:15px;font-weight:700}'
            + '.utbip-close{border:0;background:transparent;font-size:28px;line-height:1;cursor:pointer;color:#445166}'
            + '.utbip-body{padding:10px 12px;overflow:auto}'
            + '.utbip-label{display:block;font-size:13px;color:#5b6778;margin:0 0 4px}'
            + '.utbip-field{margin:0 0 8px}'
            + '.utbip-input,.utbip-select{width:100%;height:34px;border:1px solid #cfd5de;border-radius:8px;padding:6px 9px;background:#fff;color:#243040}'
            + '.utbip-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:6px;max-height:265px;overflow:auto;padding:1px 0 2px}'
            + '.utbip-cell{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:72px;padding:7px 5px;border:1px solid #cfd5de;border-radius:8px;background:#fff;cursor:pointer}'
            + '.utbip-cell:hover{border-color:#2074d4;background:#f4f8ff}'
            + '.utbip-cell.is-active{border-color:#1e6bd6;background:#e9f1ff;box-shadow:0 0 0 1px #1e6bd6 inset}'
            + '.utbip-name{font-size:11px;line-height:1.15;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}'
            + '.utbip-preview{margin:6px 0 8px;display:flex;align-items:center;gap:6px;min-height:22px}'
            + '.utbip-preview code{font-size:12px}'
            + '.utbip-actions{display:flex;justify-content:flex-end;gap:8px;padding:8px 12px;border-top:1px solid #e4e8ef}'
            + '.utbip-btn{height:32px;padding:0 12px;border-radius:8px;border:1px solid #cfd5de;background:#fff;color:#243040;cursor:pointer;font-weight:600}'
            + '.utbip-btn--primary{border-color:#1e6bd6;background:#1e6bd6;color:#fff}'
            + '.utbip-icon-preview{display:inline-flex;align-items:center;justify-content:center;line-height:1}'
            + '.utbip-empty{padding:10px;color:#6a7484;border:1px dashed #c9ced6;border-radius:8px}'
            + '.utbip-section-label{font-size:12px;font-weight:700;color:#5b6778;margin:8px 0 6px;text-transform:uppercase;letter-spacing:0.5px}'
            + '.utbip-recent-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:6px;margin:0 0 12px;padding:0 0 10px;border-bottom:1px solid #e4e8ef}'
            + 'body.rex-theme-dark .utbip-panel{border-color:#3a4654;background:#1f2933;color:#e8f0f7}'
            + 'body.rex-theme-dark .utbip-head,body.rex-theme-dark .utbip-actions{border-color:#3a4654}'
            + 'body.rex-theme-dark .utbip-label{color:#b8c7d6}'
            + 'body.rex-theme-dark .utbip-input,body.rex-theme-dark .utbip-select{border-color:#3a4654;background:#2a3642;color:#e8f0f7}'
            + 'body.rex-theme-dark .utbip-cell{border-color:#3a4654;background:#2a3642}'
            + 'body.rex-theme-dark .utbip-cell:hover{border-color:#67a3ea;background:#314252}'
            + 'body.rex-theme-dark .utbip-cell.is-active{border-color:#67a3ea;background:#2f4760;box-shadow:0 0 0 1px #67a3ea inset}'
            + 'body.rex-theme-dark .utbip-btn{border-color:#3a4654;background:#2a3642;color:#e8f0f7}'
            + 'body.rex-theme-dark .utbip-btn--primary{border-color:#67a3ea;background:#67a3ea;color:#152231}'
            + 'body.rex-theme-dark .utbip-section-label{color:#b8c7d6}'
            + 'body.rex-theme-dark .utbip-recent-grid{border-color:#3a4654}'
            + '@media (prefers-color-scheme: dark){body.rex-has-theme:not(.rex-theme-light) .utbip-panel{border-color:#3a4654;background:#1f2933;color:#e8f0f7}body.rex-has-theme:not(.rex-theme-light) .utbip-head,body.rex-has-theme:not(.rex-theme-light) .utbip-actions{border-color:#3a4654}body.rex-has-theme:not(.rex-theme-light) .utbip-label{color:#b8c7d6}body.rex-has-theme:not(.rex-theme-light) .utbip-input,body.rex-has-theme:not(.rex-theme-light) .utbip-select{border-color:#3a4654;background:#2a3642;color:#e8f0f7}body.rex-has-theme:not(.rex-theme-light) .utbip-cell{border-color:#3a4654;background:#2a3642}body.rex-has-theme:not(.rex-theme-light) .utbip-cell:hover{border-color:#67a3ea;background:#314252}body.rex-has-theme:not(.rex-theme-light) .utbip-cell.is-active{border-color:#67a3ea;background:#2f4760;box-shadow:0 0 0 1px #67a3ea inset}body.rex-has-theme:not(.rex-theme-light) .utbip-btn{border-color:#3a4654;background:#2a3642;color:#e8f0f7}body.rex-has-theme:not(.rex-theme-light) .utbip-btn--primary{border-color:#67a3ea;background:#67a3ea;color:#152231}body.rex-has-theme:not(.rex-theme-light) .utbip-section-label{color:#b8c7d6}body.rex-has-theme:not(.rex-theme-light) .utbip-recent-grid{border-color:#3a4654}}';

        var style = document.createElement('style');
        style.setAttribute('data-utbip-style', '1');
        style.textContent = css;
        document.head.appendChild(style);
        cssInjected = true;
    }

    function normalizeIconSize(value) {
        var parsed = parseFloat(value);
        if (!isFinite(parsed) || parsed <= 0) {
            return 1;
        }
        return parsed;
    }

    function buildInsertHtml(icon, iconSize) {
        var iconName = icon.name;
        var size = normalizeIconSize(iconSize);
        var baseStyle = 'display:inline-flex;align-items:center;vertical-align:middle;line-height:1;font-size:' + size + 'em;';

        var inlineSvg = sanitizeInlineSvg(icon.svg);
        if (!inlineSvg) {
            return '<span class="for-uikit-icon" data-for-uikit-icon="1" data-uikit-icon-name="' + iconName + '" role="img" aria-label="Icon ' + iconName + '" style="' + baseStyle + '"></span>';
        }

        return '<span class="for-uikit-icon" data-for-uikit-icon="1" data-uikit-icon-name="' + iconName + '" role="img" aria-label="Icon ' + iconName + '" style="' + baseStyle + '">' + inlineSvg + '</span>';
    }

    function updatePanel(root) {
        var icons = root.__icons || [];
        var state = root.__state;
        var filtered = filterIcons(icons, state.category, state.search).slice(0, 500);
        if (!state.icon || !filtered.some(function (item) { return item.name === state.icon; })) {
            state.icon = filtered.length ? filtered[0].name : '';
        }

        var grid = root.querySelector('[data-utbip-grid]');
        var preview = root.querySelector('[data-utbip-preview]');
        var recentContainer = root.querySelector('[data-utbip-recent]');

        // Show recently used only when no search/category filter is active
        var showRecent = state.category === 'all' && (!state.search || state.search.trim() === '');
        var recentList = showRecent ? getRecentlyUsed() : [];
        var recentIcons = recentList
            .map(function (name) { return icons.find(function (i) { return i.name === name; }); })
            .filter(function (i) { return i; });

        if (recentIcons.length && showRecent) {
            var recentHtml = '<div class="utbip-section-label">Zuletzt verwendet</div>';
            recentHtml += '<div class="utbip-recent-grid">';
            recentIcons.forEach(function (icon) {
                var activeClass = icon.name === state.icon ? ' is-active' : '';
                recentHtml += '<button type="button" class="utbip-cell' + activeClass + '" data-utbip-icon="' + escapeHtml(icon.name) + '" title="' + escapeHtml(icon.name) + '">';
                recentHtml += renderIconPreview(icon, 1.35);
                recentHtml += '<span class="utbip-name">' + escapeHtml(icon.name) + '</span>';
                recentHtml += '</button>';
            });
            recentHtml += '</div>';
            recentContainer.innerHTML = recentHtml;
        } else {
            recentContainer.innerHTML = '';
        }

        if (!filtered.length) {
            grid.innerHTML = '<div class="utbip-empty">Keine Icons fuer den aktuellen Filter gefunden.</div>';
            preview.innerHTML = '<span style="color:#7b8697;">Kein Icon ausgewaehlt</span>';
            return;
        }

        var html = '';
        filtered.forEach(function (icon) {
            var activeClass = icon.name === state.icon ? ' is-active' : '';
            html += '<button type="button" class="utbip-cell' + activeClass + '" data-utbip-icon="' + escapeHtml(icon.name) + '" title="' + escapeHtml(icon.name) + '">';
            html += renderIconPreview(icon, 1.35);
            html += '<span class="utbip-name">' + escapeHtml(icon.name) + '</span>';
            html += '</button>';
        });
        grid.innerHTML = html;

        var selectedIcon = filtered.find(function (item) { return item.name === state.icon; });
        if (!selectedIcon) {
            preview.innerHTML = '<span style="color:#7b8697;">Kein Icon ausgewaehlt</span>';
            return;
        }

        preview.innerHTML = renderIconPreview(selectedIcon, normalizeIconSize(state.size) + 0.2) + '<code>' + escapeHtml(selectedIcon.name) + '</code>';
    }

    function makePanelHtml(categoryOptionsHtml) {
        return ''
            + '<div class="utbip-head" data-utbip-drag>'
            + '  <span class="utbip-title">UIKit Icon auswaehlen</span>'
            + '  <button class="utbip-close" type="button" data-utbip-close aria-label="Schliessen">×</button>'
            + '</div>'
            + '<div class="utbip-body">'
            + '  <div class="utbip-field">'
            + '    <label class="utbip-label">Kategorie</label>'
            + '    <select class="utbip-select" data-utbip-category>' + categoryOptionsHtml + '</select>'
            + '  </div>'
            + '  <div class="utbip-field">'
            + '    <label class="utbip-label">Suche</label>'
            + '    <input class="utbip-input" data-utbip-search type="text" placeholder="Icon-Name eingeben (z.B. home, custom-...)" />'
            + '  </div>'
            + '  <div class="utbip-recent-container" data-utbip-recent></div>'
            + '  <div class="utbip-grid" data-utbip-grid></div>'
            + '  <div class="utbip-preview" data-utbip-preview></div>'
            + '  <div class="utbip-field">'
            + '    <label class="utbip-label">Groesse</label>'
            + '    <select class="utbip-select" data-utbip-size>'
            + '      <option value="0.75">Klein (0.75em)</option>'
            + '      <option value="1" selected>Normal (1em)</option>'
            + '      <option value="1.25">Mittel (1.25em)</option>'
            + '      <option value="1.5">Gross (1.5em)</option>'
            + '      <option value="2">Sehr gross (2em)</option>'
            + '    </select>'
            + '  </div>'
            + '</div>'
            + '<div class="utbip-actions">'
            + '  <button class="utbip-btn" type="button" data-utbip-cancel>Abbrechen</button>'
            + '  <button class="utbip-btn utbip-btn--primary" type="button" data-utbip-insert>Icon einfuegen</button>'
            + '</div>';
    }

    function wireDrag(root) {
        var head = root.querySelector('[data-utbip-drag]');
        var drag = { on: false, x: 0, y: 0, left: 0, top: 0 };

        head.addEventListener('mousedown', function (event) {
            if (event.target.closest('[data-utbip-close]')) {
                return;
            }
            drag.on = true;
            drag.x = event.clientX;
            drag.y = event.clientY;
            var rect = root.getBoundingClientRect();
            drag.left = rect.left;
            drag.top = rect.top;
            event.preventDefault();
        });

        document.addEventListener('mousemove', function (event) {
            if (!drag.on) {
                return;
            }
            var nextLeft = drag.left + (event.clientX - drag.x);
            var nextTop = drag.top + (event.clientY - drag.y);
            nextLeft = Math.max(8, Math.min(window.innerWidth - 120, nextLeft));
            nextTop = Math.max(8, Math.min(window.innerHeight - 64, nextTop));
            root.style.left = nextLeft + 'px';
            root.style.top = nextTop + 'px';
        });

        document.addEventListener('mouseup', function () {
            drag.on = false;
        });
    }

    function setPanelPosition(editor, root) {
        var container = editor.getContainer();
        var rect = container ? container.getBoundingClientRect() : { top: 64, right: window.innerWidth - 20 };
        var panelWidth = 560;
        var left = Math.max(8, Math.min(window.innerWidth - panelWidth - 8, (rect.right || window.innerWidth) - panelWidth));
        var top = Math.max(8, rect.top || 64);
        root.style.left = left + 'px';
        root.style.top = top + 'px';
    }

    function hidePanel(editor) {
        var root = panelsByEditor.get(editor);
        if (root) {
            root.hidden = true;
        }
    }

    function readIconsApiUrl() {
        var urls = [];

        try {
            urls.push(window.uikitThemeIconsApiUrl);
        } catch (e) {
            // ignore
        }
        try {
            if (window.parent && window.parent !== window) {
                urls.push(window.parent.uikitThemeIconsApiUrl);
            }
        } catch (e) {
            // ignore
        }
        try {
            if (window.top && window.top !== window && window.top !== window.parent) {
                urls.push(window.top.uikitThemeIconsApiUrl);
            }
        } catch (e) {
            // ignore
        }

        for (var i = 0; i < urls.length; i += 1) {
            if (typeof urls[i] === 'string' && urls[i].trim() !== '') {
                return urls[i];
            }
        }
        return 'index.php?rex-api-call=uikit_theme_icons';
    }

    function readIconsFromKnownWindows() {
        var candidates = [];

        try {
            candidates.push(window.uikitAvailableIcons);
        } catch (e) {
            // ignore
        }
        try {
            if (window.parent && window.parent !== window) {
                candidates.push(window.parent.uikitAvailableIcons);
            }
        } catch (e) {
            // ignore
        }
        try {
            if (window.top && window.top !== window && window.top !== window.parent) {
                candidates.push(window.top.uikitAvailableIcons);
            }
        } catch (e) {
            // ignore
        }

        for (var i = 0; i < candidates.length; i += 1) {
            var normalized = normalizeIcons(candidates[i]);
            if (normalized.length) {
                return normalized;
            }
        }
        return [];
    }

    function loadIconsFromApi() {
        return fetch(readIconsApiUrl(), {
            method: 'GET',
            credentials: 'same-origin'
        })
            .then(function (response) { return response.json(); })
            .then(function (result) {
                if (!result || result.success !== true || !Array.isArray(result.data)) {
                    return [];
                }
                return normalizeIcons(result.data);
            })
            .catch(function () {
                return [];
            });
    }

    function resolveIcons() {
        if (Array.isArray(cachedIcons) && cachedIcons.length) {
            return Promise.resolve(cachedIcons);
        }

        var direct = readIconsFromKnownWindows();
        if (direct.length) {
            cachedIcons = direct;
            return Promise.resolve(direct);
        }

        return loadIconsFromApi().then(function (apiIcons) {
            if (apiIcons.length) {
                cachedIcons = apiIcons;
            }
            return apiIcons;
        });
    }

    function createPanel(editor, icons) {
        var root = document.createElement('div');
        root.className = 'utbip-panel';
        root.setAttribute('role', 'dialog');
        root.setAttribute('aria-label', 'UIKit Icon auswaehlen');

        root.__icons = icons;
        root.__state = {
            category: 'all',
            search: '',
            icon: icons.length ? icons[0].name : '',
            size: '1'
        };

        root.innerHTML = makePanelHtml(buildCategoryOptionsHtml(icons));

        var categorySelect = root.querySelector('[data-utbip-category]');
        var searchInput = root.querySelector('[data-utbip-search]');
        var sizeSelect = root.querySelector('[data-utbip-size]');

        categorySelect.value = root.__state.category;
        sizeSelect.value = root.__state.size;

        root.addEventListener('mousedown', function (event) {
            if (!event.target.closest('input,select,textarea,button')) {
                event.preventDefault();
            }
        });

        root.addEventListener('click', function (event) {
            if (event.target.closest('[data-utbip-close]') || event.target.closest('[data-utbip-cancel]')) {
                hidePanel(editor);
                return;
            }

            if (event.target.closest('[data-utbip-insert]')) {
                var selectedIcon = root.__icons.find(function (item) {
                    return item.name === root.__state.icon;
                });

                if (!selectedIcon) {
                    editor.notificationManager.open({
                        text: 'Bitte ein Icon auswaehlen.',
                        type: 'warning'
                    });
                    return;
                }

                addToRecentlyUsed(selectedIcon.name);
                editor.execCommand('mceInsertContent', false, buildInsertHtml(selectedIcon, root.__state.size));
                hidePanel(editor);
                return;
            }

            var iconButton = event.target.closest('[data-utbip-icon]');
            if (iconButton) {
                root.__state.icon = iconButton.getAttribute('data-utbip-icon') || '';
                updatePanel(root);
                if (event.detail >= 2) {
                    var picked = root.__icons.find(function (item) {
                        return item.name === root.__state.icon;
                    });
                    if (picked) {
                        addToRecentlyUsed(picked.name);
                        editor.execCommand('mceInsertContent', false, buildInsertHtml(picked, root.__state.size));
                        hidePanel(editor);
                    }
                }
            }
        });

        categorySelect.addEventListener('change', function () {
            root.__state.category = categorySelect.value || 'all';
            updatePanel(root);
        });

        searchInput.addEventListener('input', function () {
            root.__state.search = searchInput.value || '';
            updatePanel(root);
        });

        sizeSelect.addEventListener('change', function () {
            root.__state.size = sizeSelect.value || '1';
            updatePanel(root);
        });

        wireDrag(root);
        updatePanel(root);
        document.body.appendChild(root);
        setPanelPosition(editor, root);

        return root;
    }

    function setup(editor) {
        editor.on('PreInit', function () {
            if (editor.schema && typeof editor.schema.addValidElements === 'function') {
                editor.schema.addValidElements('span[class|style|data-for-uikit-icon|data-uikit-icon-name|aria-label|role]');
                editor.schema.addValidElements('svg[class|style|xmlns|viewBox|width|height|fill|stroke|stroke-width|stroke-linecap|stroke-linejoin|aria-hidden|focusable|role]');
                editor.schema.addValidElements('path[d|fill|stroke|stroke-width|stroke-linecap|stroke-linejoin|transform]');
                editor.schema.addValidElements('g[fill|stroke|stroke-width|transform]');
                editor.schema.addValidElements('circle[cx|cy|r|fill|stroke|stroke-width]');
                editor.schema.addValidElements('rect[x|y|width|height|rx|ry|fill|stroke|stroke-width]');
                editor.schema.addValidElements('line[x1|y1|x2|y2|stroke|stroke-width|stroke-linecap]');
                editor.schema.addValidElements('polyline[points|fill|stroke|stroke-width|stroke-linecap|stroke-linejoin]');
                editor.schema.addValidElements('polygon[points|fill|stroke|stroke-width|stroke-linejoin]');
            }
        });

        var openPicker = function () {
            ensureCss();

            resolveIcons().then(function (icons) {
                if (!icons.length) {
                    editor.notificationManager.open({
                        text: 'Keine UIKit-Icons verfuegbar. Bitte uikit_theme_builder pruefen.',
                        type: 'warning'
                    });
                    return;
                }

                var root = panelsByEditor.get(editor);
                if (!root || !root.isConnected) {
                    root = createPanel(editor, icons);
                    panelsByEditor.set(editor, root);
                    editor.on('remove', function () {
                        if (root && root.parentNode) {
                            root.parentNode.removeChild(root);
                        }
                        panelsByEditor.delete(editor);
                    });
                } else {
                    root.__icons = icons;
                    root.hidden = false;
                    setPanelPosition(editor, root);
                    updatePanel(root);
                }
            });
        };

        editor.ui.registry.addButton('uikit_iconpicker', {
            icon: 'browse',
            tooltip: 'UIKit Icon Picker',
            onAction: openPicker
        });

        editor.ui.registry.addMenuItem('uikit_iconpicker', {
            text: 'UIKit Icon einfuegen',
            icon: 'browse',
            onAction: openPicker
        });

        editor.on('click', function (event) {
            var target = event.target;
            if (!target || typeof target.closest !== 'function') {
                return;
            }

            var iconNode = target.closest('.for-uikit-icon');
            if (!iconNode) {
                return;
            }

            editor.selection.select(iconNode);
            editor.nodeChanged();
        });

        editor.on('keydown', function (event) {
            if (event.key !== 'Backspace' && event.key !== 'Delete') {
                return;
            }

            var node = editor.selection.getNode();
            if (!node || typeof node.closest !== 'function') {
                return;
            }

            var iconNode = node.closest('.for-uikit-icon');
            if (!iconNode) {
                return;
            }

            event.preventDefault();
            editor.dom.remove(iconNode);
            editor.nodeChanged();
        });
    }

    if (typeof tinymce !== 'undefined') {
        tinymce.PluginManager.add('uikit_iconpicker', setup);
    }
})();

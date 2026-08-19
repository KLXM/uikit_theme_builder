(function () {
    'use strict';

    window.__utbTinyDebugExternalLoaded = true;

    if (!document || !document.body) {
        return;
    }

    var el = document.getElementById('utb-tiny-debug-external');
    if (!el) {
        el = document.createElement('div');
        el.id = 'utb-tiny-debug-external';
        el.style.position = 'fixed';
        el.style.left = '8px';
        el.style.bottom = '8px';
        el.style.zIndex = '2147483647';
        el.style.padding = '4px 8px';
        el.style.borderRadius = '4px';
        el.style.font = '12px/1.2 monospace';
        el.style.background = '#0d47a1';
        el.style.color = '#fff';
        el.style.pointerEvents = 'none';
        document.body.appendChild(el);
    }

    el.textContent = 'UTB external JS executed';
})();

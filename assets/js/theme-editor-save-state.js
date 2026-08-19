(function () {
    'use strict';

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
                statusText.textContent = 'Speichert gerade...';
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
                saveButton.setAttribute('disabled', 'disabled');
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
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditorSaveState);
    } else {
        initEditorSaveState();
    }
})();

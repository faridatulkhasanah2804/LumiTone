/**
 * theme.js
 * ------------------------------------------------------------------
 * Light / Dark mode for the whole LumiTone dashboard.
 * Pairs with the `[data-theme="dark"]` tokens in assets/css/variables.css.
 *
 * - Loaded on every page via includes/header.php, so dark mode works
 *   the same way on dashboard.php, history.php, products.php,
 *   saved.php, profile.php, settings.php, etc.
 * - A tiny inline snippet at the very top of <head> (see header.php)
 *   applies the saved theme before first paint, so there's no flash
 *   of the wrong theme.
 * - Exposes window.LumiTheme.set('light' | 'dark') so any page/button
 *   can change the theme programmatically (e.g. a quick toggle in
 *   the topbar later on).
 * - Automatically wires up any `input[name="theme"]` radios it finds
 *   on the page (currently only settings.php has them).
 * - Keeps every open tab/page in sync via the "storage" event, so
 *   switching theme in one tab updates the others immediately.
 * ------------------------------------------------------------------
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'lt-theme'; // same key settings.js used for its old local-only toggle

    function getStoredTheme() {
        try {
            return localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }
    }

    function currentTheme() {
        return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function syncThemeInputs(theme) {
        var wantedValue = theme === 'dark' ? 'Dark Mode' : 'Light Mode';
        document.querySelectorAll('input[name="theme"]').forEach(function (input) {
            input.checked = (input.value === wantedValue);
        });
    }

    function setTheme(theme, opts) {
        opts = opts || {};
        theme = theme === 'dark' ? 'dark' : 'light';

        applyTheme(theme);

        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (e) {
            /* localStorage unavailable (private mode, etc.) — theme still
               applies for this page load, it just won't persist. */
        }

        if (!opts.silent) {
            syncThemeInputs(theme);
        }

        document.dispatchEvent(new CustomEvent('lumitheme:change', { detail: { theme: theme } }));
    }

    function initToggleUI() {
        document.querySelectorAll('input[name="theme"]').forEach(function (input) {
            input.addEventListener('change', function () {
                if (!input.checked) return;
                setTheme(input.value === 'Dark Mode' ? 'dark' : 'light', { silent: true });
            });
        });
        // Make sure whichever radio matches the active theme is checked,
        // even if the page rendered "Light Mode" as checked by default.
        syncThemeInputs(currentTheme());
    }

    // Cross-tab sync: if the user flips the switch on another tab of the
    // dashboard, reflect it here immediately.
    window.addEventListener('storage', function (e) {
        if (e.key === STORAGE_KEY && e.newValue) {
            applyTheme(e.newValue);
            syncThemeInputs(e.newValue);
        }
    });

    document.addEventListener('DOMContentLoaded', initToggleUI);

    window.LumiTheme = {
        set: setTheme,
        get: currentTheme,
        STORAGE_KEY: STORAGE_KEY
    };
})();
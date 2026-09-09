/**
 * theme.js
 * ------------------------------------------------------------------
 * Dark mode for LumiTone. Toggling is driven by data-theme="dark"|"light"
 * on <html>, which flips every color token in variables.css (see the
 * "Dark Mode" block at the bottom of that file).
 *
 * Two responsibilities:
 *   1. Apply the saved theme as early as possible (see note below) so
 *      there's no light-mode flash before the page paints.
 *   2. Keep the Theme radio pills on settings.php (name="theme",
 *      values "Light Mode" / "Dark Mode") in sync with it.
 *
 * IMPORTANT — avoiding flash of light mode:
 * Loading this whole file with a normal <script src="..."> at the
 * bottom of <body> (like dashboard.php/settings.php do with their
 * page scripts) still lets the page paint light-mode colors for a
 * split second first. To prevent that, copy just the IIFE below
 * ("1. Apply saved theme") into an inline <script> at the very top
 * of <head> in includes/header.php, before any <link rel="stylesheet">
 * tags. Keep this full file too (loaded normally) — it's what wires
 * up the Settings toggle in step 2.
 * ------------------------------------------------------------------
 */

(function applySavedTheme() {
    var STORAGE_KEY = 'lt-theme';
    var saved = null;
    try {
        saved = localStorage.getItem(STORAGE_KEY);
    } catch (e) {
        /* localStorage unavailable (private mode, etc.) — fall back to light */
    }
    var theme = saved === 'dark' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', theme);
})();

/* ------------------------------------------------------------------
 * 2. Sync with the Settings page toggle + expose a helper for any
 *    other toggle (e.g. a future sun/moon button in the topbar).
 * ------------------------------------------------------------------ */
window.ltSetTheme = function ltSetTheme(theme) {
    theme = theme === 'dark' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', theme);
    try {
        localStorage.setItem('lt-theme', theme);
    } catch (e) {
        /* ignore — theme just won't persist across reloads */
    }
};

window.ltToggleTheme = function ltToggleTheme() {
    var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    ltSetTheme(current === 'dark' ? 'light' : 'dark');
};

document.addEventListener('DOMContentLoaded', function () {
    /* Make sure the settings.php radio pills reflect the theme actually
       in effect (covers e.g. a saved DB preference disagreeing with
       localStorage on first load). */
    var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    var themeRadios = document.querySelectorAll('input[name="theme"]');
    themeRadios.forEach(function (radio) {
        var isDarkOption = radio.value === 'Dark Mode';
        radio.checked = isDarkOption ? current === 'dark' : current === 'light';

        radio.addEventListener('change', function () {
            if (!radio.checked) return;
            ltSetTheme(radio.value === 'Dark Mode' ? 'dark' : 'light');
        });
    });
});
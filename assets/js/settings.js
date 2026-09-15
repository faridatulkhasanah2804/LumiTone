/**
 * settings.js
 * ------------------------------------------------------------------
 * Interactions for the Settings page (settings.php):
 *  1) Toggle switches (Notifications, Data Sharing, Two-Factor) —
 *     dummy state only, logged to console for now
 *  2) Radio pill groups (Theme, Font Size) + color swatch group
 *     (Accent Color) — logged to console; Theme's *actual* dark-mode
 *     switching lives in assets/js/theme.js (loaded globally by
 *     includes/header.php), which already listens on the same
 *     input[name="theme"] radios and applies [data-theme="dark"] on
 *     <html> + persists it to localStorage under "lt-theme". No need
 *     to duplicate that here.
 *  3) Inline select dropdowns (Profile Visibility, Language, etc.)
 *  4) Action buttons (Clear Cache, Download My Data, Export History,
 *     Restore Defaults, Manage Devices, Logout) — dummy confirmations
 *  5) Delete Account — requires an explicit confirmation before
 *     "deleting" (dummy only, no backend call yet)
 *
 * Depends on nothing outside the DOM; dashboard.js (loaded globally by
 * includes/footer.php) is untouched and unaffected by this file.
 * ------------------------------------------------------------------
 */
(function () {
    'use strict';

    /* ---- 1) Toggle switches ---- */
    document.querySelectorAll('.settings-switch input[type="checkbox"]').forEach(function (input) {
        input.addEventListener('change', function () {
            // Front-end only for now — wire this up to a real endpoint later.
            console.log('[Settings] ' + (input.dataset.pref || 'toggle') + ' = ' + input.checked);
        });
    });

    /* ---- 2) Radio pill / color groups (purely visual state, browser handles the radio logic) ----
       Theme's actual dark-mode switching is handled globally by assets/js/theme.js —
       see the file header above. This listener just keeps logging every pill group,
       Theme included, for consistency/debugging. */
    document.querySelectorAll('.settings-pill input[type="radio"], .settings-color-option input[type="radio"]').forEach(function (input) {
        input.addEventListener('change', function () {
            console.log('[Settings] ' + input.name + ' = ' + input.value);
        });
    });

    /* ---- 3) Inline selects ---- */
    document.querySelectorAll('[data-pref]').forEach(function (el) {
        if (el.tagName !== 'SELECT') return;
        el.addEventListener('change', function () {
            console.log('[Settings] ' + el.dataset.pref + ' = ' + el.value);
        });
    });

    /* ---- 4) Action buttons ---- */
    var actionMessages = {
        'devices':          'Membuka daftar perangkat yang terhubung (dummy — belum terhubung ke server).',
        'clear-cache':      'Cache berhasil dibersihkan (dummy — belum terhubung ke server).',
        'download-data':    'Menyiapkan unduhan data akunmu (dummy — belum terhubung ke server).',
        'export-history':   'Riwayat analisis sedang diekspor ke CSV (dummy — belum terhubung ke server).',
        'restore-defaults': 'Semua pengaturan dikembalikan ke default (dummy — belum terhubung ke server).',
        'logout':           'Logout berhasil (dummy — belum terhubung ke server).',
    };

    document.querySelectorAll('.settings-action-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var action = btn.dataset.action;
            var message = actionMessages[action] || 'Aksi dijalankan (dummy — belum terhubung ke server).';

            if (action === 'restore-defaults') {
                var confirmed = window.confirm('Kembalikan semua pengaturan ke default?');
                if (!confirmed) return;

                // Restore Defaults should also bring the theme back to light mode,
                // since it's a "back to app defaults" action.
                if (window.LumiTheme) {
                    window.LumiTheme.set('light');
                }
            }

            window.alert(message);
        });
    });

    /* ---- 5) Delete Account ---- */
    var deleteBtn = document.querySelector('.settings-delete-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            var confirmed = window.confirm(
                'Hapus akun secara permanen? Semua data analisis, riwayat, dan hasil tersimpan akan hilang dan tidak dapat dikembalikan.'
            );
            if (!confirmed) return;

            window.alert('Akun berhasil dihapus (dummy — belum terhubung ke server).');
        });
    }
})();
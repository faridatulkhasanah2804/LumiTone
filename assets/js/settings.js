/**
 * settings.js
 * ------------------------------------------------------------------
 * Interactions for the Settings page (settings.php)
 * ------------------------------------------------------------------
 */

(function () {
    'use strict';

    var ENDPOINT = 'includes/settings_actions.php';

    /* ---- tiny helpers ---- */

    function post(action, extraFields) {
        var body = new URLSearchParams();
        body.set('action', action);

        if (extraFields) {
            Object.keys(extraFields).forEach(function (k) {
                body.set(k, extraFields[k]);
            });
        }

        return fetch(ENDPOINT, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        })
            .then(function (res) {
                return res.json().catch(function () {
                    return {
                        success: false,
                        message: 'Respon server tidak valid.'
                    };
                });
            })
            .catch(function () {
                return {
                    success: false,
                    message: 'Tidak bisa terhubung ke server.'
                };
            });
    }

    function toast(message, isError) {
        if (isError) {
            window.alert(message);
            return;
        }

        var el = document.createElement('div');

        el.textContent = message;

        el.style.cssText = [
            'position:fixed',
            'left:50%',
            'bottom:28px',
            'transform:translate(-50%,12px)',
            'background:#1f2937',
            'color:#fff',
            'padding:10px 18px',
            'border-radius:999px',
            'font:500 13px/1.4 Poppins,sans-serif',
            'box-shadow:0 8px 24px rgba(0,0,0,.18)',
            'opacity:0',
            'transition:opacity .25s ease, transform .25s ease',
            'z-index:9999',
            'pointer-events:none'
        ].join(';');

        document.body.appendChild(el);

        requestAnimationFrame(function () {
            el.style.opacity = '1';
            el.style.transform = 'translate(-50%,0)';
        });

        setTimeout(function () {
            el.style.opacity = '0';
            el.style.transform = 'translate(-50%,12px)';

            setTimeout(function () {
                el.remove();
            }, 300);
        }, 2200);
    }

    function savePref(key, value, onDone) {
        post('update_pref', {
            key: key,
            value: value
        }).then(function (res) {
            if (res.success) {
                toast('Tersimpan.');
            } else {
                toast(
                    res.message || 'Gagal menyimpan pengaturan.',
                    true
                );
            }

            if (typeof onDone === 'function') {
                onDone(res);
            }
        });
    }

    /* ---- 1) Toggle switches ---- */

    document
        .querySelectorAll('.settings-switch input[type="checkbox"]')
        .forEach(function (input) {

            input.addEventListener('change', function () {
                var key = input.dataset.pref;

                if (!key) return;

                savePref(
                    key,
                    input.checked ? '1' : '0',
                    function (res) {
                        if (!res.success) {
                            input.checked = !input.checked;
                        }
                    }
                );
            });
        });

    /* ---- 2) Radio pill / color groups ---- */

    var RADIO_PREF_MAP = {
        theme: 'appearance.theme',
        accent: 'appearance.accent',
        fontSize: 'appearance.fontSize'
    };

    document
        .querySelectorAll(
            '.settings-pill input[type="radio"], ' +
            '.settings-color-option input[type="radio"]'
        )
        .forEach(function (input) {

            input.addEventListener('change', function () {
                if (!input.checked) return;

                var key = RADIO_PREF_MAP[input.name];

                if (!key) return;

                savePref(key, input.value);

                /*
                 * Theme switching is primarily handled by theme.js.
                 * If LumiTheme is available, use it so there is only
                 * one source responsible for the visual theme.
                 */
                if (
                    input.name === 'theme' &&
                    window.LumiTheme &&
                    typeof window.LumiTheme.set === 'function'
                ) {
                    var themeValue =
                        input.value === 'Dark Mode'
                            ? 'dark'
                            : 'light';

                    window.LumiTheme.set(themeValue);
                }
            });
        });

    /* ---- 3) Inline selects ---- */

    document
        .querySelectorAll('[data-pref]')
        .forEach(function (el) {

            if (el.tagName !== 'SELECT') return;

            el.addEventListener('change', function () {
                savePref(
                    el.dataset.pref,
                    el.value
                );
            });
        });

    /* ---- 4) Action buttons ---- */

    function downloadViaGet(action) {
        var iframe = document.createElement('iframe');

        iframe.style.display = 'none';

        iframe.src =
            ENDPOINT +
            '?action=' +
            encodeURIComponent(action);

        document.body.appendChild(iframe);

        setTimeout(function () {
            iframe.remove();
        }, 10000);
    }

    document
        .querySelectorAll('.settings-action-btn')
        .forEach(function (btn) {

            btn.addEventListener('click', function () {

                var action = btn.dataset.action;

                switch (action) {

                    case 'devices':

                        post('get_devices')
                            .then(function (res) {

                                if (!res.success) {
                                    toast(
                                        res.message ||
                                        'Gagal memuat daftar perangkat.',
                                        true
                                    );
                                    return;
                                }

                                var lines = res.devices.map(
                                    function (d) {
                                        return (
                                            d.current
                                                ? '• (Perangkat ini) '
                                                : '• '
                                        ) +
                                        d.user_agent +
                                        ' — ' +
                                        d.ip;
                                    }
                                );

                                window.alert(
                                    'Perangkat aktif:\n\n' +
                                    lines.join('\n') +
                                    (
                                        res.note
                                            ? '\n\n' + res.note
                                            : ''
                                    )
                                );
                            });

                        break;

                    case 'clear-cache':

                        btn.disabled = true;

                        post('clear_cache')
                            .then(function (res) {

                                btn.disabled = false;

                                toast(
                                    res.message ||
                                    (
                                        res.success
                                            ? 'Cache dibersihkan.'
                                            : 'Gagal membersihkan cache.'
                                    ),
                                    !res.success
                                );
                            });

                        break;

                    case 'download-data':

                        downloadViaGet('download_data');

                        toast(
                            'Menyiapkan unduhan data akunmu…'
                        );

                        break;

                    case 'export-history':

                        downloadViaGet('export_history');

                        toast(
                            'Riwayat analisis sedang diekspor…'
                        );

                        break;

                    case 'restore-defaults':

                        if (
                            !window.confirm(
                                'Kembalikan semua pengaturan ke default?'
                            )
                        ) {
                            return;
                        }

                        btn.disabled = true;

                        post('restore_defaults')
                            .then(function (res) {

                                btn.disabled = false;

                                if (res.success) {

                                    /*
                                     * Restore the visual theme as well.
                                     */
                                    if (
                                        window.LumiTheme &&
                                        typeof window.LumiTheme.set === 'function'
                                    ) {
                                        window.LumiTheme.set('light');
                                    }

                                    toast(
                                        'Pengaturan dikembalikan ke default. Memuat ulang…'
                                    );

                                    setTimeout(function () {
                                        window.location.reload();
                                    }, 600);

                                } else {

                                    toast(
                                        res.message ||
                                        'Gagal mengembalikan pengaturan.',
                                        true
                                    );
                                }
                            });

                        break;

                    case 'logout':

                        post('logout')
                            .then(function (res) {
                                window.location.href =
                                    res.redirect || 'login.php';
                            });

                        break;

                    default:

                        toast(
                            'Aksi tidak dikenal.',
                            true
                        );
                }
            });
        });

    /* ---- 5) Delete Account ---- */

    var deleteBtn =
        document.querySelector('.settings-delete-btn');

    if (deleteBtn) {

        deleteBtn.addEventListener('click', function () {

            var typed = window.prompt(
                'Tindakan ini permanen. Semua data analisis, ' +
                'riwayat, dan hasil tersimpan akan hilang.\n\n' +
                'Ketik DELETE untuk konfirmasi:'
            );

            if (typed !== 'DELETE') {

                if (typed !== null) {
                    window.alert(
                        'Konfirmasi tidak cocok. Akun tidak dihapus.'
                    );
                }

                return;
            }

            deleteBtn.disabled = true;

            post('delete_account', {
                confirm: 'DELETE'
            }).then(function (res) {

                if (res.success) {

                    window.alert(
                        'Akun berhasil dihapus.'
                    );

                    window.location.href =
                        res.redirect || 'login.php';

                } else {

                    deleteBtn.disabled = false;

                    window.alert(
                        res.message ||
                        'Gagal menghapus akun.'
                    );
                }
            });
        });
    }

})();
/**
 * profile.js
 * ------------------------------------------------------------------
 * Interactions for the Profile page (profile.php):
 *  1) Tab switching (Account Info / Security / Preferences)
 *  2) Edit-mode toggle for the Account Info form (enables the fields
 *     + reveals Save/Cancel actions)
 *  3) Dummy form submit handlers (front-end only — no backend call yet)
 *  4) Toggle switches for notification preferences
 *
 * Depends on nothing outside the DOM; dashboard.js (loaded globally by
 * includes/footer.php) is untouched and unaffected by this file.
 * ------------------------------------------------------------------
 */
(function () {
    'use strict';

    /* ---- 1) Tab switching ---- */
    var tabsWrap = document.getElementById('profileTabs');
    var panels = {
        account:     document.getElementById('profilePanelAccount'),
        security:    document.getElementById('profilePanelSecurity'),
        preferences: document.getElementById('profilePanelPreferences'),
    };

    if (tabsWrap) {
        tabsWrap.addEventListener('click', function (event) {
            var tab = event.target.closest('.profile-tab');
            if (!tab) return;

            Array.prototype.forEach.call(tabsWrap.querySelectorAll('.profile-tab'), function (btn) {
                btn.classList.remove('is-active');
                btn.setAttribute('aria-selected', 'false');
            });
            tab.classList.add('is-active');
            tab.setAttribute('aria-selected', 'true');

            var target = tab.dataset.tab;
            Object.keys(panels).forEach(function (key) {
                if (panels[key]) panels[key].classList.toggle('is-hidden', key !== target);
            });
        });
    }

    /* ---- 2) Edit-mode toggle for Account Info ---- */
    var editToggle   = document.getElementById('profileEditToggle');
    var accountForm  = document.getElementById('profileAccountForm');
    var accountActions = document.getElementById('profileAccountActions');
    var cancelBtn    = document.getElementById('profileCancelBtn');

    function setAccountFieldsEnabled(enabled) {
        if (!accountForm) return;
        Array.prototype.forEach.call(accountForm.querySelectorAll('input'), function (input) {
            input.disabled = !enabled;
        });
        if (accountActions) accountActions.classList.toggle('is-hidden', !enabled);
    }

    if (editToggle) {
        editToggle.addEventListener('click', function () {
            setAccountFieldsEnabled(true);
            // Switch to the Account Info tab if the user isn't already there
            var accountTab = tabsWrap ? tabsWrap.querySelector('[data-tab="account"]') : null;
            if (accountTab && !accountTab.classList.contains('is-active')) {
                accountTab.click();
            }
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            setAccountFieldsEnabled(false);
        });
    }

    if (accountForm) {
        accountForm.addEventListener('submit', function (event) {
            event.preventDefault();
            setAccountFieldsEnabled(false);
            // Front-end only for now — wire this up to a real endpoint later.
            window.alert('Perubahan profil disimpan (dummy — belum terhubung ke server).');
        });
    }

    /* ---- 3) Security form (dummy submit) ---- */
    var securityForm = document.getElementById('profileSecurityForm');
    if (securityForm) {
        securityForm.addEventListener('submit', function (event) {
            event.preventDefault();

            var newPass = securityForm.querySelector('[name="new_password"]');
            var confirmPass = securityForm.querySelector('[name="confirm_password"]');

            if (newPass && confirmPass && newPass.value !== confirmPass.value) {
                window.alert('Konfirmasi password baru tidak cocok.');
                return;
            }

            window.alert('Password berhasil diperbarui (dummy — belum terhubung ke server).');
            securityForm.reset();
        });
    }

    /* ---- 4) Preferences: save button (dummy) ---- */
    var savePrefsBtn = document.getElementById('profileSavePrefsBtn');
    if (savePrefsBtn) {
        savePrefsBtn.addEventListener('click', function () {
            window.alert('Preferensi berhasil disimpan (dummy — belum terhubung ke server).');
        });
    }
})();
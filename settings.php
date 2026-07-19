<?php
/**
 * settings.php
 * ------------------------------------------------------------------
 * Settings page: account & application preferences — Appearance,
 * Notifications, Privacy & Security, Language & Region, Application,
 * and a Danger Zone. Reads dummy data from $settings (includes/data.php),
 * ready to be swapped for real database queries + POST handlers later.
 *
 * Follows the exact same page skeleton as dashboard.php / history.php
 * / products.php / saved.php / profile.php:
 *   $pageTitle / $activePage -> header.php -> sidebar.php -> topbar.php
 *   -> .page-content -> footer.php
 * ------------------------------------------------------------------
 */

$pageTitle  = 'Settings';
$activePage = 'settings';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/includes/data.php';
/**
 * A handful of icons used on this page aren't in includes/icons.php's
 * lt_icon() library. Rather than touching that shared helper, we define
 * a small local fallback here (same approach analysis.php's ai_icon(),
 * history.php's hist_icon(), products.php's prod_icon(), saved.php's
 * saved_icon(), and profile.php's profile_icon() use), matching the
 * same 24x24 / rounded-stroke visual language.
 */
if (!function_exists('settings_icon')) {
    function settings_icon(string $name, int $size = 18, string $class = ''): string
    {
        $paths = [
            'sun'        => '<circle cx="12" cy="12" r="4.2"></circle><path d="M12 2.5v2.4M12 19.1v2.4M4.6 4.6l1.7 1.7M17.7 17.7l1.7 1.7M2.5 12h2.4M19.1 12h2.4M4.6 19.4l1.7-1.7M17.7 6.3l1.7-1.7"></path>',
            'moon'       => '<path d="M20.5 14.4A8.5 8.5 0 1 1 9.6 3.5a7 7 0 0 0 10.9 10.9Z"></path>',
            'type'       => '<path d="M5 6.5h14M12 6.5V18M9 18h6"></path>',
            'share'      => '<circle cx="18" cy="5.5" r="2.4"></circle><circle cx="6" cy="12" r="2.4"></circle><circle cx="18" cy="18.5" r="2.4"></circle><path d="M8.1 10.8l7.8-4.4M8.1 13.2l7.8 4.4"></path>',
            'lock'       => '<rect x="5" y="10.5" width="14" height="9.5" rx="1.8"></rect><path d="M7.5 10.5V7.8a4.5 4.5 0 0 1 9 0v2.7"></path>',
            'smartphone' => '<rect x="6.5" y="2.8" width="11" height="18.4" rx="2"></rect><path d="M11 18.3h2"></path>',
            'globe'      => '<circle cx="12" cy="12" r="9"></circle><path d="M3 12h18"></path><path d="M12 3c2.5 2.6 3.8 5.7 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.7-3.8-9S9.5 5.6 12 3Z"></path>',
            'dollar'     => '<path d="M12 3v18"></path><path d="M16.5 7.2c0-1.5-2-2.7-4.5-2.7s-4.5 1.2-4.5 3c0 4.4 9 2.2 9 6.5 0 1.8-2 3-4.5 3s-4.5-1.2-4.5-2.8"></path>',
            'download'   => '<path d="M12 3.5v11.5M8 11.5 12 15.5 16 11.5"></path><path d="M4.5 17v2a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-2"></path>',
            'refresh'    => '<path d="M4 12a8 8 0 0 1 13.7-5.7L20 8.5"></path><path d="M20 4v4.5h-4.5"></path><path d="M20 12a8 8 0 0 1-13.7 5.7L4 15.5"></path><path d="M4 20v-4.5h4.5"></path>',
            'file-text'  => '<path d="M6.5 3.2h7.3L18.5 8v12.3a1.2 1.2 0 0 1-1.2 1.2H6.5a1.2 1.2 0 0 1-1.2-1.2V4.4a1.2 1.2 0 0 1 1.2-1.2Z"></path><path d="M13.8 3.2V8h4.7"></path><path d="M8.3 12.5h7.4M8.3 15.8h7.4M8.3 19h4.5"></path>',
            'user-x'     => '<circle cx="9.5" cy="8.2" r="3.5"></circle><path d="M2.8 20c1-3.6 3.6-5.7 6.7-5.7 1.1 0 2.1.2 3 .7"></path><path d="M16.5 9.5l4.7 4.7M21.2 9.5l-4.7 4.7"></path>',
        ];
        $inner = $paths[$name] ?? $paths['type'];
        return sprintf(
            '<svg class="icon %s" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
            htmlspecialchars($class, ENT_QUOTES),
            $size,
            $size,
            $inner
        );
    }
}
?>
<main class="main-content">

    <link rel="stylesheet" href="assets/css/settings.css">

    <?php require __DIR__ . '/includes/topbar.php'; ?>

    <div class="page-content">

        <!-- ==================================================
             PAGE HEADER
        =================================================== -->
        <section class="settings-hero reveal">
            <div>
                <span class="settings-eyebrow"><?= lt_icon('settings', '', 13) ?> Pengaturan</span>
                <h2 class="settings-title">Settings</h2>
                <p class="settings-subtitle">Manage your account preferences and application settings.</p>
            </div>
        </section>

        <div class="settings-grid">

            <!-- ==================================================
                 SECTION 1 — APPEARANCE
            =================================================== -->
            <section class="card settings-card reveal">
                <div class="settings-card-head">
                    <span class="icon-tile"><?= lt_icon('palette', '', 20) ?></span>
                    <div>
                        <h3>Appearance</h3>
                        <p>Sesuaikan tampilan aplikasi sesuai preferensimu.</p>
                    </div>
                </div>

                <div class="settings-field">
                    <span class="settings-field-label">Theme</span>
                    <div class="settings-pill-group" data-group="theme">
                        <label class="settings-pill">
                            <input type="radio" name="theme" value="Light Mode" <?= $settings['appearance']['theme'] === 'Light Mode' ? 'checked' : '' ?>>
                            <span><?= settings_icon('sun', 15) ?> Light Mode</span>
                        </label>
                        <label class="settings-pill">
                            <input type="radio" name="theme" value="Dark Mode" <?= $settings['appearance']['theme'] === 'Dark Mode' ? 'checked' : '' ?>>
                            <span><?= settings_icon('moon', 15) ?> Dark Mode</span>
                        </label>
                    </div>
                </div>

                <div class="settings-field">
                    <span class="settings-field-label">Accent Color</span>
                    <div class="settings-color-group" data-group="accent">
                        <?php
                        $accentSwatches = ['Blue' => '#4C7FD9', 'Purple' => '#8B6FD9', 'Green' => '#6F8E52'];
                        foreach ($accentSwatches as $accentName => $accentHex):
                        ?>
                            <label class="settings-color-option">
                                <input type="radio" name="accent" value="<?= htmlspecialchars($accentName) ?>" <?= $settings['appearance']['accent'] === $accentName ? 'checked' : '' ?>>
                                <span class="settings-color-swatch" style="background:<?= $accentHex ?>;"></span>
                                <span class="settings-color-name"><?= htmlspecialchars($accentName) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="settings-field">
                    <span class="settings-field-label">Font Size</span>
                    <div class="settings-pill-group" data-group="fontSize">
                        <?php foreach (['Small', 'Medium', 'Large'] as $size): ?>
                            <label class="settings-pill">
                                <input type="radio" name="fontSize" value="<?= $size ?>" <?= $settings['appearance']['fontSize'] === $size ? 'checked' : '' ?>>
                                <span><?= settings_icon('type', 15) ?> <?= $size ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- ==================================================
                 SECTION 2 — NOTIFICATIONS
            =================================================== -->
            <section class="card settings-card reveal">
                <div class="settings-card-head">
                    <span class="icon-tile"><?= lt_icon('bell', '', 20) ?></span>
                    <div>
                        <h3>Notifications</h3>
                        <p>Atur notifikasi apa saja yang ingin kamu terima.</p>
                    </div>
                </div>

                <div class="settings-toggle-list">
                    <?php
                    $notifRows = [
                        ['key' => 'email',     'label' => 'Email Notifications',     'desc' => 'Terima notifikasi penting melalui email.'],
                        ['key' => 'analysis',  'label' => 'Analysis Completed',      'desc' => 'Beri tahu saat hasil analisis kulit selesai diproses.'],
                        ['key' => 'product',   'label' => 'Product Recommendations', 'desc' => 'Beri tahu saat ada rekomendasi produk baru.'],
                        ['key' => 'promotion', 'label' => 'Promotional Updates',     'desc' => 'Info promo dan penawaran khusus dari LumiTone.'],
                    ];
                    foreach ($notifRows as $row):
                    ?>
                        <div class="settings-toggle-row">
                            <span class="settings-toggle-label"><?= htmlspecialchars($row['label']) ?></span>
                            <label class="settings-switch">
                                <input type="checkbox" data-pref="notification.<?= htmlspecialchars($row['key']) ?>" <?= $settings['notification'][$row['key']] ? 'checked' : '' ?>>
                                <span class="settings-switch-track"></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- ==================================================
                 SECTION 3 — PRIVACY & SECURITY
            =================================================== -->
            <section class="card settings-card reveal">
                <div class="settings-card-head">
                    <span class="icon-tile"><?= lt_icon('shield-check', '', 20) ?></span>
                    <div>
                        <h3>Privacy &amp; Security</h3>
                        <p>Kendalikan siapa yang bisa melihat data dan aktivitasmu.</p>
                    </div>
                </div>

                <div class="settings-row">
                    <span class="settings-row-icon"><?= lt_icon('eye', '', 17) ?></span>
                    <div class="settings-row-main">
                        <span class="settings-row-label">Profile Visibility</span>
                        <span class="settings-row-desc">Siapa yang bisa melihat profilmu.</span>
                    </div>
                    <select class="settings-select-inline" data-pref="privacy.profile">
                        <option <?= $settings['privacy']['profile'] === 'Private' ? 'selected' : '' ?>>Private</option>
                        <option <?= $settings['privacy']['profile'] === 'Public' ? 'selected' : '' ?>>Public</option>
                    </select>
                </div>

                <div class="settings-row">
                    <span class="settings-row-icon"><?= settings_icon('share', 17) ?></span>
                    <div class="settings-row-main">
                        <span class="settings-row-label">Data Sharing</span>
                        <span class="settings-row-desc">Izinkan data dipakai untuk peningkatan rekomendasi AI.</span>
                    </div>
                    <label class="settings-switch">
                        <input type="checkbox" data-pref="privacy.dataSharing" <?= $settings['privacy']['dataSharing'] ? 'checked' : '' ?>>
                        <span class="settings-switch-track"></span>
                    </label>
                </div>

                <div class="settings-row">
                    <span class="settings-row-icon"><?= lt_icon('shield-check', '', 17) ?></span>
                    <div class="settings-row-main">
                        <span class="settings-row-label">Two-Factor Authentication</span>
                        <span class="settings-row-desc">Lapisan keamanan tambahan saat login.</span>
                    </div>
                    <label class="settings-switch">
                        <input type="checkbox" data-pref="privacy.twoFactor" <?= $settings['privacy']['twoFactor'] ? 'checked' : '' ?>>
                        <span class="settings-switch-track"></span>
                    </label>
                </div>

                <div class="settings-row">
                    <span class="settings-row-icon"><?= settings_icon('lock', 17) ?></span>
                    <div class="settings-row-main">
                        <span class="settings-row-label">Change Password</span>
                        <span class="settings-row-desc">Perbarui password akunmu secara berkala.</span>
                    </div>
                    <a href="profile.php" class="btn btn-secondary btn-sm">Ubah</a>
                </div>

                <div class="settings-row">
                    <span class="settings-row-icon"><?= settings_icon('smartphone', 17) ?></span>
                    <div class="settings-row-main">
                        <span class="settings-row-label">Manage Devices</span>
                        <span class="settings-row-desc">Lihat perangkat yang sedang login ke akunmu.</span>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm settings-action-btn" data-action="devices">Kelola</button>
                </div>
            </section>

            <!-- ==================================================
                 SECTION 4 — LANGUAGE & REGION
            =================================================== -->
            <section class="card settings-card reveal">
                <div class="settings-card-head">
                    <span class="icon-tile"><?= settings_icon('globe', 20) ?></span>
                    <div>
                        <h3>Language &amp; Region</h3>
                        <p>Sesuaikan bahasa, zona waktu, dan format tampilan.</p>
                    </div>
                </div>

                <div class="settings-form-grid">
                    <label class="settings-select-field">
                        <span class="settings-field-label">Language</span>
                        <select data-pref="language.current">
                            <option <?= $settings['language']['current'] === 'English' ? 'selected' : '' ?>>English</option>
                            <option <?= $settings['language']['current'] === 'Bahasa Indonesia' ? 'selected' : '' ?>>Bahasa Indonesia</option>
                        </select>
                    </label>
                    <label class="settings-select-field">
                        <span class="settings-field-label">Timezone</span>
                        <select data-pref="language.timezone">
                            <option selected>GMT+7 (WIB)</option>
                            <option>GMT+8 (WITA)</option>
                            <option>GMT+9 (WIT)</option>
                        </select>
                    </label>
                    <label class="settings-select-field">
                        <span class="settings-field-label">Date Format</span>
                        <select data-pref="language.dateFormat">
                            <option selected>DD/MM/YYYY</option>
                            <option>MM/DD/YYYY</option>
                            <option>YYYY-MM-DD</option>
                        </select>
                    </label>
                    <label class="settings-select-field">
                        <span class="settings-field-label">Currency</span>
                        <select data-pref="language.currency">
                            <option selected>IDR (Rp)</option>
                            <option>USD ($)</option>
                        </select>
                    </label>
                </div>
            </section>

            <!-- ==================================================
                 SECTION 5 — APPLICATION
            =================================================== -->
            <section class="card settings-card reveal">
                <div class="settings-card-head">
                    <span class="icon-tile"><?= lt_icon('grid', '', 20) ?></span>
                    <div>
                        <h3>Application</h3>
                        <p>Kelola data dan penyimpanan aplikasi LumiTone.</p>
                    </div>
                </div>

                <div class="settings-action-list">
                    <div class="settings-row">
                        <span class="settings-row-icon"><?= settings_icon('refresh', 17) ?></span>
                        <div class="settings-row-main">
                            <span class="settings-row-label">Clear Cache</span>
                            <span class="settings-row-desc">Kosongkan data sementara untuk mempercepat aplikasi.</span>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm settings-action-btn" data-action="clear-cache">Clear</button>
                    </div>

                    <div class="settings-row">
                        <span class="settings-row-icon"><?= settings_icon('download', 17) ?></span>
                        <div class="settings-row-main">
                            <span class="settings-row-label">Download My Data</span>
                            <span class="settings-row-desc">Unduh salinan seluruh data akunmu.</span>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm settings-action-btn" data-action="download-data">Download</button>
                    </div>

                    <div class="settings-row">
                        <span class="settings-row-icon"><?= settings_icon('file-text', 17) ?></span>
                        <div class="settings-row-main">
                            <span class="settings-row-label">Export Analysis History</span>
                            <span class="settings-row-desc">Ekspor seluruh riwayat analisis ke file CSV.</span>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm settings-action-btn" data-action="export-history">Export</button>
                    </div>

                    <div class="settings-row">
                        <span class="settings-row-icon"><?= settings_icon('refresh', 17) ?></span>
                        <div class="settings-row-main">
                            <span class="settings-row-label">Restore Default Settings</span>
                            <span class="settings-row-desc">Kembalikan seluruh pengaturan ke bawaan aplikasi.</span>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm settings-action-btn" data-action="restore-defaults">Restore</button>
                    </div>
                </div>
            </section>

            <!-- ==================================================
                 SECTION 6 — DANGER ZONE
            =================================================== -->
            <section class="card settings-danger-card reveal">
                <div class="settings-card-head">
                    <span class="icon-tile settings-danger-icon-tile"><?= settings_icon('user-x', 20) ?></span>
                    <div>
                        <h3>Danger Zone</h3>
                        <p>Tindakan berikut bersifat sensitif, lakukan dengan hati-hati.</p>
                    </div>
                </div>

                <div class="settings-row">
                    <span class="settings-row-icon"><?= lt_icon('log-out', '', 17) ?></span>
                    <div class="settings-row-main">
                        <span class="settings-row-label">Logout</span>
                        <span class="settings-row-desc">Keluar dari akun LumiTone di perangkat ini.</span>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm settings-action-btn" data-action="logout">Logout</button>
                </div>

                <div class="settings-row settings-row-danger">
                    <span class="settings-row-icon"><?= settings_icon('user-x', 17) ?></span>
                    <div class="settings-row-main">
                        <span class="settings-row-label">Delete Account</span>
                        <span class="settings-row-desc">Semua data analisis, riwayat, dan hasil tersimpan akan dihapus permanen dan tidak dapat dikembalikan.</span>
                    </div>
                    <button type="button" class="btn settings-delete-btn" data-action="delete-account">
                        <?= settings_icon('user-x', 15) ?> Delete Account
                    </button>
                </div>
            </section>

        </div>

    </div>

    <script src="assets/js/settings.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
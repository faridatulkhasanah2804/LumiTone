<?php
/**
 * profile.php
 * ------------------------------------------------------------------
 * User Profile page: account overview, editable account info,
 * security (password), and skincare preferences. Uses $currentUser
 * from includes/data.php plus a small dummy $profileStats /
 * $profilePreferences array, ready to be swapped for real database
 * queries later.
 *
 * Follows the exact same page skeleton as dashboard.php / history.php
 * / products.php / saved.php:
 *   $pageTitle / $activePage -> header.php -> sidebar.php -> topbar.php
 *   -> .page-content -> footer.php
 * ------------------------------------------------------------------
 */

$pageTitle  = 'Profile';
$activePage = 'profile';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

/**
 * A couple of icons used on this page aren't in includes/icons.php's
 * lt_icon() library. Rather than touching that shared helper, we define
 * a small local fallback here (same approach analysis.php's ai_icon(),
 * history.php's hist_icon(), products.php's prod_icon(), and
 * saved.php's saved_icon() use), matching the same 24x24 /
 * rounded-stroke visual language.
 */
if (!function_exists('profile_icon')) {
    function profile_icon(string $name, int $size = 18, string $class = ''): string
    {
        $paths = [
            'camera'  => '<path d="M4 8.2A1.2 1.2 0 0 1 5.2 7h2l1-1.8h7.6L16.8 7h2A1.2 1.2 0 0 1 20 8.2v10.6A1.2 1.2 0 0 1 18.8 20H5.2A1.2 1.2 0 0 1 4 18.8Z"></path><circle cx="12" cy="13" r="3.6"></circle>',
            'shield'  => '<path d="M12 3.4 19 6v6c0 4.4-3 8.2-7 9.6-4-1.4-7-5.2-7-9.6V6Z"></path>',
            'sliders' => '<path d="M5 6h9"></path><path d="M17.5 6h1.5"></path><circle cx="14.5" cy="6" r="2"></circle><path d="M5 12h1.5"></path><path d="M10 12h9"></path><circle cx="8" cy="12" r="2"></circle><path d="M5 18h9"></path><path d="M17.5 18h1.5"></path><circle cx="14.5" cy="18" r="2"></circle>',
            'trash'   => '<path d="M4.5 7h15"></path><path d="M9.5 7V5.2a1.2 1.2 0 0 1 1.2-1.2h2.6a1.2 1.2 0 0 1 1.2 1.2V7"></path><path d="M6.5 7l.8 12.1A2 2 0 0 0 9.3 21h5.4a2 2 0 0 0 2-1.9L17.5 7"></path><path d="M10.3 11v6"></path><path d="M13.7 11v6"></path>',
            'mail'    => '<rect x="3.2" y="5.2" width="17.6" height="13.6" rx="1.8"></rect><path d="M4 6.5l8 6.5 8-6.5"></path>',
            'edit'    => '<path d="M15.2 4.8l4 4L7.5 20.5l-4.6.7.7-4.6Z"></path><path d="M13.3 6.7l4 4"></path>',
        ];
        $inner = $paths[$name] ?? $paths['sliders'];
        return sprintf(
            '<svg class="icon %s" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
            htmlspecialchars($class, ENT_QUOTES),
            $size,
            $size,
            $inner
        );
    }
}

/**
 * ------------------------------------------------------------------
 * DUMMY DATA — profile stats + skincare preferences.
 * $currentUser already comes from includes/data.php (included via
 * header.php). Replace these with real queries once the database is
 * ready.
 * ------------------------------------------------------------------
 */
$profileStats = [
    ['icon' => 'scan-face', 'value' => '12',  'label' => 'Total Analysis'],
    ['icon' => 'bookmark',  'value' => '3',   'label' => 'Saved Results'],
    ['icon' => 'calendar',  'value' => 'Mar 2023', 'label' => 'Member Since'],
];

$skinPreferences = [
    'skin_type'    => 'Kombinasi',
    'main_concern'  => 'Pori-pori besar',
    'sensitivity'  => 'Sedang',
];

$notificationPrefs = [
    ['key' => 'analysis_ready', 'label' => 'Hasil analisis selesai', 'desc' => 'Beri tahu saat AI selesai memproses hasil analisis kulit.', 'checked' => true],
    ['key' => 'product_tips',   'label' => 'Rekomendasi produk baru', 'desc' => 'Beri tahu saat ada rekomendasi produk baru untuk kulitmu.', 'checked' => true],
    ['key' => 'newsletter',     'label' => 'Newsletter & tips skincare', 'desc' => 'Tips perawatan kulit mingguan dari tim LumiTone.', 'checked' => false],
];
?>
<main class="main-content">

    <link rel="stylesheet" href="assets/css/profile.css">

    <?php require __DIR__ . '/includes/topbar.php'; ?>

    <div class="page-content">

        <!-- ==================================================
             1. PAGE HEADER
        =================================================== -->
        <section class="profile-hero-header reveal">
            <div>
                <span class="profile-eyebrow"><?= lt_icon('user', '', 13) ?> Akun Saya</span>
                <h2 class="profile-title">Profile</h2>
                <p class="profile-subtitle">Kelola informasi akun dan preferensi skincare-mu.</p>
            </div>
        </section>

        <!-- ==================================================
             2. PROFILE OVERVIEW CARD
        =================================================== -->
        <section class="card profile-overview reveal">
            <div class="profile-overview-main">
                <div class="profile-avatar-wrap">
                    <span class="avatar avatar-lg profile-avatar"><?= htmlspecialchars($currentUser['initials']) ?></span>
                    <button type="button" class="profile-avatar-edit" aria-label="Ganti foto profil">
                        <?= profile_icon('camera', 15) ?>
                    </button>
                </div>
                <div class="profile-overview-info">
                    <h3><?= htmlspecialchars($currentUser['full_name']) ?></h3>
                    <p><?= profile_icon('mail', 14) ?> <?= htmlspecialchars($currentUser['email']) ?></p>
                    <span class="badge badge-neutral"><?= lt_icon('sparkles', '', 12) ?> <?= htmlspecialchars($currentUser['plan']) ?></span>
                </div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" id="profileEditToggle">
                <?= profile_icon('edit', 15) ?> Edit Profil
            </button>
        </section>

        <!-- ==================================================
             3. STAT CHIPS
        =================================================== -->
        <section class="profile-stats reveal">
            <?php foreach ($profileStats as $stat): ?>
                <div class="card profile-stat-card">
                    <span class="icon-tile"><?= lt_icon($stat['icon'], '', 20) ?></span>
                    <div>
                        <p class="profile-stat-value"><?= htmlspecialchars($stat['value']) ?></p>
                        <p class="profile-stat-label"><?= htmlspecialchars($stat['label']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- ==================================================
             4. TABS
        =================================================== -->
        <div class="profile-tabs reveal" id="profileTabs" role="tablist">
            <button type="button" class="profile-tab is-active" data-tab="account" role="tab" aria-selected="true">
                <?= lt_icon('user', '', 15) ?> Account Info
            </button>
            <button type="button" class="profile-tab" data-tab="security" role="tab" aria-selected="false">
                <?= profile_icon('shield', 15) ?> Security
            </button>
            <button type="button" class="profile-tab" data-tab="preferences" role="tab" aria-selected="false">
                <?= profile_icon('sliders', 15) ?> Preferences
            </button>
        </div>

        <!-- ==================================================
             5a. TAB: ACCOUNT INFO
        =================================================== -->
        <section class="card profile-panel reveal" id="profilePanelAccount" data-panel="account">
            <div class="section-heading">
                <div>
                    <h2>Informasi Akun</h2>
                    <p>Data ini digunakan untuk personalisasi hasil analisismu.</p>
                </div>
            </div>

            <form class="profile-form" id="profileAccountForm">
                <div class="profile-form-grid">
                    <label class="profile-field">
                        <span class="profile-field-label">Nama Lengkap</span>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($currentUser['full_name']) ?>" disabled>
                    </label>
                    <label class="profile-field">
                        <span class="profile-field-label">Nama Panggilan</span>
                        <input type="text" name="name" value="<?= htmlspecialchars($currentUser['name']) ?>" disabled>
                    </label>
                    <label class="profile-field">
                        <span class="profile-field-label">Email</span>
                        <input type="email" name="email" value="<?= htmlspecialchars($currentUser['email']) ?>" disabled>
                    </label>
                    <label class="profile-field">
                        <span class="profile-field-label">Nomor Telepon</span>
                        <input type="tel" name="phone" value="+62 812-3456-7890" disabled>
                    </label>
                </div>

                <div class="profile-form-actions is-hidden" id="profileAccountActions">
                    <button type="button" class="btn btn-secondary btn-sm" id="profileCancelBtn">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm"><?= lt_icon('check-circle', '', 15) ?> Simpan Perubahan</button>
                </div>
            </form>
        </section>

        <!-- ==================================================
             5b. TAB: SECURITY
        =================================================== -->
        <section class="card profile-panel is-hidden" id="profilePanelSecurity" data-panel="security">
            <div class="section-heading">
                <div>
                    <h2>Keamanan Akun</h2>
                    <p>Perbarui password secara berkala untuk menjaga keamanan akunmu.</p>
                </div>
            </div>

            <form class="profile-form" id="profileSecurityForm">
                <div class="profile-form-grid">
                    <label class="profile-field profile-field-full">
                        <span class="profile-field-label">Password Saat Ini</span>
                        <input type="password" name="current_password" placeholder="Masukkan password saat ini">
                    </label>
                    <label class="profile-field">
                        <span class="profile-field-label">Password Baru</span>
                        <input type="password" name="new_password" placeholder="Minimal 8 karakter">
                    </label>
                    <label class="profile-field">
                        <span class="profile-field-label">Konfirmasi Password Baru</span>
                        <input type="password" name="confirm_password" placeholder="Ulangi password baru">
                    </label>
                </div>

                <div class="profile-form-actions">
                    <button type="submit" class="btn btn-primary btn-sm"><?= lt_icon('check-circle', '', 15) ?> Update Password</button>
                </div>
            </form>

            <div class="profile-danger-zone">
                <div>
                    <p class="profile-danger-title"><?= profile_icon('trash', 15) ?> Hapus Akun</p>
                    <p class="profile-danger-desc">Semua data analisis, riwayat, dan hasil tersimpan akan dihapus permanen.</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm profile-danger-btn">Hapus Akun</button>
            </div>
        </section>

        <!-- ==================================================
             5c. TAB: PREFERENCES
        =================================================== -->
        <section class="card profile-panel is-hidden" id="profilePanelPreferences" data-panel="preferences">
            <div class="section-heading">
                <div>
                    <h2>Preferensi Kulit</h2>
                    <p>Membantu AI memberi rekomendasi yang lebih akurat untukmu.</p>
                </div>
            </div>

            <div class="profile-form-grid">
                <label class="profile-field">
                    <span class="profile-field-label">Tipe Kulit</span>
                    <select name="skin_type">
                        <option <?= $skinPreferences['skin_type'] === 'Kombinasi' ? 'selected' : '' ?>>Kombinasi</option>
                        <option <?= $skinPreferences['skin_type'] === 'Berminyak' ? 'selected' : '' ?>>Berminyak</option>
                        <option <?= $skinPreferences['skin_type'] === 'Kering' ? 'selected' : '' ?>>Kering</option>
                        <option <?= $skinPreferences['skin_type'] === 'Normal' ? 'selected' : '' ?>>Normal</option>
                    </select>
                </label>
                <label class="profile-field">
                    <span class="profile-field-label">Concern Utama</span>
                    <select name="main_concern">
                        <option <?= $skinPreferences['main_concern'] === 'Pori-pori besar' ? 'selected' : '' ?>>Pori-pori besar</option>
                        <option <?= $skinPreferences['main_concern'] === 'Kemerahan' ? 'selected' : '' ?>>Kemerahan</option>
                        <option <?= $skinPreferences['main_concern'] === 'Garis halus' ? 'selected' : '' ?>>Garis halus</option>
                        <option <?= $skinPreferences['main_concern'] === 'Kusam' ? 'selected' : '' ?>>Kusam</option>
                    </select>
                </label>
                <label class="profile-field">
                    <span class="profile-field-label">Sensitivitas Kulit</span>
                    <select name="sensitivity">
                        <option <?= $skinPreferences['sensitivity'] === 'Rendah' ? 'selected' : '' ?>>Rendah</option>
                        <option <?= $skinPreferences['sensitivity'] === 'Sedang' ? 'selected' : '' ?>>Sedang</option>
                        <option <?= $skinPreferences['sensitivity'] === 'Tinggi' ? 'selected' : '' ?>>Tinggi</option>
                    </select>
                </label>
            </div>

            <hr class="profile-divider">

            <div class="section-heading">
                <div>
                    <h2>Notifikasi</h2>
                    <p>Atur notifikasi apa saja yang ingin kamu terima.</p>
                </div>
            </div>

            <div class="profile-toggle-list">
                <?php foreach ($notificationPrefs as $pref): ?>
                    <div class="profile-toggle-row">
                        <div>
                            <p class="profile-toggle-label"><?= htmlspecialchars($pref['label']) ?></p>
                            <p class="profile-toggle-desc"><?= htmlspecialchars($pref['desc']) ?></p>
                        </div>
                        <label class="profile-switch">
                            <input type="checkbox" <?= $pref['checked'] ? 'checked' : '' ?> data-pref="<?= htmlspecialchars($pref['key']) ?>">
                            <span class="profile-switch-track"></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="profile-form-actions">
                <button type="button" class="btn btn-primary btn-sm" id="profileSavePrefsBtn"><?= lt_icon('check-circle', '', 15) ?> Simpan Preferensi</button>
            </div>
        </section>

    </div>

    <script src="assets/js/profile.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
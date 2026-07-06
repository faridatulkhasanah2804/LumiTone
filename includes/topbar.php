<?php
/**
 * topbar.php
 * ------------------------------------------------------------------
 * Top navigation bar. Expects $currentUser (from data.php).
 * The greeting adapts to the current server time of day.
 * ------------------------------------------------------------------
 */

$hour = (int) date('G');
if ($hour < 11) {
    $greeting = 'Good Morning';
} elseif ($hour < 15) {
    $greeting = 'Good Afternoon';
} elseif ($hour < 19) {
    $greeting = 'Good Evening';
} else {
    $greeting = 'Good Night';
}
?>
<header class="topbar">

    <!-- Mobile menu toggle -->
    <button type="button" class="icon-btn sidebar-toggle" id="sidebarToggle" aria-label="Buka menu">
        <?= lt_icon('menu', '', 20) ?>
    </button>

    <!-- Greeting -->
    <div class="topbar-greeting">
        <h1><?= $greeting ?>, <?= htmlspecialchars($currentUser['name']) ?> <span class="wave">👋</span></h1>
        <p>Ini ringkasan aktivitas kulitmu hari ini.</p>
    </div>

    <!-- Search -->
    <form class="topbar-search" role="search" action="#" method="get">
        <span class="topbar-search-icon"><?= lt_icon('search', '', 17) ?></span>
        <input type="search" name="q" placeholder="Cari analisis, produk, atau tips...">
    </form>

    <!-- Actions -->
    <div class="topbar-actions">
        <button type="button" class="icon-btn" aria-label="Notifikasi">
            <?= lt_icon('bell', '', 19) ?>
            <span class="notif-dot"></span>
        </button>

        <button type="button" class="topbar-profile" id="profileMenuBtn" aria-haspopup="true" aria-expanded="false">
            <span class="avatar avatar-sm"><?= htmlspecialchars($currentUser['initials']) ?></span>
            <span class="topbar-profile-name"><?= htmlspecialchars($currentUser['full_name']) ?></span>
            <?= lt_icon('chevron-down', 'topbar-profile-chevron', 15) ?>
        </button>

        <!-- Simple profile dropdown (toggled via JS) -->
        <div class="profile-dropdown" id="profileDropdown">
            <a href="#"><?= lt_icon('user', '', 16) ?> Profil Saya</a>
            <a href="#"><?= lt_icon('settings', '', 16) ?> Pengaturan</a>
            <hr>
            <a href="#" class="text-danger"><?= lt_icon('log-out', '', 16) ?> Logout</a>
        </div>
    </div>
</header>

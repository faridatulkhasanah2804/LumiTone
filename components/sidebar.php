<?php
/**
 * Sidebar Component
 * --------------------------------------------------------------
 * Reusable left navigation for every logged-in dashboard page.
 * Include this file and set $activePage before including it so
 * the correct menu item gets the "active" state, e.g.:
 *
 *   $activePage = 'dashboard';
 *   include '../components/sidebar.php';
 *
 * Frontend only — no auth / session logic here yet.
 * -------------------------------------------------------------- */

if (!isset($activePage)) {
    $activePage = 'dashboard';
}

// Menu definition kept in one place so it's easy to extend later.
$sidebarMenu = [
    'dashboard'  => ['label' => 'Dashboard',            'icon' => '🏠', 'href' => '../dashboard/index.php'],
    'analysis'   => ['label' => 'Skin Analysis',        'icon' => '📷', 'href' => '../analysis.php'],
    'history'    => ['label' => 'Analysis History',     'icon' => '📜', 'href' => '../dashboard/history.php'],
    'products'   => ['label' => 'Recommended Products', 'icon' => '💄', 'href' => '../dashboard/products.php'],
    'profile'    => ['label' => 'Profile',               'icon' => '👤', 'href' => '../dashboard/profile.php'],
    'settings'   => ['label' => 'Settings',              'icon' => '⚙️', 'href' => '../dashboard/settings.php'],
];
?>
<aside class="sidebar" id="sidebar">

    <!-- Collapse / expand trigger -->
    <button class="sidebar__toggle" id="sidebarToggle" aria-label="Collapse sidebar">‹</button>

    <!-- Brand -->
    <div class="sidebar__brand">
        <div class="sidebar__brand-icon">L</div>
        <span class="sidebar__brand-name">LumiTone</span>
    </div>

    <!-- Navigation -->
    <nav class="sidebar__nav">
        <p class="sidebar__section-title">Menu</p>
        <ul>
            <?php foreach ($sidebarMenu as $key => $item): ?>
                <li>
                    <a href="<?php echo $item['href']; ?>"
                       class="sidebar__item <?php echo ($activePage === $key) ? 'active' : ''; ?>">
                        <span class="sidebar__item-icon"><?php echo $item['icon']; ?></span>
                        <span class="sidebar__label"><?php echo $item['label']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Logout -->
    <div class="sidebar__footer">
        <a href="../auth/logout.php" class="sidebar__logout">
            <span class="sidebar__item-icon">🚪</span>
            <span class="sidebar__label">Logout</span>
        </a>
    </div>

</aside>

<!-- Overlay used to close the sidebar on mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
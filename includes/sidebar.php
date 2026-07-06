<?php
/**
 * sidebar.php
 * ------------------------------------------------------------------
 * Left sidebar navigation. Expects $menuItems (from data.php) and
 * $activePage (defined by the calling page) to be available.
 * ------------------------------------------------------------------
 */
?>
<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="dashboard.php" class="sidebar-logo-mark-word">
            <span class="logo-mark"><?= lt_icon('search', '', 22) ?></span>
            <span class="logo-word">LumiTone</span>
        </a>
        <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseToggle" aria-label="Ciutkan sidebar" aria-expanded="true" title="Ciutkan sidebar">
            <?= lt_icon('chevron-right', '', 15) ?>
        </button>
    </div>

    <!-- Primary navigation -->
    <nav class="sidebar-nav" aria-label="Navigasi utama">
        <ul>
            <?php foreach ($menuItems as $item): ?>
                <?php $isActive = ($item['key'] === $activePage); ?>
                <li>
                    <a href="<?= htmlspecialchars($item['href']) ?>"
                       class="nav-link <?= $isActive ? 'is-active' : '' ?>"
                       <?= $isActive ? 'aria-current="page"' : '' ?>>
                        <span class="nav-icon"><?= lt_icon($item['icon'], '', 19) ?></span>
                        <span class="nav-label"><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Upgrade to Pro promo card -->
    <div class="sidebar-promo">
        <span class="sidebar-promo-badge"><?= lt_icon('sparkles', '', 16) ?></span>
        <p class="sidebar-promo-title">Upgrade ke LumiTone Pro</p>
        <p class="sidebar-promo-desc">Analisis lebih detail &amp; riwayat tak terbatas.</p>
        <a href="#" class="btn btn-primary btn-sm btn-block">Upgrade Sekarang</a>
    </div>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="#" class="nav-link nav-link-logout">
            <span class="nav-icon"><?= lt_icon('log-out', '', 19) ?></span>
            <span class="nav-label">Logout</span>
        </a>
    </div>

    <!-- Drag-to-resize handle (desktop only) -->
    <div class="sidebar-resizer"
         id="sidebarResizer"
         role="separator"
         aria-orientation="vertical"
         aria-label="Ubah lebar sidebar"
         tabindex="0"
         title="Tarik untuk mengubah lebar"></div>
</aside>

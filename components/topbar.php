<?php
/**
 * Top Navbar Component (Dashboard)
 * --------------------------------------------------------------
 * Reusable top bar shown on every logged-in dashboard page.
 * Expects an optional $currentUser array to be set before include;
 * falls back to dummy data so the component works standalone.
 *
 *   $currentUser = ['name' => 'Farida', 'role' => 'Member'];
 *   include '../components/topbar.php';
 * -------------------------------------------------------------- */

if (!isset($currentUser)) {
    // Dummy user data — replace with real session data later.
    $currentUser = [
        'name' => 'Farida',
        'role' => 'Premium Member',
    ];
}

// Simple time-based greeting using dummy logic (frontend only).
$hour = (int) date('H');
if ($hour < 11) {
    $greeting = 'Good Morning';
} elseif ($hour < 15) {
    $greeting = 'Good Afternoon';
} elseif ($hour < 19) {
    $greeting = 'Good Evening';
} else {
    $greeting = 'Good Night';
}

$firstName = htmlspecialchars(explode(' ', $currentUser['name'])[0]);
$initial   = strtoupper(substr($currentUser['name'], 0, 1));
?>
<header class="topbar">

    <div class="topbar__left">
        <!-- Mobile hamburger -->
        <button class="topbar__menu-btn" id="mobileMenuBtn" aria-label="Open menu">☰</button>

        <div>
            <h1 class="topbar__greeting-title"><?php echo $greeting . ', ' . $firstName; ?> 👋</h1>
            <p class="topbar__greeting-sub">Continue your skincare journey with AI</p>
        </div>
    </div>

    <!-- Search -->
    <form class="topbar__search" role="search" onsubmit="return false;">
        <span class="topbar__search-icon">🔍</span>
        <input type="text" placeholder="Search analysis, products, tips..." aria-label="Search">
    </form>

    <div class="topbar__right">
        <!-- Notifications -->
        <button class="topbar__icon-btn" data-notif-btn aria-label="Notifications">
            🔔
            <span class="topbar__badge"></span>
        </button>

        <!-- User -->
        <a href="../dashboard/profile.php" class="topbar__user">
            <div class="topbar__avatar"><?php echo $initial; ?></div>
            <div>
                <div class="topbar__user-name"><?php echo htmlspecialchars($currentUser['name']); ?></div>
                <div class="topbar__user-role"><?php echo htmlspecialchars($currentUser['role']); ?></div>
            </div>
        </a>
    </div>

</header>
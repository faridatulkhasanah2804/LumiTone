<?php
/**
 * data.php
 * ------------------------------------------------------------------
 * Shared data available on every dashboard page (loaded via
 * header.php). $currentUser now comes from the `users` table for
 * the logged-in session. $stats / $recentAnalyses / $products used
 * to live here as dummy arrays — they're page-specific, so they now
 * live in dashboard.php as real queries instead.
 *
 * $quickActions / $dailyTip / $activities / $settings are left as
 * static/dummy for now since they aren't backed by a table in the
 * current schema and weren't part of the pages reviewed for this
 * refactor.
 * ------------------------------------------------------------------
 */

// Sidebar navigation menu. `key` is matched against $activePage
// (defined in each page) to highlight the active item.
$menuItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard',              'icon' => 'grid',       'href' => 'dashboard.php'],
    ['key' => 'analysis',  'label' => 'AI Analysis',            'icon' => 'scan-face',  'href' => 'analysis.php'],
    ['key' => 'history',   'label' => 'Analysis History',       'icon' => 'history',    'href' => 'history.php'],
    ['key' => 'products',  'label' => 'Product Recommendations','icon' => 'package',    'href' => 'products.php'],
    ['key' => 'saved',     'label' => 'Saved Results',          'icon' => 'bookmark',   'href' => 'saved.php'],
    ['key' => 'profile',   'label' => 'Profile',                'icon' => 'user',       'href' => 'profile.php'],
    ['key' => 'settings',  'label' => 'Settings',                'icon' => 'settings',   'href' => 'settings.php'],
];

// Currently logged-in user — loaded from `users` via $_SESSION['user_id'].
$currentUser = [
    'name'      => 'Guest',
    'full_name' => 'Guest',
    'email'     => '',
    'initials'  => 'GU',
    'plan'      => 'LumiTone',
    'photo'     => null,
];

if (isset($_SESSION['user_id'])) {
    $stmt = mysqli_prepare($conn, 'SELECT fullname, email, photo FROM users WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user) {
        $fullName = $user['fullname'] ?: 'User';
        $words    = preg_split('/\s+/', trim($fullName));
        $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

        $currentUser = [
            'name'      => $words[0],
            'full_name' => $fullName,
            'email'     => $user['email'] ?? '',
            'initials'  => $initials,
            'plan'      => 'LumiTone Pro',
            'photo'     => $user['photo'],
        ];
    }
}

// Quick action shortcut cards (static — not tied to a DB table yet)
$quickActions = [
    [
        'icon'  => 'upload-cloud',
        'title' => 'Upload Photo',
        'desc'  => 'Unggah foto wajah baru untuk dianalisis AI.',
        'href'  => '#',
    ],
    [
        'icon'  => 'scan-face',
        'title' => 'AI Analysis',
        'desc'  => 'Mulai analisis instan skintone & kondisi kulit.',
        'href'  => '#',
    ],
    [
        'icon'  => 'history',
        'title' => 'View History',
        'desc'  => 'Lihat seluruh riwayat analisis sebelumnya.',
        'href'  => '#',
    ],
    [
        'icon'  => 'sparkles',
        'title' => 'Product Recommendation',
        'desc'  => 'Rekomendasi skincare sesuai kondisi kulitmu.',
        'href'  => '#',
    ],
];

// Daily skincare tip (static — no `tips` table in the current schema)
$dailyTip = [
    'title' => 'Jangan Lewatkan Sunscreen di Pagi Hari',
    'body'  => 'Meski di dalam ruangan, sinar UV tetap bisa menembus jendela dan mempercepat penuaan kulit. Gunakan sunscreen minimal SPF 30 setiap pagi, dan aplikasikan ulang setiap 3-4 jam jika beraktivitas di luar ruangan.',
];

// Recent activity timeline (static — no `activity_log` table in the current schema)
$activities = [
    [
        'icon'  => 'upload-cloud',
        'title' => 'Foto berhasil diunggah',
        'time'  => '2 menit yang lalu',
    ],
    [
        'icon'  => 'scan-face',
        'title' => 'Analisis kulit selesai diproses',
        'time'  => '1 menit yang lalu',
    ],
    [
        'icon'  => 'sparkles',
        'title' => 'Rekomendasi produk baru dibuat',
        'time'  => 'Baru saja',
    ],
];

// Settings page defaults (static — no `settings` table in the current schema)
$settings = [
    'appearance' => [
        'theme'    => 'Light Mode',
        'accent'   => 'Blue',
        'fontSize' => 'Medium',
    ],
    'notification' => [
        'email'     => true,
        'analysis'  => true,
        'product'   => true,
        'promotion' => false,
    ],
    'privacy' => [
        'profile'      => 'Private',
        'dataSharing'  => false,
        'twoFactor'    => true,
    ],
    'language' => [
        'current' => 'English',
    ],
];
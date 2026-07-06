<?php
/**
 * data.php
 * ------------------------------------------------------------------
 * Centralized dummy data for the LumiTone Dashboard.
 * In a real backend, these arrays would be replaced with data fetched
 * from a database (MySQL via PDO/MySQLi). Keeping them here means the
 * view files (dashboard.php) stay clean and only loop over data.
 * ------------------------------------------------------------------
 */

// Currently logged-in user (dummy session data)
$currentUser = [
    'name'        => 'Farida',
    'full_name'   => 'Farida Nur Aini',
    'email'       => 'farida@lumitone.app',
    'initials'    => 'FN',
    'plan'        => 'LumiTone Pro',
];

// Sidebar navigation menu. `key` is matched against $activePage
// (defined in each page) to highlight the active item.
$menuItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard',              'icon' => 'grid',       'href' => 'dashboard.php'],
    ['key' => 'analysis',  'label' => 'AI Analysis',            'icon' => 'scan-face',  'href' => '#'],
    ['key' => 'history',   'label' => 'Analysis History',       'icon' => 'history',    'href' => '#'],
    ['key' => 'products',  'label' => 'Product Recommendations','icon' => 'package',    'href' => '#'],
    ['key' => 'saved',     'label' => 'Saved Results',          'icon' => 'bookmark',   'href' => '#'],
    ['key' => 'profile',   'label' => 'Profile',                'icon' => 'user',       'href' => '#'],
    ['key' => 'settings',  'label' => 'Settings',                'icon' => 'settings',   'href' => '#'],
];

// Statistic summary cards
$stats = [
    [
        'icon'  => 'scan-face',
        'label' => 'Total Analyses',
        'value' => '24',
        'meta'  => '+3 bulan ini',
    ],
    [
        'icon'  => 'palette',
        'label' => 'Detected Skin Tone',
        'value' => 'Soft Autumn (Warm)',
        'meta'  => 'Hasil terakhir',
    ],
    [
        'icon'  => 'package',
        'label' => 'Recommended Products',
        'value' => '12',
        'meta'  => 'Produk untukmu',
    ],
    [
        'icon'  => 'calendar',
        'label' => 'Last Analysis',
        'value' => '2 Jan 2024',
        'meta'  => '3 hari yang lalu',
    ],
];

// Quick action shortcut cards
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

// Recent analysis table rows
$recentAnalyses = [
    [
        'initials'  => 'FN',
        'date'      => '2 Jan 2024, 14:30',
        'skintone'  => 'Light - Medium',
        'swatch'    => '#E7B98F',
        'undertone' => 'Warm (Kuning/Emas)',
        'status'    => 'Selesai',
    ],
    [
        'initials'  => 'FN',
        'date'      => '18 Des 2023, 10:15',
        'skintone'  => 'Light - Medium',
        'swatch'    => '#E7B98F',
        'undertone' => 'Warm (Kuning/Emas)',
        'status'    => 'Selesai',
    ],
    [
        'initials'  => 'FN',
        'date'      => '5 Des 2023, 16:45',
        'skintone'  => 'Medium',
        'swatch'    => '#C99169',
        'undertone' => 'Neutral',
        'status'    => 'Selesai',
    ],
    [
        'initials'  => 'FN',
        'date'      => '29 Nov 2023, 09:05',
        'skintone'  => 'Medium',
        'swatch'    => '#C99169',
        'undertone' => 'Neutral',
        'status'    => 'Diproses',
    ],
];

// Recommended product cards
$products = [
    [
        'icon'  => 'droplet',
        'name'  => 'Gentle Foaming Cleanser',
        'tag'   => 'Cleanser',
        'desc'  => 'Membersihkan wajah tanpa membuat kulit terasa kering atau tertarik.',
    ],
    [
        'icon'  => 'sparkles',
        'name'  => 'Vitamin C Brightening Serum',
        'tag'   => 'Serum',
        'desc'  => 'Mencerahkan warna kulit dan menyamarkan bekas jerawat secara bertahap.',
    ],
    [
        'icon'  => 'shield-check',
        'name'  => 'Daily Matte Sunscreen SPF 50+',
        'tag'   => 'Sunscreen',
        'desc'  => 'Melindungi kulit dari sinar UV tanpa meninggalkan white cast.',
    ],
];

// Daily skincare tip
$dailyTip = [
    'title' => 'Jangan Lewatkan Sunscreen di Pagi Hari',
    'body'  => 'Meski di dalam ruangan, sinar UV tetap bisa menembus jendela dan mempercepat penuaan kulit. Gunakan sunscreen minimal SPF 30 setiap pagi, dan aplikasikan ulang setiap 3-4 jam jika beraktivitas di luar ruangan.',
];

// Recent activity timeline
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

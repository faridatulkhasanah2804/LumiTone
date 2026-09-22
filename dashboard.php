<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}


/**
 * dashboard.php
 * ------------------------------------------------------------------
 * Main dashboard ("Beranda") page shown after a user logs in.
 * $stats / $recentAnalyses / $products below are now real queries
 * against the `analysis`, `products`, and `analysis_products` tables
 * (see migration_lumitone.sql), scoped to the logged-in user.
 * ------------------------------------------------------------------
 */

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$userId = (int) $_SESSION['user_id'];

/* ---------------------------------------------------------------
 * Total analyses for this user
 * --------------------------------------------------------------- */
$stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM analysis WHERE user_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$totalAnalyses = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

/* ---------------------------------------------------------------
 * Latest analysis (drives "Detected Skin Tone" & "Last Analysis")
 * --------------------------------------------------------------- */
$stmt = mysqli_prepare($conn, 'SELECT id, season, undertone, skin_tone, created_at
    FROM analysis WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$latestAnalysis = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$stats = [
    [
        'icon'  => 'scan-face',
        'label' => 'Total Analyses',
        'value' => (string) $totalAnalyses,
        'meta'  => 'Total analisis kamu',
    ],
    [
        'icon'  => 'palette',
        'label' => 'Detected Skin Tone',
        'value' => $latestAnalysis['season'] ?? 'Belum ada data',
        'meta'  => 'Hasil terakhir',
    ],
];

/* ---------------------------------------------------------------
 * Recent analyses table (latest 4)
 * --------------------------------------------------------------- */
$recentAnalyses = [];
$stmt = mysqli_prepare($conn, 'SELECT id, skin_tone, undertone, status, created_at
    FROM analysis WHERE user_id = ? ORDER BY created_at DESC LIMIT 4');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$rows = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rows)) {
    $recentAnalyses[] = [
        'id'        => $row['id'],
        'initials'  => $currentUser['initials'],
        'date'      => date('j M Y, H:i', strtotime($row['created_at'])),
        'skintone'  => $row['skin_tone'] ?? '—',
        'swatch'    => lt_skintone_to_hex($row['skin_tone']),
        'undertone' => $row['undertone'] ?? '—',
        'status'    => $row['status'] === 'completed' ? 'Selesai' : 'Diproses',
    ];
}
mysqli_stmt_close($stmt);
?>
<main class="main-content">
    <?php require __DIR__ . '/includes/topbar.php'; ?>

    <div class="page-content">

        <!-- ==================================================
             1. WELCOME CARD (with key stats inline, so the page
                doesn't need a separate stats section)
        =================================================== -->
        <section class="welcome-card reveal">
            <div class="welcome-card-content">
                <span class="welcome-eyebrow"><?= lt_icon('sparkles', '', 14) ?> AI-Powered Skincare</span>
                <h2>Welcome Back!</h2>
                <p>Continue your skincare journey with AI-powered analysis. Lanjutkan pantau perkembangan kulitmu bersama LumiTone.</p>
                <div class="welcome-actions">
                    <a href="analysis.php" class="btn btn-white"><?= lt_icon('scan-face', '', 17) ?> Start New Analysis</a>
                    <a href="history.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.4); color:#fff;">
                        <?= lt_icon('history', '', 17) ?> View History
                    </a>
                </div>
                <div class="welcome-stats">
                    <?php foreach ($stats as $stat): ?>
                        <div class="welcome-stat-chip">
                            <span class="welcome-stat-chip-icon"><?= lt_icon($stat['icon'], '', 16) ?></span>
                            <div>
                                <p class="welcome-stat-chip-value"><?= htmlspecialchars($stat['value']) ?></p>
                                <p class="welcome-stat-chip-label"><?= htmlspecialchars($stat['label']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="welcome-visual">
                <video class="welcome-visual-video" src="assets/hero.mp4" autoplay loop muted playsinline></video>
            </div>
        </section>

        <!-- ==================================================
             2. RECENT ANALYSIS TABLE
        =================================================== -->
        <section class="reveal">
            <div class="section-heading">
                <div>
                    <h2>Recent Analysis</h2>
                    <p>Ringkasan hasil analisis kulit terbarumu.</p>
                </div>
                <a href="history.php" class="section-link">Lihat Semua <?= lt_icon('chevron-right', '', 15) ?></a>
            </div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Date</th>
                            <th>Skin Tone</th>
                            <th>Undertone</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentAnalyses)): ?>
                            <tr><td colspan="6" class="cell-muted">Belum ada analisis. <a href="analysis.php">Mulai analisis pertamamu</a>.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($recentAnalyses as $row): ?>
                            <tr>
                                <td>
                                    <div class="cell-photo">
                                        <span class="avatar avatar-sm"><?= htmlspecialchars($row['initials']) ?></span>
                                    </div>
                                </td>
                                <td class="cell-muted"><?= htmlspecialchars($row['date']) ?></td>
                                <td>
                                    <span style="display:inline-flex; align-items:center; gap:0.5rem;">
                                        <span style="width:14px; height:14px; border-radius:50%; background:<?= htmlspecialchars($row['swatch']) ?>; border:1px solid var(--color-border); flex-shrink:0;"></span>
                                        <?= htmlspecialchars($row['skintone']) ?>
                                    </span>
                                </td>
                                <td class="cell-muted"><?= htmlspecialchars($row['undertone']) ?></td>
                                <td>
                                    <?php if ($row['status'] === 'Selesai'): ?>
                                        <span class="badge badge-success"><span class="badge-dot"></span> Selesai</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning"><span class="badge-dot"></span> Diproses</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="history.php" class="btn btn-secondary btn-sm"><?= lt_icon('eye', '', 15) ?> Lihat Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <?php require __DIR__ . '/includes/footer.php'; ?>
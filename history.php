<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

/**
 * history.php
 * ------------------------------------------------------------------
 * Analysis History page: shows every past AI Skin Analysis result
 * the user has run, with search, filter, sort, a detail modal, and
 * pagination. $historyData is now built from `analysis` +
 * `recommendations` + `analysis_products` for the logged-in user.
 *
 * Follows the exact same page skeleton as dashboard.php / analysis.php:
 *   $pageTitle / $activePage -> header.php -> sidebar.php -> topbar.php
 *   -> .page-content -> footer.php
 * ------------------------------------------------------------------
 */

$pageTitle  = 'Analysis History';
$activePage = 'history';


require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

/**
 * A few icons used on this page aren't in includes/icons.php's lt_icon()
 * library. Rather than touching that shared helper, we define a small
 * local fallback here (same approach analysis.php uses with ai_icon()),
 * matching the same 24x24 / rounded-stroke visual language.
 */
if (!function_exists('hist_icon')) {
    function hist_icon(string $name, int $size = 18, string $class = ''): string
    {
        $paths = [
            'filter'       => '<path d="M4 5h16"></path><path d="M7 12h10"></path><path d="M10 19h4"></path>',
            'trash'        => '<path d="M4.5 7h15"></path><path d="M9.5 7V5.2a1.2 1.2 0 0 1 1.2-1.2h2.6a1.2 1.2 0 0 1 1.2 1.2V7"></path><path d="M6.5 7l.8 12.1A2 2 0 0 0 9.3 21h5.4a2 2 0 0 0 2-1.9L17.5 7"></path><path d="M10.3 11v6"></path><path d="M13.7 11v6"></path>',
            'chevron-left' => '<path d="M15 6l-6 6 6 6"></path>',
            'inbox'        => '<path d="M4 12.5V7.2a1.2 1.2 0 0 1 1.2-1.2h13.6A1.2 1.2 0 0 1 20 7.2v5.3"></path><path d="M4 12.5h4.4l1.2 2.3h4.8l1.2-2.3H20"></path><path d="M4 12.5v6.3A1.2 1.2 0 0 0 5.2 20h13.6a1.2 1.2 0 0 0 1.2-1.2v-6.3"></path>',
            'swatch'       => '<rect x="3.2" y="3.2" width="7.2" height="17.6" rx="2"></rect><path d="M13.5 5.6l4.4-1.9a2 2 0 0 1 2.6 1l3.2 7.4"></path>',
        ];
        $inner = $paths[$name] ?? $paths['inbox'];
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
 * $historyData — every past AI skin analysis for the logged-in user.
 * Sourced from `analysis`, with per-row lookups into
 * `recommendations` (for the color palette) and `analysis_products`
 * + `products` (for the recommended product names).
 * ------------------------------------------------------------------
 */
$userId = (int) $_SESSION['user_id'];

$historyData = [];

$stmt = mysqli_prepare($conn, 'SELECT id, skin_tone, undertone, skin_type, concerns, status, confidence, created_at
    FROM analysis WHERE user_id = ? ORDER BY created_at DESC');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$analysisResult = mysqli_stmt_get_result($stmt);
$analysisRows   = mysqli_fetch_all($analysisResult, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$colorStmt   = mysqli_prepare($conn, 'SELECT clothing FROM recommendations WHERE analysis_id = ? LIMIT 1');
$productStmt = mysqli_prepare($conn, 'SELECT p.product_name FROM analysis_products ap
    INNER JOIN products p ON p.id = ap.product_id WHERE ap.analysis_id = ?');

foreach ($analysisRows as $row) {
    $concerns    = lt_json_list($row['concerns']);
    $mainConcern = $concerns[0]['label'] ?? ($row['status'] === 'processing' ? 'Sedang diproses' : '—');

    $colors = [];
    mysqli_stmt_bind_param($colorStmt, 'i', $row['id']);
    mysqli_stmt_execute($colorStmt);
    if ($rec = mysqli_fetch_assoc(mysqli_stmt_get_result($colorStmt))) {
        foreach (lt_json_list($rec['clothing']) as $c) {
            $colors[] = is_array($c) ? ($c['hex'] ?? '') : $c;
        }
        $colors = array_filter($colors);
    }

    $products = [];
    mysqli_stmt_bind_param($productStmt, 'i', $row['id']);
    mysqli_stmt_execute($productStmt);
    $prodRows = mysqli_stmt_get_result($productStmt);
    while ($p = mysqli_fetch_assoc($prodRows)) {
        $products[] = $p['product_name'];
    }

    $historyData[] = [
        'id'         => $row['id'],
        'initials'   => $currentUser['initials'],
        'photo'      => '',
        'date'       => date('j M Y, H:i', strtotime($row['created_at'])),
        'month'      => date('Y-m', strtotime($row['created_at'])),
        'skintone'   => $row['skin_tone'] ?? '—',
        'swatch'     => lt_skintone_to_hex($row['skin_tone']),
        'undertone'  => $row['undertone'] ?? '—',
        'skintype'   => $row['skin_type'] ?? '—',
        'concern'    => $mainConcern,
        'status'     => $row['status'] === 'completed' ? 'Selesai' : 'Diproses',
        'confidence' => (int) $row['confidence'],
        'colors'     => $colors,
        'products'   => $products,
    ];
}

mysqli_stmt_close($colorStmt);
mysqli_stmt_close($productStmt);
?>
<main class="main-content">

    <link rel="stylesheet" href="assets/css/history.css">

    <?php require __DIR__ . '/includes/topbar.php'; ?>

    <div class="page-content">

        <!-- ==================================================
             1. PAGE HEADER
        =================================================== -->
        <section class="history-hero reveal">
            <div>
                <span class="history-eyebrow"><?= hist_icon('inbox', 13) ?> Riwayat Analisis</span>
                <h2 class="history-title">Analysis History</h2>
                <p class="history-subtitle">View all of your previous AI skin analysis results.</p>
            </div>
            <a href="analysis.php" class="btn btn-primary btn-sm">
                <?= lt_icon('scan-face', '', 16) ?> Analisis Baru
            </a>
        </section>

        <!-- ==================================================
             2. TOOLBAR: search / filter / sort
        =================================================== -->
        <section class="history-toolbar reveal">
            <div class="history-search">
                <span class="history-search-icon"><?= lt_icon('search', '', 16) ?></span>
                <input type="search" id="historySearch" placeholder="Search analysis...">
            </div>

            <div class="history-toolbar-selects">
                <label class="history-select">
                    <?= hist_icon('filter', 15) ?>
                    <select id="historyFilter">
                        <option value="all">All</option>
                        <option value="this-month">This Month</option>
                        <option value="last-month">Last Month</option>
                    </select>
                </label>

                <label class="history-select">
                    <?= lt_icon('clock', '', 15) ?>
                    <select id="historySort">
                        <option value="newest">Newest</option>
                        <option value="oldest">Oldest</option>
                    </select>
                </label>
            </div>
        </section>

        <!-- ==================================================
             3. HISTORY TABLE
        =================================================== -->
        <section class="reveal">
            <div class="table-wrap" id="historyTableWrap">
                <table class="data-table" id="historyTable">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Date</th>
                            <th>Skin Tone</th>
                            <th>Undertone</th>
                            <th>Skin Type</th>
                            <th>Main Concern</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                        <?php foreach ($historyData as $row): ?>
                            <tr class="history-row"
                                data-id="<?= (int) $row['id'] ?>"
                                data-month="<?= htmlspecialchars($row['month']) ?>"
                                data-date="<?= htmlspecialchars($row['date']) ?>"
                                data-search="<?= htmlspecialchars(strtolower($row['skintone'] . ' ' . $row['undertone'] . ' ' . $row['skintype'] . ' ' . $row['concern'])) ?>">
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
                                <td class="cell-muted"><?= htmlspecialchars($row['skintype']) ?></td>
                                <td class="cell-muted"><?= htmlspecialchars($row['concern']) ?></td>
                                <td>
                                    <?php if ($row['status'] === 'Selesai'): ?>
                                        <span class="badge badge-success"><span class="badge-dot"></span> Completed</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning"><span class="badge-dot"></span> Processing</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="history-actions">
                                        <button type="button" class="btn btn-secondary btn-sm history-view-btn" data-id="<?= (int) $row['id'] ?>">
                                            <?= lt_icon('eye', '', 15) ?> View Detail
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-sm history-delete-btn" data-id="<?= (int) $row['id'] ?>" aria-label="Hapus riwayat">
                                            <?= hist_icon('trash', 15) ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ==================================================
                 EMPTY STATE (hidden unless the table has 0 visible rows)
            =================================================== -->
            <div class="history-empty is-hidden" id="historyEmpty">
                <div class="history-empty-icon"><?= hist_icon('inbox', 30) ?></div>
                <p class="history-empty-title">No analysis history yet</p>
                <p class="history-empty-desc">Start your first AI skin analysis.</p>
                <a href="analysis.php" class="btn btn-primary btn-sm">
                    <?= lt_icon('scan-face', '', 16) ?> Start Analysis
                </a>
            </div>

            <!-- ==================================================
                 PAGINATION
            =================================================== -->
            <nav class="history-pagination" id="historyPagination" aria-label="Navigasi halaman riwayat">
                <button type="button" class="history-page-btn history-page-prev" id="historyPrev" disabled>
                    <?= hist_icon('chevron-left', 15) ?> Previous
                </button>
                <div class="history-page-numbers" id="historyPageNumbers">
                    <button type="button" class="history-page-num is-active" data-page="1">1</button>
                    <button type="button" class="history-page-num" data-page="2">2</button>
                    <button type="button" class="history-page-num" data-page="3">3</button>
                </div>
                <button type="button" class="history-page-btn history-page-next" id="historyNext">
                    Next <?= lt_icon('chevron-right', '', 15) ?>
                </button>
            </nav>
        </section>

    </div>

    <!-- ==================================================
         DETAIL MODAL
    =================================================== -->
    <div class="history-modal-overlay" id="historyModalOverlay" aria-hidden="true">
        <div class="history-modal" role="dialog" aria-modal="true" aria-labelledby="historyModalTitle">
            <button type="button" class="history-modal-close" id="historyModalClose" aria-label="Tutup">
                <?= lt_icon('x', '', 16) ?>
            </button>

            <div class="history-modal-photo">
                <span class="avatar avatar-lg" id="historyModalAvatar">FN</span>
            </div>

            <h3 class="history-modal-title" id="historyModalTitle">Detail Analisis</h3>
            <p class="history-modal-date" id="historyModalDate">&mdash;</p>

            <div class="history-modal-grid">
                <div class="history-modal-stat">
                    <span class="history-modal-label"><?= lt_icon('palette', '', 15) ?> Skin Tone</span>
                    <span class="history-modal-value" id="historyModalSkintone">&mdash;</span>
                </div>
                <div class="history-modal-stat">
                    <span class="history-modal-label"><?= hist_icon('swatch', 15) ?> Undertone</span>
                    <span class="history-modal-value" id="historyModalUndertone">&mdash;</span>
                </div>
                <div class="history-modal-stat">
                    <span class="history-modal-label"><?= lt_icon('droplet', '', 15) ?> Skin Type</span>
                    <span class="history-modal-value" id="historyModalSkintype">&mdash;</span>
                </div>
                <div class="history-modal-stat">
                    <span class="history-modal-label"><?= lt_icon('activity', '', 15) ?> Main Concern</span>
                    <span class="history-modal-value" id="historyModalConcern">&mdash;</span>
                </div>
            </div>

            <div class="history-modal-section">
                <span class="history-modal-label"><?= lt_icon('palette', '', 15) ?> Recommended Colors</span>
                <div class="history-modal-swatches" id="historyModalColors"></div>
            </div>

            <div class="history-modal-section">
                <span class="history-modal-label"><?= lt_icon('package', '', 15) ?> Recommended Products</span>
                <ul class="history-modal-products" id="historyModalProducts"></ul>
            </div>

            <div class="history-modal-confidence">
                <div class="history-modal-confidence-head">
                    <span><?= lt_icon('check-circle', '', 15) ?> Confidence Score</span>
                    <span id="historyModalConfidenceValue">&mdash;</span>
                </div>
                <div class="history-modal-confidence-bar">
                    <div class="history-modal-confidence-fill" id="historyModalConfidenceFill"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        /* Dummy history data passed to history.js to power the detail modal
           without a second round-trip / database call. */
        window.LT_HISTORY_DATA = <?= json_encode(array_map(function ($row) {
            return [
                'id'         => $row['id'],
                'initials'   => $row['initials'],
                'date'       => $row['date'],
                'skintone'   => $row['skintone'],
                'undertone'  => $row['undertone'],
                'skintype'   => $row['skintype'],
                'concern'    => $row['concern'],
                'status'     => $row['status'],
                'confidence' => $row['confidence'],
                'colors'     => $row['colors'],
                'products'   => $row['products'],
            ];
        }, $historyData), JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="assets/js/history.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
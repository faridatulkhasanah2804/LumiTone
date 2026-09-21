<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

/**
 * saved.php
 * ------------------------------------------------------------------
 * Saved Results page: a gallery of the user's favorited AI skin
 * analysis results (bookmarked from analysis.php / history.php).
 * Includes search, filter, sort, a detail modal, unsave action,
 * empty state, and pagination.
 *
 * CHANGE LOG (save-to-favorites fix):
 *  - $savedData is no longer a hardcoded dummy array. It's now built
 *    from `analysis` (WHERE user_id = ? AND is_saved = 1), joined
 *    against `recommendations` and `analysis_products` exactly the
 *    same way history.php does it.
 *  - The unsave button now round-trips through toggle_save.php via
 *    saved.js instead of just removing the card from the DOM.
 *
 * Follows the exact same page skeleton as dashboard.php / history.php
 * / products.php:
 *   $pageTitle / $activePage -> header.php -> sidebar.php -> topbar.php
 *   -> .page-content -> footer.php
 * ------------------------------------------------------------------
 */

$pageTitle  = 'Saved Results';
$activePage = 'saved';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

/**
 * A couple of icons used on this page aren't in includes/icons.php's
 * lt_icon() library. Rather than touching that shared helper, we define
 * a small local fallback here (same approach analysis.php's ai_icon(),
 * history.php's hist_icon(), and products.php's prod_icon() use),
 * matching the same 24x24 / rounded-stroke visual language.
 */
if (!function_exists('saved_icon')) {
    function saved_icon(string $name, int $size = 18, string $class = ''): string
    {
        $paths = [
            'filter'         => '<path d="M4 5h16"></path><path d="M7 12h10"></path><path d="M10 19h4"></path>',
            'chevron-left'   => '<path d="M15 6l-6 6 6 6"></path>',
            'inbox'          => '<path d="M4 12.5V7.2a1.2 1.2 0 0 1 1.2-1.2h13.6A1.2 1.2 0 0 1 20 7.2v5.3"></path><path d="M4 12.5h4.4l1.2 2.3h4.8l1.2-2.3H20"></path><path d="M4 12.5v6.3A1.2 1.2 0 0 0 5.2 20h13.6a1.2 1.2 0 0 0 1.2-1.2v-6.3"></path>',
            'swatch'         => '<rect x="3.2" y="3.2" width="7.2" height="17.6" rx="2"></rect><path d="M13.5 5.6l4.4-1.9a2 2 0 0 1 2.6 1l3.2 7.4"></path>',
            'bookmark-x'     => '<path d="M6.5 4.2h11a1 1 0 0 1 1 1V20l-6.5-3.6L5.5 20V5.2a1 1 0 0 1 1-1z"></path><path d="M10.2 9l3.6 3.6"></path><path d="M13.8 9l-3.6 3.6"></path>',
            'bookmark-filled'=> '<path d="M6.5 4.2h11a1 1 0 0 1 1 1V20l-6.5-3.6L5.5 20V5.2a1 1 0 0 1 1-1z" fill="currentColor"></path>',
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
 * $savedData — analysis results the user has bookmarked as favorites,
 * sourced from `analysis` (is_saved = 1), joined against
 * `recommendations` (color palette) and `analysis_products` +
 * `products` (recommended product names) — same pattern history.php
 * already uses.
 * ------------------------------------------------------------------
 */
$userId = (int) $_SESSION['user_id'];

$savedData = [];

$stmt = mysqli_prepare($conn, 'SELECT id, skin_tone, undertone, skin_type, concerns, status, confidence, created_at
    FROM analysis WHERE user_id = ? AND is_saved = 1 ORDER BY created_at DESC');
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

    $confidence = (int) $row['confidence'];

    $savedData[] = [
        'id'          => $row['id'],
        'initials'    => $currentUser['initials'],
        'date'        => date('j M Y, H:i', strtotime($row['created_at'])),
        'month'       => date('Y-m', strtotime($row['created_at'])),
        'skintone'    => $row['skin_tone'] ?? '—',
        'swatch'      => lt_skintone_to_hex($row['skin_tone']),
        'undertone'   => $row['undertone'] ?? '—',
        'skintype'    => $row['skin_type'] ?? '—',
        'concern'     => $mainConcern,
        // `analysis` has no free-text "note" column yet, so we show a
        // small, genuinely-derived summary instead of inventing text.
        'note'        => $confidence
            ? 'Confidence score analisis ini: ' . $confidence . '%.'
            : 'Ditandai sebagai hasil favorit.',
        'confidence'  => $confidence,
        'colors'      => $colors,
        'products'    => $products,
    ];
}

mysqli_stmt_close($colorStmt);
mysqli_stmt_close($productStmt);
?>
<main class="main-content">

    <link rel="stylesheet" href="assets/css/saved.css">

    <?php require __DIR__ . '/includes/topbar.php'; ?>

    <div class="page-content">

        <!-- ==================================================
             1. PAGE HEADER
        =================================================== -->
        <section class="saved-hero reveal">
            <div>
                <span class="saved-eyebrow"><?= lt_icon('bookmark', '', 13) ?> Favorit Kamu</span>
                <h2 class="saved-title">Saved Results</h2>
                <p class="saved-subtitle">Kumpulan hasil AI Skin Analysis yang kamu tandai sebagai favorit.</p>
            </div>
            <a href="history.php" class="btn btn-secondary btn-sm">
                <?= lt_icon('history', '', 16) ?> Lihat Semua Riwayat
            </a>
        </section>

        <!-- ==================================================
             2. TOOLBAR: search / filter / sort
        =================================================== -->
        <section class="saved-toolbar reveal">
            <div class="saved-search">
                <span class="saved-search-icon"><?= lt_icon('search', '', 16) ?></span>
                <input type="search" id="savedSearch" placeholder="Search saved results...">
            </div>

            <div class="saved-toolbar-selects">
                <label class="saved-select">
                    <?= saved_icon('filter', 15) ?>
                    <select id="savedFilter">
                        <option value="all">All</option>
                        <option value="this-month">This Month</option>
                        <option value="last-month">Last Month</option>
                    </select>
                </label>

                <label class="saved-select">
                    <?= lt_icon('clock', '', 15) ?>
                    <select id="savedSort">
                        <option value="newest">Newest</option>
                        <option value="oldest">Oldest</option>
                    </select>
                </label>
            </div>
        </section>

        <!-- ==================================================
             3. SAVED RESULTS GRID
        =================================================== -->
        <section class="reveal">
            <div class="saved-grid" id="savedGrid">
                <?php foreach ($savedData as $item): ?>
                    <div class="saved-card"
                         data-id="<?= (int) $item['id'] ?>"
                         data-month="<?= htmlspecialchars($item['month']) ?>"
                         data-date="<?= htmlspecialchars($item['date']) ?>"
                         data-search="<?= htmlspecialchars(strtolower($item['skintone'] . ' ' . $item['undertone'] . ' ' . $item['skintype'] . ' ' . $item['concern'])) ?>">
                        <button type="button" class="saved-unsave-btn" data-id="<?= (int) $item['id'] ?>" aria-label="Hapus dari saved">
                            <?= saved_icon('bookmark-filled', 17) ?>
                        </button>

                        <div class="saved-card-head">
                            <span class="avatar avatar-sm"><?= htmlspecialchars($item['initials']) ?></span>
                            <div>
                                <p class="saved-card-date"><?= htmlspecialchars($item['date']) ?></p>
                                <span class="saved-card-tone">
                                    <span class="saved-swatch" style="background:<?= htmlspecialchars($item['swatch']) ?>;"></span>
                                    <?= htmlspecialchars($item['skintone']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="saved-card-meta">
                            <div class="saved-card-meta-item">
                                <span class="saved-card-meta-label">Undertone</span>
                                <span class="saved-card-meta-value"><?= htmlspecialchars($item['undertone']) ?></span>
                            </div>
                            <div class="saved-card-meta-item">
                                <span class="saved-card-meta-label">Skin Type</span>
                                <span class="saved-card-meta-value"><?= htmlspecialchars($item['skintype']) ?></span>
                            </div>
                            <div class="saved-card-meta-item">
                                <span class="saved-card-meta-label">Main Concern</span>
                                <span class="saved-card-meta-value"><?= htmlspecialchars($item['concern']) ?></span>
                            </div>
                        </div>

                        <p class="saved-card-note"><?= lt_icon('activity', '', 13) ?> <?= htmlspecialchars($item['note']) ?></p>

                        <button type="button" class="btn btn-secondary btn-sm btn-block saved-view-btn" data-id="<?= (int) $item['id'] ?>">
                            <?= lt_icon('eye', '', 15) ?> View Detail
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ==================================================
                 EMPTY STATE (hidden unless the grid has 0 visible cards)
            =================================================== -->
            <div class="saved-empty<?= empty($savedData) ? '' : ' is-hidden' ?>" id="savedEmpty">
                <div class="saved-empty-icon"><?= saved_icon('inbox', 30) ?></div>
                <p class="saved-empty-title">No saved results yet</p>
                <p class="saved-empty-desc">Tandai hasil analisis favoritmu dari halaman Analysis History.</p>
                <a href="history.php" class="btn btn-primary btn-sm">
                    <?= lt_icon('history', '', 16) ?> Buka Analysis History
                </a>
            </div>

            <!-- ==================================================
                 PAGINATION
            =================================================== -->
            <nav class="saved-pagination" id="savedPagination" aria-label="Navigasi halaman saved results">
                <button type="button" class="saved-page-btn saved-page-prev" id="savedPrev" disabled>
                    <?= saved_icon('chevron-left', 15) ?> Previous
                </button>
                <div class="saved-page-numbers" id="savedPageNumbers">
                    <button type="button" class="saved-page-num is-active" data-page="1">1</button>
                    <button type="button" class="saved-page-num" data-page="2">2</button>
                    <button type="button" class="saved-page-num" data-page="3">3</button>
                </div>
                <button type="button" class="saved-page-btn saved-page-next" id="savedNext">
                    Next <?= lt_icon('chevron-right', '', 15) ?>
                </button>
            </nav>
        </section>

    </div>

    <!-- ==================================================
         DETAIL MODAL
    =================================================== -->
    <div class="saved-modal-overlay" id="savedModalOverlay" aria-hidden="true">
        <div class="saved-modal" role="dialog" aria-modal="true" aria-labelledby="savedModalTitle">
            <button type="button" class="saved-modal-close" id="savedModalClose" aria-label="Tutup">
                <?= lt_icon('x', '', 16) ?>
            </button>

            <div class="saved-modal-photo">
                <span class="avatar avatar-lg" id="savedModalAvatar">FN</span>
            </div>

            <h3 class="saved-modal-title" id="savedModalTitle">Detail Hasil Tersimpan</h3>
            <p class="saved-modal-date" id="savedModalDate">&mdash;</p>

            <div class="saved-modal-grid">
                <div class="saved-modal-stat">
                    <span class="saved-modal-label"><?= lt_icon('palette', '', 15) ?> Skin Tone</span>
                    <span class="saved-modal-value" id="savedModalSkintone">&mdash;</span>
                </div>
                <div class="saved-modal-stat">
                    <span class="saved-modal-label"><?= saved_icon('swatch', 15) ?> Undertone</span>
                    <span class="saved-modal-value" id="savedModalUndertone">&mdash;</span>
                </div>
                <div class="saved-modal-stat">
                    <span class="saved-modal-label"><?= lt_icon('droplet', '', 15) ?> Skin Type</span>
                    <span class="saved-modal-value" id="savedModalSkintype">&mdash;</span>
                </div>
                <div class="saved-modal-stat">
                    <span class="saved-modal-label"><?= lt_icon('activity', '', 15) ?> Main Concern</span>
                    <span class="saved-modal-value" id="savedModalConcern">&mdash;</span>
                </div>
            </div>

            <div class="saved-modal-section">
                <span class="saved-modal-label"><?= lt_icon('palette', '', 15) ?> Recommended Colors</span>
                <div class="saved-modal-swatches" id="savedModalColors"></div>
            </div>

            <div class="saved-modal-section">
                <span class="saved-modal-label"><?= lt_icon('package', '', 15) ?> Recommended Products</span>
                <ul class="saved-modal-products" id="savedModalProducts"></ul>
            </div>

            <div class="saved-modal-confidence">
                <div class="saved-modal-confidence-head">
                    <span><?= lt_icon('check-circle', '', 15) ?> Confidence Score</span>
                    <span id="savedModalConfidenceValue">&mdash;</span>
                </div>
                <div class="saved-modal-confidence-bar">
                    <div class="saved-modal-confidence-fill" id="savedModalConfidenceFill"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        /* Saved-results data (now sourced from the database) passed to
           saved.js to power the detail modal without a second
           round-trip / database call. */
        window.LT_SAVED_DATA = <?= json_encode($savedData, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="assets/js/saved.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
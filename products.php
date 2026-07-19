<?php
/**
 * products.php
 * ------------------------------------------------------------------
 * Product Recommendation page: shows AI-curated skincare products
 * based on the user's latest skin analysis, with search, category
 * filter, sort, a detail modal, and pagination. Uses dummy PHP array
 * data for now ($productsData), ready to be swapped for real
 * database queries later.
 *
 * Follows the exact same page skeleton as dashboard.php / history.php:
 *   $pageTitle / $activePage -> header.php -> sidebar.php -> topbar.php
 *   -> .page-content -> footer.php
 * ------------------------------------------------------------------
 */

$pageTitle  = 'Product Recommendation';
$activePage = 'products';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

/**
 * A couple of icons used on this page aren't in includes/icons.php's
 * lt_icon() library. Rather than touching that shared helper, we define
 * a small local fallback here (same approach analysis.php's ai_icon()
 * and history.php's hist_icon() use), matching the same 24x24 /
 * rounded-stroke visual language.
 */
if (!function_exists('prod_icon')) {
    function prod_icon(string $name, int $size = 18, string $class = ''): string
    {
        $paths = [
            'filter'       => '<path d="M4 5h16"></path><path d="M7 12h10"></path><path d="M10 19h4"></path>',
            'chevron-left' => '<path d="M15 6l-6 6 6 6"></path>',
            'inbox'        => '<path d="M4 12.5V7.2a1.2 1.2 0 0 1 1.2-1.2h13.6A1.2 1.2 0 0 1 20 7.2v5.3"></path><path d="M4 12.5h4.4l1.2 2.3h4.8l1.2-2.3H20"></path><path d="M4 12.5v6.3A1.2 1.2 0 0 0 5.2 20h13.6a1.2 1.2 0 0 0 1.2-1.2v-6.3"></path>',
            'target'       => '<circle cx="12" cy="12" r="8.5"></circle><circle cx="12" cy="12" r="4.8"></circle><circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"></circle>',
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
 * DUMMY DATA — AI-recommended products based on the user's latest
 * skin analysis. Replace with a real query once the database and
 * recommendation engine are ready.
 * ------------------------------------------------------------------
 */
$productsData = [
    [
        'id'          => 1,
        'name'        => 'Gentle Foaming Cleanser',
        'category'    => 'Cleanser',
        'icon'        => 'droplet',
        'desc'        => 'Membersihkan wajah tanpa membuat kulit terasa kering atau tertarik.',
        'match'       => 96,
        'concerns'    => ['Pori-pori besar', 'Kulit kombinasi'],
        'ingredients' => ['Ceramide', 'Panthenol', 'Amino Surfactant'],
        'how_to_use'  => 'Gunakan pagi dan malam pada wajah lembap, pijat lembut 30 detik lalu bilas.',
    ],
    [
        'id'          => 2,
        'name'        => 'Vitamin C Brightening Serum',
        'category'    => 'Serum',
        'icon'        => 'sparkles',
        'desc'        => 'Mencerahkan warna kulit dan menyamarkan bekas jerawat secara bertahap.',
        'match'       => 92,
        'concerns'    => ['Kusam', 'Bekas jerawat'],
        'ingredients' => ['Vitamin C 10%', 'Vitamin E', 'Ferulic Acid'],
        'how_to_use'  => 'Aplikasikan 2-3 tetes di pagi hari sebelum sunscreen.',
    ],
    [
        'id'          => 3,
        'name'        => 'Daily Matte Sunscreen SPF 50+',
        'category'    => 'Sunscreen',
        'icon'        => 'shield-check',
        'desc'        => 'Melindungi kulit dari sinar UV tanpa meninggalkan white cast.',
        'match'       => 98,
        'concerns'    => ['Kulit berminyak', 'Proteksi UV'],
        'ingredients' => ['Niacinamide', 'Zinc Oxide', 'Silica'],
        'how_to_use'  => 'Aplikasikan sebagai langkah terakhir skincare pagi, ulangi tiap 3-4 jam.',
    ],
    [
        'id'          => 4,
        'name'        => 'Soothing Centella Toner',
        'category'    => 'Toner',
        'icon'        => 'droplet',
        'desc'        => 'Menenangkan kemerahan dan menjaga keseimbangan pH kulit.',
        'match'       => 89,
        'concerns'    => ['Kemerahan ringan', 'Kulit sensitif'],
        'ingredients' => ['Centella Asiatica', 'Panthenol', 'Allantoin'],
        'how_to_use'  => 'Tuang ke kapas atau tangan, tepuk lembut setelah cleansing.',
    ],
    [
        'id'          => 5,
        'name'        => 'Barrier Repair Moisturizer',
        'category'    => 'Moisturizer',
        'icon'        => 'droplet',
        'desc'        => 'Memperkuat skin barrier dan menjaga kelembapan sepanjang hari.',
        'match'       => 94,
        'concerns'    => ['Kulit kering', 'Skin barrier lemah'],
        'ingredients' => ['Ceramide NP', 'Squalane', 'Shea Butter'],
        'how_to_use'  => 'Gunakan sebagai langkah terakhir pagi dan malam pada wajah bersih.',
    ],
    [
        'id'          => 6,
        'name'        => 'Retinol Night Serum',
        'category'    => 'Serum',
        'icon'        => 'sparkles',
        'desc'        => 'Membantu regenerasi kulit dan menyamarkan garis halus.',
        'match'       => 85,
        'concerns'    => ['Garis halus', 'Tekstur kulit'],
        'ingredients' => ['Retinol 0.3%', 'Squalane', 'Vitamin E'],
        'how_to_use'  => 'Gunakan malam hari 2-3x seminggu, selalu diikuti sunscreen keesokan paginya.',
    ],
    [
        'id'          => 7,
        'name'        => 'Oil Control Clay Mask',
        'category'    => 'Mask',
        'icon'        => 'palette',
        'desc'        => 'Menyerap minyak berlebih dan membersihkan pori-pori secara mendalam.',
        'match'       => 87,
        'concerns'    => ['Kulit berminyak', 'Pori-pori besar'],
        'ingredients' => ['Kaolin Clay', 'Tea Tree Oil', 'Niacinamide'],
        'how_to_use'  => 'Aplikasikan tipis 10-15 menit, 1-2x seminggu, lalu bilas dengan air hangat.',
    ],
    [
        'id'          => 8,
        'name'        => 'Niacinamide 10% Serum',
        'category'    => 'Serum',
        'icon'        => 'sparkles',
        'desc'        => 'Mengecilkan tampilan pori dan meratakan warna kulit.',
        'match'       => 90,
        'concerns'    => ['Pori-pori besar', 'Kusam'],
        'ingredients' => ['Niacinamide 10%', 'Zinc PCA'],
        'how_to_use'  => 'Gunakan pagi/malam setelah toner, sebelum moisturizer.',
    ],
];

$categories = ['All', 'Cleanser', 'Toner', 'Serum', 'Moisturizer', 'Sunscreen', 'Mask'];
?>
<main class="main-content">

    <link rel="stylesheet" href="assets/css/products.css">

    <?php require __DIR__ . '/includes/topbar.php'; ?>

    <div class="page-content">

        <!-- ==================================================
             1. PAGE HEADER
        =================================================== -->
        <section class="products-hero reveal">
            <div>
                <span class="products-eyebrow"><?= lt_icon('sparkles', '', 13) ?> Rekomendasi AI</span>
                <h2 class="products-title">Product Recommendation</h2>
                <p class="products-subtitle">Skincare yang dipersonalisasi berdasarkan hasil AI Skin Analysis terbarumu.</p>
            </div>
            <a href="analysis.php" class="btn btn-primary btn-sm">
                <?= lt_icon('scan-face', '', 16) ?> Analisis Baru
            </a>
        </section>

        <!-- ==================================================
             2. TOOLBAR: search / category filter / sort
        =================================================== -->
        <section class="products-toolbar reveal">
            <div class="products-search">
                <span class="products-search-icon"><?= lt_icon('search', '', 16) ?></span>
                <input type="search" id="productsSearch" placeholder="Search products...">
            </div>

            <div class="products-toolbar-selects">
                <label class="products-select">
                    <?= prod_icon('filter', 15) ?>
                    <select id="productsSort">
                        <option value="match">Highest Match</option>
                        <option value="az">Name A-Z</option>
                        <option value="za">Name Z-A</option>
                    </select>
                </label>
            </div>
        </section>

        <!-- ==================================================
             3. CATEGORY TABS
        =================================================== -->
        <div class="products-tabs reveal" id="productsTabs" role="tablist">
            <?php foreach ($categories as $i => $cat): ?>
                <button type="button"
                        class="products-tab <?= $i === 0 ? 'is-active' : '' ?>"
                        data-category="<?= htmlspecialchars($cat) ?>"
                        role="tab"
                        aria-selected="<?= $i === 0 ? 'true' : 'false' ?>">
                    <?= htmlspecialchars($cat) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- ==================================================
             4. PRODUCTS GRID
        =================================================== -->
        <section class="reveal">
            <div class="products-grid" id="productsGrid">
                <?php foreach ($productsData as $product): ?>
                    <div class="product-card products-card"
                         data-id="<?= (int) $product['id'] ?>"
                         data-category="<?= htmlspecialchars($product['category']) ?>"
                         data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>"
                         data-match="<?= (int) $product['match'] ?>">
                        <div class="product-thumb"><?= lt_icon($product['icon'], '', 34) ?></div>
                        <div class="product-body">
                            <div class="products-card-tags">
                                <span class="badge badge-neutral product-tag"><?= htmlspecialchars($product['category']) ?></span>
                                <span class="badge badge-success products-match-badge">
                                    <?= prod_icon('target', 12) ?> <?= (int) $product['match'] ?>% Match
                                </span>
                            </div>
                            <p class="product-name"><?= htmlspecialchars($product['name']) ?></p>
                            <p class="product-desc"><?= htmlspecialchars($product['desc']) ?></p>
                            <button type="button" class="btn btn-secondary btn-sm btn-block products-view-btn" data-id="<?= (int) $product['id'] ?>">
                                <?= lt_icon('eye', '', 15) ?> Lihat Detail
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ==================================================
                 EMPTY STATE (hidden unless the grid has 0 visible cards)
            =================================================== -->
            <div class="products-empty is-hidden" id="productsEmpty">
                <div class="products-empty-icon"><?= prod_icon('inbox', 30) ?></div>
                <p class="products-empty-title">No products found</p>
                <p class="products-empty-desc">Coba ubah kata kunci pencarian atau kategori filter.</p>
            </div>

            <!-- ==================================================
                 PAGINATION
            =================================================== -->
            <nav class="products-pagination" id="productsPagination" aria-label="Navigasi halaman produk">
                <button type="button" class="products-page-btn products-page-prev" id="productsPrev" disabled>
                    <?= prod_icon('chevron-left', 15) ?> Previous
                </button>
                <div class="products-page-numbers" id="productsPageNumbers">
                    <button type="button" class="products-page-num is-active" data-page="1">1</button>
                    <button type="button" class="products-page-num" data-page="2">2</button>
                    <button type="button" class="products-page-num" data-page="3">3</button>
                </div>
                <button type="button" class="products-page-btn products-page-next" id="productsNext">
                    Next <?= lt_icon('chevron-right', '', 15) ?>
                </button>
            </nav>
        </section>

    </div>

    <!-- ==================================================
         DETAIL MODAL
    =================================================== -->
    <div class="products-modal-overlay" id="productsModalOverlay" aria-hidden="true">
        <div class="products-modal" role="dialog" aria-modal="true" aria-labelledby="productsModalTitle">
            <button type="button" class="products-modal-close" id="productsModalClose" aria-label="Tutup">
                <?= lt_icon('x', '', 16) ?>
            </button>

            <div class="products-modal-thumb" id="productsModalThumb"></div>

            <span class="badge badge-neutral products-modal-tag" id="productsModalTag">&mdash;</span>
            <h3 class="products-modal-title" id="productsModalTitle">Nama Produk</h3>
            <p class="products-modal-desc" id="productsModalDesc">&mdash;</p>

            <div class="products-modal-match">
                <div class="products-modal-match-head">
                    <span><?= prod_icon('target', 15) ?> Match Score</span>
                    <span id="productsModalMatchValue">&mdash;</span>
                </div>
                <div class="products-modal-match-bar">
                    <div class="products-modal-match-fill" id="productsModalMatchFill"></div>
                </div>
            </div>

            <div class="products-modal-section">
                <span class="products-modal-label"><?= lt_icon('activity', '', 15) ?> Mengatasi</span>
                <div class="products-modal-chips" id="productsModalConcerns"></div>
            </div>

            <div class="products-modal-section">
                <span class="products-modal-label"><?= lt_icon('palette', '', 15) ?> Kandungan Utama</span>
                <div class="products-modal-chips" id="productsModalIngredients"></div>
            </div>

            <div class="products-modal-section">
                <span class="products-modal-label"><?= lt_icon('check-circle', '', 15) ?> Cara Pakai</span>
                <p class="products-modal-value" id="productsModalHowTo">&mdash;</p>
            </div>
        </div>
    </div>

    <script>
        /* Dummy product data passed to products.js to power the detail
           modal without a second round-trip / database call. */
        window.LT_PRODUCTS_DATA = <?= json_encode($productsData, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="assets/js/products.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
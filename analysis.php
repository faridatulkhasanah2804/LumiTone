<?php
/**
 * dashboard.php
 * ------------------------------------------------------------------
 * Main dashboard ("Beranda") page shown after a user logs in.
 * All data comes from includes/data.php (dummy for now, ready to be
 * swapped for real database queries later).
 * ------------------------------------------------------------------
 */

$pageTitle  = 'Analysis';
$activePage = 'analysis';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">

<?php
/**
 * analysis.php
 * ------------------------------------------------------------------
 * Halaman AI Analysis: pengguna mengunggah/mengambil foto wajah,
 * lalu melihat hasil deteksi skin tone, skin type, dan concern.
 *
 * $activePage harus diset ke 'analysis' oleh halaman pemanggil,
 * lalu file ini di-include di dalam .page-content pada layout utama
 * (setelah sidebar.php dan topbar.php).
 *
 * Icon dipakai langsung sebagai inline SVG (bukan lewat lt_icon())
 * supaya tidak tergantung nama icon yang tersedia di helper icon kamu.
 * ------------------------------------------------------------------
 */

if (!function_exists('ai_icon')) {
    function ai_icon(string $name, int $size = 18): string
    {
        $paths = [
            'sparkles'   => '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>',
            'upload'     => '<path d="M12 3v12"/><path d="m17 8-5-5-5 5"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>',
            'camera'     => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
            'x'          => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
            'check'      => '<path d="M20 6 9 17l-5-5"/>',
            'lightbulb'  => '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>',
            'rotate-ccw' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>',
            'bookmark'   => '<path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
            'package'    => '<path d="M16.5 9.4 7.55 4.24"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.29 7 12 12l8.71-5"/><path d="M12 22V12"/>',
            'palette'    => '<path d="M12 22a1 1 0 0 1 0-20 10 9 0 0 1 10 9 5 5 0 0 1-5 5h-2.25a1.75 1.75 0 0 0-1.4 2.8l.3.4a1.75 1.75 0 0 1-1.4 2.8z"/><circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/>',
            'droplet'    => '<path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>',
            'activity'   => '<path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/>',
            'scan-face'  => '<path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/>',
            'shirt'      => '<path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/>',
            'ban'        => '<circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/>',
        ];
        $body = $paths[$name] ?? $paths['sparkles'];
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="ai-icon">' . $body . '</svg>';
    }
}
?>

<div class="analysis-page">

    <div class="page-header">
        <div>
            <span class="page-eyebrow"><?= ai_icon('sparkles', 13) ?> AI-Powered</span>
            <h2 class="page-title">AI Skin Analysis</h2>
            <p class="page-subtitle">Unggah atau ambil foto wajahmu, biar AI yang bacakan kondisi kulitmu.</p>
        </div>
    </div>

    <div class="analysis-layout" id="analysisLayout">

        <!-- ============== STATE 1: UPLOAD ============== -->
        <section class="analysis-upload-card" id="uploadState">

            <div class="upload-tabs" role="tablist">
                <button type="button" class="upload-tab is-active" data-mode="upload" role="tab" aria-selected="true">
                    <?= ai_icon('upload', 16) ?> <span>Unggah Foto</span>
                </button>
                <button type="button" class="upload-tab" data-mode="camera" role="tab" aria-selected="false">
                    <?= ai_icon('camera', 16) ?> <span>Gunakan Kamera</span>
                </button>
            </div>

            <div class="dropzone" id="dropzone">
                <input type="file" id="fileInput" accept="image/*" hidden>
                <div class="dropzone-icon"><?= ai_icon('scan-face', 30) ?></div>
                <p class="dropzone-title">Tarik &amp; lepas foto di sini</p>
                <p class="dropzone-desc">atau klik untuk memilih file &mdash; JPG/PNG, maks. 10MB</p>
                <button type="button" class="btn btn-primary btn-sm" id="chooseFileBtn">Pilih Foto</button>
            </div>

            <div class="preview-box is-hidden" id="previewBox">
                <img id="previewImg" alt="Pratinjau foto">
                <button type="button" class="preview-remove" id="removePreviewBtn" aria-label="Hapus foto">
                    <?= ai_icon('x', 14) ?>
                </button>
            </div>

            <button type="button" class="btn btn-primary btn-block analyze-btn" id="analyzeBtn" disabled>
                <?= ai_icon('sparkles', 16) ?> <span>Mulai Analisis</span>
            </button>
        </section>

        <!-- ============== TIPS PANEL ============== -->
        <aside class="analysis-tips-card" id="tipsCard">
            <h3 class="tips-title"><?= ai_icon('lightbulb', 17) ?> Tips hasil terbaik</h3>
            <ul class="tips-list">
                <li><?= ai_icon('check', 14) ?> <span>Gunakan cahaya alami, hindari lampu kuning</span></li>
                <li><?= ai_icon('check', 14) ?> <span>Wajah bersih tanpa makeup</span></li>
                <li><?= ai_icon('check', 14) ?> <span>Hadap kamera lurus, seluruh wajah terlihat</span></li>
                <li><?= ai_icon('check', 14) ?> <span>Lepas kacamata dan aksesoris penutup wajah</span></li>
            </ul>
            <p class="tips-note">Foto hanya dipakai untuk analisis dan tidak dibagikan ke pihak lain.</p>
        </aside>

        <!-- ============== ANALYZING OVERLAY ============== -->
        <div class="analyzing-overlay is-hidden" id="analyzingOverlay">
            <div class="analyzing-spinner"></div>
            <p class="analyzing-title">Menganalisis foto kamu&hellip;</p>
            <p class="analyzing-step" id="analyzingStep">Mendeteksi warna kulit</p>
        </div>

        <!-- ============== STATE 2: HASIL ============== -->
        <section class="analysis-result is-hidden" id="resultState">

            <div class="result-summary-card">
                <img class="result-thumb" id="resultThumb" alt="Foto yang dianalisis">
                <div class="result-summary-body">
                    <span class="result-badge"><?= ai_icon('sparkles', 13) ?> Analisis Selesai</span>
                    <h3 class="result-tone" id="resultToneName">Soft Autumn (Warm)</h3>
                    <p class="result-desc">Skin tone kamu masuk kategori warm undertone dengan tingkat kecerahan medium.</p>
                    <div class="result-actions">
                        <button type="button" class="btn btn-outline btn-sm" id="newAnalysisBtn">
                            <?= ai_icon('rotate-ccw', 15) ?> Analisis Baru
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" id="saveResultBtn">
                            <?= ai_icon('bookmark', 15) ?> Simpan Hasil
                        </button>
                        <a href="products.php" class="btn btn-primary btn-sm">
                            <?= ai_icon('package', 15) ?> Lihat Rekomendasi Produk
                        </a>
                    </div>
                </div>
            </div>

            <div class="result-grid">
                <div class="result-stat-card">
                    <span class="result-stat-label"><?= ai_icon('palette', 15) ?> Skin Tone</span>
                    <span class="result-stat-value">Warm, Medium</span>
                    <div class="tone-swatch-row">
                        <span class="tone-swatch" style="background:#C98A5E"></span>
                        <span class="tone-swatch" style="background:#D9A374"></span>
                        <span class="tone-swatch" style="background:#E4B98C"></span>
                    </div>
                </div>

                <div class="result-stat-card">
                    <span class="result-stat-label"><?= ai_icon('droplet', 15) ?> Skin Type</span>
                    <span class="result-stat-value">Kombinasi</span>
                    <p class="result-stat-note">T-zone berminyak, pipi cenderung normal.</p>
                </div>

                <div class="result-stat-card result-stat-card--wide">
                    <span class="result-stat-label"><?= ai_icon('activity', 15) ?> Skin Concerns</span>
                    <div class="concern-tags">
                        <span class="concern-tag">Pori-pori besar &mdash; 62%</span>
                        <span class="concern-tag">Kemerahan ringan &mdash; 34%</span>
                        <span class="concern-tag">Garis halus &mdash; 18%</span>
                    </div>
                </div>
            </div>
<!-- ============== COLOR ANALYSIS ============== -->
            <div class="result-color-section">

                <div class="color-section-head">
                    <span class="color-season-badge"><?= ai_icon('sparkles', 13) ?> Soft Autumn &middot; Warm Palette</span>
                    <h3>Warna yang Cocok Untukmu</h3>
                    <p>Berdasarkan skin tone <strong>Warm, Medium</strong>, palet berikut paling menonjolkan kecerahan wajahmu.</p>
                </div>

                <div class="color-palette-grid">

                    <div class="color-palette-card">
                        <div class="palette-card-head">
                            <span class="palette-icon-tile"><?= ai_icon('shirt', 16) ?></span>
                            <span class="palette-label">Warna Pakaian</span>
                        </div>
                        <div class="palette-swatch-row">
                            <div class="palette-swatch-item">
                                <span class="palette-swatch" style="background:#C97C4C"></span>
                                <span class="palette-name">Terracotta</span>
                            </div>
                            <div class="palette-swatch-item">
                                <span class="palette-swatch" style="background:#A98B4E"></span>
                                <span class="palette-name">Mustard</span>
                            </div>
                            <div class="palette-swatch-item">
                                <span class="palette-swatch" style="background:#7C8A5A"></span>
                                <span class="palette-name">Olive</span>
                            </div>
                            <div class="palette-swatch-item">
                                <span class="palette-swatch" style="background:#8B5E3C"></span>
                                <span class="palette-name">Camel</span>
                            </div>
                            <div class="palette-swatch-item">
                                <span class="palette-swatch" style="background:#B5451B"></span>
                                <span class="palette-name">Rust</span>
                            </div>
                        </div>
                    </div>

                    <div class="color-palette-card">
                        <div class="palette-card-head">
                            <span class="palette-icon-tile"><?= ai_icon('droplet', 16) ?></span>
                            <span class="palette-label">Warna Makeup</span>
                        </div>
                        <div class="palette-swatch-row">
                            <div class="palette-swatch-item">
                                <span class="palette-swatch" style="background:#C97A5D"></span>
                                <span class="palette-name">Peach Coral</span>
                            </div>
                            <div class="palette-swatch-item">
                                <span class="palette-swatch" style="background:#A85D45"></span>
                                <span class="palette-name">Brick Red</span>
                            </div>
                            <div class="palette-swatch-item">
                                <span class="palette-swatch" style="background:#D9A374"></span>
                                <span class="palette-name">Warm Bronze</span>
                            </div>
                            <div class="palette-swatch-item">
                                <span class="palette-swatch" style="background:#8C4A3A"></span>
                                <span class="palette-name">Deep Brown</span>
                            </div>
                        </div>
                    </div>

                    <div class="color-palette-card color-palette-card--avoid">
                        <div class="palette-card-head">
                            <span class="palette-icon-tile palette-icon-tile--avoid"><?= ai_icon('ban', 16) ?></span>
                            <span class="palette-label">Sebaiknya Dihindari</span>
                        </div>
                        <div class="palette-swatch-row">
                            <div class="palette-swatch-item">
                                <span class="palette-swatch palette-swatch--avoid" style="background:#C6A9D9"></span>
                                <span class="palette-name">Cool Lavender</span>
                            </div>
                            <div class="palette-swatch-item">
                                <span class="palette-swatch palette-swatch--avoid" style="background:#7A8FA6"></span>
                                <span class="palette-name">Icy Blue</span>
                            </div>
                            <div class="palette-swatch-item">
                                <span class="palette-swatch palette-swatch--avoid" style="background:#E8E8E8"></span>
                                <span class="palette-name">Pure White</span>
                            </div>
                            <div class="palette-swatch-item">
                                <span class="palette-swatch palette-swatch--avoid" style="background:#3A3A5C"></span>
                                <span class="palette-name">Cool Navy</span>
                            </div>
                        </div>
                        <p class="palette-avoid-note">Warna dingin cenderung membuat wajahmu terlihat pucat &amp; kurang bercahaya.</p>
                    </div>

                </div>
            </div>
            <!-- ============== QUICK SWATCH CARDS ============== -->
                <div class="swatch-cards-grid">

                    <div class="swatch-card">
                        <h4 class="swatch-card-title">Best Neutrals</h4>
                        <div class="swatch-circle-row">
                            <span class="swatch-circle" style="background:#E8C9A0"></span>
                            <span class="swatch-circle" style="background:#D9A374"></span>
                            <span class="swatch-circle" style="background:#8B5E3C"></span>
                            <span class="swatch-circle" style="background:#4A3427"></span>
                        </div>
                    </div>

                    <div class="swatch-card swatch-card--makeup">
                        <h4 class="swatch-card-title">Best Make Up Colors</h4>
                        <div class="swatch-makeup-groups">
                            <div class="swatch-makeup-group">
                                <span class="swatch-group-label">Blush</span>
                                <div class="swatch-circle-row swatch-circle-row--sm">
                                    <span class="swatch-circle swatch-circle--sm" style="background:#E39C9C"></span>
                                    <span class="swatch-circle swatch-circle--sm" style="background:#D9727C"></span>
                                    <span class="swatch-circle swatch-circle--sm" style="background:#C25368"></span>
                                    <span class="swatch-circle swatch-circle--sm" style="background:#A83B52"></span>
                                </div>
                            </div>
                            <div class="swatch-makeup-group">
                                <span class="swatch-group-label">Lip</span>
                                <div class="swatch-circle-row swatch-circle-row--sm">
                                    <span class="swatch-circle swatch-circle--sm" style="background:#C2607A"></span>
                                    <span class="swatch-circle swatch-circle--sm" style="background:#A8455C"></span>
                                    <span class="swatch-circle swatch-circle--sm" style="background:#8C2E42"></span>
                                    <span class="swatch-circle swatch-circle--sm" style="background:#6E1F30"></span>
                                </div>
                            </div>
                            <div class="swatch-makeup-group">
                                <span class="swatch-group-label">Eyeshadow</span>
                                <div class="swatch-circle-row swatch-circle-row--sm">
                                    <span class="swatch-circle swatch-circle--sm" style="background:#B8895A"></span>
                                    <span class="swatch-circle swatch-circle--sm" style="background:#8C5E3C"></span>
                                    <span class="swatch-circle swatch-circle--sm" style="background:#6E4128"></span>
                                    <span class="swatch-circle swatch-circle--sm" style="background:#4A2A18"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="swatch-card">
                        <h4 class="swatch-card-title">Best Accessory Colors</h4>
                        <div class="swatch-circle-row">
                            <span class="swatch-circle" style="background:#D9B45C"></span>
                            <span class="swatch-circle" style="background:#B5451B"></span>
                            <span class="swatch-circle" style="background:#8B5E3C"></span>
                            <span class="swatch-circle" style="background:#7C8A5A"></span>
                            <span class="swatch-circle" style="background:#2E4A4A"></span>
                        </div>
                    </div>

                    <div class="swatch-card">
                        <h4 class="swatch-card-title">Best Patterns</h4>
                        <div class="swatch-circle-row">
                            <span class="swatch-circle swatch-circle--pattern" style="background-color:#E8C9A0; background-image: radial-gradient(circle, #8B5E3C 15%, transparent 16%); background-size: 8px 8px;"></span>
                            <span class="swatch-circle" style="background:#8B5E3C"></span>
                            <span class="swatch-circle" style="background:#4A3427"></span>
                        </div>
                    </div>

                </div>
        </section>

    </div>
</div>

<script src="assets/js/analysis.js"></script>
 <?php require __DIR__ . '/includes/footer.php'; ?>
    <?php

<?php
/**
 * dashboard.php
 * ------------------------------------------------------------------
 * Main dashboard ("Beranda") page shown after a user logs in.
 * All data comes from includes/data.php (dummy for now, ready to be
 * swapped for real database queries later).
 * ------------------------------------------------------------------
 */

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
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
                    <a href="#" class="btn btn-white"><?= lt_icon('scan-face', '', 17) ?> Start New Analysis</a>
                    <a href="#" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.4); color:#fff;">
                        <?= lt_icon('history', '', 17) ?> View History
                    </a>
                </div>
                <div class="welcome-stats">
                    <?php foreach (array_slice($stats, 0, 3) as $stat): ?>
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
            <div class="welcome-visual"><?= lt_icon('scan-face', '', 56) ?></div>
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
                <a href="#" class="section-link">Lihat Semua <?= lt_icon('chevron-right', '', 15) ?></a>
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
                                    <a href="#" class="btn btn-secondary btn-sm"><?= lt_icon('eye', '', 15) ?> Lihat Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ==================================================
             3. RECOMMENDED PRODUCTS
        =================================================== -->
        <section class="reveal">
            <div class="section-heading">
                <div>
                    <h2>Recommended Products</h2>
                    <p>Dipilih berdasarkan hasil analisis kulitmu.</p>
                </div>
                <a href="#" class="section-link">Lihat Semua <?= lt_icon('chevron-right', '', 15) ?></a>
            </div>

            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-thumb"><?= lt_icon($product['icon'], '', 34) ?></div>
                        <div class="product-body">
                            <span class="badge badge-neutral product-tag"><?= htmlspecialchars($product['tag']) ?></span>
                            <p class="product-name"><?= htmlspecialchars($product['name']) ?></p>
                            <p class="product-desc"><?= htmlspecialchars($product['desc']) ?></p>
                            <a href="#" class="btn btn-secondary btn-sm btn-block">Lihat Detail</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div>

    <?php require __DIR__ . '/includes/footer.php'; ?>

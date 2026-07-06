<?php
/**
 * LumiTone — AI Skin Analysis Landing Page
 * Main entry point. Every section is a separate PHP component,
 * included in order below. No business logic lives here —
 * this file only assembles the page.
 */
$pageTitle = "LumiTone — Kenali Skintone-mu, Rawat dengan Tepat";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="LumiTone menganalisis skintone, undertone, dan kondisi kulitmu dengan AI dari sebuah foto. Cepat, akurat, dan privasimu tetap terjaga.">

    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Favicon -->
    <link rel="icon" href="assets/icons/logo.svg" type="image/svg+xml">
</head>
<body>

    <!-- ===== Navbar ===== -->
    <?php include 'components/navbar.php'; ?>

    <main>
        <!-- ===== Hero Section ===== -->
        <?php include 'components/hero.php'; ?>

        <!-- ===== Trusted By Section ===== -->
        <?php include 'components/trusted.php'; ?>

        <!-- ===== Features Section ===== -->
        <?php include 'components/features.php'; ?>

        <!-- ===== How It Works Section ===== -->
        <?php include 'components/how-it-works.php'; ?>

        <!-- ===== Statistics Section ===== -->
        <?php include 'components/statistics.php'; ?>

        <!-- ===== Testimonials Section ===== -->
        <?php include 'components/testimonial.php'; ?>

        <!-- ===== FAQ Section ===== -->
        <?php include 'components/faq.php'; ?>

        <!-- ===== CTA Section ===== -->
        <?php include 'components/cta.php'; ?>
    </main>

    <!-- ===== Footer ===== -->
    <?php include 'components/footer.php'; ?>

    <!-- ===== Auth Modal (Masuk / Daftar) ===== -->
    <?php include 'components/auth-modal.php'; ?>

    <!-- Scripts -->
    <script src="assets/js/script.js"></script>
</body>
</html>
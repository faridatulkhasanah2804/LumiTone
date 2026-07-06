<?php
/**
 * Dashboard — Home
 * --------------------------------------------------------------
 * Frontend only. No auth/session logic yet — $currentUser below
 * is dummy data standing in for the future logged-in session.
 * -------------------------------------------------------------- */

$activePage  = 'dashboard';
$currentUser = [
    'name' => 'Farida',
    'role' => 'Premium Member',
];

$pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> — LumiTone</title>

    <!-- Global design system (shared with Landing Page) -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Dashboard-specific layout -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

    <div class="app-shell">

        <?php include '../components/sidebar.php'; ?>

        <div class="main-col">

            <?php include '../components/topbar.php'; ?>

            <!-- ==============================================================
                 Main dashboard content
                 Sections below (Welcome Card, Quick Stats, Quick Actions,
                 Recent Analysis table, Recommendations, Skin Care Tips,
                 Activity Timeline, Footer) will be added in the next step.
                 ============================================================== -->
            <main class="dashboard-content fade-in">

                <div class="dashboard-content__placeholder">
                    Dashboard sections (Welcome Card, Quick Stats, Quick Actions,
                    Recent Analysis, Recommendations, Tips, Activity Timeline, Footer)
                    will be built here next.
                </div>

            </main>

        </div>

    </div>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
<?php
/**
 * header.php
 * ------------------------------------------------------------------
 * Opens the HTML document. Every dashboard page must define
 * $pageTitle and $activePage BEFORE including this file, e.g.:
 *
 *   $pageTitle  = 'Dashboard';
 *   $activePage = 'dashboard';
 *   require 'includes/header.php';
 * ------------------------------------------------------------------
 */

if (!isset($pageTitle))  $pageTitle  = 'Dashboard';
if (!isset($activePage)) $activePage = 'dashboard';

require_once __DIR__ . '/data.php';
require_once __DIR__ . '/icons.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — LumiTone</title>

<!-- Fonts: Poppins (UI) + Fraunces (wordmark), matching the landing page -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Fraunces:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">

<!-- Stylesheets (split by concern, no framework) -->
<link rel="stylesheet" href="assets/css/variables.css">
<link rel="stylesheet" href="assets/css/layout.css">
<link rel="stylesheet" href="assets/css/components.css">
<link rel="stylesheet" href="assets/css/dashboard.css">
<?php if (($activePage ?? '') === 'analysis'): ?>
<link rel="stylesheet" href="assets/css/analysis.css">
<?php endif; ?>
</head>
<body>

<!-- Mobile sidebar backdrop (shown via JS when sidebar is open on small screens) -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="app-shell">

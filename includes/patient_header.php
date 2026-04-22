<?php
declare(strict_types=1);
$ap = '../';
$pageTitle = $pageTitle ?? 'Patient';
$GLOBALS['__asset_prefix'] = $ap;
$current = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> â€” Patient | Dhami Hospital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h($ap) ?>assets/css/style.css">
</head>
<body class="theme-dark dashboard-body">
    <div class="bg-grid"></div>
    <div class="bg-glow bg-glow--2"></div>
    <div class="dashboard-layout">
        <aside class="sidebar glass" id="sidebar">
            <div class="sidebar-brand">
                <span class="logo-icon">â—‡</span>
                <span>Patient<span class="logo-accent">Portal</span></span>
            </div>
            <nav class="sidebar-nav">
                <a class="<?= $current === 'dash' ? 'active' : '' ?>" href="index.php">Overview</a>
                <a class="<?= $current === 'profile' ? 'active' : '' ?>" href="profile.php">My Profile</a>
                <a class="<?= $current === 'appointments' ? 'active' : '' ?>" href="appointments.php">Appointments</a>
                <a class="<?= $current === 'reports' ? 'active' : '' ?>" href="reports.php">Medical Reports</a>
                <a class="<?= $current === 'bills' ? 'active' : '' ?>" href="bills.php">Bills &amp; Invoices</a>
                <a class="<?= $current === 'complaints' ? 'active' : '' ?>" href="complaints.php">Complaints</a>
            </nav>
            <div class="sidebar-footer">
                <a class="btn btn--ghost btn--block" href="../index.php">Home</a>
                <a class="btn btn--danger btn--block" href="../logout.php">Logout</a>
            </div>
        </aside>
        <div class="dash-main">
            <header class="dash-topbar glass">
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">â˜°</button>
                <h1 class="dash-title"><?= h($pageTitle) ?></h1>
                <div class="dash-user"><?= h($_SESSION['patient_name'] ?? 'Patient') ?></div>
            </header>
            <div class="dash-content container--wide">

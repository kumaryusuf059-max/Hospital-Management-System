<?php
declare(strict_types=1);
$ap = '../';
$pageTitle = $pageTitle ?? 'Admin';
$GLOBALS['__asset_prefix'] = $ap;
$current = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> â€” Admin | Dhami Hospital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h($ap) ?>assets/css/style.css">
</head>
<body class="theme-dark dashboard-body">
    <div class="bg-grid"></div>
    <div class="bg-glow bg-glow--1"></div>
    <div class="dashboard-layout">
        <aside class="sidebar glass" id="sidebar">
            <div class="sidebar-brand">
                <span class="logo-icon">â—ˆ</span>
                <span>Dhami<span class="logo-accent">Admin</span></span>
            </div>
            <nav class="sidebar-nav">
                <a class="<?= $current === 'dash' ? 'active' : '' ?>" href="index.php">Dashboard</a>
                <a class="<?= $current === 'analytics' ? 'active' : '' ?>" href="analytics.php">Analytics</a>
                <a class="<?= $current === 'profile' ? 'active' : '' ?>" href="profile.php">My Profile</a>
                <a class="<?= $current === 'admins' ? 'active' : '' ?>" href="admins.php">Add Admin</a>
                <a class="<?= $current === 'patients' ? 'active' : '' ?>" href="patients.php">Patients</a>
                <a class="<?= $current === 'appointments' ? 'active' : '' ?>" href="appointments.php">Appointments</a>
                <a class="<?= $current === 'bills' ? 'active' : '' ?>" href="bills.php">Bills &amp; Payments</a>
                <a class="<?= $current === 'reports' ? 'active' : '' ?>" href="reports.php">Medical Reports</a>
                <a class="<?= $current === 'tests' ? 'active' : '' ?>" href="tests.php">Medical Tests</a>
                <a class="<?= $current === 'complaints' ? 'active' : '' ?>" href="complaints.php">Complaints</a>
                <a class="<?= $current === 'contact' ? 'active' : '' ?>" href="contact_messages.php">Contact Inbox</a>
            </nav>
            <div class="sidebar-footer">
                <a class="btn btn--ghost btn--block" href="../index.php">Public Site</a>
                <a class="btn btn--danger btn--block" href="../logout.php">Logout</a>
            </div>
        </aside>
        <div class="dash-main">
            <header class="dash-topbar glass">
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">â˜°</button>
                <h1 class="dash-title"><?= h($pageTitle) ?></h1>
                <div class="dash-user"><?= h($_SESSION['admin_name'] ?? 'Admin') ?></div>
            </header>
            <div class="dash-content container--wide">

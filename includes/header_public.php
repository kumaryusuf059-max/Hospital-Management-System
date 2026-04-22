<?php
declare(strict_types=1);
$ap = $GLOBALS['__asset_prefix'] ?? '';
$pageTitle = $pageTitle ?? 'Dhami Hospital';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> â€” Dhami Hospital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h($ap) ?>assets/css/style.css">
</head>
<body class="theme-dark">
    <div class="bg-grid"></div>
    <div class="bg-glow bg-glow--1"></div>
    <div class="bg-glow bg-glow--2"></div>

    <header class="site-header glass">
        <div class="container header-inner">
            <a class="logo" href="<?= h($ap) ?>index.php">
                <span class="logo-icon">â—ˆ</span>
                <span>Dhami<span class="logo-accent">Hospital</span></span>
            </a>
            <nav class="nav-main" id="navMain">
                <a href="<?= h($ap) ?>index.php#about">About</a>
                <a href="<?= h($ap) ?>index.php#services">Services</a>
                <a href="<?= h($ap) ?>index.php#contact">Contact</a>
                <a href="<?= h($ap) ?>login.php?role=patient" class="btn btn--ghost btn--sm">Patient Login</a>
                <a href="<?= h($ap) ?>login.php?role=admin" class="btn btn--primary btn--sm">Admin</a>
            </nav>
            <button type="button" class="nav-toggle" aria-label="Menu" id="navToggle">â˜°</button>
        </div>
    </header>
    <main class="main-content">

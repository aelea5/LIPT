<?php
require_once __DIR__ . '/site.php';

$page_title = $page_title ?? SITE_NAME;
$body_class = $body_class ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta name="description" content="<?= htmlspecialchars(SITE_TAGLINE, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/style.css') . '?v=' . ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="<?= htmlspecialchars($body_class, ENT_QUOTES, 'UTF-8') ?>">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <header class="site-header">
        <div class="container site-header__inner">
            <a class="site-logo" href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8') ?>">
                <img
                    src="<?= htmlspecialchars(image_url(LITP_IMAGE_LOGO), ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?> official logo"
                    class="site-logo__img"
                    height="75"
                    width="auto"
                    decoding="async"
                >
                <span class="site-logo__tagline"><?= htmlspecialchars(SITE_TAGLINE, ENT_QUOTES, 'UTF-8') ?></span>
            </a>

            <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="site-nav" data-nav-toggle aria-label="Open menu">
                <span class="nav-toggle__bars" aria-hidden="true">
                    <span class="nav-toggle__bar"></span>
                    <span class="nav-toggle__bar"></span>
                    <span class="nav-toggle__bar"></span>
                </span>
            </button>

            <nav id="site-nav" class="site-nav" aria-label="Main navigation">
                <ul class="site-nav__list">
                    <li><a href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8') ?>"<?= is_current_page('index.php') ? ' aria-current="page"' : '' ?>>Home</a></li>
                    <li><a href="<?= htmlspecialchars(site_url('roster.php'), ENT_QUOTES, 'UTF-8') ?>"<?= is_current_page('roster.php') ? ' aria-current="page"' : '' ?>>Schedule</a></li>
                    <li><a href="<?= htmlspecialchars(site_url('nonprofit.php'), ENT_QUOTES, 'UTF-8') ?>"<?= is_current_page('nonprofit.php') ? ' aria-current="page"' : '' ?>>Nonprofit Spotlight</a></li>
                    <li><a href="<?= htmlspecialchars(site_url('about.php'), ENT_QUOTES, 'UTF-8') ?>"<?= is_current_page('about.php') ? ' aria-current="page"' : '' ?>>About</a></li>
                    <li><a href="<?= htmlspecialchars(site_url('faq.php'), ENT_QUOTES, 'UTF-8') ?>"<?= is_current_page('faq.php') ? ' aria-current="page"' : '' ?>>FAQ</a></li>
                    <li><a href="<?= htmlspecialchars(site_url('menu-ideas.php'), ENT_QUOTES, 'UTF-8') ?>"<?= is_current_page('menu-ideas.php') ? ' aria-current="page"' : '' ?>>Menu Ideas</a></li>
                    <li><a href="<?= htmlspecialchars(site_url('suggestions.php'), ENT_QUOTES, 'UTF-8') ?>"<?= is_current_page('suggestions.php') ? ' aria-current="page"' : '' ?>>Suggestions</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main id="main-content" class="site-main">

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'SINDAK') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<header class="site-header">
    <a href="<?= site_url('dashboard') ?>" class="site-header__logo">
        <img src="<?= base_url('assets/images/logo-sindak.png') ?>" alt="SINDAK — Ditjen Bimas Kristen">
    </a>

    <nav class="site-header__nav">
        <a href="<?= site_url('dashboard') ?>" class="site-header__link">Beranda</a>
        <a href="<?= site_url('kontak') ?>" class="site-header__link">Kontak</a>

        <div class="user-menu" id="userMenu">
            <button type="button" class="user-menu__button" id="userMenuButton">
                <?= esc(session()->get('full_name') ?? 'Akun') ?>
                <span class="user-menu__caret"></span>
            </button>
            <div class="user-menu__panel">
                <a href="<?= site_url('akun') ?>">Akun</a>
                <form action="<?= site_url('logout') ?>" method="get">
                    <button type="submit" class="link-like">Logout</button>
                </form>
            </div>
        </div>
    </nav>
</header>

<div class="site-navbar" id="siteNavbar">
    <button type="button" class="hamburger" id="hamburgerButton" aria-label="Buka menu" aria-expanded="false">
        <span class="hamburger__bar"></span>
        <span class="hamburger__bar"></span>
        <span class="hamburger__bar"></span>
    </button>
</div>

<div class="navbar-panel" id="navbarPanel">
    <div class="navbar-panel__inner">
        <a class="navbar-panel__item" href="<?= site_url('admin/pegawai?kategori=pusat') ?>">Pegawai Pusat</a>
        <a class="navbar-panel__item" href="<?= site_url('admin/pegawai?kategori=daerah') ?>">Pegawai Daerah</a>
    </div>
</div>

<main class="page-content">

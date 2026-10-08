<?php
// header.php : en-tête commun (menu de navigation). Variables optionnelles : $titre, $base
require_once __DIR__ . '/functions.php';
$base  = $base  ?? '';              // '' pour les pages à la racine, '../' pour admin/ et livreur/
$titre = $titre ?? 'ReWear';
$u = user();
?>
<!DOCTYPE html>
<html lang="<?= langue() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titre) ?> · ReWear</title>
  <link rel="stylesheet" href="<?= $base ?>assets/style.css">
</head>
<body>
<header class="site-header">
  <div class="container nav">
    <a class="logo" href="<?= $base ?>index.php">♻ ReWear</a>
    <nav>
      <a href="<?= $base ?>catalogue.php"><?= t('menu_shop') ?></a>
      <a href="<?= $base ?>don.php"><?= t('menu_donate') ?></a>
      <?php if ($u && $u['role'] === 'admin'): ?>
        <a href="<?= $base ?>admin/index.php"><?= t('menu_admin') ?></a>
      <?php elseif ($u && $u['role'] === 'livreur'): ?>
        <a href="<?= $base ?>livreur/index.php"><?= t('menu_missions') ?></a>
      <?php endif; ?>
    </nav>
    <div class="nav-right">
      <span class="langues">
        <a href="<?= e(url_langue('fr')) ?>" <?= langue() === 'fr' ? 'style="font-weight:800"' : '' ?>>FR</a> |
        <a href="<?= e(url_langue('en')) ?>" <?= langue() === 'en' ? 'style="font-weight:800"' : '' ?>>EN</a>
      </span>
      <?php if ($u && $u['role'] === 'utilisateur'): ?>
        <a href="<?= $base ?>panier.php">🛒 <?= t('cart') ?> (<?= panier_compte() ?>)</a>
      <?php endif; ?>
      <?php if ($u): ?>
        <?php if ($u['role'] === 'utilisateur'): ?>
          <a href="<?= $base ?>mon-compte.php">👤 <?= e($u['nom']) ?></a>
        <?php else: ?>
          <span>👤 <?= e($u['nom']) ?></span>
        <?php endif; ?>
        <a class="btn btn-small btn-outline" href="<?= $base ?>logout.php"><?= t('logout') ?></a>
      <?php else: ?>
        <a href="<?= $base ?>login.php"><?= t('login') ?></a>
        <a class="btn btn-small" href="<?= $base ?>register.php"><?= t('register') ?></a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main class="container">
<?php if (!empty($_SESSION['flash'])): ?>
  <div class="alert alert-<?= e($_SESSION['flash']['type']) ?>"><?= e($_SESSION['flash']['msg']) ?></div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php
// index.php : page d'accueil avec la mission du site et quelques produits récents
require_once __DIR__ . '/includes/functions.php';
$titre = t('home');
require __DIR__ . '/includes/header.php';

$recents = $pdo->query("SELECT * FROM produits WHERE actif = 1 AND stock > 0 ORDER BY created_at DESC LIMIT 4")->fetchAll();
?>
<section class="hero">
  <h1><?= t('hero_title') ?></h1>
  <p><?= t('hero_text') ?></p>
  <div class="actions">
    <a class="btn" href="don.php"><?= t('btn_donate') ?></a>
    <a class="btn btn-outline" href="catalogue.php"><?= t('btn_shop') ?></a>
  </div>
</section>

<section class="impact">
  <div class="box"><div class="icone">🌍</div><h3><?= t('impact_env_t') ?></h3><p><?= t('impact_env_p') ?></p></div>
  <div class="box"><div class="icone">🤝</div><h3><?= t('impact_soc_t') ?></h3><p><?= t('impact_soc_p') ?></p></div>
  <div class="box"><div class="icone">🚚</div><h3><?= t('impact_del_t') ?></h3><p><?= t('impact_del_p') ?></p></div>
</section>

<h2><?= t('news') ?></h2>
<?php if ($recents): ?>
  <div class="grille">
    <?php foreach ($recents as $p): ?>
      <a class="carte" href="produit.php?id=<?= (int)$p['id'] ?>" style="color:inherit;text-decoration:none">
        <div class="photo"><?php if ($p['image']): ?><img src="uploads/<?= e($p['image']) ?>" alt="<?= e($p['nom']) ?>"><?php else: ?>👕<?php endif; ?></div>
        <div class="corps">
          <h3><?= e($p['nom']) ?></h3>
          <span class="muted"><?= e(t('cat_' . $p['categorie'])) ?> · <?= t('size') ?> <?= e($p['taille']) ?></span>
          <span class="prix"><?= prix($p['prix']) ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <p class="muted"><?= t('no_products') ?></p>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>

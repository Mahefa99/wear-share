<?php
// catalogue.php : boutique avec recherche et filtre par catégorie
require_once __DIR__ . '/includes/functions.php';
$titre = t('menu_shop');
require __DIR__ . '/includes/header.php';

$q   = trim($_GET['q'] ?? '');
$cat = $_GET['cat'] ?? '';
$categories = ['Homme', 'Femme', 'Enfant', 'Chaussures', 'Accessoires', 'Livres'];   // valeurs stockées en base

$sql = 'SELECT * FROM produits WHERE actif = 1 AND stock > 0';
$params = [];
if ($q !== '')                         { $sql .= ' AND (nom LIKE ? OR description LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
if (in_array($cat, $categories, true)) { $sql .= ' AND categorie = ?'; $params[] = $cat; }
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll();
?>
<h1><?= t('shop_title') ?></h1>
<p class="muted"><?= t('shop_sub') ?></p>

<form class="filtres" method="get">
  <input type="text" name="q" placeholder="<?= e(t('search')) ?>" value="<?= e($q) ?>">
  <select name="cat">
    <option value=""><?= t('all_cats') ?></option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= e($c) ?>" <?= $c === $cat ? 'selected' : '' ?>><?= e(t('cat_' . $c)) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-small" type="submit"><?= t('filter') ?></button>
</form>

<?php if (!$produits): ?>
  <p class="muted"><?= t('none_found') ?></p>
<?php else: ?>
<div class="grille">
  <?php foreach ($produits as $p): ?>
    <a class="carte" href="produit.php?id=<?= (int)$p['id'] ?>" style="color:inherit;text-decoration:none">
      <div class="photo"><?php if ($p['image']): ?><img src="uploads/<?= e($p['image']) ?>" alt="<?= e($p['nom']) ?>"><?php else: ?>👕<?php endif; ?></div>
      <div class="corps">
        <h3><?= e($p['nom']) ?></h3>
        <span><span class="badge"><?= e(t('cat_' . $p['categorie'])) ?></span> <span class="badge badge-gris"><?= e(t('etat_' . $p['etat'])) ?></span></span>
        <span class="muted"><?= t('size') ?> <?= e($p['taille']) ?></span>
        <span class="prix"><?= prix($p['prix']) ?></span>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>

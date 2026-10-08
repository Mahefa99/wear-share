<?php
// produit.php : fiche détaillée d'un produit + ajout au panier
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM produits WHERE id = ? AND actif = 1');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); die('404'); }

// Ajout au panier (connexion obligatoire)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    exiger_connexion();
    if (user()['role'] !== 'utilisateur') {
        flash('error', t('only_users_buy'));
        redirect('produit.php?id=' . $id);
    }
    $qte = max(1, (int)($_POST['quantite'] ?? 1));
    $deja = $_SESSION['panier'][$id] ?? 0;
    $_SESSION['panier'][$id] = min($p['stock'], $deja + $qte);   // jamais plus que le stock
    flash('success', t('added_cart'));
    redirect('panier.php');
}

$titre = $p['nom'];
require __DIR__ . '/includes/header.php';
?>
<p><a href="catalogue.php"><?= t('back_shop') ?></a></p>
<div class="detail">
  <div class="photo-grande"><?php if ($p['image']): ?><img src="uploads/<?= e($p['image']) ?>" alt="<?= e($p['nom']) ?>"><?php else: ?>👕<?php endif; ?></div>
  <div>
    <h1><?= e($p['nom']) ?></h1>
    <p><span class="badge"><?= e(t('cat_' . $p['categorie'])) ?></span> <span class="badge badge-gris"><?= e(t('etat_' . $p['etat'])) ?></span></p>
    <p class="prix" style="font-size:1.6rem"><?= prix($p['prix']) ?></p>
    <p><?= nl2br(e($p['description'])) ?></p>
    <p class="muted"><?= t('size') ?> : <?= e($p['taille'] ?: '—') ?> · <?= t('in_stock', (int)$p['stock']) ?></p>
    <?php if ($p['stock'] > 0): ?>
      <form method="post">
        <?= csrf_field() ?>
        <label><?= t('qty') ?> <input type="number" name="quantite" value="1" min="1" max="<?= (int)$p['stock'] ?>" style="width:80px;padding:8px"></label>
        <button class="btn" type="submit"><?= t('add_cart') ?></button>
      </form>
      <?php if (!est_connecte()): ?><p class="muted"><?= t('need_account') ?></p><?php endif; ?>
    <?php else: ?>
      <p class="alert alert-error"><?= t('out_of_stock') ?></p>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
// panier.php : voir, modifier et vider le panier
require_once __DIR__ . '/includes/functions.php';
exiger_connexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $action = $_POST['action'] ?? '';
    $pid = (int)($_POST['id'] ?? 0);
    if ($action === 'maj') {
        $qte = (int)($_POST['quantite'] ?? 0);
        if ($qte <= 0) unset($_SESSION['panier'][$pid]);
        else $_SESSION['panier'][$pid] = $qte;
    } elseif ($action === 'retirer') {
        unset($_SESSION['panier'][$pid]);
    } elseif ($action === 'vider') {
        $_SESSION['panier'] = [];
    }
    redirect('panier.php');
}

// Charger les produits du panier depuis la base
$lignes = [];
$total = 0;
if (panier()) {
    $ids = array_keys(panier());
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id IN ($in) AND actif = 1");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $p) {
        $qte = min((int)panier()[$p['id']], (int)$p['stock']);
        if ($qte < 1) continue;
        $sous = $qte * $p['prix'];
        $total += $sous;
        $lignes[] = ['p' => $p, 'qte' => $qte, 'sous' => $sous];
    }
}

$titre = t('cart_title');
require __DIR__ . '/includes/header.php';
?>
<h1><?= t('cart_title') ?></h1>
<?php if (!$lignes): ?>
  <p><?= t('cart_empty') ?> <a href="catalogue.php"><?= t('discover_shop') ?></a></p>
<?php else: ?>
<div class="table-wrap"><table>
  <tr><th><?= t('th_product') ?></th><th><?= t('th_price') ?></th><th><?= t('th_qty') ?></th><th><?= t('th_subtotal') ?></th><th></th></tr>
  <?php foreach ($lignes as $l): ?>
  <tr>
    <td><a href="produit.php?id=<?= (int)$l['p']['id'] ?>"><?= e($l['p']['nom']) ?></a></td>
    <td><?= prix($l['p']['prix']) ?></td>
    <td>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="action" value="maj"><input type="hidden" name="id" value="<?= (int)$l['p']['id'] ?>">
        <input type="number" name="quantite" value="<?= (int)$l['qte'] ?>" min="0" max="<?= (int)$l['p']['stock'] ?>" style="width:70px;padding:6px">
        <button class="btn btn-small btn-outline" type="submit"><?= t('ok') ?></button>
      </form>
    </td>
    <td><?= prix($l['sous']) ?></td>
    <td>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="retirer"><input type="hidden" name="id" value="<?= (int)$l['p']['id'] ?>">
        <button class="btn btn-small btn-danger" type="submit"><?= t('remove') ?></button></form>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<h2><?= t('total') ?> : <?= prix($total) ?></h2>
<div style="display:flex;gap:10px;flex-wrap:wrap">
  <a class="btn" href="commande.php"><?= t('checkout_btn') ?></a>
  <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="vider"><button class="btn btn-outline" type="submit"><?= t('clear_cart') ?></button></form>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>

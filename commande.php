<?php
// commande.php : validation de la commande (transaction SQL pour garder le stock cohérent)
require_once __DIR__ . '/includes/functions.php';
exiger_connexion();
if (user()['role'] !== 'utilisateur') redirect('index.php');
if (!panier()) { flash('error', t('cart_empty')); redirect('catalogue.php'); }

$stmtU = $pdo->prepare('SELECT adresse FROM users WHERE id = ?');
$stmtU->execute([user()['id']]);
$adresseDefaut = $stmtU->fetchColumn() ?: '';

// Recalculer le panier depuis la base (on ne fait jamais confiance au navigateur pour les prix)
$ids = array_keys(panier());
$in  = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id IN ($in) AND actif = 1");
$stmt->execute($ids);
$produits = $stmt->fetchAll();

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $adresse = trim($_POST['adresse'] ?? '');
    // 'livraison' = paiement en liquide à la livraison, 'carte' = paiement en ligne (simulé)
    $mode = in_array($_POST['mode_paiement'] ?? '', ['livraison', 'carte'], true) ? $_POST['mode_paiement'] : 'livraison';
    if ($adresse === '') {
        $erreur = t('err_address');
    } else {
        try {
            $pdo->beginTransaction();
            $total = 0; $lignes = [];
            foreach ($produits as $p) {
                // Verrouiller la ligne pour éviter que deux personnes achètent la dernière pièce
                $s = $pdo->prepare('SELECT stock, prix FROM produits WHERE id = ? FOR UPDATE');
                $s->execute([$p['id']]);
                $frais = $s->fetch();
                $qte = (int)panier()[$p['id']];
                if ($qte > $frais['stock']) throw new Exception(t('err_stock', $p['nom']));
                $total += $qte * $frais['prix'];
                $lignes[] = [$p['id'], $qte, $frais['prix']];
            }
            $pdo->prepare('INSERT INTO commandes (user_id, total, adresse_livraison, mode_paiement) VALUES (?,?,?,?)')
                ->execute([user()['id'], $total, $adresse, $mode]);
            $cid = $pdo->lastInsertId();
            $insL = $pdo->prepare('INSERT INTO commande_lignes (commande_id, produit_id, quantite, prix_unitaire) VALUES (?,?,?,?)');
            $maj  = $pdo->prepare('UPDATE produits SET stock = stock - ? WHERE id = ?');
            foreach ($lignes as [$pid, $qte, $pu]) {
                $insL->execute([$cid, $pid, $qte, $pu]);
                $maj->execute([$qte, $pid]);
            }
            $pdo->commit();
            $_SESSION['panier'] = [];
            flash('success', t('order_ok', (int)$cid));
            redirect('mon-compte.php');
        } catch (Exception $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erreur = $ex->getMessage();
        }
    }
}

$total = 0;
foreach ($produits as $p) $total += (int)panier()[$p['id']] * $p['prix'];

$titre = t('order_title');
require __DIR__ . '/includes/header.php';
?>
<h1><?= t('order_title') ?></h1>
<?php if ($erreur): ?><div class="alert alert-error"><?= e($erreur) ?></div><?php endif; ?>
<p><?= t('total_to_pay') ?> : <strong><?= prix($total) ?></strong></p>
<form class="form" method="post">
  <?= csrf_field() ?>
  <label><?= t('delivery_address') ?></label>
  <input type="text" name="adresse" value="<?= e($_POST['adresse'] ?? $adresseDefaut) ?>" required>
  <label><?= t('pay_method') ?></label>
  <select name="mode_paiement">
    <option value="livraison" <?= ($_POST['mode_paiement'] ?? '') === 'livraison' ? 'selected' : '' ?>><?= t('pay_cash') ?></option>
    <option value="carte" <?= ($_POST['mode_paiement'] ?? '') === 'carte' ? 'selected' : '' ?>><?= t('pay_online') ?></option>
  </select>
  <p class="muted"><?= t('pay_online_note') ?></p>
  <button class="btn" type="submit"><?= t('confirm_order') ?></button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>

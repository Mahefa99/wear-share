<?php
// admin/commandes.php : suivi des commandes, changement de statut, assignation d'un livreur
require_once __DIR__ . '/../includes/functions.php';
exiger_role('admin');

$statuts = ['en_attente', 'confirmee', 'en_livraison', 'livree', 'annulee'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $id = (int)($_POST['id'] ?? 0);
    $statut = $_POST['statut'] ?? '';
    $livreur = (int)($_POST['livreur_id'] ?? 0) ?: null;
    if (!in_array($statut, $statuts, true)) redirect('commandes.php');

    // Si on annule une commande non annulée, on remet les pièces en stock
    $s = $pdo->prepare('SELECT statut FROM commandes WHERE id = ?');
    $s->execute([$id]);
    $ancien = $s->fetchColumn();
    if ($ancien === false) redirect('commandes.php');

    $pdo->beginTransaction();
    if ($statut === 'annulee' && $ancien !== 'annulee') {
        $pdo->prepare('UPDATE produits p JOIN commande_lignes l ON l.produit_id = p.id SET p.stock = p.stock + l.quantite WHERE l.commande_id = ?')->execute([$id]);
    } elseif ($ancien === 'annulee' && $statut !== 'annulee') {
        flash('error', 'Une commande annulée ne peut pas être réactivée.');
        $pdo->rollBack();
        redirect('commandes.php');
    }
    $pdo->prepare('UPDATE commandes SET statut = ?, livreur_id = ? WHERE id = ?')->execute([$statut, $livreur, $id]);
    $pdo->commit();
    flash('success', 'Commande mise à jour.');
    redirect('commandes.php');
}

$livreurs = $pdo->query("SELECT id, nom FROM users WHERE role = 'livreur' AND actif = 1 ORDER BY nom")->fetchAll();
$commandes = $pdo->query('SELECT c.*, u.nom AS client, u.telephone FROM commandes c JOIN users u ON u.id = c.user_id ORDER BY c.created_at DESC')->fetchAll();
$detail = $pdo->prepare('SELECT p.nom, l.quantite FROM commande_lignes l JOIN produits p ON p.id = l.produit_id WHERE l.commande_id = ?');

$base = '../'; $titre = 'Commandes'; $page = 'commandes';
require __DIR__ . '/../includes/header.php';
?>
<h1>Commandes</h1>
<?php require __DIR__ . '/_menu.php'; ?>
<?php if (!$commandes): ?><p class="muted">Aucune commande.</p><?php else: ?>
<div class="table-wrap"><table>
  <tr><th>N°</th><th>Client</th><th>Articles</th><th>Total</th><th>Livraison</th><th>Statut / Livreur</th></tr>
  <?php foreach ($commandes as $c): $detail->execute([$c['id']]); ?>
  <tr>
    <td>#<?= (int)$c['id'] ?><br><span class="muted"><?= e(date('d/m/Y', strtotime($c['created_at']))) ?></span></td>
    <td><?= e($c['client']) ?><br><span class="muted"><?= e($c['telephone']) ?></span></td>
    <td><?php foreach ($detail->fetchAll() as $l): ?><?= e($l['nom']) ?> × <?= (int)$l['quantite'] ?><br><?php endforeach; ?></td>
    <td><?= prix($c['total']) ?><br><span class="muted"><?= libelle_paiement($c['mode_paiement']) ?></span></td>
    <td><?= e($c['adresse_livraison']) ?></td>
    <td>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <select name="statut"><?php foreach ($statuts as $s): ?><option value="<?= $s ?>" <?= $c['statut'] === $s ? 'selected' : '' ?>><?= e(libelle_statut($s)) ?></option><?php endforeach; ?></select>
        <select name="livreur_id"><option value="">— Livreur —</option>
          <?php foreach ($livreurs as $l): ?><option value="<?= (int)$l['id'] ?>" <?= (int)$c['livreur_id'] === (int)$l['id'] ? 'selected' : '' ?>><?= e($l['nom']) ?></option><?php endforeach; ?></select>
        <button class="btn btn-small" type="submit">OK</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>

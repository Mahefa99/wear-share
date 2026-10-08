<?php
// mon-compte.php : historique des commandes et des dons de l'utilisateur connecté
require_once __DIR__ . '/includes/functions.php';
exiger_connexion();
$uid = user()['id'];

$commandes = $pdo->prepare('SELECT * FROM commandes WHERE user_id = ? ORDER BY created_at DESC');
$commandes->execute([$uid]);
$commandes = $commandes->fetchAll();

$dons = $pdo->prepare('SELECT * FROM dons WHERE user_id = ? ORDER BY created_at DESC');
$dons->execute([$uid]);
$dons = $dons->fetchAll();

$titre = t('account_title');
require __DIR__ . '/includes/header.php';
?>
<h1><?= t('account_title') ?></h1>

<h2><?= t('my_orders') ?></h2>
<?php if (!$commandes): ?><p class="muted"><?= t('no_orders') ?></p><?php else: ?>
<div class="table-wrap"><table>
  <tr><th><?= t('th_no') ?></th><th><?= t('th_date') ?></th><th><?= t('th_total') ?></th><th><?= t('th_payment') ?></th><th><?= t('th_status') ?></th></tr>
  <?php foreach ($commandes as $c): ?>
  <tr><td>#<?= (int)$c['id'] ?></td><td><?= e(date('d/m/Y', strtotime($c['created_at']))) ?></td><td><?= prix($c['total']) ?></td>
      <td><?= e(libelle_paiement($c['mode_paiement'])) ?></td><td><span class="badge"><?= e(libelle_statut($c['statut'])) ?></span></td></tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>

<h2><?= t('my_donations') ?></h2>
<?php if (!$dons): ?><p class="muted"><?= t('no_donations') ?> <a href="don.php"><?= t('btn_donate') ?></a></p><?php else: ?>
<div class="table-wrap"><table>
  <tr><th><?= t('th_date') ?></th><th><?= t('th_desc') ?></th><th><?= t('th_qty_short') ?></th><th><?= t('th_status') ?></th></tr>
  <?php foreach ($dons as $d): ?>
  <tr><td><?= e(date('d/m/Y', strtotime($d['created_at']))) ?></td><td><?= e($d['description']) ?></td><td><?= (int)$d['quantite'] ?></td>
      <td><span class="badge"><?= e(libelle_statut($d['statut'])) ?></span></td></tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>

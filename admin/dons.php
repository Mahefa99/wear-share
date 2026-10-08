<?php
// admin/dons.php : liste des dons à collecter, assignation d'un livreur, changement de statut
require_once __DIR__ . '/../includes/functions.php';
exiger_role('admin');

$statuts = ['en_attente', 'assigne', 'collecte', 'recu', 'refuse'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $id = (int)($_POST['id'] ?? 0);
    $statut = $_POST['statut'] ?? '';
    $livreur = (int)($_POST['livreur_id'] ?? 0) ?: null;
    if (in_array($statut, $statuts, true)) {
        // Si un livreur est choisi alors que le don est "en attente", on passe automatiquement à "assigné"
        if ($livreur && $statut === 'en_attente') $statut = 'assigne';
        $pdo->prepare('UPDATE dons SET statut = ?, livreur_id = ? WHERE id = ?')->execute([$statut, $livreur, $id]);
        flash('success', 'Don mis à jour.');
    }
    redirect('dons.php');
}

$livreurs = $pdo->query("SELECT id, nom FROM users WHERE role = 'livreur' AND actif = 1 ORDER BY nom")->fetchAll();
$dons = $pdo->query('SELECT d.*, u.nom AS donateur, u.telephone FROM dons d JOIN users u ON u.id = d.user_id ORDER BY d.statut = \'recu\', d.created_at DESC')->fetchAll();

$base = '../'; $titre = 'Dons'; $page = 'dons';
require __DIR__ . '/../includes/header.php';
?>
<h1>Dons</h1>
<?php require __DIR__ . '/_menu.php'; ?>
<?php if (!$dons): ?><p class="muted">Aucun don.</p><?php else: ?>
<div class="table-wrap"><table>
  <tr><th>Date</th><th>Donateur</th><th>Don</th><th>Collecte</th><th>Statut / Livreur</th></tr>
  <?php foreach ($dons as $d): ?>
  <tr>
    <td><?= e(date('d/m/Y', strtotime($d['created_at']))) ?></td>
    <td><?= e($d['donateur']) ?><br><span class="muted"><?= e($d['telephone']) ?></span></td>
    <td><?= e($d['description']) ?><br><span class="muted">Qté : <?= (int)$d['quantite'] ?></span></td>
    <td><?= e($d['adresse_collecte']) ?><?php if ($d['date_souhaitee']): ?><br><span class="muted">Souhaité : <?= e(date('d/m/Y', strtotime($d['date_souhaitee']))) ?></span><?php endif; ?></td>
    <td>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
        <select name="statut"><?php foreach ($statuts as $s): ?><option value="<?= $s ?>" <?= $d['statut'] === $s ? 'selected' : '' ?>><?= e(libelle_statut($s)) ?></option><?php endforeach; ?></select>
        <select name="livreur_id"><option value="">— Livreur —</option>
          <?php foreach ($livreurs as $l): ?><option value="<?= (int)$l['id'] ?>" <?= (int)$d['livreur_id'] === (int)$l['id'] ? 'selected' : '' ?>><?= e($l['nom']) ?></option><?php endforeach; ?></select>
        <button class="btn btn-small" type="submit">OK</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>

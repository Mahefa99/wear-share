<?php
// livreur/index.php : missions du livreur (collectes de dons + livraisons de commandes)
require_once __DIR__ . '/../includes/functions.php';
exiger_role('livreur');
$moi = user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $id = (int)($_POST['id'] ?? 0);
    $type = $_POST['type'] ?? '';

    // Le livreur ne peut modifier que SES missions, et seulement vers l'étape suivante
    if ($type === 'don' && in_array($_POST['statut'] ?? '', ['collecte', 'recu'], true)) {
        $pdo->prepare('UPDATE dons SET statut = ? WHERE id = ? AND livreur_id = ?')->execute([$_POST['statut'], $id, $moi]);
        flash('success', 'Don mis à jour.');
    } elseif ($type === 'commande' && in_array($_POST['statut'] ?? '', ['en_livraison', 'livree'], true)) {
        $pdo->prepare('UPDATE commandes SET statut = ? WHERE id = ? AND livreur_id = ?')->execute([$_POST['statut'], $id, $moi]);
        flash('success', 'Commande mise à jour.');
    }
    redirect('index.php');
}

$collectes = $pdo->prepare("SELECT d.*, u.nom AS donateur, u.telephone FROM dons d JOIN users u ON u.id = d.user_id
                            WHERE d.livreur_id = ? AND d.statut IN ('assigne','collecte') ORDER BY d.date_souhaitee IS NULL, d.date_souhaitee");
$collectes->execute([$moi]);
$collectes = $collectes->fetchAll();

$livraisons = $pdo->prepare("SELECT c.*, u.nom AS client, u.telephone FROM commandes c JOIN users u ON u.id = c.user_id
                             WHERE c.livreur_id = ? AND c.statut IN ('confirmee','en_livraison') ORDER BY c.created_at");
$livraisons->execute([$moi]);
$livraisons = $livraisons->fetchAll();

$base = '../'; $titre = 'Mes missions';
require __DIR__ . '/../includes/header.php';
?>
<h1>Mes missions</h1>

<h2>Collectes de dons (<?= count($collectes) ?>)</h2>
<?php if (!$collectes): ?><p class="muted">Aucune collecte assignée.</p><?php else: ?>
<div class="table-wrap"><table>
  <tr><th>Donateur</th><th>Don</th><th>Adresse</th><th>Statut</th><th>Action</th></tr>
  <?php foreach ($collectes as $d): ?>
  <tr>
    <td><?= e($d['donateur']) ?><br><span class="muted"><?= e($d['telephone']) ?></span></td>
    <td><?= e($d['description']) ?> (<?= (int)$d['quantite'] ?>)</td>
    <td><?= e($d['adresse_collecte']) ?><?php if ($d['date_souhaitee']): ?><br><span class="muted">Le <?= e(date('d/m/Y', strtotime($d['date_souhaitee']))) ?></span><?php endif; ?></td>
    <td><span class="badge"><?= e(libelle_statut($d['statut'])) ?></span></td>
    <td>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="type" value="don"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
        <?php if ($d['statut'] === 'assigne'): ?><button class="btn btn-small" name="statut" value="collecte">Marquer collecté</button>
        <?php else: ?><button class="btn btn-small" name="statut" value="recu">Remis à l'entrepôt</button><?php endif; ?>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>

<h2>Livraisons de commandes (<?= count($livraisons) ?>)</h2>
<?php if (!$livraisons): ?><p class="muted">Aucune livraison assignée.</p><?php else: ?>
<div class="table-wrap"><table>
  <tr><th>N°</th><th>Client</th><th>Adresse</th><th>À encaisser</th><th>Statut</th><th>Action</th></tr>
  <?php foreach ($livraisons as $c): ?>
  <tr>
    <td>#<?= (int)$c['id'] ?></td>
    <td><?= e($c['client']) ?><br><span class="muted"><?= e($c['telephone']) ?></span></td>
    <td><?= e($c['adresse_livraison']) ?></td>
    <td><?= $c['mode_paiement'] === 'livraison' ? prix($c['total']) . ' (liquide)' : 'Payé en ligne' ?></td>
    <td><span class="badge"><?= e(libelle_statut($c['statut'])) ?></span></td>
    <td>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="type" value="commande"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <?php if ($c['statut'] === 'confirmee'): ?><button class="btn btn-small" name="statut" value="en_livraison">Prendre en charge</button>
        <?php else: ?><button class="btn btn-small" name="statut" value="livree">Marquer livrée</button><?php endif; ?>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>
<p class="muted">Les commandes apparaissent ici une fois confirmées par l'administration.</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>

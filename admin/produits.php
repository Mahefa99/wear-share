<?php
// admin/produits.php : liste, ajout, modification, désactivation des produits
require_once __DIR__ . '/../includes/functions.php';
exiger_role('admin');

$categories = ['Homme', 'Femme', 'Enfant', 'Chaussures', 'Accessoires', 'Livres'];
$etats = ['Comme neuf', 'Très bon état', 'Bon état'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $action = $_POST['action'] ?? '';

    if ($action === 'sauver') {
        $id    = (int)($_POST['id'] ?? 0);
        $nom   = trim($_POST['nom'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $cat   = in_array($_POST['categorie'] ?? '', $categories, true) ? $_POST['categorie'] : 'Homme';
        $etat  = in_array($_POST['etat'] ?? '', $etats, true) ? $_POST['etat'] : 'Bon état';
        $taille = trim($_POST['taille'] ?? '');
        $prix  = (float)str_replace(',', '.', $_POST['prix'] ?? '0');
        $stock = max(0, (int)($_POST['stock'] ?? 0));

        if ($nom === '' || $prix < 0) {
            flash('error', 'Le nom est obligatoire et le prix doit être positif.');
            redirect('produits.php' . ($id ? "?edit=$id" : ''));
        }
        $image = enregistrer_image('image');

        if ($id) {
            if ($image) {
                $pdo->prepare('UPDATE produits SET nom=?, description=?, categorie=?, taille=?, etat=?, prix=?, stock=?, image=? WHERE id=?')
                    ->execute([$nom, $desc, $cat, $taille, $etat, $prix, $stock, $image, $id]);
            } else {
                $pdo->prepare('UPDATE produits SET nom=?, description=?, categorie=?, taille=?, etat=?, prix=?, stock=? WHERE id=?')
                    ->execute([$nom, $desc, $cat, $taille, $etat, $prix, $stock, $id]);
            }
            flash('success', 'Produit modifié.');
        } else {
            $pdo->prepare('INSERT INTO produits (nom, description, categorie, taille, etat, prix, stock, image) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$nom, $desc, $cat, $taille, $etat, $prix, $stock, $image]);
            flash('success', 'Produit mis en ligne.');
        }
    } elseif ($action === 'basculer') {
        // On ne supprime pas : on masque (les anciennes commandes gardent leur historique)
        $pdo->prepare('UPDATE produits SET actif = 1 - actif WHERE id = ?')->execute([(int)$_POST['id']]);
        flash('success', 'Visibilité du produit modifiée.');
    }
    redirect('produits.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM produits WHERE id = ?');
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch() ?: null;
}
$produits = $pdo->query('SELECT * FROM produits ORDER BY created_at DESC')->fetchAll();

$base = '../'; $titre = 'Produits'; $page = 'produits';
require __DIR__ . '/../includes/header.php';
?>
<h1>Produits</h1>
<?php require __DIR__ . '/_menu.php'; ?>

<h2><?= $edit ? 'Modifier le produit' : 'Mettre une pièce en ligne' ?></h2>
<form class="form large" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="sauver">
  <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
  <label>Nom</label><input type="text" name="nom" value="<?= e($edit['nom'] ?? '') ?>" required>
  <label>Description</label><textarea name="description"><?= e($edit['description'] ?? '') ?></textarea>
  <label>Catégorie</label>
  <select name="categorie"><?php foreach ($categories as $c): ?><option <?= ($edit['categorie'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select>
  <label>État</label>
  <select name="etat"><?php foreach ($etats as $c): ?><option <?= ($edit['etat'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select>
  <label>Taille</label><input type="text" name="taille" value="<?= e($edit['taille'] ?? '') ?>">
  <label>Prix (Rs)</label><input type="number" name="prix" step="0.01" min="0" value="<?= e($edit['prix'] ?? '') ?>" required>
  <label>Stock</label><input type="number" name="stock" min="0" value="<?= e($edit['stock'] ?? 1) ?>" required>
  <label>Photo (JPG, PNG ou WebP, 3 Mo max)</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp">
  <button class="btn" type="submit"><?= $edit ? 'Enregistrer' : 'Mettre en ligne' ?></button>
  <?php if ($edit): ?><a class="btn btn-outline" href="produits.php">Annuler</a><?php endif; ?>
</form>

<h2>Tous les produits (<?= count($produits) ?>)</h2>
<div class="table-wrap"><table>
  <tr><th></th><th>Nom</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Visible</th><th></th></tr>
  <?php foreach ($produits as $p): ?>
  <tr>
    <td><?php if ($p['image']): ?><img class="miniature" src="../uploads/<?= e($p['image']) ?>" alt=""><?php else: ?>👕<?php endif; ?></td>
    <td><?= e($p['nom']) ?></td><td><?= e($p['categorie']) ?></td><td><?= prix($p['prix']) ?></td><td><?= (int)$p['stock'] ?></td>
    <td><?= $p['actif'] ? 'Oui' : 'Non' ?></td>
    <td>
      <a class="btn btn-small btn-outline" href="produits.php?edit=<?= (int)$p['id'] ?>">Modifier</a>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="basculer"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <button class="btn btn-small <?= $p['actif'] ? 'btn-danger' : '' ?>" type="submit"><?= $p['actif'] ? 'Masquer' : 'Afficher' ?></button></form>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>

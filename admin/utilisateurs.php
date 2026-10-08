<?php
// admin/utilisateurs.php : gérer les comptes (créer un livreur, changer le rôle, activer/désactiver)
require_once __DIR__ . '/../includes/functions.php';
exiger_role('admin');

$roles = ['utilisateur', 'livreur', 'admin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $action = $_POST['action'] ?? '';
    $moi = user()['id'];

    if ($action === 'creer') {
        $nom = trim($_POST['nom'] ?? ''); $email = trim($_POST['email'] ?? ''); $mdp = $_POST['mot_de_passe'] ?? '';
        $role = in_array($_POST['role'] ?? '', $roles, true) ? $_POST['role'] : 'livreur';
        $existe = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $existe->execute([$email]);
        if ($nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($mdp) < 8) {
            flash('error', 'Nom, e-mail valide et mot de passe (8 caractères min.) requis.');
        } elseif ($existe->fetch()) {
            flash('error', 'Cet e-mail est déjà utilisé.');
        } else {
            $pdo->prepare('INSERT INTO users (nom, email, mot_de_passe, telephone, role) VALUES (?,?,?,?,?)')
                ->execute([$nom, $email, password_hash($mdp, PASSWORD_DEFAULT), trim($_POST['telephone'] ?? ''), $role]);
            flash('success', 'Compte créé.');
        }
    } elseif ($action === 'role') {
        $id = (int)$_POST['id'];
        if ($id !== $moi && in_array($_POST['role'] ?? '', $roles, true)) {   // on ne peut pas modifier son propre rôle
            $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$_POST['role'], $id]);
            flash('success', 'Rôle modifié.');
        }
    } elseif ($action === 'actif') {
        $id = (int)$_POST['id'];
        if ($id !== $moi) {                                                    // on ne peut pas se désactiver soi-même
            $pdo->prepare('UPDATE users SET actif = 1 - actif WHERE id = ?')->execute([$id]);
            flash('success', 'Statut du compte modifié.');
        }
    }
    redirect('utilisateurs.php');
}

$users = $pdo->query('SELECT * FROM users ORDER BY role, nom')->fetchAll();

$base = '../'; $titre = 'Utilisateurs'; $page = 'utilisateurs';
require __DIR__ . '/../includes/header.php';
?>
<h1>Utilisateurs</h1>
<?php require __DIR__ . '/_menu.php'; ?>

<h2>Créer un compte (livreur, admin…)</h2>
<form class="form large" method="post">
  <?= csrf_field() ?><input type="hidden" name="action" value="creer">
  <label>Nom</label><input type="text" name="nom" required>
  <label>E-mail</label><input type="email" name="email" required>
  <label>Téléphone</label><input type="tel" name="telephone">
  <label>Mot de passe temporaire (8 caractères min.)</label><input type="password" name="mot_de_passe" required>
  <label>Rôle</label>
  <select name="role"><?php foreach ($roles as $r): ?><option <?= $r === 'livreur' ? 'selected' : '' ?>><?= e($r) ?></option><?php endforeach; ?></select>
  <button class="btn" type="submit">Créer</button>
</form>

<h2>Tous les comptes (<?= count($users) ?>)</h2>
<div class="table-wrap"><table>
  <tr><th>Nom</th><th>E-mail</th><th>Téléphone</th><th>Rôle</th><th>Actif</th></tr>
  <?php foreach ($users as $u): $moiMeme = (int)$u['id'] === user()['id']; ?>
  <tr>
    <td><?= e($u['nom']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['telephone']) ?></td>
    <td>
      <?php if ($moiMeme): ?><?= e($u['role']) ?> (vous)<?php else: ?>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="role"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
        <select name="role" onchange="this.form.submit()"><?php foreach ($roles as $r): ?><option <?= $u['role'] === $r ? 'selected' : '' ?>><?= e($r) ?></option><?php endforeach; ?></select></form>
      <?php endif; ?>
    </td>
    <td>
      <?php if ($moiMeme): ?>Oui<?php else: ?>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="actif"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
        <button class="btn btn-small <?= $u['actif'] ? 'btn-danger' : '' ?>" type="submit"><?= $u['actif'] ? 'Désactiver' : 'Réactiver' ?></button></form>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>

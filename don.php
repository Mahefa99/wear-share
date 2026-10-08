<?php
// don.php : formulaire de don (vêtements et livres). Compte obligatoire avant de soumettre.
require_once __DIR__ . '/includes/functions.php';

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    exiger_connexion();
    if (user()['role'] !== 'utilisateur') {
        flash('error', t('only_users_donate'));
        redirect('index.php');
    }
    $desc    = trim($_POST['description'] ?? '');
    $qte     = max(1, (int)($_POST['quantite'] ?? 1));
    $adresse = trim($_POST['adresse_collecte'] ?? '');
    $date    = $_POST['date_souhaitee'] ?? '';
    $date    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;

    if ($desc === '' || $adresse === '') {
        $erreur = t('err_donate');
    } else {
        $pdo->prepare('INSERT INTO dons (user_id, description, quantite, adresse_collecte, date_souhaitee) VALUES (?,?,?,?,?)')
            ->execute([user()['id'], $desc, $qte, $adresse, $date]);
        flash('success', t('donate_ok'));
        redirect('mon-compte.php');
    }
}

$adresseDefaut = '';
if (est_connecte()) {
    $s = $pdo->prepare('SELECT adresse FROM users WHERE id = ?');
    $s->execute([user()['id']]);
    $adresseDefaut = $s->fetchColumn() ?: '';
}

$titre = t('donate_title');
require __DIR__ . '/includes/header.php';
?>
<h1><?= t('donate_title') ?></h1>
<p class="muted"><?= t('donate_sub') ?></p>
<?php if (!est_connecte()): ?>
  <div class="alert alert-info"><?= t('donate_need', '<a href="login.php">' . t('login_lc') . '</a>', '<a href="register.php">' . t('register_lc') . '</a>') ?></div>
<?php endif; ?>
<?php if ($erreur): ?><div class="alert alert-error"><?= e($erreur) ?></div><?php endif; ?>
<form class="form" method="post">
  <?= csrf_field() ?>
  <label><?= t('what_donate') ?></label>
  <textarea name="description" placeholder="<?= e(t('what_ph')) ?>" required><?= e($_POST['description'] ?? '') ?></textarea>
  <label><?= t('nb_items') ?></label>
  <input type="number" name="quantite" min="1" value="<?= (int)($_POST['quantite'] ?? 1) ?>">
  <label><?= t('collect_address') ?></label>
  <input type="text" name="adresse_collecte" value="<?= e($_POST['adresse_collecte'] ?? $adresseDefaut) ?>" required>
  <label><?= t('wish_date') ?></label>
  <input type="date" name="date_souhaitee" min="<?= date('Y-m-d') ?>">
  <button class="btn" type="submit"><?= t('send_donation') ?></button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>

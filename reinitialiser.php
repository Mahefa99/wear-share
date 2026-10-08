<?php
// reinitialiser.php : choix du nouveau mot de passe via le lien reçu
require_once __DIR__ . '/includes/functions.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$s = $pdo->prepare('SELECT id, user_id FROM password_resets WHERE token_hash = ? AND used = 0 AND expires_at > NOW()');
$s->execute([hash('sha256', (string)$token)]);
$reset = $s->fetch();

$erreur = '';
if ($reset && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $mdp = $_POST['mot_de_passe'] ?? '';
    if (strlen($mdp) < 8)                              $erreur = t('err_pwd_len');
    elseif ($mdp !== ($_POST['mot_de_passe2'] ?? ''))  $erreur = t('err_pwd_match');
    else {
        $pdo->prepare('UPDATE users SET mot_de_passe = ? WHERE id = ?')->execute([password_hash($mdp, PASSWORD_DEFAULT), $reset['user_id']]);
        $pdo->prepare('UPDATE password_resets SET used = 1 WHERE id = ?')->execute([$reset['id']]);   // lien à usage unique
        flash('success', t('pwd_changed'));
        redirect('login.php');
    }
}

$titre = t('reset_title');
require __DIR__ . '/includes/header.php';
?>
<h1><?= t('reset_title') ?></h1>
<?php if (!$reset): ?>
  <div class="alert alert-error"><?= t('link_invalid') ?> <a href="mot-de-passe-oublie.php"><?= t('retry') ?></a></div>
<?php else: ?>
  <?php if ($erreur): ?><div class="alert alert-error"><?= e($erreur) ?></div><?php endif; ?>
  <form class="form" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <label><?= t('new_pwd') ?></label><input type="password" name="mot_de_passe" required>
    <label><?= t('confirm') ?></label><input type="password" name="mot_de_passe2" required>
    <button class="btn" type="submit"><?= t('save') ?></button>
  </form>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
// mot-de-passe-oublie.php : demande d'un lien de réinitialisation
require_once __DIR__ . '/includes/functions.php';
if (est_connecte()) redirect('index.php');

$envoye = false;
$lien = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $email = trim($_POST['email'] ?? '');

    $s = $pdo->prepare('SELECT id FROM users WHERE email = ? AND actif = 1');
    $s->execute([$email]);
    $uid = $s->fetchColumn();

    if ($uid) {
        $token = bin2hex(random_bytes(32));                          // jeton aléatoire impossible à deviner
        $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$uid]);
        // On stocke l'empreinte (hash) du jeton, jamais le jeton lui-même. Valable 1 heure.
        $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))')
            ->execute([$uid, hash('sha256', $token)]);
        $lien = SITE_URL . '/reinitialiser.php?token=' . $token;

        if (!MODE_DEV) {   // en production : envoi par e-mail (le serveur doit être configuré pour mail())
            mail($email, t('mail_subject'), t('mail_body', $lien));
        }
    }
    $envoye = true;   // même message que le compte existe ou non (ne révèle rien)
}

$titre = t('forgot_title');
require __DIR__ . '/includes/header.php';
?>
<h1><?= t('forgot_title') ?></h1>
<?php if ($envoye): ?>
  <div class="alert alert-success"><?= t('forgot_sent') ?></div>
  <?php if (MODE_DEV && $lien): ?>
    <div class="alert alert-info"><?= t('dev_mode') ?> <a href="<?= e($lien) ?>"><?= t('dev_click') ?></a></div>
  <?php endif; ?>
<?php else: ?>
  <form class="form" method="post">
    <?= csrf_field() ?>
    <label><?= t('your_email') ?></label><input type="email" name="email" required>
    <button class="btn" type="submit"><?= t('send_link') ?></button>
  </form>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>

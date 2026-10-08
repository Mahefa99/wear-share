<?php
// login.php : connexion
require_once __DIR__ . '/includes/functions.php';
if (est_connecte()) redirect('index.php');

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if ($u && password_verify($mdp, $u['mot_de_passe'])) {
        if (!$u['actif']) {
            $erreur = t('disabled');
        } else {
            session_regenerate_id(true);   // évite le vol de session
            $_SESSION['user'] = ['id' => (int)$u['id'], 'nom' => $u['nom'], 'email' => $u['email'], 'role' => $u['role']];
            $retour = $_SESSION['retour'] ?? null;
            unset($_SESSION['retour']);
            if ($u['role'] === 'admin')   redirect('admin/index.php');
            if ($u['role'] === 'livreur') redirect('livreur/index.php');
            redirect($retour && str_starts_with($retour, '/') && !str_starts_with($retour, '//') ? $retour : 'index.php');
        }
    } else {
        $erreur = t('bad_login');
    }
}

$titre = t('login_title');
require __DIR__ . '/includes/header.php';
?>
<h1><?= t('login_title') ?></h1>
<?php if ($erreur): ?><div class="alert alert-error"><?= e($erreur) ?></div><?php endif; ?>
<form class="form" method="post">
  <?= csrf_field() ?>
  <label><?= t('email') ?></label><input type="email" name="email" required>
  <label><?= t('password') ?></label><input type="password" name="mot_de_passe" required>
  <button class="btn" type="submit"><?= t('login_link') ?></button>
  <p><a href="mot-de-passe-oublie.php"><?= t('forgot') ?></a></p>
</form>
<p><?= t('no_account') ?> <a href="register.php"><?= t('register') ?></a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>

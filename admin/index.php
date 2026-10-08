<?php
// admin/index.php : tableau de bord administrateur
require_once __DIR__ . '/../includes/functions.php';
exiger_role('admin');
$base = '../'; $titre = 'Administration'; $page = 'index';
require __DIR__ . '/../includes/header.php';

// Petite fonction pour compter des lignes
function compter($pdo, $sql) { return (int)$pdo->query($sql)->fetchColumn(); }

$stats = [
    'Utilisateurs'          => compter($pdo, "SELECT COUNT(*) FROM users WHERE role = 'utilisateur'"),
    'Produits en vente'     => compter($pdo, "SELECT COUNT(*) FROM produits WHERE actif = 1 AND stock > 0"),
    'Commandes à traiter'   => compter($pdo, "SELECT COUNT(*) FROM commandes WHERE statut IN ('en_attente','confirmee')"),
    'Dons en attente'       => compter($pdo, "SELECT COUNT(*) FROM dons WHERE statut = 'en_attente'"),
    'Vêtements reçus (dons)'=> compter($pdo, "SELECT COALESCE(SUM(quantite),0) FROM dons WHERE statut = 'recu'"),
];
?>
<h1>Administration</h1>
<?php require __DIR__ . '/_menu.php'; ?>
<div class="stats">
  <?php foreach ($stats as $label => $n): ?>
    <div class="stat"><strong><?= $n ?></strong><span class="muted"><?= e($label) ?></span></div>
  <?php endforeach; ?>
</div>
<p>Utilisez le menu ci-dessus pour mettre des pièces en ligne, suivre les commandes, assigner les collectes aux livreurs et gérer les comptes.</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>

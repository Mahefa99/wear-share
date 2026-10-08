<?php
// Sous-menu commun aux pages d'administration. $page = nom de la page active.
$items = [
    'index' => ['index.php', 'Tableau de bord'],
    'produits' => ['produits.php', 'Produits'],
    'commandes' => ['commandes.php', 'Commandes'],
    'dons' => ['dons.php', 'Dons'],
    'utilisateurs' => ['utilisateurs.php', 'Utilisateurs'],
];
?>
<div class="sous-menu">
  <?php foreach ($items as $cle => [$lien, $libelle]): ?>
    <a href="<?= $lien ?>" class="<?= ($page ?? '') === $cle ? 'actif' : '' ?>"><?= $libelle ?></a>
  <?php endforeach; ?>
</div>

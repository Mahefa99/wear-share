# ReWear — plateforme de don et de réutilisation de vêtements

Site PHP / MySQL : on y donne des vêtements et des livres, et on y achète des pièces de seconde main à prix très réduits.

## Rôles

| Rôle | Ce qu'il peut faire |
|---|---|
| **Utilisateur** (donateur + acheteur) | Créer un compte, faire un don, acheter (panier, commande), suivre ses dons et commandes |
| **Administrateur** | Mettre les pièces en ligne, gérer produits / commandes / dons / comptes, assigner les livreurs |
| **Livreur** | Voir ses missions : collecter les dons, livrer les commandes |

Un compte est **obligatoire** avant de soumettre un don ou d'acheter.

## Installation (XAMPP ou WAMP)

1. Copier le dossier `reuse-wear` dans `htdocs` (XAMPP) ou `www` (WAMP).
2. Démarrer Apache et MySQL.
3. Ouvrir phpMyAdmin → **Importer** → choisir `database.sql`.
4. Vérifier les identifiants MySQL dans `includes/config.php` (par défaut : `root`, mot de passe vide).
5. Ouvrir `http://localhost/reuse-wear/`.

**Compte administrateur par défaut :** `admin@rewear.mu` / `Admin123!` → à changer dès la première connexion
(pour l'instant : créer un nouvel admin dans *Administration → Utilisateurs*, puis désactiver l'ancien).

## Nouveautés de la version 2
- **Paiement** : « Liquide (à la livraison) » ou « En ligne » (le paiement en ligne est simulé).
- **Langue** : boutons FR | EN dans le menu. Les textes sont dans `includes/lang/fr.php` et `includes/lang/en.php`.
  Pour traduire un nouveau texte : ajouter la même clé dans les deux fichiers, puis écrire `<?= t('ma_cle') ?>` dans la page.
  Les espaces admin et livreur restent en français (seul le menu est traduit).
- **Mot de passe oublié** : lien sur la page de connexion. En local (`MODE_DEV = true` dans `includes/config.php`),
  le lien de réinitialisation s'affiche à l'écran ; en ligne, mettre `false` pour l'envoyer par e-mail.
- **Base déjà installée ?** Importer `update_v2.sql` une fois (ajoute la table `password_resets`).
- `MODE_DEV = true` affiche aussi les erreurs PHP (plus de page blanche). À passer à `false` en production.

## Structure

```
reuse-wear/
├── database.sql          schéma + données d'exemple
├── includes/             config, fonctions, en-tête, pied de page, lang/ (traductions)
├── assets/style.css      styles
├── uploads/              photos des produits
├── index.php, catalogue.php, produit.php     pages publiques
├── register.php, login.php, logout.php       comptes
├── panier.php, commande.php                  achat
├── don.php, mon-compte.php                   dons et historique
├── admin/                espace administrateur
└── livreur/              espace livreur
```

## Sécurité déjà en place
Mots de passe hachés (`password_hash`), requêtes préparées PDO (anti-injection SQL), échappement des sorties (anti-XSS),
jetons CSRF sur tous les formulaires, contrôle des rôles, vérification du type des images envoyées, stock verrouillé pendant la commande.

## Pistes pour la suite
Vrai paiement en ligne, e-mails de confirmation, mot de passe oublié, page de statistiques d'impact (kg de vêtements sauvés), plusieurs photos par produit.

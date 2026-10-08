<?php
// ============================================================
// functions.php : fonctions utilitaires partagées par toutes les pages
// ============================================================
require_once __DIR__ . '/config.php';

// --- Langue : choisie via ?lang=fr ou ?lang=en, mémorisée en session ---
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
}
function langue() {
    return $_SESSION['lang'] ?? 'fr';
}

// t('cle') renvoie le texte dans la langue choisie.
// t('cle', 5) remplace %d / %s dans le texte. Si la clé manque en anglais, on prend le français.
function t($cle, ...$args) {
    static $chargees = [];
    foreach (['fr', langue()] as $l) {
        if (!isset($chargees[$l])) {
            $fichier = __DIR__ . '/lang/' . $l . '.php';
            $chargees[$l] = is_file($fichier) ? require $fichier : [];
        }
    }
    $txt = $chargees[langue()][$cle] ?? $chargees['fr'][$cle] ?? $cle;
    return $args ? vsprintf($txt, $args) : $txt;
}

// Lien de changement de langue qui garde les autres paramètres de l'URL (ex. ?id=3)
function url_langue($l) {
    return '?' . http_build_query(array_merge($_GET, ['lang' => $l]));
}

// Échapper le texte avant de l'afficher (protège contre les attaques XSS)
function e($texte) {
    return htmlspecialchars((string)$texte, ENT_QUOTES, 'UTF-8');
}

// Formater un prix en roupies
function prix($montant) {
    return 'Rs ' . number_format((float)$montant, 2, ',', ' ');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function est_connecte() {
    return isset($_SESSION['user']);
}

function user() {
    return $_SESSION['user'] ?? null;
}

// Exiger une connexion (sinon redirection vers la page de connexion)
function exiger_connexion($base = '') {
    if (!est_connecte()) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => t('login_required')];
        $_SESSION['retour'] = $_SERVER['REQUEST_URI'];
        redirect($base . 'login.php');
    }
}

// Exiger un rôle précis (admin, livreur...)
function exiger_role($role, $base = '../') {
    exiger_connexion($base);
    if (user()['role'] !== $role) {
        http_response_code(403);
        die('Accès refusé.');
    }
}

// Messages "flash" : affichés une seule fois après une action
function flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

// --- Protection CSRF : jeton caché dans chaque formulaire POST ---
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function csrf_verifier() {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(400);
        die('Requête invalide (jeton de sécurité manquant). Rechargez la page.');
    }
}

// --- Panier : stocké en session sous la forme [id_produit => quantité] ---
function panier() {
    return $_SESSION['panier'] ?? [];
}
function panier_compte() {
    return array_sum(panier());
}

// Enregistrer une image envoyée via un formulaire (retourne le nom du fichier ou null)
function enregistrer_image($champ) {
    if (empty($_FILES[$champ]['name']) || $_FILES[$champ]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $autorises = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $type = mime_content_type($_FILES[$champ]['tmp_name']);
    if (!isset($autorises[$type]) || $_FILES[$champ]['size'] > 3 * 1024 * 1024) {
        return null;
    }
    $nom = uniqid('p_', true) . '.' . $autorises[$type];
    move_uploaded_file($_FILES[$champ]['tmp_name'], UPLOAD_DIR . $nom);
    return $nom;
}

// Libellé lisible d'un statut (traduit selon la langue)
function libelle_statut($s) {
    return t('st_' . $s);
}

// Libellé du mode de paiement : 'livraison' = liquide, 'carte' = en ligne
function libelle_paiement($mode) {
    return $mode === 'carte' ? t('pay_online_short') : t('pay_cash_short');
}

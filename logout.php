<?php
// logout.php : déconnexion (on garde la langue choisie)
require_once __DIR__ . '/includes/functions.php';
$lang = langue();
$_SESSION = [];
session_destroy();
session_start();
$_SESSION['lang'] = $lang;
flash('info', t('logged_out'));
redirect('index.php');

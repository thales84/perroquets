<?php
/* ================================================================
   Maple Perroquets — Routeur principal
   ================================================================ */
require_once __DIR__ . '/configuration/config.php';
require_once __DIR__ . '/configuration/connexion.php';
require_once __DIR__ . '/configuration/fonctions.php';
require_once __DIR__ . '/configuration/traduction.php';
require_once __DIR__ . '/configuration/seo.php';
require_once __DIR__ . '/configuration/securite-client.php';

// Session cliente démarrée ici pour toutes les pages publiques
demarrerSessionClient();

$page = $_GET['page'] ?? 'accueil';

$cartographie = [
    'accueil'      => 'pages/accueil.php',
    'oiseaux'      => 'pages/liste-oiseaux.php',
    'oiseau'       => 'pages/fiche-oiseau.php',
    'reservation'  => 'pages/reservation.php',
    'confirmation' => 'pages/confirmation.php',
    'inscription'  => 'pages/inscription.php',
    'connexion'    => 'pages/connexion.php',
    'deconnexion'  => 'pages/deconnexion.php',
    'compte'       => 'pages/compte.php',
    'suivi'        => 'pages/suivi.php',
    '404'          => 'pages/404.php',
];

if (!array_key_exists($page, $cartographie)) {
    $page = '404';
}

$fichier = __DIR__ . '/' . $cartographie[$page];

if (!file_exists($fichier)) {
    http_response_code(404);
    $fichier = __DIR__ . '/pages/404.php';
}

require_once $fichier;

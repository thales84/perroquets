<?php
// Point d'entrée unique — routeur principal.
require_once __DIR__ . '/configuration/config.php';
require_once __DIR__ . '/configuration/connexion.php';
require_once __DIR__ . '/configuration/fonctions.php';
require_once __DIR__ . '/configuration/traduction.php';
require_once __DIR__ . '/configuration/seo.php';

if (MODE_DEVELOPPEMENT) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

$page   = obtenirParametreUrl('page', 'accueil');
$slug   = obtenirParametreUrl('slug');
$langue = obtenirParametreUrl('langue', LANGUE_PAR_DEFAUT);

// Pages disponibles — à compléter à chaque étape
$pagesAutorisees = ['accueil', 'oiseaux', 'oiseau', 'espece', 'reservation', 'confirmation'];

if (!in_array($page, $pagesAutorisees, true)) {
    http_response_code(404);
    $page = '404';
}

// --- Gabarits (étape 03) ---
// Chaque fichier de page définit $titrePage et $descriptionPage avant d'inclure entete.php.
switch ($page) {
    case 'accueil':
        include __DIR__ . '/pages/accueil.php';
        break;

    case 'oiseaux':
        include __DIR__ . '/pages/liste-oiseaux.php';
        break;

    case 'oiseau':
        include __DIR__ . '/pages/fiche-oiseau.php';
        break;

    case 'espece':
        include __DIR__ . '/pages/liste-espece.php';
        break;

    case 'reservation':
        include __DIR__ . '/pages/reservation.php';
        break;

    case 'confirmation':
        include __DIR__ . '/pages/confirmation.php';
        break;

    default:
        include __DIR__ . '/pages/404.php';
        break;
}

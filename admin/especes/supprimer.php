<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/espece-modele.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id || !verifierJetonCsrf($_GET['csrf'] ?? '')) {
    header('Location: ' . URL_SITE . '/admin/especes/liste.php');
    exit;
}

$resultat = supprimerEspece($id);

if ($resultat === 'oiseaux') {
    header('Location: ' . URL_SITE . '/admin/especes/liste.php?erreur=oiseaux');
} elseif ($resultat === true) {
    header('Location: ' . URL_SITE . '/admin/especes/liste.php?succes=supprime');
} else {
    header('Location: ' . URL_SITE . '/admin/especes/liste.php');
}
exit;

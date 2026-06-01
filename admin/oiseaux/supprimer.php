<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/oiseau-modele.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id || !verifierJetonCsrf($_GET['csrf'] ?? '')) {
    header('Location: ' . URL_SITE . '/admin/oiseaux/liste.php');
    exit;
}

supprimerOiseau($id);
header('Location: ' . URL_SITE . '/admin/oiseaux/liste.php?succes=supprime');
exit;

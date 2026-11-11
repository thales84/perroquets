<?php
// Actions rapides depuis la liste (GET + jeton CSRF) :
//   ?id=N&csrf=…              → suppression
//   ?id=N&csrf=…&action=basculer → publier / masquer
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/temoignage-modele.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$retour = URL_SITE . '/admin/temoignages/liste.php';

if (!$id || !verifierJetonCsrf($_GET['csrf'] ?? '')) {
    header('Location: ' . $retour);
    exit;
}

if (($_GET['action'] ?? '') === 'basculer') {
    basculerVisibiliteTemoignage($id);
    header('Location: ' . $retour . '?succes=bascule');
} else {
    supprimerTemoignage($id);
    header('Location: ' . $retour . '?succes=supprime');
}
exit;

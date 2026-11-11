<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/temoignage-modele.php';
require_once RACINE . '/modeles/parametre-modele.php';

$id         = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$temoignage = $id ? recupererTemoignage($id) : null;

if (!$temoignage) {
    header('Location: ' . URL_SITE . '/admin/temoignages/liste.php');
    exit;
}

$valeurs = [];
foreach (CHAMPS_TEMOIGNAGE as $cle) {
    $valeurs[$cle] = $temoignage[$cle] ?? '';
}

require __DIR__ . '/inc/traitement-temoignage.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($erreurs)) {
    if (modifierTemoignage($id, $valeurs)) {
        header('Location: ' . URL_SITE . '/admin/temoignages/liste.php?succes=modifie');
        exit;
    }
    $erreurs['_global'] = 'Erreur lors de la modification.';
}

$titrePage = 'Modifier un témoignage';
require_once dirname(__DIR__) . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Modifier : <?= echapper($temoignage['auteur']) ?></h1>
        <a href="<?= echapper(URL_SITE) ?>/admin/temoignages/liste.php" class="bouton bouton-contour bouton-sm">← Retour</a>
    </div>

    <?php if (!empty($erreurs['_global'])) : ?>
        <div class="alerte alerte--erreur" role="alert"><?= echapper($erreurs['_global']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= echapper(URL_SITE) ?>/admin/temoignages/modifier.php?id=<?= $id ?>" class="formulaire-admin">
        <input type="hidden" name="csrf_token" value="<?= echapper(genererJetonCsrf()) ?>">

        <?php include __DIR__ . '/inc/champs-temoignage.php'; ?>

        <div class="form-actions">
            <button type="submit" class="bouton bouton-primaire">Enregistrer</button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/inc/pied-admin.php'; ?>

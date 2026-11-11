<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/temoignage-modele.php';
require_once RACINE . '/modeles/parametre-modele.php';

$valeurs = [
    'titre_fr' => '', 'titre_en' => '', 'texte_fr' => '', 'texte_en' => '',
    'auteur' => '', 'ville' => '', 'province' => '', 'visible' => 1, 'ordre' => 0,
];

require __DIR__ . '/inc/traitement-temoignage.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($erreurs)) {
    if (creerTemoignage($valeurs)) {
        header('Location: ' . URL_SITE . '/admin/temoignages/liste.php?succes=ajoute');
        exit;
    }
    $erreurs['_global'] = "Erreur lors de l'enregistrement.";
}

$titrePage = 'Ajouter un témoignage';
require_once dirname(__DIR__) . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Ajouter un témoignage</h1>
        <a href="<?= echapper(URL_SITE) ?>/admin/temoignages/liste.php" class="bouton bouton-contour bouton-sm">← Retour</a>
    </div>

    <?php if (!empty($erreurs['_global'])) : ?>
        <div class="alerte alerte--erreur" role="alert"><?= echapper($erreurs['_global']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= echapper(URL_SITE) ?>/admin/temoignages/ajouter.php" class="formulaire-admin">
        <input type="hidden" name="csrf_token" value="<?= echapper(genererJetonCsrf()) ?>">

        <?php include __DIR__ . '/inc/champs-temoignage.php'; ?>

        <div class="form-actions">
            <button type="submit" class="bouton bouton-primaire">Ajouter le témoignage</button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/inc/pied-admin.php'; ?>

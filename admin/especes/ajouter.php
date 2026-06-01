<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/espece-modele.php';

$erreurs = [];
$valeurs = [
    'nom_commun_fr' => '', 'nom_commun_en' => '',
    'nom_scientifique' => '', 'famille_fr' => '', 'famille_en' => '',
    'slug_fr' => '', 'slug_en' => '',
    'description_fr' => '', 'description_en' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierJetonCsrf($_POST['csrf_token'] ?? '')) {
        $erreurs['_global'] = 'Jeton de sécurité invalide. Réessayez.';
    } else {
        foreach ($valeurs as $cle => $_) {
            $valeurs[$cle] = trim($_POST[$cle] ?? '');
        }
        if ($valeurs['nom_commun_fr'] === '') {
            $erreurs['nom_commun_fr'] = 'Ce champ est requis.';
        }

        if (empty($erreurs)) {
            $id = creerEspece($valeurs);
            if ($id) {
                header('Location: ' . URL_SITE . '/admin/especes/liste.php?succes=ajoute');
                exit;
            }
            $erreurs['_global'] = 'Erreur lors de la création (slug déjà utilisé ?).';
        }
    }
}

$titrePage = 'Ajouter une espèce';
require_once dirname(__DIR__) . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Ajouter une espèce</h1>
        <a href="<?= echapper(URL_SITE) ?>/admin/especes/liste.php" class="bouton bouton-contour bouton-sm">← Retour</a>
    </div>

    <?php if (!empty($erreurs['_global'])) : ?>
        <div class="alerte alerte--erreur" role="alert"><?= echapper($erreurs['_global']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= echapper(URL_SITE) ?>/admin/especes/ajouter.php" class="formulaire-admin">
        <input type="hidden" name="csrf_token" value="<?= echapper(genererJetonCsrf()) ?>">

        <?php include __DIR__ . '/inc/champs-espece.php'; ?>

        <div class="form-actions">
            <button type="submit" class="bouton bouton-primaire">Créer l'espèce</button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/inc/pied-admin.php'; ?>

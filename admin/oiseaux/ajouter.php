<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/oiseau-modele.php';
require_once RACINE . '/modeles/espece-modele.php';

$especes = listerEspeces();
$erreurs = [];
$valeurs = [
    'id_espece' => '', 'sexe' => 'inconnu', 'date_naissance' => '',
    'num_bague' => '', 'prix_cad' => '', 'sevre_main' => 0,
    'statut' => 'disponible', 'description_fr' => '', 'description_en' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierJetonCsrf($_POST['csrf_token'] ?? '')) {
        $erreurs['_global'] = 'Jeton invalide.';
    } else {
        foreach ($valeurs as $cle => $_) {
            $valeurs[$cle] = trim($_POST[$cle] ?? '');
        }
        $valeurs['sevre_main'] = isset($_POST['sevre_main']) ? 1 : 0;

        if (empty($valeurs['id_espece'])) {
            $erreurs['id_espece'] = 'Espèce requise.';
        }

        if (empty($erreurs)) {
            // Trouver le nom de l'espèce pour le slug
            $especeChoisie = null;
            foreach ($especes as $e) {
                if ((int)$e['id_espece'] === (int)$valeurs['id_espece']) {
                    $especeChoisie = $e;
                    break;
                }
            }
            $valeurs['espece_nom'] = $especeChoisie['nom_commun_fr'] ?? 'oiseau';

            $id = creerOiseau($valeurs);
            if ($id) {
                header('Location: ' . URL_SITE . '/admin/oiseaux/modifier.php?id=' . $id . '&succes=ajoute');
                exit;
            }
            $erreurs['_global'] = 'Erreur lors de la création.';
        }
    }
}

$titrePage = 'Ajouter un oiseau';
require_once dirname(__DIR__) . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Ajouter un oiseau</h1>
        <a href="<?= echapper(URL_SITE) ?>/admin/oiseaux/liste.php" class="bouton bouton-contour bouton-sm">← Retour</a>
    </div>

    <?php if (!empty($erreurs['_global'])) : ?>
        <div class="alerte alerte--erreur"><?= echapper($erreurs['_global']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= echapper(URL_SITE) ?>/admin/oiseaux/ajouter.php" class="formulaire-admin">
        <input type="hidden" name="csrf_token" value="<?= echapper(genererJetonCsrf()) ?>">
        <?php include __DIR__ . '/inc/champs-oiseau.php'; ?>
        <div class="form-actions">
            <button type="submit" class="bouton bouton-primaire">Créer l'oiseau</button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/inc/pied-admin.php'; ?>

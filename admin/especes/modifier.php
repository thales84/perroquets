<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/espece-modele.php';

$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$espece = $id ? recupererEspece($id) : null;

if (!$espece) {
    header('Location: ' . URL_SITE . '/admin/especes/liste.php');
    exit;
}

$erreurs = [];
$valeurs = [
    'nom_commun_fr'    => $espece['nom_commun_fr']    ?? '',
    'nom_commun_en'    => $espece['nom_commun_en']    ?? '',
    'nom_scientifique' => $espece['nom_scientifique'] ?? '',
    'famille_fr'       => $espece['famille_fr']       ?? '',
    'famille_en'       => $espece['famille_en']       ?? '',
    'slug_fr'          => $espece['slug_fr']          ?? '',
    'slug_en'          => $espece['slug_en']          ?? '',
    'description_fr'   => $espece['description_fr']   ?? '',
    'description_en'   => $espece['description_en']   ?? '',
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
            $ok = modifierEspece($id, $valeurs);
            if ($ok) {
                header('Location: ' . URL_SITE . '/admin/especes/liste.php?succes=modifie');
                exit;
            }
            $erreurs['_global'] = 'Erreur lors de la modification (slug déjà utilisé ?).';
        }
    }
}

$titrePage = 'Modifier : ' . $espece['nom_commun_fr'];
require_once dirname(__DIR__) . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Modifier : <?= echapper($espece['nom_commun_fr']) ?></h1>
        <a href="<?= echapper(URL_SITE) ?>/admin/especes/liste.php" class="bouton bouton-contour bouton-sm">← Retour</a>
    </div>

    <?php if (!empty($erreurs['_global'])) : ?>
        <div class="alerte alerte--erreur" role="alert"><?= echapper($erreurs['_global']) ?></div>
    <?php endif; ?>

    <form method="post"
          action="<?= echapper(URL_SITE) ?>/admin/especes/modifier.php?id=<?= $id ?>"
          class="formulaire-admin">
        <input type="hidden" name="csrf_token" value="<?= echapper(genererJetonCsrf()) ?>">

        <?php include __DIR__ . '/inc/champs-espece.php'; ?>

        <div class="form-actions">
            <button type="submit" class="bouton bouton-primaire">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/inc/pied-admin.php'; ?>

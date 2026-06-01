<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/oiseau-modele.php';
require_once RACINE . '/modeles/espece-modele.php';

$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$oiseau = $id ? recupererOiseauParId($id) : null;

if (!$oiseau) {
    header('Location: ' . URL_SITE . '/admin/oiseaux/liste.php');
    exit;
}

$especes = listerEspeces();
$erreurs = [];
$valeurs = [
    'id_espece'      => $oiseau['id_espece'],
    'sexe'           => $oiseau['sexe'],
    'date_naissance' => $oiseau['date_naissance'] ?? '',
    'num_bague'      => $oiseau['num_bague'] ?? '',
    'prix_cad'       => $oiseau['prix_cad'] ?? '',
    'sevre_main'     => $oiseau['sevre_main'],
    'statut'         => $oiseau['statut'],
    'description_fr' => $oiseau['description_fr'] ?? '',
    'description_en' => $oiseau['description_en'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
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
            modifierOiseau($id, $valeurs);
            header('Location: ' . URL_SITE . '/admin/oiseaux/modifier.php?id=' . $id . '&succes=modifie');
            exit;
        }
    }
}

$photos    = recupererPhotosOiseauAdmin($id);
$csrf      = genererJetonCsrf();
$titrePage = 'Modifier : ' . ($oiseau['espece_nom'] ?? '');
require_once dirname(__DIR__) . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Modifier : <?= echapper($oiseau['espece_nom'] ?? '') ?></h1>
        <a href="<?= echapper(URL_SITE) ?>/admin/oiseaux/liste.php" class="bouton bouton-contour bouton-sm">← Retour</a>
    </div>

    <?php if (isset($_GET['succes'])) : ?>
        <div class="alerte alerte--succes">
            <?= $_GET['succes'] === 'ajoute' ? 'Oiseau créé. Ajoutez des photos ci-dessous.' : 'Modifications enregistrées.' ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($erreurs['_global'])) : ?>
        <div class="alerte alerte--erreur"><?= echapper($erreurs['_global']) ?></div>
    <?php endif; ?>

    <div class="modifier-grille">

        <!-- Formulaire données -->
        <section>
            <h2>Caractéristiques</h2>
            <form method="post"
                  action="<?= echapper(URL_SITE) ?>/admin/oiseaux/modifier.php?id=<?= $id ?>"
                  class="formulaire-admin">
                <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">
                <input type="hidden" name="action" value="modifier">
                <?php include __DIR__ . '/inc/champs-oiseau.php'; ?>
                <div class="form-actions">
                    <button type="submit" class="bouton bouton-primaire">Enregistrer</button>
                </div>
            </form>
        </section>

        <!-- Gestion photos -->
        <section class="section-photos">
            <h2>Photos</h2>

            <!-- Upload -->
            <form method="post"
                  action="<?= echapper(URL_SITE) ?>/admin/oiseaux/photos.php"
                  enctype="multipart/form-data"
                  class="formulaire-upload">
                <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">
                <input type="hidden" name="id_oiseau"  value="<?= $id ?>">
                <input type="hidden" name="action"     value="upload">

                <div class="champ">
                    <label for="fichiers">Ajouter des photos (jpg, png, webp — 5 Mo max)</label>
                    <input type="file" id="fichiers" name="fichiers[]"
                           accept="image/jpeg,image/png,image/webp" multiple>
                </div>
                <div class="champ">
                    <label for="alt_upload">Texte alternatif (alt)</label>
                    <input type="text" id="alt_upload" name="texte_alt_fr"
                           placeholder="Description de l'image">
                </div>
                <button type="submit" class="bouton bouton-contour bouton-sm">Téléverser</button>
            </form>

            <?php if (isset($_GET['upload_erreur'])) : ?>
                <div class="alerte alerte--erreur"><?= echapper(urldecode($_GET['upload_erreur'])) ?></div>
            <?php endif; ?>

            <!-- Photos existantes -->
            <?php if (empty($photos)) : ?>
                <p class="texte-discret">Aucune photo encore.</p>
            <?php else : ?>
                <div class="photos-grille">
                    <?php foreach ($photos as $p) : ?>
                    <div class="photo-vignette <?= $p['est_principale'] ? 'photo-vignette--principale' : '' ?>">
                        <img src="<?= echapper($p['chemin_fichier']) ?>"
                             alt="<?= echapper($p['texte_alt_fr'] ?? '') ?>"
                             loading="lazy">
                        <?php if ($p['est_principale']) : ?>
                            <span class="badge-principale">Principale</span>
                        <?php endif; ?>
                        <div class="photo-actions">
                            <?php if (!$p['est_principale']) : ?>
                            <form method="post" action="<?= echapper(URL_SITE) ?>/admin/oiseaux/photos.php">
                                <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">
                                <input type="hidden" name="action"   value="principal">
                                <input type="hidden" name="id_photo"  value="<?= (int)$p['id_photo'] ?>">
                                <input type="hidden" name="id_oiseau" value="<?= $id ?>">
                                <button type="submit" class="bouton bouton-contour bouton-xs">Principale</button>
                            </form>
                            <?php endif; ?>
                            <form method="post" action="<?= echapper(URL_SITE) ?>/admin/oiseaux/photos.php"
                                  onsubmit="return confirm('Supprimer cette photo ?')">
                                <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">
                                <input type="hidden" name="action"   value="supprimer">
                                <input type="hidden" name="id_photo"  value="<?= (int)$p['id_photo'] ?>">
                                <input type="hidden" name="id_oiseau" value="<?= $id ?>">
                                <button type="submit" class="bouton bouton-danger bouton-xs">Supprimer</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</div>

<?php require_once dirname(__DIR__) . '/inc/pied-admin.php'; ?>

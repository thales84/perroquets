<?php
require_once __DIR__ . '/../modeles/oiseau-modele.php';

// --- Filtres depuis GET (nettoyés) ---
$filtres = [];

if (!empty($_GET['id_espece']) && ctype_digit($_GET['id_espece'])) {
    $filtres['id_espece'] = (int) $_GET['id_espece'];
}

if (!empty($_GET['sexe']) && in_array($_GET['sexe'], ['male', 'femelle', 'inconnu'], true)) {
    $filtres['sexe'] = $_GET['sexe'];
}

if (isset($_GET['sevre_main']) && in_array($_GET['sevre_main'], ['0', '1'], true)) {
    $filtres['sevre_main'] = $_GET['sevre_main'];
}

// --- Données ---
$oiseaux = recupererOiseauxDisponibles($filtres);
$especes = recupererListeEspeces();

// --- SEO ---
$titrePage       = t('liste_titre');
$descriptionPage = 'Perroquets disponibles à la vente au Canada — Maple Perroquets. ' . t('liste_sous_titre');

require_once __DIR__ . '/../gabarits/entete.php';

$langue    = langueActive();
$urlOiseaux = echapper(URL_SITE) . '/' . echapper($langue) . '/oiseaux';
?>

<div class="conteneur">
    <h1><?= echapper(t('liste_titre')) ?></h1>
    <p class="marge-bas-1"><?= echapper(t('liste_sous_titre')) ?></p>

    <!-- Barre de filtres -->
    <form class="filtres" method="get" action="<?= $urlOiseaux ?>">
        <div class="filtres-groupe">

            <label for="filtre-espece"><?= echapper(t('filtre_espece')) ?></label>
            <select id="filtre-espece" name="id_espece">
                <option value=""><?= echapper(t('filtre_tous')) ?></option>
                <?php foreach ($especes as $espece) : ?>
                    <option value="<?= echapper($espece['id_espece']) ?>"
                        <?= isset($filtres['id_espece']) && $filtres['id_espece'] === (int) $espece['id_espece'] ? 'selected' : '' ?>>
                        <?= echapper($espece['nom_commun_fr']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="filtre-sexe"><?= echapper(t('filtre_sexe')) ?></label>
            <select id="filtre-sexe" name="sexe">
                <option value=""><?= echapper(t('filtre_tous')) ?></option>
                <option value="male"     <?= ($filtres['sexe'] ?? '') === 'male'     ? 'selected' : '' ?>><?= echapper(t('sexe_male')) ?></option>
                <option value="femelle"  <?= ($filtres['sexe'] ?? '') === 'femelle'  ? 'selected' : '' ?>><?= echapper(t('sexe_femelle')) ?></option>
                <option value="inconnu"  <?= ($filtres['sexe'] ?? '') === 'inconnu'  ? 'selected' : '' ?>><?= echapper(t('sexe_inconnu')) ?></option>
            </select>

            <label for="filtre-eam"><?= echapper(t('filtre_eam')) ?></label>
            <select id="filtre-eam" name="sevre_main">
                <option value=""><?= echapper(t('filtre_tous')) ?></option>
                <option value="1" <?= ($filtres['sevre_main'] ?? '') === '1' ? 'selected' : '' ?>>Oui</option>
                <option value="0" <?= ($filtres['sevre_main'] ?? '') === '0' ? 'selected' : '' ?>>Non</option>
            </select>

            <button type="submit" class="bouton bouton-primaire">Filtrer</button>
            <?php if (!empty($filtres)) : ?>
                <a href="<?= $urlOiseaux ?>" class="bouton bouton-contour"><?= echapper(t('filtre_tous')) ?></a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Grille oiseaux -->
    <?php if (empty($oiseaux)) : ?>
        <p class="aucun-resultat"><?= echapper(t('aucun_resultat')) ?></p>
    <?php else : ?>
        <div class="grille-oiseaux">
            <?php foreach ($oiseaux as $oiseau) : ?>
                <?php
                $urlFiche = echapper(URL_SITE) . '/' . echapper($langue) . '/oiseau/' . echapper($oiseau['slug_fr']);
                ?>
                <article class="carte-oiseau">
                    <a href="<?= $urlFiche ?>" tabindex="-1" aria-hidden="true">
                        <?php if (!empty($oiseau['photo_chemin'])) : ?>
                            <img class="carte-oiseau__image"
                                 src="<?= echapper($oiseau['photo_chemin']) ?>"
                                 alt="<?= echapper($oiseau['photo_alt'] ?? $oiseau['espece_nom']) ?>"
                                 loading="lazy"
                                 width="400" height="300">
                        <?php else : ?>
                            <div class="carte-oiseau__image--placeholder" aria-hidden="true">🦜</div>
                        <?php endif; ?>
                    </a>

                    <div class="carte-oiseau__corps">
                        <p class="carte-oiseau__espece"><?= echapper($oiseau['espece_nom']) ?></p>

                        <h2 class="carte-oiseau__nom">
                            <a href="<?= $urlFiche ?>"><?= echapper($oiseau['espece_nom']) ?></a>
                        </h2>

                        <p>
                            <?= echapper(t('fiche_sexe')) ?> :
                            <?= echapper(t('sexe_' . $oiseau['sexe'])) ?>
                            <?php if ($oiseau['sevre_main']) : ?>
                                &nbsp;<span class="badge badge--disponible"><?= echapper(t('fiche_eam_oui')) ?></span>
                            <?php endif; ?>
                        </p>

                        <p class="carte-oiseau__prix">
                            <?= echapper(formaterPrixCad($oiseau['prix_cad'])) ?>
                        </p>
                    </div>

                    <div class="carte-oiseau__pied">
                        <span class="badge badge--disponible"><?= echapper(t('statut_disponible')) ?></span>
                        <a href="<?= $urlFiche ?>" class="bouton bouton-primaire"><?= echapper(t('fiche_reserver')) ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

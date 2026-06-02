<?php
require_once __DIR__ . '/../modeles/oiseau-modele.php';

$langue = langueActive();

/* Filtres depuis GET */
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

$oiseaux = recupererOiseauxDisponibles($filtres);
$especes = recupererListeEspeces();

$titrePage       = t('liste_titre');
$descriptionPage = 'Perroquets disponibles à la vente au Canada — Maple Perroquets.';

$urlListe = echapper(URL_SITE) . '/' . echapper($langue) . '/oiseaux';

require_once __DIR__ . '/../gabarits/entete.php';
?>

<section class="section">
    <p class="eyebrow">Nos oiseaux</p>
    <h1 class="s-titre"><?= echapper(t('liste_titre')) ?></h1>
    <p class="s-sous"><?= echapper(t('liste_sous_titre')) ?></p>

    <!-- Filtres -->
    <div class="filtres-bar filtres-auto">
        <form method="get" action="<?= $urlListe ?>">
            <div class="filtre-groupe">
                <label for="f-espece">Espèce</label>
                <select id="f-espece" name="id_espece">
                    <option value="">Toutes les espèces</option>
                    <?php foreach ($especes as $e): ?>
                    <option value="<?= (int)$e['id_espece'] ?>"
                        <?= isset($filtres['id_espece']) && $filtres['id_espece'] === (int)$e['id_espece'] ? 'selected' : '' ?>>
                        <?= echapper($e['nom_commun_fr']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filtre-groupe">
                <label for="f-sexe">Sexe</label>
                <select id="f-sexe" name="sexe">
                    <option value="">Tous</option>
                    <option value="male"    <?= ($filtres['sexe'] ?? '') === 'male'    ? 'selected' : '' ?>><?= echapper(t('sexe_male')) ?></option>
                    <option value="femelle" <?= ($filtres['sexe'] ?? '') === 'femelle' ? 'selected' : '' ?>><?= echapper(t('sexe_femelle')) ?></option>
                    <option value="inconnu" <?= ($filtres['sexe'] ?? '') === 'inconnu' ? 'selected' : '' ?>><?= echapper(t('sexe_inconnu')) ?></option>
                </select>
            </div>
            <div class="filtre-groupe">
                <label for="f-eam">Sevré à la main</label>
                <select id="f-eam" name="sevre_main">
                    <option value="">Tous</option>
                    <option value="1" <?= ($filtres['sevre_main'] ?? '') === '1' ? 'selected' : '' ?>>Oui</option>
                    <option value="0" <?= ($filtres['sevre_main'] ?? '') === '0' ? 'selected' : '' ?>>Non</option>
                </select>
            </div>
            <div class="filtres-actions">
                <button type="submit" class="btn btn-jungle btn-sm">Filtrer</button>
                <?php if (!empty($filtres)): ?>
                <a href="<?= $urlListe ?>" class="btn btn-ghost btn-sm">Réinitialiser</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Résultats -->
    <?php if (empty($oiseaux)): ?>
    <p style="text-align:center;padding:3rem 0;color:var(--doux);"><?= echapper(t('aucun_resultat')) ?></p>
    <?php else: ?>
    <div class="oiseaux-grille">
        <?php foreach ($oiseaux as $o):
            $urlFiche = echapper(URL_SITE) . '/' . $langue . '/oiseau/' . echapper($o['slug_fr']);
            $prix     = echapper(formaterPrixCad($o['prix_cad']));
        ?>
        <article class="carte-o reveal">
            <div class="carte-o-img">
                <span class="ef" aria-hidden="true">🦜</span>
                <?php if (!empty($o['photo_chemin'])): ?>
                <img src="<?= echapper(URL_SITE . $o['photo_chemin']) ?>"
                     alt="<?= echapper($o['photo_alt'] ?? $o['espece_nom']) ?>"
                     loading="lazy"
                     onerror="this.style.display='none'">
                <?php endif; ?>
                <span class="badge-st b-dispo">Disponible</span>
                <button class="btn-fav" data-actif="false" aria-label="Ajouter aux favoris">♡</button>
            </div>
            <div class="carte-o-corps">
                <div class="carte-o-esp"><?= echapper($o['espece_nom']) ?></div>
                <div class="carte-o-nom">
                    <a href="<?= $urlFiche ?>" style="color:inherit;"><?= echapper($o['espece_nom']) ?></a>
                </div>
                <div class="carte-o-tags">
                    <span class="tag"><?= echapper(t('sexe_' . $o['sexe'])) ?></span>
                    <?php if ($o['sevre_main']): ?>
                    <span class="tag">Sevré à la main</span>
                    <?php endif; ?>
                </div>
                <div class="carte-o-prix"><?= $prix ?></div>
            </div>
            <div class="carte-o-pied">
                <a href="<?= $urlFiche ?>" class="btn btn-primaire btn-full">Réserver cet oiseau</a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

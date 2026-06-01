<?php
require_once __DIR__ . '/../modeles/oiseau-modele.php';

$slug = obtenirParametreUrl('slug');
$langue = langueActive();

// --- Récupération de l'oiseau ---
$oiseau = $slug ? recupererOiseauParSlug($slug) : null;

if (!$oiseau) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

$photos     = recupererPhotosOiseau((int) $oiseau['id_oiseau']);
$disponible = $oiseau['statut'] === 'disponible';

// --- Calcul de l'âge ---
function calculerAge(?string $dateNaissance): string
{
    if (!$dateNaissance) {
        return t('non_communique');
    }
    $naissance = new DateTimeImmutable($dateNaissance);
    $maintenant = new DateTimeImmutable();
    $diff = $maintenant->diff($naissance);

    if ($diff->y >= 1) {
        return $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
    }
    if ($diff->m >= 1) {
        return $diff->m . ' mois';
    }
    return 'Moins d\'un mois';
}

// --- SEO dynamique ---
$nomEspece  = $oiseau['espece_nom_fr'] ?? '';
$eamLabel   = $oiseau['sevre_main'] ? ' EAM' : '';
$sexeLabel  = t('sexe_' . $oiseau['sexe']);
$titrePage  = $nomEspece . ' ' . mb_strtolower($sexeLabel) . $eamLabel . ' — Perroquet à vendre (Canada)';

$extraitDesc = $oiseau['description_fr']
    ? mb_substr(strip_tags($oiseau['description_fr']), 0, 155) . '…'
    : t('meta_description_defaut');
$descriptionPage = $extraitDesc;

$urlCanonique = URL_SITE . '/' . $langue . '/oiseau/' . $oiseau['slug_fr'];

// Photo principale pour OG
$photoOg = null;
foreach ($photos as $p) {
    if ($p['est_principale']) {
        $photoOg = URL_SITE . $p['chemin_fichier'];
        break;
    }
}
$ogImage = $photoOg;

// --- JSON-LD Schema.org Product ---
$disponibilite = $disponible ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut';
$jsonLdData = [
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => $titrePage,
    'description' => $extraitDesc,
    'image'       => $photoOg ?? '',
    'offers'      => [
        '@type'         => 'Offer',
        'priceCurrency' => 'CAD',
        'price'         => $oiseau['prix_cad'] ?? '0',
        'availability'  => $disponibilite,
        'url'           => $urlCanonique,
    ],
];
$jsonLd = json_encode($jsonLdData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$hreflangChemin = 'oiseau/' . $oiseau['slug_fr'];

require_once __DIR__ . '/../gabarits/entete.php';
?>

<div class="conteneur">

    <!-- Fil d'Ariane -->
    <nav class="fil-ariane" aria-label="Fil d'Ariane">
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"><?= echapper(t('liste_titre')) ?></a>
        <span aria-hidden="true"> › </span>
        <span aria-current="page"><?= echapper($nomEspece) ?></span>
    </nav>

    <div class="fiche-oiseau">

        <!-- Galerie -->
        <section class="fiche-galerie" aria-label="<?= echapper(t('fiche_galerie')) ?>">
            <?php if (!empty($photos)) : ?>
                <?php $photoPrincipale = $photos[0]; ?>
                <div class="galerie-principale">
                    <img id="photo-principale"
                         src="<?= echapper($photoPrincipale['chemin_fichier']) ?>"
                         alt="<?= echapper($photoPrincipale['texte_alt_fr'] ?? $nomEspece) ?>"
                         class="galerie-img-principale"
                         loading="eager">
                </div>
                <?php if (count($photos) > 1) : ?>
                    <div class="galerie-miniatures" role="list">
                        <?php foreach ($photos as $i => $photo) : ?>
                            <button class="galerie-miniature <?= $i === 0 ? 'active' : '' ?>"
                                    role="listitem"
                                    data-src="<?= echapper($photo['chemin_fichier']) ?>"
                                    data-alt="<?= echapper($photo['texte_alt_fr'] ?? $nomEspece) ?>"
                                    aria-label="Photo <?= $i + 1 ?>">
                                <img src="<?= echapper($photo['chemin_fichier']) ?>"
                                     alt=""
                                     loading="lazy">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="galerie-placeholder" aria-label="Aucune photo disponible">🦜</div>
            <?php endif; ?>
        </section>

        <!-- Informations -->
        <section class="fiche-infos">

            <h1 class="fiche-titre">
                <?= echapper($nomEspece) ?>
                <span class="fiche-titre-detail"><?= echapper($sexeLabel) ?><?= $oiseau['sevre_main'] ? ' · ' . echapper(t('fiche_eam_oui')) : '' ?></span>
            </h1>

            <?php if (!$disponible) : ?>
                <div class="fiche-indisponible" role="alert">
                    <?php if ($oiseau['statut'] === 'vendu') : ?>
                        Cet oiseau a trouvé sa famille. <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"><?= echapper(t('fiche_retour')) ?></a>
                    <?php else : ?>
                        <?= echapper(t('statut_' . $oiseau['statut'])) ?> — <?= echapper(t('fiche_retour')) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Tableau de caractéristiques -->
            <table class="fiche-tableau">
                <tbody>
                    <tr>
                        <th scope="row"><?= echapper(t('fiche_espece')) ?></th>
                        <td>
                            <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/espece/<?= echapper($oiseau['espece_slug_fr']) ?>">
                                <?= echapper($nomEspece) ?>
                            </a>
                        </td>
                    </tr>
                    <?php if (!empty($oiseau['nom_scientifique'])) : ?>
                    <tr>
                        <th scope="row">Nom scientifique</th>
                        <td><em><?= echapper($oiseau['nom_scientifique'] ?? '') ?></em></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th scope="row"><?= echapper(t('fiche_sexe')) ?></th>
                        <td><?= echapper($sexeLabel) ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?= echapper(t('fiche_age')) ?></th>
                        <td>
                            <?= echapper(calculerAge($oiseau['date_naissance'])) ?>
                            <?php if ($oiseau['date_naissance']) : ?>
                                <span class="texte-discret">(<?= echapper(t('naissance')) ?> <?= echapper(formaterDate($oiseau['date_naissance'])) ?>)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($oiseau['num_bague']) : ?>
                    <tr>
                        <th scope="row"><?= echapper(t('fiche_bague')) ?></th>
                        <td><?= echapper($oiseau['num_bague']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th scope="row"><?= echapper(t('filtre_eam')) ?></th>
                        <td><?= $oiseau['sevre_main'] ? echapper(t('fiche_eam_oui')) : echapper(t('fiche_eam_non')) ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?= echapper(t('fiche_prix')) ?></th>
                        <td class="fiche-prix"><?= echapper(formaterPrixCad($oiseau['prix_cad'])) ?></td>
                    </tr>
                </tbody>
            </table>

            <?php if ($disponible) : ?>
                <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/reservation/<?= echapper($oiseau['slug_fr']) ?>"
                   class="bouton bouton-secondaire fiche-bouton-resa">
                    <?= echapper(t('fiche_reserver')) ?>
                </a>
            <?php endif; ?>

            <!-- Description -->
            <?php if ($oiseau['description_fr']) : ?>
                <section class="fiche-description">
                    <h2><?= echapper(t('fiche_description')) ?></h2>
                    <p><?= nl2br(echapper($oiseau['description_fr'])) ?></p>
                </section>
            <?php endif; ?>

            <p class="fiche-retour-lien">
                <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">← <?= echapper(t('fiche_retour')) ?></a>
            </p>

        </section>
    </div>
</div>

<script>
// Galerie — miniatures cliquables, sans bibliothèque
(function () {
    var principale = document.getElementById('photo-principale');
    if (!principale) return;

    document.querySelectorAll('.galerie-miniature').forEach(function (btn) {
        btn.addEventListener('click', function () {
            principale.src = btn.dataset.src;
            principale.alt = btn.dataset.alt;
            document.querySelectorAll('.galerie-miniature').forEach(function (b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
        });
    });
})();
</script>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

<?php
require_once __DIR__ . '/../modeles/oiseau-modele.php';

$slug   = obtenirParametreUrl('slug');
$langue = langueActive();

$oiseau = $slug ? recupererOiseauParSlug($slug) : null;
if (!$oiseau) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

$photos     = recupererPhotosOiseau((int) $oiseau['id_oiseau']);
$disponible = $oiseau['statut'] === 'disponible';

function calculerAge(?string $d): string
{
    if (!$d) return t('non_communique');
    $diff = (new DateTimeImmutable())->diff(new DateTimeImmutable($d));
    if ($diff->y >= 1) return $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
    if ($diff->m >= 1) return $diff->m . ' mois';
    return 'Moins d\'un mois';
}

$nomEspece  = $oiseau['espece_nom_fr'] ?? '';
$sexeLabel  = t('sexe_' . $oiseau['sexe']);
$eamLabel   = $oiseau['sevre_main'] ? ' · Sevré à la main' : '';
$titrePage  = $nomEspece . ' ' . mb_strtolower($sexeLabel) . $eamLabel . ' — Perroquet à vendre (Canada)';
$extraitDesc = $oiseau['description_fr']
    ? mb_substr(strip_tags($oiseau['description_fr']), 0, 155) . '…'
    : t('meta_description_defaut');
$descriptionPage = $extraitDesc;
$urlCanonique    = URL_SITE . '/' . $langue . '/oiseau/' . $oiseau['slug_fr'];

/* Photo principale pour OG */
$ogImage = null;
foreach ($photos as $p) {
    if ($p['est_principale']) { $ogImage = URL_SITE . $p['chemin_fichier']; break; }
}

/* JSON-LD : Product enrichi (orienté Canada) + fil d'Ariane, en @graph */
$siteNom = param('site_nom', 'Maple Perroquets');

$produit = [
    '@type'         => 'Product',
    'name'          => $titrePage,
    'description'   => $extraitDesc,
    'image'         => $ogImage ?? '',
    'category'      => 'Animaux de compagnie › Perroquets',
    'itemCondition' => 'https://schema.org/NewCondition',
    'brand'         => ['@type' => 'Brand', 'name' => $siteNom],
    'offers'        => [
        '@type'           => 'Offer',
        'priceCurrency'   => 'CAD',
        'price'           => $oiseau['prix_cad'] ?? '0',
        'availability'    => $disponible ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
        'url'             => $urlCanonique,
        'priceValidUntil' => date('Y-m-d', strtotime('+30 days')),
        'areaServed'      => ['@type' => 'Country', 'name' => 'Canada'],
        'seller'          => ['@type' => 'Organization', 'name' => $siteNom],
    ],
];
if (!empty($oiseau['num_bague'])) {
    $produit['sku'] = $oiseau['num_bague'];
}

$ariane = [
    ['nom' => 'Accueil',             'url' => URL_SITE . '/' . $langue . '/'],
    ['nom' => 'Oiseaux disponibles', 'url' => URL_SITE . '/' . $langue . '/oiseaux'],
    ['nom' => $nomEspece,            'url' => $urlCanonique],
];
$filAriane = [];
foreach ($ariane as $i => $el) {
    $filAriane[] = [
        '@type'    => 'ListItem',
        'position' => $i + 1,
        'name'     => $el['nom'],
        'item'     => $el['url'],
    ];
}

$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph'   => [
        $produit,
        ['@type' => 'BreadcrumbList', 'itemListElement' => $filAriane],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$hreflangChemin = 'oiseau/' . $oiseau['slug_fr'];

require_once __DIR__ . '/../gabarits/entete.php';
?>

<section class="section">
    <!-- Fil d'Ariane -->
    <nav class="fil-ariane" aria-label="Fil d'Ariane">
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Oiseaux disponibles</a>
        <span class="sep" aria-hidden="true">›</span>
        <span aria-current="page"><?= echapper($nomEspece) ?></span>
    </nav>

    <div class="fiche-layout">

        <!-- Galerie -->
        <div>
            <?php if (!empty($photos)): ?>
            <div class="galerie-principale">
                <img id="photo-principale"
                     src="<?= echapper(URL_SITE . $photos[0]['chemin_fichier']) ?>"
                     alt="<?= echapper($photos[0]['texte_alt_fr'] ?? $nomEspece) ?>"
                     class="galerie-img-principale"
                     loading="eager">
            </div>
            <?php if (count($photos) > 1): ?>
            <div class="galerie-miniatures">
                <?php foreach ($photos as $i => $p): ?>
                <button class="galerie-miniature <?= $i === 0 ? 'active' : '' ?>"
                        data-src="<?= echapper(URL_SITE . $p['chemin_fichier']) ?>"
                        data-alt="<?= echapper($p['texte_alt_fr'] ?? $nomEspece) ?>"
                        aria-label="Photo <?= $i + 1 ?>">
                    <img src="<?= echapper(URL_SITE . $p['chemin_fichier']) ?>"
                         alt="" loading="lazy">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="galerie-placeholder">🦜</div>
            <?php endif; ?>
        </div>

        <!-- Infos -->
        <div>
            <h1 class="fiche-titre">
                <?= echapper($nomEspece) ?>
                <span class="fiche-titre-detail"><?= echapper($sexeLabel) ?><?= echapper($eamLabel) ?></span>
            </h1>

            <!-- Badge statut -->
            <span class="fiche-badge-statut <?= echapper($oiseau['statut']) ?>">
                <?php if ($oiseau['statut'] === 'disponible'): ?>
                    ● Disponible
                <?php elseif ($oiseau['statut'] === 'reserve'): ?>
                    ● Réservé
                <?php else: ?>
                    ● Vendu
                <?php endif; ?>
            </span>

            <?php if (!$disponible): ?>
            <div class="fiche-indispo" role="alert">
                <?php if ($oiseau['statut'] === 'vendu'): ?>
                    Cet oiseau a trouvé sa famille. <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Voir les oiseaux disponibles</a>
                <?php else: ?>
                    Cet oiseau est actuellement réservé. <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Voir les autres oiseaux</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <table class="fiche-tableau">
                <tbody>
                    <tr>
                        <th>Espèce</th>
                        <td><?= echapper($nomEspece) ?></td>
                    </tr>
                    <?php if (!empty($oiseau['nom_scientifique'])): ?>
                    <tr>
                        <th>Nom scientifique</th>
                        <td><em><?= echapper($oiseau['nom_scientifique']) ?></em></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Sexe</th>
                        <td><?= echapper($sexeLabel) ?></td>
                    </tr>
                    <tr>
                        <th>Âge</th>
                        <td>
                            <?= echapper(calculerAge($oiseau['date_naissance'])) ?>
                            <?php if ($oiseau['date_naissance']): ?>
                            <span class="texte-discret">(né le <?= echapper(formaterDate($oiseau['date_naissance'])) ?>)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if (!empty($oiseau['num_bague'])): ?>
                    <tr>
                        <th>Bague</th>
                        <td><?= echapper($oiseau['num_bague']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Sevré à la main</th>
                        <td><?= $oiseau['sevre_main'] ? 'Oui' : 'Non' ?></td>
                    </tr>
                    <tr>
                        <th>Prix</th>
                        <td class="fiche-prix"><?= echapper(formaterPrixCad($oiseau['prix_cad'])) ?></td>
                    </tr>
                </tbody>
            </table>

            <?php if ($disponible): ?>
            <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/reservation/<?= echapper($oiseau['slug_fr']) ?>"
               class="btn btn-primaire btn-lg btn-reserver">
                Réserver cet oiseau
            </a>
            <?php endif; ?>

            <?php if ($oiseau['description_fr']): ?>
            <div class="fiche-description">
                <h2>Description</h2>
                <p><?= nl2br(echapper($oiseau['description_fr'])) ?></p>
            </div>
            <?php endif; ?>

            <p style="margin-top:1.5rem;font-size:.9rem;">
                <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"
                   style="color:var(--ara);">← Retour à la liste</a>
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

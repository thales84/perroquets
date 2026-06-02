<?php
require_once __DIR__ . '/../modeles/espece-modele.php';

$titrePage       = t('nav_accueil');
$descriptionPage = t('meta_description_defaut');

$especes = recupererEspecesAvecNbDisponibles();
$nbTotal = (int) array_sum(array_column($especes, 'nb_disponibles'));

// Photos libres de droit (Unsplash) par espèce
$photosEspeces = [
    'gris-du-gabon'     => 'https://images.unsplash.com/photo-1552728089-57bdde30beb3?w=640&h=360&fit=crop&auto=format&q=75',
    'ara-bleu-et-jaune' => 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=640&h=360&fit=crop&auto=format&q=75',
    'cacatoes-blanc'    => 'https://images.unsplash.com/photo-1567974863832-2aec2a752cfe?w=640&h=360&fit=crop&auto=format&q=75',
];

// Couleur d'accent par espèce (fallback si image absente)
$couleursEspeces = [
    'gris-du-gabon'     => '#607d8b',
    'ara-bleu-et-jaune' => '#1565c0',
    'cacatoes-blanc'    => '#8d6e63',
];

require_once __DIR__ . '/../gabarits/entete.php';
?>

<!-- ========================================================
     HÉROS — split-screen
     ======================================================== -->
<section class="heros heros--accueil">
    <div class="heros-contenu">
        <p class="heros-surtitre">🌿 Éleveur passionné au Québec</p>
        <h1 class="heros-titre">
            <?= echapper(t('accueil_heros_titre')) ?>
        </h1>
        <p class="heros-texte"><?= echapper(t('accueil_heros_sous_titre')) ?></p>
        <div class="heros-cta-groupe">
            <a href="<?= echapper(URL_SITE) ?>/<?= echapper(langueActive()) ?>/oiseaux"
               class="bouton bouton-secondaire bouton-lg">
                <?= echapper(t('accueil_voir_oiseaux')) ?>
            </a>
            <a href="#notre-elevage" class="bouton bouton-contour-blanc">
                <?= echapper(t('accueil_notre_elevage')) ?>
            </a>
        </div>
    </div>

    <div class="heros-visuel" aria-hidden="true">
        <div class="heros-image-cadre">
            <img src="https://images.unsplash.com/photo-1552728089-57bdde30beb3?w=700&h=700&fit=crop&auto=format&q=80"
                 alt=""
                 class="heros-photo"
                 loading="eager"
                 onerror="this.parentElement.classList.add('heros-image-cadre--erreur');this.remove();">
            <div class="heros-badge-flottant">
                <span class="heros-badge-icone">✅</span>
                <span>Oiseaux certifiés sains</span>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================
     BARRE DE STATISTIQUES
     ======================================================== -->
<div class="stats-barre">
    <div class="stats-barre-interieur">
        <div class="stat-item">
            <span class="stat-nombre"><?= $nbTotal ?: count($especes) ?></span>
            <span class="stat-label"><?= echapper(t('accueil_stat_disponibles')) ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-nombre"><?= count($especes) ?></span>
            <span class="stat-label"><?= echapper(t('accueil_stat_especes')) ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-nombre">100&nbsp;%</span>
            <span class="stat-label"><?= echapper(t('accueil_stat_main')) ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-nombre">48&nbsp;h</span>
            <span class="stat-label"><?= echapper(t('accueil_stat_reponse')) ?></span>
        </div>
    </div>
</div>

<!-- ========================================================
     NOS ESPÈCES
     ======================================================== -->
<?php if ($especes): ?>
<section class="section section-especes" id="nos-especes">
    <div class="conteneur">
        <div class="section-entete texte-centre">
            <h2><?= echapper(t('accueil_especes_titre')) ?></h2>
            <p class="section-sous-titre"><?= echapper(t('accueil_especes_sous_titre')) ?></p>
        </div>
        <div class="especes-grille">
            <?php foreach ($especes as $e): ?>
            <?php
                $slug    = $e['slug_fr'] ?? '';
                $photo   = $photosEspeces[$slug] ?? null;
                $couleur = $couleursEspeces[$slug] ?? '#2d7a4f';
                $lien    = echapper(URL_SITE) . '/' . langueActive() . '/oiseaux?espece=' . (int) $e['id_espece'];
            ?>
            <article class="carte-espece">
                <a href="<?= $lien ?>" class="carte-espece__image-lien"
                   style="background-color: <?= echapper($couleur) ?>33;">
                    <div class="carte-espece__fallback" aria-hidden="true">🦜</div>
                    <?php if ($photo): ?>
                    <img src="<?= echapper($photo) ?>"
                         alt="<?= echapper($e['nom_commun_fr']) ?>"
                         class="carte-espece__image"
                         loading="lazy"
                         onerror="this.style.display='none'">
                    <?php endif; ?>
                </a>
                <div class="carte-espece__corps">
                    <?php if ($e['nom_scientifique']): ?>
                    <p class="carte-espece__scientifique"><?= echapper($e['nom_scientifique']) ?></p>
                    <?php endif; ?>
                    <h3 class="carte-espece__nom"><?= echapper($e['nom_commun_fr']) ?></h3>
                    <?php if ($e['description_fr']): ?>
                    <p class="carte-espece__description">
                        <?= echapper(mb_strimwidth($e['description_fr'], 0, 130, '…')) ?>
                    </p>
                    <?php endif; ?>
                    <div class="carte-espece__pied">
                        <?php if ($e['nb_disponibles'] > 0): ?>
                        <span class="badge badge--disponible">
                            <?= (int) $e['nb_disponibles'] ?> disponible<?= $e['nb_disponibles'] > 1 ? 's' : '' ?>
                        </span>
                        <?php else: ?>
                        <span class="badge badge--vendu">Aucun disponible</span>
                        <?php endif; ?>
                        <a href="<?= $lien ?>" class="bouton bouton-contour bouton-sm">
                            <?= echapper(t('accueil_especes_voir')) ?> →
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========================================================
     COMMENT ÇA MARCHE
     ======================================================== -->
<section class="section section-processus" id="notre-elevage">
    <div class="conteneur">
        <div class="section-entete texte-centre">
            <h2><?= echapper(t('accueil_processus_titre')) ?></h2>
        </div>
        <div class="processus-grille">
            <div class="processus-etape">
                <div class="processus-numero">1</div>
                <h3><?= echapper(t('accueil_etape1_titre')) ?></h3>
                <p><?= echapper(t('accueil_etape1_texte')) ?></p>
            </div>
            <div class="processus-connecteur" aria-hidden="true">→</div>
            <div class="processus-etape">
                <div class="processus-numero">2</div>
                <h3><?= echapper(t('accueil_etape2_titre')) ?></h3>
                <p><?= echapper(t('accueil_etape2_texte')) ?></p>
            </div>
            <div class="processus-connecteur" aria-hidden="true">→</div>
            <div class="processus-etape">
                <div class="processus-numero">3</div>
                <h3><?= echapper(t('accueil_etape3_titre')) ?></h3>
                <p><?= echapper(t('accueil_etape3_texte')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================
     POURQUOI NOUS CHOISIR
     ======================================================== -->
<section class="section section-confiance">
    <div class="conteneur">
        <div class="section-entete texte-centre">
            <h2><?= echapper(t('accueil_pourquoi_titre')) ?></h2>
        </div>
        <div class="confiance-grille">
            <div class="confiance-item">
                <div class="confiance-icone">🐣</div>
                <h3><?= echapper(t('accueil_confiance1_titre')) ?></h3>
                <p><?= echapper(t('accueil_confiance1_texte')) ?></p>
            </div>
            <div class="confiance-item">
                <div class="confiance-icone">🩺</div>
                <h3><?= echapper(t('accueil_confiance2_titre')) ?></h3>
                <p><?= echapper(t('accueil_confiance2_texte')) ?></p>
            </div>
            <div class="confiance-item">
                <div class="confiance-icone">🌿</div>
                <h3><?= echapper(t('accueil_confiance3_titre')) ?></h3>
                <p><?= echapper(t('accueil_confiance3_texte')) ?></p>
            </div>
            <div class="confiance-item">
                <div class="confiance-icone">🤝</div>
                <h3><?= echapper(t('accueil_confiance4_titre')) ?></h3>
                <p><?= echapper(t('accueil_confiance4_texte')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================
     CTA FINAL
     ======================================================== -->
<section class="section-cta-final">
    <div class="conteneur texte-centre">
        <h2><?= echapper(t('accueil_cta_titre')) ?></h2>
        <p><?= echapper(t('accueil_cta_texte')) ?></p>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper(langueActive()) ?>/oiseaux"
           class="bouton bouton-secondaire bouton-lg">
            <?= echapper(t('accueil_cta_bouton')) ?>
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

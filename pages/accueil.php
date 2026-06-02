<?php
require_once __DIR__ . '/../modeles/espece-modele.php';
require_once __DIR__ . '/../modeles/oiseau-modele.php';

$titrePage       = 'Perroquets élevés à la main au Canada';
$descriptionPage = t('meta_description_defaut');
$langue          = langueActive();

/* Données réelles depuis la base */
$especes = recupererEspecesAvecNbDisponibles();
$oiseaux = array_slice(recupererOiseauxDisponibles(), 0, 6);
$nbTotal = (int) array_sum(array_column($especes, 'nb_disponibles'));

/* Mapping photos d'espèces (fichiers dans assets/img/) */
$photosEspeces = [
    'gris-du-gabon'          => 'assets/img/espece-gris-du-gabon.jpg',
    'ara-ararauna'           => 'assets/img/espece-ara-ararauna.jpg',
    'cacatoes-a-huppe-jaune' => 'assets/img/espece-cacatoes.jpg',
];
$fondsEspeces = [
    'gris-du-gabon'          => 'linear-gradient(145deg,#2a2a2a,#555)',
    'ara-ararauna'           => 'linear-gradient(145deg,#0d3d7a,#1565c0)',
    'cacatoes-a-huppe-jaune' => 'linear-gradient(145deg,#5a4a3a,#8a7060)',
];
$sousEspeces = [
    'gris-du-gabon'          => 'Le génie de la parole',
    'ara-ararauna'           => 'La star des couleurs',
    'cacatoes-a-huppe-jaune' => 'Le clown câlin',
];
$emojisEspeces = [
    'gris-du-gabon'          => '🐦',
    'ara-ararauna'           => '💙',
    'cacatoes-a-huppe-jaune' => '🤍',
];

require_once __DIR__ . '/../gabarits/entete.php';
?>

<!-- ============================================================
     HÉROS
     ============================================================ -->
<section class="hero">
    <div>
        <p class="hero-pastille reveal">Élevage canadien · Oiseaux élevés à la main</p>
        <h1 class="hero-titre reveal">
            Des oiseaux <em>passionnément</em><br>élevés pour vous
        </h1>
        <p class="hero-texte reveal">
            Chaque perroquet que nous élevons est un individu unique, suivi depuis sa naissance
            au Québec. Socialisés à la main, prêts à rejoindre votre famille.
        </p>
        <div class="hero-btns reveal">
            <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"
               class="btn btn-primaire btn-lg">Voir les oiseaux disponibles</a>
            <a href="#comment" class="btn btn-ghost btn-lg">Comment ça marche</a>
        </div>
        <div class="hero-stats reveal">
            <div>
                <span class="hero-stat-chiffre"><?= $nbTotal ?: count($especes) ?></span>
                <span class="hero-stat-label">Oiseaux disponibles</span>
            </div>
            <div>
                <span class="hero-stat-chiffre"><?= count($especes) ?></span>
                <span class="hero-stat-label">Espèces disponibles</span>
            </div>
            <div>
                <span class="hero-stat-chiffre">CAD</span>
                <span class="hero-stat-label">Prix en dollars canadiens</span>
            </div>
        </div>
    </div>

    <div class="hero-visuel reveal">
        <div class="hero-img-wrap">
            <span class="ef" aria-hidden="true">🦜</span>
            <img src="<?= echapper(URL_SITE) ?>/assets/img/hero-perroquet.jpg"
                 alt="Perroquet ara perché sur l'épaule d'un éleveur québécois"
                 loading="eager"
                 onerror="this.style.display='none'">
        </div>
        <div class="badge-f badge-sante">
            <span style="font-size:1.1rem">✅</span> Garantie de santé
        </div>
        <div class="badge-f badge-canada">100&thinsp;% Canadien 🍁</div>
    </div>
</section>

<!-- ============================================================
     BARRE DE CONFIANCE
     ============================================================ -->
<div class="confiance-barre">
    <div class="confiance-inner">
        <div class="conf-item reveal">
            <div class="conf-icone">🐣</div>
            <div class="conf-titre">Élevés à la main</div>
            <p class="conf-texte">Socialisés dès la naissance pour une vie en famille épanouie.</p>
        </div>
        <div class="conf-item reveal d1">
            <div class="conf-icone">📋</div>
            <div class="conf-titre">Conformité légale</div>
            <p class="conf-texte">Élevage déclaré, conforme à la réglementation faunique canadienne.</p>
        </div>
        <div class="conf-item reveal d2">
            <div class="conf-icone">🩺</div>
            <div class="conf-titre">Garantie de santé</div>
            <p class="conf-texte">Suivi vétérinaire régulier. Carnet de santé remis à l'adoption.</p>
        </div>
        <div class="conf-item reveal d3">
            <div class="conf-icone">🤝</div>
            <div class="conf-titre">Suivi après adoption</div>
            <p class="conf-texte">Nous restons disponibles pour vous accompagner après l'accueil.</p>
        </div>
    </div>
</div>

<!-- ============================================================
     ESPÈCES
     ============================================================ -->
<?php if ($especes): ?>
<section class="section" id="especes">
    <p class="eyebrow reveal">Nos espèces</p>
    <h2 class="s-titre reveal">Trouvez l'oiseau qui vous <em>ressemble</em></h2>
    <p class="s-sous reveal">Chaque espèce a sa propre personnalité. Laquelle correspond à votre mode de vie&nbsp;?</p>
    <div class="especes-grille">
        <?php foreach ($especes as $i => $e):
            $slug   = $e['slug_fr'] ?? '';
            $photo  = $photosEspeces[$slug] ?? null;
            $fond   = $fondsEspeces[$slug]  ?? 'linear-gradient(145deg, var(--jungle-fonce), var(--turquoise))';
            $sous   = $sousEspeces[$slug]   ?? '';
            $emoji  = $emojisEspeces[$slug] ?? '🦜';
            $lien   = echapper(URL_SITE) . '/' . $langue . '/oiseaux?id_espece=' . (int)$e['id_espece'];
        ?>
        <a href="<?= $lien ?>" class="carte-esp reveal d<?= min($i + 1, 4) ?>"
           aria-label="Voir les <?= echapper($e['nom_commun_fr']) ?>">
            <div class="carte-esp-img" style="background:<?= echapper($fond) ?>;">
                <?php if ($photo): ?>
                <img src="<?= echapper(URL_SITE . '/' . $photo) ?>"
                     alt="<?= echapper($e['nom_commun_fr']) ?>"
                     loading="lazy"
                     onerror="this.style.display='none'">
                <?php endif; ?>
            </div>
            <div class="carte-esp-emoji" aria-hidden="true"><?= $emoji ?></div>
            <div class="carte-esp-grad"></div>
            <div class="carte-esp-contenu">
                <div class="carte-esp-nom"><?= echapper($e['nom_commun_fr']) ?></div>
                <?php if ($sous): ?>
                <div class="carte-esp-sous"><?= echapper($sous) ?></div>
                <?php endif; ?>
                <span class="carte-esp-fleche" aria-hidden="true">→</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     NOS OISEAUX DISPONIBLES
     ============================================================ -->
<?php if ($oiseaux): ?>
<section class="section-fond-alt" id="oiseaux">
    <div class="section-inner">
        <p class="eyebrow reveal">Disponibles maintenant</p>
        <h2 class="s-titre reveal">Chaque oiseau est <em>unique</em></h2>
        <p class="s-sous reveal">
            Une fois réservé, un oiseau quitte la vitrine. Les disponibilités évoluent régulièrement.
        </p>
        <div class="oiseaux-grille">
            <?php foreach ($oiseaux as $i => $o):
                $dispo  = $o['statut'] === 'disponible';
                $d      = 'd' . min(($i % 3) + 1, 4);
                $prix   = echapper(formaterPrixCad($o['prix_cad']));
                $urlFiche = echapper(URL_SITE) . '/' . $langue . '/oiseau/' . echapper($o['slug_fr']);
            ?>
            <article class="carte-o reveal <?= $d ?>">
                <div class="carte-o-img">
                    <span class="ef" aria-hidden="true">🦜</span>
                    <?php if (!empty($o['photo_chemin'])): ?>
                    <img src="<?= echapper(URL_SITE . $o['photo_chemin']) ?>"
                         alt="<?= echapper($o['photo_alt'] ?? $o['espece_nom']) ?>"
                         loading="lazy"
                         onerror="this.style.display='none'">
                    <?php endif; ?>
                    <span class="badge-st <?= $dispo ? 'b-dispo' : 'b-reserve' ?>">
                        <?= $dispo ? 'Disponible' : 'Réservé' ?>
                    </span>
                    <button class="btn-fav" data-actif="false"
                            aria-label="Ajouter aux favoris">♡</button>
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
                    <?php if ($dispo): ?>
                    <a href="<?= $urlFiche ?>" class="btn btn-primaire btn-full">Réserver cet oiseau</a>
                    <?php else: ?>
                    <a href="<?= $urlFiche ?>" class="btn btn-ghost btn-full">Voir la fiche</a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     COMMENT ÇA MARCHE
     ============================================================ -->
<section class="section" id="comment">
    <p class="eyebrow reveal">Simple &amp; transparent</p>
    <h2 class="s-titre reveal">Réserver en <em>3 étapes</em></h2>
    <p class="s-sous reveal">Pas de paiement précipité en ligne. On prend le temps de se parler avant tout.</p>
    <div class="processus-grille">
        <div class="proc-etape reveal">
            <div class="proc-num">1</div>
            <div class="proc-titre">Choisissez votre oiseau</div>
            <p class="proc-texte">Parcourez la galerie et trouvez le compagnon qui vous correspond selon l'espèce, le sexe et votre mode de vie.</p>
        </div>
        <div class="proc-etape reveal d1">
            <div class="proc-num">2</div>
            <div class="proc-titre">Envoyez votre demande</div>
            <p class="proc-texte">Remplissez le formulaire de réservation. Gratuit et sans engagement. Nous vous répondons sous 48&nbsp;h.</p>
            <p class="proc-note">✅ Aucun paiement en ligne — remise en personne seulement.</p>
        </div>
        <div class="proc-etape reveal d2">
            <div class="proc-num">3</div>
            <div class="proc-titre">Accueillez votre compagnon</div>
            <p class="proc-texte">Nous organisons une rencontre au Québec. Carnet de santé et conseils personnalisés remis le jour de l'adoption.</p>
        </div>
    </div>
</section>

<!-- ============================================================
     CTA FINAL
     ============================================================ -->
<section class="cta-bandeau">
    <h2 class="cta-titre reveal">Votre compagnon ailé<br>vous attend peut-être</h2>
    <p class="cta-texte reveal">
        Les oiseaux disponibles partent vite — chaque oiseau ne peut être réservé qu'une seule fois.
    </p>
    <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"
       class="btn btn-or btn-lg reveal">Voir les oiseaux disponibles →</a>
</section>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

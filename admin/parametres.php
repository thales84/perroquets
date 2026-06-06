<?php
require_once __DIR__ . '/inc/securite.php';
require_once RACINE . '/modeles/parametre-modele.php';
require_once RACINE . '/modeles/espece-modele.php';

// Clés gérées par ce formulaire (sécurité : liste blanche).
$champs = [
    // Général
    'site_nom', 'site_slogan', 'meta_titre_gabarit', 'meta_description', 'partage_image',
    // Réseaux sociaux
    'og_locale', 'twitter_compte', 'social_facebook', 'social_instagram',
    // Contact & WhatsApp
    'whatsapp_numero', 'whatsapp_message',
    // Indexation & Analytics
    'index_autoriser', 'verif_google', 'verif_bing', 'ga4_id', 'gtm_id', 'pixel_id',
    // Page d'accueil
    'accueil_hero_pastille', 'accueil_hero_titre', 'accueil_hero_texte',
    'accueil_hero_cta', 'accueil_hero_image',
    'accueil_cta_titre', 'accueil_cta_texte', 'accueil_vedettes_nb',
];

// Espèces disponibles pour le choix des vedettes (cases à cocher).
$especes = listerEspeces();
// Slugs actuellement en vedette (stockés en CSV).
$vedettesActuelles = array_filter(array_map('trim', explode(',', param('accueil_vedettes_slugs', ''))));

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierJetonCsrf($_POST['csrf_token'] ?? '')) {
        $erreur = 'Jeton de sécurité invalide. Réessayez.';
    } else {
        $valeurs = [];
        foreach ($champs as $cle) {
            if ($cle === 'index_autoriser') {
                // Case à cocher : présente = 1, absente = 0.
                $valeurs[$cle] = isset($_POST['index_autoriser']) ? '1' : '0';
            } else {
                $valeurs[$cle] = trim($_POST[$cle] ?? '');
            }
        }

        // Espèces vedettes : cases à cocher → CSV de slugs valides uniquement.
        $slugsValides  = array_column($especes, 'slug_fr');
        $vedettesPostees = $_POST['vedettes'] ?? [];
        $vedettesPostees = is_array($vedettesPostees) ? $vedettesPostees : [];
        $vedettesPostees = array_values(array_intersect($vedettesPostees, $slugsValides));
        $valeurs['accueil_vedettes_slugs'] = implode(',', $vedettesPostees);

        enregistrerParametres($valeurs);
        // PRG : redirige pour recharger les valeurs fraîches.
        header('Location: ' . URL_SITE . '/admin/parametres.php?succes=1');
        exit;
    }
}

// Aide d'affichage : valeur échappée d'un paramètre.
$val = fn(string $cle, string $defaut = ''): string => echapper(param($cle, $defaut));

$titrePage = 'Paramètres';
require_once __DIR__ . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Paramètres du site</h1>
    </div>

    <?php if (isset($_GET['succes'])) : ?>
        <div class="alerte alerte--succes" role="status">Paramètres enregistrés.</div>
    <?php endif; ?>

    <?php if ($erreur) : ?>
        <div class="alerte alerte--erreur" role="alert"><?= echapper($erreur) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= echapper(URL_SITE) ?>/admin/parametres.php" class="formulaire-admin">
        <input type="hidden" name="csrf_token" value="<?= echapper(genererJetonCsrf()) ?>">

        <!-- Barre d'onglets -->
        <div class="param-onglets" role="tablist" aria-label="Sections des paramètres">
            <button type="button" class="param-onglet actif" role="tab" data-cible="panel-general">Général</button>
            <button type="button" class="param-onglet" role="tab" data-cible="panel-accueil">Page d'accueil</button>
            <button type="button" class="param-onglet" role="tab" data-cible="panel-social">Réseaux sociaux</button>
            <button type="button" class="param-onglet" role="tab" data-cible="panel-contact">Contact &amp; WhatsApp</button>
            <button type="button" class="param-onglet" role="tab" data-cible="panel-indexation">Indexation &amp; Analytics</button>
        </div>

        <!-- ===================== Général ===================== -->
        <section class="param-section param-panneau actif" id="panel-general" role="tabpanel">
            <h2>Général</h2>

            <div class="champ">
                <label for="site_nom">Nom du site</label>
                <input type="text" id="site_nom" name="site_nom" value="<?= $val('site_nom', 'Maple Perroquets') ?>" maxlength="60">
            </div>

            <div class="champ">
                <label for="site_slogan">Slogan</label>
                <input type="text" id="site_slogan" name="site_slogan" value="<?= $val('site_slogan') ?>" maxlength="160">
            </div>

            <div class="champ">
                <label for="meta_titre_gabarit">Gabarit de titre</label>
                <input type="text" id="meta_titre_gabarit" name="meta_titre_gabarit" value="<?= $val('meta_titre_gabarit', '%page% — %site%') ?>" maxlength="80">
                <small class="texte-discret">Variables disponibles : <code>%page%</code> (titre de la page), <code>%site%</code> (nom du site).</small>
            </div>

            <div class="champ">
                <label for="meta_description">Meta description par défaut</label>
                <textarea id="meta_description" name="meta_description" rows="3" maxlength="320"><?= $val('meta_description') ?></textarea>
                <small class="texte-discret">Utilisée quand une page n'a pas sa propre description. Idéal : 150–160 caractères.</small>
            </div>

            <div class="champ">
                <label for="partage_image">Image de partage par défaut (URL absolue)</label>
                <input type="url" id="partage_image" name="partage_image" value="<?= $val('partage_image') ?>" placeholder="https://mapleperroquets.com/assets/img/partage.jpg">
                <small class="texte-discret">Affichée lors d'un partage (Open Graph / Twitter) si la page n'a pas d'image propre. Recommandé : 1200×630 px.</small>
            </div>
        </section>

        <!-- ===================== Page d'accueil ===================== -->
        <section class="param-section param-panneau" id="panel-accueil" role="tabpanel" hidden>
            <h2>Page d'accueil</h2>

            <h3 class="param-sous-titre">Héros (bloc d'en-tête)</h3>

            <div class="champ">
                <label for="accueil_hero_pastille">Pastille (petit texte au-dessus du titre)</label>
                <input type="text" id="accueil_hero_pastille" name="accueil_hero_pastille" value="<?= $val('accueil_hero_pastille') ?>" placeholder="Élevage canadien · Oiseaux élevés à la main" maxlength="80">
            </div>

            <div class="champ">
                <label for="accueil_hero_titre">Titre principal</label>
                <input type="text" id="accueil_hero_titre" name="accueil_hero_titre" value="<?= $val('accueil_hero_titre') ?>" placeholder="Des oiseaux passionnément élevés pour vous" maxlength="120">
                <small class="texte-discret">Laisser vide pour garder le titre stylé par défaut (avec mise en valeur).</small>
            </div>

            <div class="champ">
                <label for="accueil_hero_texte">Texte d'introduction</label>
                <textarea id="accueil_hero_texte" name="accueil_hero_texte" rows="3" maxlength="320"><?= $val('accueil_hero_texte') ?></textarea>
            </div>

            <div class="champ">
                <label for="accueil_hero_cta">Libellé du bouton principal</label>
                <input type="text" id="accueil_hero_cta" name="accueil_hero_cta" value="<?= $val('accueil_hero_cta') ?>" placeholder="Voir les oiseaux disponibles" maxlength="40">
            </div>

            <div class="champ">
                <label for="accueil_hero_image">Image du héros (chemin ou URL)</label>
                <input type="text" id="accueil_hero_image" name="accueil_hero_image" value="<?= $val('accueil_hero_image') ?>" placeholder="assets/img/hero-perroquet.jpg" maxlength="255">
                <small class="texte-discret">Chemin relatif (ex. <code>assets/img/hero-perroquet.jpg</code>) ou URL absolue. Vide = image par défaut.</small>
            </div>

            <h3 class="param-sous-titre">Espèces vedettes</h3>

            <div class="champ">
                <label>Espèces mises en avant sur l'accueil</label>
                <?php if (empty($especes)) : ?>
                    <p class="texte-discret">Aucune espèce en base. Ajoutez des espèces pour les mettre en vedette.</p>
                <?php else : ?>
                    <div class="param-vedettes">
                        <?php foreach ($especes as $e) :
                            $slug = $e['slug_fr'] ?? '';
                        ?>
                        <label class="champ-bascule">
                            <input type="checkbox" name="vedettes[]" value="<?= echapper($slug) ?>"
                                   <?= in_array($slug, $vedettesActuelles, true) ? 'checked' : '' ?>>
                            <?= echapper($e['nom_commun_fr']) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <small class="texte-discret">Si aucune case n'est cochée, l'accueil affiche un choix par défaut.</small>
                <?php endif; ?>
            </div>

            <div class="champ">
                <label for="accueil_vedettes_nb">Nombre maximum de vedettes affichées</label>
                <input type="number" id="accueil_vedettes_nb" name="accueil_vedettes_nb" value="<?= $val('accueil_vedettes_nb', '4') ?>" min="1" max="8" step="1">
                <small class="texte-discret">La grille est conçue pour 4. Au-delà, les cartes passent sur plusieurs rangées.</small>
            </div>

            <h3 class="param-sous-titre">Bandeau d'appel à l'action (bas de page)</h3>

            <div class="champ">
                <label for="accueil_cta_titre">Titre du bandeau</label>
                <input type="text" id="accueil_cta_titre" name="accueil_cta_titre" value="<?= $val('accueil_cta_titre') ?>" placeholder="Votre compagnon ailé vous attend peut-être" maxlength="120">
            </div>

            <div class="champ">
                <label for="accueil_cta_texte">Texte du bandeau</label>
                <textarea id="accueil_cta_texte" name="accueil_cta_texte" rows="2" maxlength="240"><?= $val('accueil_cta_texte') ?></textarea>
            </div>
        </section>

        <!-- ===================== Réseaux sociaux ===================== -->
        <section class="param-section param-panneau" id="panel-social" role="tabpanel" hidden>
            <h2>Réseaux sociaux</h2>

            <div class="champ">
                <label for="og_locale">Locale Open Graph</label>
                <input type="text" id="og_locale" name="og_locale" value="<?= $val('og_locale', 'fr_CA') ?>" maxlength="10">
                <small class="texte-discret">Format : <code>fr_CA</code> (français Canada).</small>
            </div>

            <div class="champ">
                <label for="twitter_compte">Compte Twitter / X</label>
                <input type="text" id="twitter_compte" name="twitter_compte" value="<?= $val('twitter_compte') ?>" placeholder="@mapleperroquets" maxlength="30">
            </div>

            <div class="champ">
                <label for="social_facebook">Page Facebook (URL)</label>
                <input type="url" id="social_facebook" name="social_facebook" value="<?= $val('social_facebook') ?>" placeholder="https://facebook.com/...">
            </div>

            <div class="champ">
                <label for="social_instagram">Compte Instagram (URL)</label>
                <input type="url" id="social_instagram" name="social_instagram" value="<?= $val('social_instagram') ?>" placeholder="https://instagram.com/...">
            </div>
        </section>

        <!-- ===================== Contact & WhatsApp ===================== -->
        <section class="param-section param-panneau" id="panel-contact" role="tabpanel" hidden>
            <h2>Contact &amp; WhatsApp</h2>

            <div class="champ">
                <label for="whatsapp_numero">Numéro WhatsApp</label>
                <input type="text" id="whatsapp_numero" name="whatsapp_numero" value="<?= $val('whatsapp_numero') ?>" placeholder="15145551234" maxlength="20">
                <small class="texte-discret">Format international, chiffres uniquement (Canada = <code>1</code> + indicatif + numéro). Ex. <code>15145551234</code>. Laisser vide = pas de bouton.</small>
            </div>

            <div class="champ">
                <label for="whatsapp_message">Message pré-rempli</label>
                <textarea id="whatsapp_message" name="whatsapp_message" rows="2" maxlength="300"><?= $val('whatsapp_message', 'Bonjour, je suis intéressé par vos perroquets.') ?></textarea>
                <small class="texte-discret">Texte inséré automatiquement dans la conversation quand le visiteur clique le bouton flottant.</small>
            </div>
        </section>

        <!-- ===================== Indexation & Analytics ===================== -->
        <section class="param-section param-panneau" id="panel-indexation" role="tabpanel" hidden>
            <h2>Indexation &amp; Analytics</h2>

            <div class="champ">
                <label class="champ-bascule">
                    <input type="checkbox" name="index_autoriser" value="1" <?= param('index_autoriser', '1') === '1' ? 'checked' : '' ?>>
                    Autoriser l'indexation par les moteurs de recherche
                </label>
                <small class="texte-discret">Décocher = <code>noindex, nofollow</code> sur tout le site (utile en construction/maintenance).</small>
            </div>

            <div class="champ">
                <label for="verif_google">Vérification Google Search Console</label>
                <input type="text" id="verif_google" name="verif_google" value="<?= $val('verif_google') ?>" placeholder="contenu de la balise meta google-site-verification">
            </div>

            <div class="champ">
                <label for="verif_bing">Vérification Bing Webmaster</label>
                <input type="text" id="verif_bing" name="verif_bing" value="<?= $val('verif_bing') ?>" placeholder="contenu de la balise meta msvalidate.01">
            </div>

            <div class="champ">
                <label for="ga4_id">Google Analytics 4 (ID de mesure)</label>
                <input type="text" id="ga4_id" name="ga4_id" value="<?= $val('ga4_id') ?>" placeholder="G-XXXXXXXXXX" maxlength="20">
            </div>

            <div class="champ">
                <label for="gtm_id">Google Tag Manager (ID conteneur)</label>
                <input type="text" id="gtm_id" name="gtm_id" value="<?= $val('gtm_id') ?>" placeholder="GTM-XXXXXXX" maxlength="20">
            </div>

            <div class="champ">
                <label for="pixel_id">Meta (Facebook) Pixel — ID</label>
                <input type="text" id="pixel_id" name="pixel_id" value="<?= $val('pixel_id') ?>" placeholder="1234567890" maxlength="30">
            </div>
        </section>

        <div class="form-actions">
            <button type="submit" class="bouton bouton-primaire">Enregistrer les paramètres</button>
        </div>
    </form>
</div>

<script>
/* Onglets des paramètres — bascule sans rechargement, onglet mémorisé */
(function () {
    var onglets  = document.querySelectorAll('.param-onglet');
    var panneaux = document.querySelectorAll('.param-panneau');
    if (!onglets.length) return;

    function activer(cible) {
        onglets.forEach(function (o) {
            var actif = o.dataset.cible === cible;
            o.classList.toggle('actif', actif);
            o.setAttribute('aria-selected', actif ? 'true' : 'false');
        });
        panneaux.forEach(function (p) {
            var actif = p.id === cible;
            p.classList.toggle('actif', actif);
            p.hidden = !actif;
        });
        try { sessionStorage.setItem('param_onglet', cible); } catch (e) {}
    }

    onglets.forEach(function (o) {
        o.addEventListener('click', function () { activer(o.dataset.cible); });
    });

    // Restaurer le dernier onglet ouvert (après enregistrement notamment)
    var memo = null;
    try { memo = sessionStorage.getItem('param_onglet'); } catch (e) {}
    if (memo && document.getElementById(memo)) {
        activer(memo);
    }
})();
</script>

<?php require_once __DIR__ . '/inc/pied-admin.php'; ?>

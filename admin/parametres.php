<?php
require_once __DIR__ . '/inc/securite.php';
require_once RACINE . '/modeles/parametre-modele.php';

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
];

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

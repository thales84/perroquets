<?php
require_once __DIR__ . '/../modeles/oiseau-modele.php';
require_once __DIR__ . '/../modeles/reservation-modele.php';

$slug   = obtenirParametreUrl('slug');
$langue = langueActive();

$oiseau = $slug ? recupererOiseauParSlug($slug) : null;

if (!$oiseau) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

$disponible = $oiseau['statut'] === 'disponible';

// Provinces et territoires canadiens
$provinces = [
    'AB' => 'Alberta',
    'BC' => 'Colombie-Britannique',
    'MB' => 'Manitoba',
    'NB' => 'Nouveau-Brunswick',
    'NL' => 'Terre-Neuve-et-Labrador',
    'NS' => 'Nouvelle-Écosse',
    'NT' => 'Territoires du Nord-Ouest',
    'NU' => 'Nunavut',
    'ON' => 'Ontario',
    'PE' => 'Île-du-Prince-Édouard',
    'QC' => 'Québec',
    'SK' => 'Saskatchewan',
    'YT' => 'Yukon',
];

$erreurs = [];
$valeurs = [
    'nom'      => '',
    'email'    => '',
    'tel'      => '',
    'province' => '',
    'message'  => '',
];

// --- Traitement POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $disponible) {

    // Honeypot : champ caché — doit rester vide
    if (!empty($_POST['adresse_site'])) {
        // Rejet silencieux : simuler une réussite
        rediriger('/' . $langue . '/confirmation');
    }

    // Récupération et nettoyage des entrées
    $valeurs['nom']      = trim($_POST['nom'] ?? '');
    $valeurs['email']    = trim($_POST['email'] ?? '');
    $valeurs['tel']      = trim($_POST['tel'] ?? '');
    $valeurs['province'] = trim($_POST['province'] ?? '');
    $valeurs['message']  = trim($_POST['message'] ?? '');
    $idOiseau            = (int) ($_POST['id_oiseau'] ?? 0);
    $langueForm          = in_array($_POST['langue_demande'] ?? '', ['fr', 'en'], true)
                           ? $_POST['langue_demande'] : 'fr';

    // Validation serveur
    if ($valeurs['nom'] === '') {
        $erreurs['nom'] = t('resa_champ_requis');
    }

    if ($valeurs['email'] === '') {
        $erreurs['email'] = t('resa_champ_requis');
    } elseif (!filter_var($valeurs['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = t('resa_email_invalide');
    }

    if ($valeurs['tel'] !== '') {
        // Validation souple : au moins 10 chiffres, caractères nord-américains autorisés
        $chiffres = preg_replace('/\D/', '', $valeurs['tel']);
        if (strlen($chiffres) < 10 || strlen($chiffres) > 15) {
            $erreurs['tel'] = 'Format invalide (ex. 514-555-0192).';
        }
    }

    if ($valeurs['province'] !== '' && !array_key_exists($valeurs['province'], $provinces)) {
        $erreurs['province'] = t('resa_champ_requis');
    }

    // Sécurité : vérifier que l'id_oiseau correspond bien au slug courant
    if ($idOiseau !== (int) $oiseau['id_oiseau']) {
        $erreurs['_global'] = t('resa_erreur');
    }

    if (empty($erreurs)) {
        $ok = enregistrerReservation([
            'id_oiseau'      => $idOiseau,
            'nom_client'     => $valeurs['nom'],
            'email_client'   => $valeurs['email'],
            'telephone'      => $valeurs['tel'],
            'province'       => $provinces[$valeurs['province']] ?? '',
            'message'        => $valeurs['message'],
            'langue_demande' => $langueForm,
        ]);

        if ($ok) {
            // POST-Redirect-GET : évite la double soumission au rafraîchissement
            rediriger('/' . $langue . '/confirmation');
        } else {
            $erreurs['_global'] = t('resa_erreur');
        }

        // Emplacement pour notification courriel au propriétaire :
        // mail('info@mapleperroquets.com', 'Nouvelle réservation', ...);
    }
}

// --- SEO ---
$titrePage       = t('resa_titre') . ' — ' . ($oiseau['espece_nom_fr'] ?? '');
$descriptionPage = t('resa_intro');
$urlCanonique    = URL_SITE . '/' . $langue . '/reservation/' . $oiseau['slug_fr'];

// Photo principale
$photos = recupererPhotosOiseau((int) $oiseau['id_oiseau']);
$photoPrincipale = null;
foreach ($photos as $p) {
    if ($p['est_principale']) {
        $photoPrincipale = $p;
        break;
    }
}

require_once __DIR__ . '/../gabarits/entete.php';
?>

<div class="conteneur">

    <nav class="fil-ariane" aria-label="Fil d'Ariane">
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"><?= echapper(t('liste_titre')) ?></a>
        <span aria-hidden="true"> › </span>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseau/<?= echapper($oiseau['slug_fr']) ?>"><?= echapper($oiseau['espece_nom_fr'] ?? '') ?></a>
        <span aria-hidden="true"> › </span>
        <span aria-current="page"><?= echapper(t('resa_titre')) ?></span>
    </nav>

    <h1><?= echapper(t('resa_titre')) ?></h1>

    <?php if (!$disponible) : ?>
        <div class="fiche-indisponible" role="alert">
            Cet oiseau n'est plus disponible à la réservation.
            <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"><?= echapper(t('liste_titre')) ?></a>
        </div>
    <?php else : ?>

        <!-- Rappel de l'oiseau -->
        <div class="resa-rappel">
            <?php if ($photoPrincipale) : ?>
                <img src="<?= echapper($photoPrincipale['chemin_fichier']) ?>"
                     alt="<?= echapper($photoPrincipale['texte_alt_fr'] ?? '') ?>"
                     class="resa-rappel__photo"
                     loading="lazy">
            <?php endif; ?>
            <div class="resa-rappel__infos">
                <p class="resa-rappel__espece"><?= echapper($oiseau['espece_nom_fr'] ?? '') ?></p>
                <p><?= echapper(t('sexe_' . $oiseau['sexe'])) ?>
                    <?= $oiseau['sevre_main'] ? ' · ' . echapper(t('fiche_eam_oui')) : '' ?></p>
                <p class="resa-rappel__prix"><?= echapper(formaterPrixCad($oiseau['prix_cad'])) ?></p>
            </div>
        </div>

        <p class="marge-bas-1"><?= echapper(t('resa_intro')) ?></p>

        <?php if (!empty($erreurs['_global'])) : ?>
            <div class="resa-erreur-global" role="alert"><?= echapper($erreurs['_global']) ?></div>
        <?php endif; ?>

        <form class="resa-formulaire" method="post"
              action="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/reservation/<?= echapper($oiseau['slug_fr']) ?>"
              novalidate>

            <!-- Champs cachés -->
            <input type="hidden" name="id_oiseau"      value="<?= echapper($oiseau['id_oiseau']) ?>">
            <input type="hidden" name="langue_demande" value="<?= echapper($langue) ?>">

            <!-- Honeypot : invisible, ne doit jamais être rempli -->
            <div class="resa-honeypot" aria-hidden="true">
                <label for="adresse_site">Ne pas remplir</label>
                <input type="text" id="adresse_site" name="adresse_site" tabindex="-1" autocomplete="off">
            </div>

            <div class="champ <?= isset($erreurs['nom']) ? 'champ--erreur' : '' ?>">
                <label for="nom"><?= echapper(t('resa_nom')) ?> <span aria-hidden="true">*</span></label>
                <input type="text" id="nom" name="nom"
                       value="<?= echapper($valeurs['nom']) ?>"
                       autocomplete="name"
                       required
                       aria-describedby="<?= isset($erreurs['nom']) ? 'erreur-nom' : '' ?>">
                <?php if (isset($erreurs['nom'])) : ?>
                    <span class="champ__erreur" id="erreur-nom" role="alert"><?= echapper($erreurs['nom']) ?></span>
                <?php endif; ?>
            </div>

            <div class="champ <?= isset($erreurs['email']) ? 'champ--erreur' : '' ?>">
                <label for="email"><?= echapper(t('resa_email')) ?> <span aria-hidden="true">*</span></label>
                <input type="email" id="email" name="email"
                       value="<?= echapper($valeurs['email']) ?>"
                       autocomplete="email"
                       required
                       aria-describedby="<?= isset($erreurs['email']) ? 'erreur-email' : '' ?>">
                <?php if (isset($erreurs['email'])) : ?>
                    <span class="champ__erreur" id="erreur-email" role="alert"><?= echapper($erreurs['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="champ <?= isset($erreurs['tel']) ? 'champ--erreur' : '' ?>">
                <label for="tel"><?= echapper(t('resa_telephone')) ?></label>
                <input type="tel" id="tel" name="tel"
                       value="<?= echapper($valeurs['tel']) ?>"
                       autocomplete="tel"
                       placeholder="514-555-0192"
                       aria-describedby="<?= isset($erreurs['tel']) ? 'erreur-tel' : '' ?>">
                <?php if (isset($erreurs['tel'])) : ?>
                    <span class="champ__erreur" id="erreur-tel" role="alert"><?= echapper($erreurs['tel']) ?></span>
                <?php endif; ?>
            </div>

            <div class="champ <?= isset($erreurs['province']) ? 'champ--erreur' : '' ?>">
                <label for="province"><?= echapper(t('resa_province')) ?></label>
                <select id="province" name="province" autocomplete="address-level1">
                    <option value="">— Choisir —</option>
                    <?php foreach ($provinces as $code => $nom) : ?>
                        <option value="<?= echapper($code) ?>"
                            <?= $valeurs['province'] === $code ? 'selected' : '' ?>>
                            <?= echapper($nom) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($erreurs['province'])) : ?>
                    <span class="champ__erreur" id="erreur-province" role="alert"><?= echapper($erreurs['province']) ?></span>
                <?php endif; ?>
            </div>

            <div class="champ">
                <label for="message"><?= echapper(t('resa_message')) ?></label>
                <textarea id="message" name="message" rows="5"><?= echapper($valeurs['message']) ?></textarea>
            </div>

            <button type="submit" class="bouton bouton-secondaire"><?= echapper(t('resa_envoyer')) ?></button>

        </form>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

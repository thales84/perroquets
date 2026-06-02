<?php
require_once __DIR__ . '/../modeles/oiseau-modele.php';
require_once __DIR__ . '/../modeles/reservation-modele.php';
require_once __DIR__ . '/../modeles/client-modele.php';

$slug   = obtenirParametreUrl('slug');
$langue = langueActive();

// Données client connecté pour pré-remplissage
$clientConnecteId = idClientConnecte();
$clientData       = $clientConnecteId ? recupererClientParId($clientConnecteId) : null;

$oiseau = $slug ? recupererOiseauParSlug($slug) : null;
if (!$oiseau) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

$disponible  = $oiseau['statut'] === 'disponible';
// Gate : affiché uniquement sur GET, si l'oiseau est disponible et que le visiteur
// n'est pas connecté ET n'a pas choisi de continuer en tant qu'invité.
$montrerGate = $disponible
    && !$clientConnecteId
    && $_SERVER['REQUEST_METHOD'] === 'GET'
    && !isset($_GET['invite']);

$provinces = [
    'AB' => 'Alberta', 'BC' => 'Colombie-Britannique', 'MB' => 'Manitoba',
    'NB' => 'Nouveau-Brunswick', 'NL' => 'Terre-Neuve-et-Labrador', 'NS' => 'Nouvelle-Écosse',
    'NT' => 'Territoires du Nord-Ouest', 'NU' => 'Nunavut', 'ON' => 'Ontario',
    'PE' => 'Île-du-Prince-Édouard', 'QC' => 'Québec', 'SK' => 'Saskatchewan', 'YT' => 'Yukon',
];

$erreurs = [];
// Pré-remplissage depuis le compte client si connecté
$valeurs = [
    'nom'      => $clientData ? trim($clientData['prenom'] . ' ' . $clientData['nom']) : '',
    'email'    => $clientData['email']     ?? '',
    'tel'      => $clientData['telephone'] ?? '',
    'province' => '',
    'message'  => '',
];
// Province — trouver le code depuis le nom complet stocké en base
if ($clientData && $clientData['province']) {
    $codesProvinces = [
        'Alberta'=>'AB','Colombie-Britannique'=>'BC','Manitoba'=>'MB','Nouveau-Brunswick'=>'NB',
        'Terre-Neuve-et-Labrador'=>'NL','Nouvelle-Écosse'=>'NS','Territoires du Nord-Ouest'=>'NT',
        'Nunavut'=>'NU','Ontario'=>'ON','Île-du-Prince-Édouard'=>'PE','Québec'=>'QC',
        'Saskatchewan'=>'SK','Yukon'=>'YT',
    ];
    $valeurs['province'] = $codesProvinces[$clientData['province']] ?? '';
}

/* Traitement POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $disponible) {

    /* Honeypot */
    if (!empty($_POST['adresse_site'])) {
        rediriger('/' . $langue . '/confirmation');
    }

    $valeurs['nom']      = trim($_POST['nom']      ?? '');
    $valeurs['email']    = trim($_POST['email']    ?? '');
    $valeurs['tel']      = trim($_POST['tel']      ?? '');
    $valeurs['province'] = trim($_POST['province'] ?? '');
    $valeurs['message']  = trim($_POST['message']  ?? '');
    $idOiseau            = (int) ($_POST['id_oiseau'] ?? 0);
    $langueForm          = in_array($_POST['langue_demande'] ?? '', ['fr', 'en'], true)
                           ? $_POST['langue_demande'] : 'fr';

    if ($valeurs['nom'] === '')                                      $erreurs['nom']      = t('resa_champ_requis');
    if ($valeurs['email'] === '')                                    $erreurs['email']    = t('resa_champ_requis');
    elseif (!filter_var($valeurs['email'], FILTER_VALIDATE_EMAIL))   $erreurs['email']    = t('resa_email_invalide');
    if ($valeurs['tel'] !== '') {
        $chiffres = preg_replace('/\D/', '', $valeurs['tel']);
        if (strlen($chiffres) < 10 || strlen($chiffres) > 15)       $erreurs['tel']      = 'Format invalide (ex. 514-555-0192).';
    }
    if ($valeurs['province'] !== '' && !array_key_exists($valeurs['province'], $provinces))
                                                                     $erreurs['province'] = t('resa_champ_requis');
    if ($idOiseau !== (int) $oiseau['id_oiseau'])                    $erreurs['_global']  = t('resa_erreur');

    if (empty($erreurs)) {
        $ok = enregistrerReservation([
            'client_id'      => $clientConnecteId, // null si invité
            'id_oiseau'      => $idOiseau,
            'nom_client'     => $valeurs['nom'],
            'email_client'   => $valeurs['email'],
            'telephone'      => $valeurs['tel'],
            'province'       => $provinces[$valeurs['province']] ?? '',
            'message'        => $valeurs['message'],
            'langue_demande' => $langueForm,
        ]);
        if ($ok) {
            // Client connecté → tableau de bord avec confirmation, sinon page générique
            if ($clientConnecteId) {
                rediriger('/' . $langue . '/mon-compte?succes=reservation');
            } else {
                rediriger('/' . $langue . '/confirmation');
            }
        } else {
            $erreurs['_global'] = t('resa_erreur');
        }
    }
}

/* Photo principale */
$photos = recupererPhotosOiseau((int) $oiseau['id_oiseau']);
$photoPrincipale = null;
foreach ($photos as $p) {
    if ($p['est_principale']) { $photoPrincipale = $p; break; }
}

$titrePage       = 'Réserver ' . ($oiseau['espece_nom_fr'] ?? '');
$descriptionPage = t('resa_intro');
$urlCanonique    = URL_SITE . '/' . $langue . '/reservation/' . $oiseau['slug_fr'];

require_once __DIR__ . '/../gabarits/entete.php';
?>

<section class="section">
    <nav class="fil-ariane" aria-label="Fil d'Ariane">
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Oiseaux disponibles</a>
        <span class="sep" aria-hidden="true">›</span>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseau/<?= echapper($oiseau['slug_fr']) ?>">
            <?= echapper($oiseau['espece_nom_fr'] ?? '') ?>
        </a>
        <span class="sep" aria-hidden="true">›</span>
        <span aria-current="page">Réservation</span>
    </nav>

    <h1 class="s-titre"><?= echapper(t('resa_titre')) ?></h1>

    <?php if (!$disponible): ?>
    <div class="fiche-indispo" role="alert">
        Cet oiseau n'est plus disponible à la réservation.
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Voir les autres oiseaux</a>
    </div>

    <?php elseif ($montrerGate):
        /* ----------------------------------------------------------------
           GATE — visiteur non connecté : proposer connexion / inscription
           avant l'accès au formulaire
           ---------------------------------------------------------------- */
        $urlRetour = '/' . $langue . '/reservation/' . $oiseau['slug_fr'];
        $urlConnexion   = echapper(URL_SITE . '/' . $langue . '/connexion?retour='   . urlencode($urlRetour));
        $urlInscription = echapper(URL_SITE . '/' . $langue . '/inscription?retour=' . urlencode($urlRetour));
        $urlInvite      = echapper(URL_SITE . '/' . $langue . '/reservation/'  . $oiseau['slug_fr'] . '?invite=1');
    ?>
    <div class="resa-gate">
        <div class="resa-gate-entete">
            <div class="resa-gate-oiseau">
                <?php if ($photoPrincipale): ?>
                <img src="<?= echapper(URL_SITE . $photoPrincipale['chemin_fichier']) ?>"
                     alt="<?= echapper($oiseau['espece_nom_fr'] ?? '') ?>"
                     class="resa-gate-img" loading="lazy"
                     onerror="this.style.display='none'">
                <?php else: ?>
                <div class="resa-gate-emoji" aria-hidden="true">🦜</div>
                <?php endif; ?>
            </div>
            <h2 class="resa-gate-titre">Vous réservez</h2>
            <p class="resa-gate-espece"><?= echapper($oiseau['espece_nom_fr'] ?? '') ?></p>
            <p class="resa-gate-sous">
                Connectez-vous pour que le formulaire se remplisse automatiquement
                et retrouver votre réservation dans votre tableau de bord.
            </p>
        </div>

        <div class="resa-gate-options">
            <div class="gate-option">
                <div class="gate-option-icone">👤</div>
                <h3>J'ai déjà un compte</h3>
                <p>Formulaire pré-rempli instantanément. Réservation visible dans votre historique.</p>
                <a href="<?= $urlConnexion ?>" class="btn btn-primaire btn-full">Se connecter</a>
            </div>
            <div class="gate-ou" aria-hidden="true">ou</div>
            <div class="gate-option">
                <div class="gate-option-icone">✨</div>
                <h3>Créer un compte</h3>
                <p>Gratuit et rapide. Suivez toutes vos réservations depuis un seul endroit.</p>
                <a href="<?= $urlInscription ?>" class="btn btn-jungle btn-full">Créer mon compte</a>
            </div>
        </div>

        <p class="gate-invite-lien">
            <a href="<?= $urlInvite ?>">Continuer sans compte →</a>
        </p>
    </div>

    <?php else: ?>

    <div class="resa-layout">

        <!-- Rappel oiseau -->
        <div class="resa-rappel">
            <?php if ($photoPrincipale): ?>
            <img src="<?= echapper(URL_SITE . $photoPrincipale['chemin_fichier']) ?>"
                 alt="<?= echapper($photoPrincipale['texte_alt_fr'] ?? '') ?>"
                 class="resa-rappel-img" loading="lazy">
            <?php else: ?>
            <div style="font-size:5rem;text-align:center;padding:1.5rem 0;">🦜</div>
            <?php endif; ?>
            <div class="resa-rappel-espece"><?= echapper($oiseau['espece_nom_fr'] ?? '') ?></div>
            <p class="resa-rappel-detail">
                <?= echapper(t('sexe_' . $oiseau['sexe'])) ?>
                <?= $oiseau['sevre_main'] ? ' · Sevré à la main' : '' ?>
            </p>
            <p class="resa-rappel-prix"><?= echapper(formaterPrixCad($oiseau['prix_cad'])) ?></p>
        </div>

        <!-- Formulaire -->
        <div>
            <?php if ($clientConnecteId): ?>
            <div class="resa-info-block" style="background:rgba(31,110,76,.07);border-color:rgba(31,110,76,.2);color:var(--jungle);">
                👤 Formulaire pré-rempli depuis votre compte. Cette réservation sera liée à votre tableau de bord.
            </div>
            <?php else: ?>
            <div class="resa-info-block">
                ✅ Aucun paiement en ligne requis. La remise se fait en personne, au Québec.
            </div>
            <?php endif; ?>

            <?php if (!empty($erreurs['_global'])): ?>
            <div class="resa-erreur-global" role="alert"><?= echapper($erreurs['_global']) ?></div>
            <?php endif; ?>

            <form class="form-resa" method="post"
                  action="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/reservation/<?= echapper($oiseau['slug_fr']) ?>"
                  novalidate>

                <input type="hidden" name="id_oiseau"      value="<?= echapper($oiseau['id_oiseau']) ?>">
                <input type="hidden" name="langue_demande" value="<?= echapper($langue) ?>">

                <!-- Honeypot -->
                <div class="resa-honeypot" aria-hidden="true">
                    <label for="adresse_site">Ne pas remplir</label>
                    <input type="text" id="adresse_site" name="adresse_site" tabindex="-1" autocomplete="off">
                </div>

                <div class="champ <?= isset($erreurs['nom']) ? 'champ--erreur' : '' ?>">
                    <label for="nom"><?= echapper(t('resa_nom')) ?> *</label>
                    <input type="text" id="nom" name="nom" value="<?= echapper($valeurs['nom']) ?>"
                           autocomplete="name" required>
                    <?php if (isset($erreurs['nom'])): ?>
                    <span class="champ__erreur" role="alert"><?= echapper($erreurs['nom']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="champ <?= isset($erreurs['email']) ? 'champ--erreur' : '' ?>">
                    <label for="email"><?= echapper(t('resa_email')) ?> *</label>
                    <input type="email" id="email" name="email" value="<?= echapper($valeurs['email']) ?>"
                           autocomplete="email" required>
                    <?php if (isset($erreurs['email'])): ?>
                    <span class="champ__erreur" role="alert"><?= echapper($erreurs['email']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="champ <?= isset($erreurs['tel']) ? 'champ--erreur' : '' ?>">
                    <label for="tel"><?= echapper(t('resa_telephone')) ?></label>
                    <input type="tel" id="tel" name="tel" value="<?= echapper($valeurs['tel']) ?>"
                           autocomplete="tel" placeholder="514-555-0192">
                    <?php if (isset($erreurs['tel'])): ?>
                    <span class="champ__erreur" role="alert"><?= echapper($erreurs['tel']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="champ <?= isset($erreurs['province']) ? 'champ--erreur' : '' ?>">
                    <label for="province"><?= echapper(t('resa_province')) ?></label>
                    <select id="province" name="province" autocomplete="address-level1">
                        <option value="">— Choisir —</option>
                        <?php foreach ($provinces as $code => $nom): ?>
                        <option value="<?= echapper($code) ?>"
                            <?= $valeurs['province'] === $code ? 'selected' : '' ?>>
                            <?= echapper($nom) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($erreurs['province'])): ?>
                    <span class="champ__erreur" role="alert"><?= echapper($erreurs['province']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="champ">
                    <label for="message"><?= echapper(t('resa_message')) ?></label>
                    <textarea id="message" name="message" rows="5"><?= echapper($valeurs['message']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primaire btn-lg">
                    <?= echapper(t('resa_envoyer')) ?>
                </button>
            </form>
        </div>
    </div>

    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

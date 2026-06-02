<?php
require_once __DIR__ . '/../modeles/client-modele.php';
require_once __DIR__ . '/../modeles/reservation-modele.php'; // genererReference(), etapesTimeline

$langue = langueActive();
exigerConnexionClient($langue);

$idClient = idClientConnecte();
$client   = recupererClientParId($idClient);

if (!$client) {
    session_destroy();
    header('Location: ' . URL_SITE . '/' . $langue . '/connexion');
    exit;
}

/* Section active */
$section = $_GET['section'] ?? 'reservations';
if (!in_array($section, ['reservations', 'profil'], true)) $section = 'reservations';

$csrf = genererJetonCsrfClient();

/* ---- Traitement POST (profil) ---- */
$flashSucces = '';
$erreursP    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $section === 'profil') {
    if (!verifierJetonCsrfClient($_POST['csrf_token'] ?? '')) {
        $erreursP['_global'] = 'Jeton invalide. Rechargez la page.';
    } else {
        $ok = mettreAJourProfil($idClient, [
            'province'  => $_POST['province']  ?? '',
            'telephone' => $_POST['telephone'] ?? '',
        ]);
        if ($ok) {
            $client = recupererClientParId($idClient); // recharger
            $flashSucces = 'Profil mis à jour.';
        } else {
            $erreursP['_global'] = 'Erreur lors de la mise à jour.';
        }
    }
}

/* ---- Données réservations ---- */
$reservations = recupererReservationsClient($idClient);

$stats = [
    'total'    => count($reservations),
    'nouvelle' => count(array_filter($reservations, fn($r) => $r['statut_reservation'] === 'nouvelle')),
    'traitee'  => count(array_filter($reservations, fn($r) => $r['statut_reservation'] === 'traitee')),
];

/* ---- SEO ---- */
$titrePage       = 'Mon compte';
$descriptionPage = 'Tableau de bord — Maple Perroquets.';

/* ---- Mapping statuts ---- */
$statutResa = [
    'nouvelle' => ['lib' => 'En attente',  'classe' => 'badge-resa-nouvelle'],
    'traitee'  => ['lib' => 'Traitée',     'classe' => 'badge-resa-traitee'],
    'annulee'  => ['lib' => 'Annulée',     'classe' => 'badge-resa-annulee'],
];

/* ---- Timeline helper ---- */
function etapesTimeline(array $r): array
{
    $sr = $r['statut_reservation'] ?? '';
    $sl = $r['statut_livraison']   ?? '';
    return [
        ['label' => 'Reçue',       'icone' => '📨', 'fait' => true],
        ['label' => 'Confirmée',   'icone' => '✅', 'fait' => $sr === 'traitee'],
        ['label' => 'Préparation', 'icone' => '📦', 'fait' => in_array($sl, ['en_preparation','expedie','livre'], true)],
        ['label' => 'Expédié',     'icone' => '🚚', 'fait' => in_array($sl, ['expedie','livre'], true)],
        ['label' => 'Remis',       'icone' => '🏠', 'fait' => $sl === 'livre'],
    ];
}

$urlsTransporteurs = [
    'Canada Post' => 'https://www.canadapost-postescanada.ca/track-reperage/en#/search?searchFor=',
    'Purolator'   => 'https://www.purolator.com/en/shipping/tracker?pin=',
    'FedEx'       => 'https://www.fedex.com/fedextrack/?tracknumbers=',
    'UPS'         => 'https://www.ups.com/track?tracknum=',
];

/* ---- Provinces ---- */
$provinces = [
    'AB'=>'Alberta','BC'=>'Colombie-Britannique','MB'=>'Manitoba','NB'=>'Nouveau-Brunswick',
    'NL'=>'Terre-Neuve-et-Labrador','NS'=>'Nouvelle-Écosse','NT'=>'Territoires du Nord-Ouest',
    'NU'=>'Nunavut','ON'=>'Ontario','PE'=>'Île-du-Prince-Édouard','QC'=>'Québec',
    'SK'=>'Saskatchewan','YT'=>'Yukon',
];
$codesProvinces = array_flip($provinces);
$codeProvince = $codesProvinces[$client['province'] ?? ''] ?? '';

/* ---- URL helper ---- */
$urlSection = fn(string $s) => echapper(URL_SITE . '/' . $langue . '/mon-compte?section=' . $s);

require_once __DIR__ . '/../gabarits/entete.php';
?>

<div class="dashboard-wrap">

    <!-- ================================================
         SIDEBAR
         ================================================ -->
    <aside class="dashboard-sidebar" aria-label="Navigation du compte">

        <div class="sidebar-profil">
            <div class="sidebar-avatar" aria-hidden="true">🦜</div>
            <div class="sidebar-nom"><?= echapper($client['prenom'] . ' ' . $client['nom']) ?></div>
            <div class="sidebar-email"><?= echapper($client['email']) ?></div>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= $urlSection('reservations') ?>"
               class="sidebar-nav-item <?= $section === 'reservations' ? 'actif' : '' ?>">
                <span class="sidebar-nav-icone">📋</span>
                Mes réservations
                <?php if ($stats['nouvelle'] > 0): ?>
                <span style="margin-left:auto;background:var(--ara);color:#fff;font-size:.68rem;font-weight:700;padding:.1rem .45rem;border-radius:2rem;">
                    <?= $stats['nouvelle'] ?>
                </span>
                <?php endif; ?>
            </a>
            <a href="<?= $urlSection('profil') ?>"
               class="sidebar-nav-item <?= $section === 'profil' ? 'actif' : '' ?>">
                <span class="sidebar-nav-icone">👤</span>
                Mon profil
            </a>
        </nav>

        <div class="sidebar-sep"></div>

        <div class="sidebar-bas">
            <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/suivi"
               class="sidebar-nav-item <?= $section === 'suivi' ? 'actif' : '' ?>">
                <span class="sidebar-nav-icone">📦</span>
                Suivi de colis
            </a>
            <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"
               class="sidebar-nav-item">
                <span class="sidebar-nav-icone">🦜</span>
                Voir les oiseaux
            </a>
            <form method="post" action="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/deconnexion">
                <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">
                <button type="submit" class="sidebar-nav-item" style="width:100%;background:none;border:none;cursor:pointer;text-align:left;">
                    <span class="sidebar-nav-icone">🔓</span>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <!-- ================================================
         MAIN
         ================================================ -->
    <div class="dashboard-main">

        <?php if (!empty($_GET['succes']) && $_GET['succes'] === 'reservation'): ?>
        <div class="dash-succes">
            ✅ Votre réservation a bien été envoyée ! Nous vous répondrons sous 48&nbsp;h.
        </div>
        <?php endif; ?>

        <?php if ($flashSucces): ?>
        <div class="dash-succes">✅ <?= echapper($flashSucces) ?></div>
        <?php endif; ?>

        <!-- ============ SECTION RÉSERVATIONS ============ -->
        <?php if ($section === 'reservations'): ?>

        <div class="dash-entete">
            <h1 class="dash-titre">Mes réservations</h1>
            <p class="dash-sous">Suivez l'état de toutes vos demandes en temps réel.</p>
        </div>

        <!-- Stats -->
        <div class="dash-stats">
            <div class="dash-stat dash-stat--total">
                <div class="dash-stat-nb"><?= $stats['total'] ?></div>
                <div class="dash-stat-label">Total</div>
            </div>
            <div class="dash-stat dash-stat--att">
                <div class="dash-stat-nb"><?= $stats['nouvelle'] ?></div>
                <div class="dash-stat-label">En attente</div>
            </div>
            <div class="dash-stat dash-stat--trait">
                <div class="dash-stat-nb"><?= $stats['traitee'] ?></div>
                <div class="dash-stat-label">Traitées</div>
            </div>
        </div>

        <!-- Liste réservations -->
        <div class="dash-section-titre">📋 Historique des demandes</div>

        <?php if (empty($reservations)): ?>
        <div class="resa-vide">
            <p>Vous n'avez pas encore de réservation.</p>
            <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"
               class="btn btn-primaire">Voir les oiseaux disponibles</a>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;background:var(--surface);border:1px solid var(--bordure);border-radius:var(--rayon);box-shadow:var(--ombre);">
            <table class="resa-table">
                <thead>
                    <tr>
                        <th>Oiseau</th>
                        <th>Date</th>
                        <th>Statut demande</th>
                        <th>Oiseau</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $r):
                        $st  = $statutResa[$r['statut_reservation']] ?? ['lib' => $r['statut_reservation'], 'classe' => ''];
                        $url = echapper(URL_SITE . '/' . $langue . '/oiseau/' . $r['oiseau_slug']);
                    ?>
                    <tr>
                        <td colspan="5" style="padding:.75rem 1rem;">
                            <div style="display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
                                <!-- Vignette -->
                                <div style="flex-shrink:0;">
                                    <?php if (!empty($r['photo_chemin'])): ?>
                                    <img src="<?= echapper(URL_SITE . $r['photo_chemin']) ?>"
                                         alt="" loading="lazy"
                                         style="width:4rem;height:4rem;object-fit:cover;border-radius:.65rem;"
                                         onerror="this.style.display='none'">
                                    <?php else: ?>
                                    <span style="font-size:2.5rem;">🦜</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Infos + timeline -->
                                <div style="flex:1;min-width:260px;">
                                    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:.6rem;">
                                        <strong class="resa-esp"><?= echapper($r['espece_nom']) ?></strong>
                                        <span class="badge-resa <?= echapper($st['classe']) ?>"><?= echapper($st['lib']) ?></span>
                                        <?php if (!empty($r['code_suivi'])): ?>
                                        <span class="resa-date">Suivi : <strong style="font-family:monospace;letter-spacing:.05em;"><?= echapper($r['code_suivi']) ?></strong></span>
                                        <?php endif; ?>
                                        <span class="resa-date"><?= echapper(formaterDate($r['date_demande'])) ?></span>
                                    </div>

                                    <!-- Timeline -->
                                    <div class="suivi-timeline" style="margin:0 0 .5rem;">
                                        <?php foreach (etapesTimeline($r) as $i => $etape):
                                            $fait   = $etape['fait'];
                                            $etapes = etapesTimeline($r);
                                            $actuel = $fait && ($i === count($etapes)-1 || !($etapes[$i+1]['fait'] ?? false));
                                            $cls    = $actuel ? 'actuel' : ($fait ? 'fait' : '');
                                        ?>
                                        <div class="suivi-etape <?= $cls ?>">
                                            <div class="suivi-point"><?= $fait ? '✓' : '' ?></div>
                                            <span class="suivi-label"><?= echapper($etape['label']) ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Transporteur + numéro de suivi -->
                                    <?php if (!empty($r['numero_suivi']) || !empty($r['transporteur'])): ?>
                                    <div class="suivi-transport">
                                        <?php if (!empty($r['transporteur'])): ?>
                                        <span><?= echapper($r['transporteur']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($r['numero_suivi'])): ?>
                                        <span style="font-family:monospace;"><?= echapper($r['numero_suivi']) ?></span>
                                        <?php
                                            $urlT = (isset($urlsTransporteurs[$r['transporteur']]))
                                                    ? $urlsTransporteurs[$r['transporteur']] . urlencode($r['numero_suivi'])
                                                    : null;
                                        ?>
                                        <?php if ($urlT): ?>
                                        <a href="<?= echapper($urlT) ?>" target="_blank" rel="noopener">Suivre →</a>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Actions -->
                                <div style="flex-shrink:0;display:flex;flex-direction:column;gap:.4rem;align-items:flex-end;">
                                    <?php if ($r['oiseau_statut'] !== 'vendu'): ?>
                                    <a href="<?= $url ?>" class="btn btn-ghost btn-sm">Voir la fiche</a>
                                    <?php endif; ?>
                                    <?php if (!empty($r['code_suivi'])): ?>
                                    <a href="<?= echapper(URL_SITE . '/' . $langue . '/suivi?code=' . $r['code_suivi']) ?>"
                                       class="btn btn-ghost btn-sm" style="font-size:.75rem;">
                                        🔍 Suivi public
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p style="margin-top:1rem;font-size:.82rem;color:var(--doux);">
            Question sur une réservation ?
            <a href="mailto:bonjour@mapleperroquets.com" style="color:var(--ara);">bonjour@mapleperroquets.com</a>
        </p>
        <?php endif; ?>

        <!-- ============ SECTION PROFIL ============ -->
        <?php elseif ($section === 'profil'): ?>

        <div class="dash-entete">
            <h1 class="dash-titre">Mon profil</h1>
            <p class="dash-sous">Vos informations personnelles. Email et nom non modifiables.</p>
        </div>

        <?php if (!empty($erreursP['_global'])): ?>
        <div class="auth-erreur" role="alert"><?= echapper($erreursP['_global']) ?></div>
        <?php endif; ?>

        <!-- Infos fixes -->
        <div class="dash-section-titre">👤 Informations du compte</div>
        <div style="background:var(--surface);border:1px solid var(--bordure);border-radius:var(--rayon);padding:1.5rem;box-shadow:var(--ombre);margin-bottom:2rem;max-width:540px;">
            <table style="width:100%;border-collapse:collapse;font-size:.92rem;">
                <tr>
                    <th style="text-align:left;color:var(--doux);padding:.5rem 0;width:35%;font-weight:600;">Prénom</th>
                    <td style="padding:.5rem 0;"><?= echapper($client['prenom']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left;color:var(--doux);padding:.5rem 0;font-weight:600;">Nom</th>
                    <td style="padding:.5rem 0;"><?= echapper($client['nom']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left;color:var(--doux);padding:.5rem 0;font-weight:600;">Courriel</th>
                    <td style="padding:.5rem 0;"><?= echapper($client['email']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left;color:var(--doux);padding:.5rem 0;font-weight:600;">Membre depuis</th>
                    <td style="padding:.5rem 0;"><?= echapper(formaterDate($client['date_inscription'])) ?></td>
                </tr>
            </table>
        </div>

        <!-- Infos modifiables -->
        <div class="dash-section-titre">✏️ Informations modifiables</div>
        <form method="post"
              action="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/mon-compte?section=profil"
              style="max-width:400px;" novalidate>
            <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">

            <div class="champ">
                <label for="province">Province</label>
                <select id="province" name="province" autocomplete="address-level1">
                    <option value="">— Non renseignée —</option>
                    <?php foreach ($provinces as $code => $nom): ?>
                    <option value="<?= echapper($code) ?>"
                        <?= $codeProvince === $code ? 'selected' : '' ?>>
                        <?= echapper($nom) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="champ">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="telephone"
                       value="<?= echapper($client['telephone'] ?? '') ?>"
                       autocomplete="tel" placeholder="514-555-0192">
            </div>

            <button type="submit" class="btn btn-primaire">Enregistrer les modifications</button>
        </form>

        <?php endif; ?>

    </div><!-- /.dashboard-main -->
</div><!-- /.dashboard-wrap -->

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

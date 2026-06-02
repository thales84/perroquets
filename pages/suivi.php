<?php
require_once __DIR__ . '/../modeles/reservation-modele.php';

$langue = langueActive();

/* Recherche par code de suivi ALÉATOIRE (ex: MP-X7K2-9QF4) — anti-énumération.
   Accepte ?code= (nouveau) ou ?ref= (rétrocompat lien). */
$codeBrut = trim($_GET['code'] ?? $_GET['ref'] ?? '');
$resa     = null;
$erreur   = '';

if ($codeBrut !== '') {
    // Format attendu : MP-XXXX-XXXX (lettres/chiffres sans ambigus)
    if (preg_match('/^MP-[0-9A-Z]{4}-[0-9A-Z]{4}$/i', $codeBrut)) {
        $resa = recupererSuiviParCode($codeBrut);
        if (!$resa) {
            $erreur = 'Aucune réservation trouvée pour ce code de suivi.';
        }
    } else {
        $erreur = 'Format de code invalide. Exemple : MP-X7K2-9QF4';
    }
}

/* Calcul des étapes de la timeline */
function etapesTimeline(array $r): array
{
    $statutResa = $r['statut_reservation'] ?? '';
    $statutLiv  = $r['statut_livraison']   ?? '';

    return [
        ['label' => 'Demande reçue',    'icone' => '📨', 'fait' => true],
        ['label' => 'Confirmée',        'icone' => '✅', 'fait' => $statutResa === 'traitee'],
        ['label' => 'En préparation',   'icone' => '📦', 'fait' => in_array($statutLiv, ['en_preparation','expedie','livre'], true)],
        ['label' => 'Expédié',          'icone' => '🚚', 'fait' => in_array($statutLiv, ['expedie','livre'], true)],
        ['label' => 'Remis',            'icone' => '🏠', 'fait' => $statutLiv === 'livre'],
    ];
}

/* Transporteurs → URL de tracking */
$urlsTransporteurs = [
    'Canada Post' => 'https://www.canadapost-postescanada.ca/track-reperage/en#/search?searchFor=',
    'Purolator'   => 'https://www.purolator.com/en/shipping/tracker?pin=',
    'FedEx'       => 'https://www.fedex.com/fedextrack/?tracknumbers=',
    'UPS'         => 'https://www.ups.com/track?tracknum=',
];

$titrePage       = 'Suivi de réservation';
$descriptionPage = 'Suivez l\'état de votre réservation Maple Perroquets grâce à votre numéro de référence.';

require_once __DIR__ . '/../gabarits/entete.php';
?>

<div class="page-suivi">

    <h1 class="suivi-titre">🔍 Suivre une réservation</h1>
    <p class="suivi-sous">
        Entrez votre code de suivi (format <strong>MP-XXXX-XXXX</strong>),
        communiqué par nos soins, pour consulter l'état de votre commande sans vous connecter.
    </p>

    <!-- Formulaire de recherche -->
    <form method="get" action="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/suivi">
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
            <input type="text" name="code"
                   value="<?= echapper($codeBrut) ?>"
                   placeholder="MP-X7K2-9QF4"
                   style="flex:1;min-width:200px;padding:.65rem .85rem;border:1.5px solid var(--bordure);border-radius:var(--rayon-sm);background:var(--fond);color:var(--encre);font-family:inherit;font-size:1rem;text-transform:uppercase;"
                   autocomplete="off"
                   pattern="[Mm][Pp]-[0-9A-Za-z]{4}-[0-9A-Za-z]{4}"
                   title="Format : MP-X7K2-9QF4">
            <button type="submit" class="btn btn-primaire">Rechercher</button>
        </div>
        <?php if ($erreur): ?>
        <p style="color:var(--ara);font-size:.88rem;margin-top:.5rem;">⚠ <?= echapper($erreur) ?></p>
        <?php endif; ?>
    </form>

    <!-- Résultat -->
    <?php if ($resa): ?>
    <?php
        $etapes     = etapesTimeline($resa);
        $nbFait     = count(array_filter($etapes, fn($e) => $e['fait']));
        $idxActuel  = $nbFait - 1; // dernière étape accomplie
        $ref        = $resa['code_suivi'] ?? '';
        $transport  = $resa['transporteur']   ?? '';
        $numSuivi   = $resa['numero_suivi']   ?? '';
        $dateExp    = $resa['date_expedition'] ?? '';
        $urlTransp  = ($numSuivi && isset($urlsTransporteurs[$transport]))
                      ? $urlsTransporteurs[$transport] . urlencode($numSuivi)
                      : null;
    ?>
    <div class="suivi-carte">
        <div class="suivi-carte-titre">
            🦜 <?= echapper($resa['espece_nom']) ?>
            <span style="float:right;font-size:.8rem;color:var(--doux);font-family:'Outfit',sans-serif;font-weight:400;"><?= echapper($ref) ?></span>
        </div>

        <!-- Timeline -->
        <div class="suivi-timeline" role="list" aria-label="Étapes de livraison">
            <?php foreach ($etapes as $i => $etape):
                $fait   = $etape['fait'];
                $actuel = $fait && ($i === count($etapes) - 1 || !($etapes[$i + 1]['fait'] ?? false));
                $classe = $actuel ? 'actuel' : ($fait ? 'fait' : '');
            ?>
            <div class="suivi-etape <?= $classe ?>" role="listitem">
                <div class="suivi-point" aria-hidden="true">
                    <?= $fait ? '✓' : ($actuel ? $etape['icone'] : '') ?>
                </div>
                <span class="suivi-label"><?= echapper($etape['label']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Détails -->
        <?php if ($transport || $numSuivi || $dateExp): ?>
        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--bordure);">
            <?php if ($transport): ?>
            <p style="font-size:.88rem;color:var(--doux);">
                Transporteur : <strong style="color:var(--encre);"><?= echapper($transport) ?></strong>
            </p>
            <?php endif; ?>
            <?php if ($numSuivi): ?>
            <p style="font-size:.88rem;color:var(--doux);margin-top:.3rem;">
                N° de suivi : <strong style="color:var(--encre);font-family:monospace;"><?= echapper($numSuivi) ?></strong>
                <?php if ($urlTransp): ?>
                &nbsp;<a href="<?= echapper($urlTransp) ?>" target="_blank" rel="noopener"
                         class="btn btn-jungle btn-sm" style="font-size:.75rem;">
                    Suivre sur <?= echapper($transport) ?> →
                </a>
                <?php endif; ?>
            </p>
            <?php endif; ?>
            <?php if ($dateExp): ?>
            <p style="font-size:.88rem;color:var(--doux);margin-top:.3rem;">
                Date d'expédition : <strong style="color:var(--encre);"><?= echapper(formaterDate($dateExp)) ?></strong>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <p style="margin-top:1.25rem;font-size:.8rem;color:var(--doux);">
            Question ? Contactez-nous : <a href="mailto:bonjour@mapleperroquets.com" style="color:var(--ara);">bonjour@mapleperroquets.com</a>
        </p>
    </div>
    <?php endif; ?>

    <?php if ($codeBrut === '' && !$resa): ?>
    <div style="margin-top:2.5rem;padding:2rem;background:var(--fond-2);border-radius:var(--rayon);border:1px solid var(--bordure);text-align:center;">
        <p style="color:var(--doux);font-size:.92rem;">
            Votre code de suivi se trouve dans votre
            <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/mon-compte?section=reservations"
               style="color:var(--ara);font-weight:600;">tableau de bord</a>
            ou vous est communiqué par courriel une fois votre réservation traitée.
        </p>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

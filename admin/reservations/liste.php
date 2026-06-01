<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/reservation-modele.php';

$filtreStatut  = '';
if (isset($_GET['statut']) && in_array($_GET['statut'], ['nouvelle','traitee','annulee'], true)) {
    $filtreStatut = $_GET['statut'];
}

$reservations = listerReservations($filtreStatut);
$nbNouvelles  = compterNouvellesReservations();

$titrePage = 'Réservations';
require_once dirname(__DIR__) . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>
            Réservations
            <?php if ($nbNouvelles > 0) : ?>
                <span class="badge-compteur"><?= $nbNouvelles ?> nouvelle<?= $nbNouvelles > 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </h1>
    </div>

    <?php if (isset($_GET['succes'])) : ?>
        <div class="alerte alerte--succes">Statut mis à jour.</div>
    <?php endif; ?>

    <!-- Filtre statut -->
    <div class="filtres">
        <div class="filtres-groupe">
            <?php foreach (['' => 'Toutes', 'nouvelle' => 'Nouvelles', 'traitee' => 'Traitées', 'annulee' => 'Annulées'] as $val => $label) : ?>
                <a href="<?= echapper(URL_SITE) ?>/admin/reservations/liste.php<?= $val ? '?statut=' . $val : '' ?>"
                   class="bouton bouton-sm <?= $filtreStatut === $val ? 'bouton-primaire' : 'bouton-contour' ?>">
                    <?= echapper($label) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($reservations)) : ?>
        <p>Aucune demande trouvée.</p>
    <?php else : ?>
        <table class="tableau-admin">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Oiseau</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $r) : ?>
                <tr class="<?= $r['statut_reservation'] === 'nouvelle' ? 'ligne-nouvelle' : '' ?>">
                    <td><?= echapper(formaterDate($r['date_demande'])) ?></td>
                    <td>
                        <?= echapper($r['nom_client']) ?><br>
                        <a href="mailto:<?= echapper($r['email_client']) ?>" class="texte-discret">
                            <?= echapper($r['email_client']) ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?= echapper(URL_SITE) ?>/fr/oiseau/<?= echapper($r['oiseau_slug']) ?>"
                           target="_blank" rel="noopener">
                            <?= echapper($r['espece_nom']) ?>
                        </a>
                        <span class="badge badge--<?= echapper($r['oiseau_statut']) ?> badge-xs">
                            <?= echapper($r['oiseau_statut']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-resa badge-resa--<?= echapper($r['statut_reservation']) ?>">
                            <?= echapper($r['statut_reservation']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= echapper(URL_SITE) ?>/admin/reservations/detail.php?id=<?= (int)$r['id_reservation'] ?>"
                           class="bouton bouton-contour bouton-sm">Voir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/inc/pied-admin.php'; ?>

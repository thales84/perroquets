<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/espece-modele.php';

$especes   = listerEspeces();
$titrePage = 'Espèces';

require_once dirname(__DIR__) . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Espèces</h1>
        <a href="<?= echapper(URL_SITE) ?>/admin/especes/ajouter.php" class="bouton bouton-primaire">+ Ajouter une espèce</a>
    </div>

    <?php if (isset($_GET['succes'])) : ?>
        <div class="alerte alerte--succes" role="status">
            <?= echapper(match($_GET['succes']) {
                'ajoute'   => 'Espèce ajoutée avec succès.',
                'modifie'  => 'Espèce modifiée avec succès.',
                'supprime' => 'Espèce supprimée.',
                default    => 'Opération réussie.'
            }) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['erreur']) && $_GET['erreur'] === 'oiseaux') : ?>
        <div class="alerte alerte--erreur" role="alert">
            Impossible de supprimer : des oiseaux sont rattachés à cette espèce.
        </div>
    <?php endif; ?>

    <?php if (empty($especes)) : ?>
        <p>Aucune espèce enregistrée. <a href="<?= echapper(URL_SITE) ?>/admin/especes/ajouter.php">Ajouter la première.</a></p>
    <?php else : ?>
        <table class="tableau-admin">
            <thead>
                <tr>
                    <th>Nom commun</th>
                    <th>Nom scientifique</th>
                    <th>Famille</th>
                    <th>Oiseaux</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($especes as $e) : ?>
                <tr>
                    <td><?= echapper($e['nom_commun_fr']) ?></td>
                    <td><em><?= echapper($e['nom_scientifique'] ?? '—') ?></em></td>
                    <td><?= echapper($e['famille_fr'] ?? '—') ?></td>
                    <td><?= (int) $e['nb_oiseaux'] ?></td>
                    <td class="actions-cellule">
                        <a href="<?= echapper(URL_SITE) ?>/admin/especes/modifier.php?id=<?= (int) $e['id_espece'] ?>"
                           class="bouton bouton-contour bouton-sm">Modifier</a>
                        <a href="<?= echapper(URL_SITE) ?>/admin/especes/supprimer.php?id=<?= (int) $e['id_espece'] ?>&csrf=<?= echapper(genererJetonCsrf()) ?>"
                           class="bouton bouton-danger bouton-sm"
                           onclick="return confirm('Supprimer l\'espèce « <?= echapper(addslashes($e['nom_commun_fr'])) ?> » ?')">
                           Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/inc/pied-admin.php'; ?>

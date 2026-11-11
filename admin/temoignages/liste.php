<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/temoignage-modele.php';
require_once RACINE . '/modeles/parametre-modele.php';

$temoignages = listerTemoignages();
$provinces   = provincesCanada();
$csrf        = genererJetonCsrf();
$titrePage   = 'Témoignages';

require_once dirname(__DIR__) . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Témoignages</h1>
        <a href="<?= echapper(URL_SITE) ?>/admin/temoignages/ajouter.php" class="bouton bouton-primaire">+ Ajouter un témoignage</a>
    </div>

    <?php if (isset($_GET['succes'])) : ?>
        <div class="alerte alerte--succes" role="status">
            <?= echapper(match($_GET['succes']) {
                'ajoute'   => 'Témoignage ajouté.',
                'modifie'  => 'Témoignage modifié.',
                'supprime' => 'Témoignage supprimé.',
                'bascule'  => 'Visibilité mise à jour.',
                default    => 'Opération réussie.'
            }) ?>
        </div>
    <?php endif; ?>

    <p class="texte-discret">Les témoignages publiés s'affichent sur la page d'accueil (6 au maximum), triés par ordre.
        Publiez uniquement des avis réels, avec l'accord des clients.</p>

    <?php if (empty($temoignages)) : ?>
        <p>Aucun témoignage. <a href="<?= echapper(URL_SITE) ?>/admin/temoignages/ajouter.php">Ajouter le premier.</a></p>
    <?php else : ?>
        <table class="tableau-admin">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Titre / extrait</th>
                    <th>Ordre</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($temoignages as $t) :
                    $lieu = implode(', ', array_filter([$t['ville'], $provinces[$t['province']] ?? '']));
                    $id   = (int) $t['id_temoignage'];
                ?>
                <tr>
                    <td>
                        <strong><?= echapper($t['auteur']) ?></strong>
                        <?php if ($lieu !== '') : ?><br><span class="texte-discret"><?= echapper($lieu) ?></span><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($t['titre_fr'] !== '') : ?><strong><?= echapper($t['titre_fr']) ?></strong><br><?php endif; ?>
                        <span class="texte-discret"><?= echapper(mb_strimwidth($t['texte_fr'], 0, 90, '…')) ?></span>
                    </td>
                    <td><?= (int) $t['ordre'] ?></td>
                    <td><?= $t['visible'] ? '✅ Publié' : '⏸️ Masqué' ?></td>
                    <td class="actions-cellule">
                        <a href="<?= echapper(URL_SITE) ?>/admin/temoignages/modifier.php?id=<?= $id ?>"
                           class="bouton bouton-contour bouton-sm">Modifier</a>
                        <a href="<?= echapper(URL_SITE) ?>/admin/temoignages/supprimer.php?id=<?= $id ?>&action=basculer&csrf=<?= echapper($csrf) ?>"
                           class="bouton bouton-contour bouton-sm"><?= $t['visible'] ? 'Masquer' : 'Publier' ?></a>
                        <a href="<?= echapper(URL_SITE) ?>/admin/temoignages/supprimer.php?id=<?= $id ?>&csrf=<?= echapper($csrf) ?>"
                           class="bouton bouton-danger bouton-sm"
                           onclick="return confirm('Supprimer le témoignage de « <?= echapper(addslashes($t['auteur'])) ?> » ?')">
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

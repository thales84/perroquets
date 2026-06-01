<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/oiseau-modele.php';
require_once RACINE . '/modeles/espece-modele.php';

// Filtres
$filtres = [];
if (!empty($_GET['statut']) && in_array($_GET['statut'], ['disponible','reserve','vendu'], true)) {
    $filtres['statut'] = $_GET['statut'];
}
if (!empty($_GET['id_espece']) && ctype_digit($_GET['id_espece'])) {
    $filtres['id_espece'] = (int) $_GET['id_espece'];
}

// Changement rapide de statut
if (isset($_GET['changer_statut'], $_GET['id'], $_GET['csrf'])
    && verifierJetonCsrf($_GET['csrf'])) {
    changerStatutOiseau((int) $_GET['id'], $_GET['changer_statut']);
    header('Location: ' . URL_SITE . '/admin/oiseaux/liste.php');
    exit;
}

$oiseaux = listerTousLesOiseaux($filtres);
$especes = recupererListeEspeces();

$titrePage = 'Oiseaux';
require_once dirname(__DIR__) . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Oiseaux</h1>
        <a href="<?= echapper(URL_SITE) ?>/admin/oiseaux/ajouter.php" class="bouton bouton-primaire">+ Ajouter un oiseau</a>
    </div>

    <?php if (isset($_GET['succes'])) : ?>
        <div class="alerte alerte--succes" role="status">
            <?= echapper(match($_GET['succes']) {
                'ajoute'   => 'Oiseau ajouté avec succès.',
                'modifie'  => 'Oiseau modifié.',
                'supprime' => 'Oiseau supprimé.',
                default    => 'Opération réussie.'
            }) ?>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <form class="filtres" method="get" action="<?= echapper(URL_SITE) ?>/admin/oiseaux/liste.php">
        <div class="filtres-groupe">
            <label for="f-statut">Statut</label>
            <select id="f-statut" name="statut">
                <option value="">Tous</option>
                <?php foreach (['disponible','reserve','vendu'] as $s) : ?>
                    <option value="<?= $s ?>" <?= ($filtres['statut'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="f-espece">Espèce</label>
            <select id="f-espece" name="id_espece">
                <option value="">Toutes</option>
                <?php foreach ($especes as $e) : ?>
                    <option value="<?= $e['id_espece'] ?>"
                        <?= ($filtres['id_espece'] ?? 0) === (int) $e['id_espece'] ? 'selected' : '' ?>>
                        <?= echapper($e['nom_commun_fr']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="bouton bouton-primaire">Filtrer</button>
            <a href="<?= echapper(URL_SITE) ?>/admin/oiseaux/liste.php" class="bouton bouton-contour">Tout</a>
        </div>
    </form>

    <?php if (empty($oiseaux)) : ?>
        <p>Aucun oiseau trouvé.</p>
    <?php else : ?>
        <table class="tableau-admin">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Espèce</th>
                    <th>Sexe</th>
                    <th>Prix</th>
                    <th>Statut</th>
                    <th>Ajouté le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($oiseaux as $o) : ?>
                <?php $csrf = echapper(genererJetonCsrf()); ?>
                <tr>
                    <td>
                        <?php if ($o['photo_chemin']) : ?>
                            <img src="<?= echapper($o['photo_chemin']) ?>"
                                 alt="" width="48" height="48"
                                 style="object-fit:cover;border-radius:4px;">
                        <?php else : ?>
                            <span aria-hidden="true">🦜</span>
                        <?php endif; ?>
                    </td>
                    <td><?= echapper($o['espece_nom']) ?></td>
                    <td><?= echapper($o['sexe']) ?></td>
                    <td><?= echapper(formaterPrixCad($o['prix_cad'])) ?></td>
                    <td>
                        <span class="badge badge--<?= echapper($o['statut']) ?>">
                            <?= echapper($o['statut']) ?>
                        </span>
                    </td>
                    <td><?= echapper(formaterDate($o['date_ajout'])) ?></td>
                    <td class="actions-cellule">
                        <!-- Changement rapide de statut -->
                        <select class="statut-rapide"
                                onchange="location.href='<?= echapper(URL_SITE) ?>/admin/oiseaux/liste.php?id=<?= (int)$o['id_oiseau'] ?>&changer_statut='+this.value+'&csrf=<?= $csrf ?>'">
                            <?php foreach (['disponible','reserve','vendu'] as $s) : ?>
                                <option value="<?= $s ?>" <?= $o['statut'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <a href="<?= echapper(URL_SITE) ?>/admin/oiseaux/modifier.php?id=<?= (int)$o['id_oiseau'] ?>"
                           class="bouton bouton-contour bouton-sm">Modifier</a>
                        <a href="<?= echapper(URL_SITE) ?>/admin/oiseaux/supprimer.php?id=<?= (int)$o['id_oiseau'] ?>&csrf=<?= $csrf ?>"
                           class="bouton bouton-danger bouton-sm"
                           onclick="return confirm('Supprimer cet oiseau et toutes ses photos ?')">
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

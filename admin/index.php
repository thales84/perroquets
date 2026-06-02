<?php
require_once __DIR__ . '/inc/securite.php';

$pdo = obtenirConnexion();

// Compteurs tableau de bord — une requête par groupe
$req = $pdo->query("
    SELECT statut, COUNT(*) AS total
    FROM oiseau
    GROUP BY statut
");
$comptesOiseaux = ['disponible' => 0, 'reserve' => 0, 'vendu' => 0];
foreach ($req->fetchAll() as $ligne) {
    $comptesOiseaux[$ligne['statut']] = (int) $ligne['total'];
}

$req = $pdo->query("SELECT COUNT(*) FROM reservation WHERE statut_reservation = 'nouvelle'");
$nouvellesReservations = (int) $req->fetchColumn();

$req = $pdo->query("SELECT COUNT(*) FROM reservation");
$totalReservations = (int) $req->fetchColumn();

$titrePage = 'Tableau de bord';
require_once __DIR__ . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <div>
            <h1>Tableau de bord</h1>
            <p class="texte-discret">Bienvenue, <?= echapper($_SESSION['admin_identifiant'] ?? 'Admin') ?> 👋</p>
        </div>
        <a href="<?= echapper(URL_SITE) ?>/admin/oiseaux/ajouter.php" class="bouton bouton-primaire">+ Ajouter un oiseau</a>
    </div>

    <div class="dashboard-grille">

        <div class="dashboard-carte dashboard-carte--vert">
            <p class="dashboard-nombre"><?= $comptesOiseaux['disponible'] ?></p>
            <p class="dashboard-label">Oiseaux disponibles</p>
        </div>

        <div class="dashboard-carte dashboard-carte--orange">
            <p class="dashboard-nombre"><?= $comptesOiseaux['reserve'] ?></p>
            <p class="dashboard-label">Oiseaux réservés</p>
        </div>

        <div class="dashboard-carte dashboard-carte--gris">
            <p class="dashboard-nombre"><?= $comptesOiseaux['vendu'] ?></p>
            <p class="dashboard-label">Oiseaux vendus</p>
        </div>

        <div class="dashboard-carte dashboard-carte--alerte">
            <p class="dashboard-nombre"><?= $nouvellesReservations ?></p>
            <p class="dashboard-label">
                Nouvelles demandes
                <?php if ($nouvellesReservations > 0) : ?>
                    <a href="<?= echapper(URL_SITE) ?>/admin/reservations/liste.php" class="dashboard-lien">Voir →</a>
                <?php endif; ?>
            </p>
        </div>

    </div>

    <div class="dashboard-raccourcis">
        <h2>Accès rapides</h2>
        <div class="raccourcis-grille">
            <a href="<?= echapper(URL_SITE) ?>/admin/oiseaux/liste.php" class="bouton bouton-primaire">Gérer les oiseaux</a>
            <a href="<?= echapper(URL_SITE) ?>/admin/reservations/liste.php" class="bouton bouton-secondaire">Voir les réservations</a>
            <a href="<?= echapper(URL_SITE) ?>/admin/especes/liste.php" class="bouton bouton-contour">Gérer les espèces</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/pied-admin.php'; ?>

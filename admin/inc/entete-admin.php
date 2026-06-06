<?php
// Variables attendues : $titrePage (string)
$titrePage ??= 'Administration';

// Détection de la section active (auto via chemin du script)
$cheminScript = $_SERVER['SCRIPT_NAME'] ?? '';
$sectionActive = 'dashboard';
if (str_contains($cheminScript, '/especes/'))           $sectionActive = 'especes';
elseif (str_contains($cheminScript, '/oiseaux/'))        $sectionActive = 'oiseaux';
elseif (str_contains($cheminScript, '/reservations/'))   $sectionActive = 'reservations';
elseif (str_contains($cheminScript, '/compte.php'))      $sectionActive = 'compte';
elseif (str_contains($cheminScript, '/parametres.php'))  $sectionActive = 'parametres';

// Badge : nombre de nouvelles réservations
$nbNouvelles = 0;
try {
    $pdoTmp = obtenirConnexion();
    $nbNouvelles = (int) $pdoTmp->query("SELECT COUNT(*) FROM reservation WHERE statut_reservation = 'nouvelle'")->fetchColumn();
} catch (Throwable $e) {
    $nbNouvelles = 0;
}

$identifiantAdmin = htmlspecialchars($_SESSION['admin_identifiant'] ?? 'Admin', ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="fr-CA" data-theme="clair">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= echapper($titrePage) ?> — Admin Maple Perroquets</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="<?= echapper(URL_SITE) ?>/assets/img/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="<?= echapper(URL_SITE) ?>/assets/img/favicon-32.png" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="<?= echapper(URL_SITE) ?>/assets/img/apple-touch-icon.png">
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            if (t) document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
<?php
    // Cache-busting : version = date de modification du fichier
    $vStyle = @filemtime(RACINE . '/ressources/css/style.css') ?: time();
    $vAdmin = @filemtime(RACINE . '/ressources/css/admin.css') ?: time();
?>
    <link rel="stylesheet" href="<?= echapper(URL_SITE) ?>/ressources/css/style.css?v=<?= $vStyle ?>">
    <link rel="stylesheet" href="<?= echapper(URL_SITE) ?>/ressources/css/admin.css?v=<?= $vAdmin ?>">
</head>
<body>

<div class="admin-shell">

    <!-- ================================================
         SIDEBAR
         ================================================ -->
    <aside class="admin-sidebar" id="admin-sidebar" aria-label="Navigation administration">

        <div class="admin-sidebar-entete">
            <a href="<?= echapper(URL_SITE) ?>/admin/" class="admin-sidebar-logo">
                🦜 <span>Maple Admin</span>
            </a>
            <button class="admin-sidebar-fermer" id="admin-sidebar-fermer" aria-label="Fermer le menu">✕</button>
        </div>

        <nav class="admin-sidebar-nav">
            <p class="admin-nav-titre">Gestion</p>

            <a href="<?= echapper(URL_SITE) ?>/admin/"
               class="admin-nav-item <?= $sectionActive === 'dashboard' ? 'actif' : '' ?>">
                <span class="admin-nav-icone">📊</span> Tableau de bord
            </a>
            <a href="<?= echapper(URL_SITE) ?>/admin/oiseaux/liste.php"
               class="admin-nav-item <?= $sectionActive === 'oiseaux' ? 'actif' : '' ?>">
                <span class="admin-nav-icone">🦜</span> Oiseaux
            </a>
            <a href="<?= echapper(URL_SITE) ?>/admin/especes/liste.php"
               class="admin-nav-item <?= $sectionActive === 'especes' ? 'actif' : '' ?>">
                <span class="admin-nav-icone">🪺</span> Espèces
            </a>
            <a href="<?= echapper(URL_SITE) ?>/admin/reservations/liste.php"
               class="admin-nav-item <?= $sectionActive === 'reservations' ? 'actif' : '' ?>">
                <span class="admin-nav-icone">📋</span> Réservations
                <?php if ($nbNouvelles > 0): ?>
                <span class="admin-nav-badge"><?= $nbNouvelles ?></span>
                <?php endif; ?>
            </a>

            <div class="admin-nav-sep"></div>

            <p class="admin-nav-titre">Site</p>
            <a href="<?= echapper(URL_SITE) ?>/fr/" target="_blank" rel="noopener" class="admin-nav-item">
                <span class="admin-nav-icone">🌐</span> Voir le site ↗
            </a>
        </nav>

        <div class="admin-sidebar-bas">
            <a href="<?= echapper(URL_SITE) ?>/admin/parametres.php"
               class="admin-nav-item <?= $sectionActive === 'parametres' ? 'actif' : '' ?>">
                <span class="admin-nav-icone">⚙️</span> Paramètres
            </a>
            <a href="<?= echapper(URL_SITE) ?>/admin/compte.php"
               class="admin-nav-item <?= $sectionActive === 'compte' ? 'actif' : '' ?>">
                <span class="admin-nav-icone">🔑</span> Mon compte
            </a>
            <div class="admin-profil">
                <div class="admin-profil-avatar">👤</div>
                <div>
                    <div class="admin-profil-nom"><?= $identifiantAdmin ?></div>
                    <div class="admin-profil-role">Administrateur</div>
                </div>
            </div>
            <a href="<?= echapper(URL_SITE) ?>/admin/deconnexion.php" class="admin-nav-item admin-nav-decon">
                <span class="admin-nav-icone">🔓</span> Déconnexion
            </a>
        </div>
    </aside>

    <!-- Overlay mobile -->
    <div class="admin-overlay" id="admin-overlay" aria-hidden="true"></div>

    <!-- ================================================
         ZONE PRINCIPALE
         ================================================ -->
    <div class="admin-zone">

        <!-- Topbar -->
        <header class="admin-topbar">
            <button class="admin-burger" id="admin-burger" aria-label="Ouvrir le menu">
                <span></span><span></span><span></span>
            </button>
            <h2 class="admin-topbar-titre"><?= echapper($titrePage) ?></h2>
            <div class="admin-topbar-actions">
                <button class="bouton-theme" id="bouton-theme" aria-label="Basculer le thème">
                    <span class="icone-theme" aria-hidden="true">☀️</span>
                </button>
            </div>
        </header>

        <main id="contenu-principal" class="admin-main">

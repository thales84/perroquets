<?php
// Variables attendues : $titrePage (string)
$titrePage ??= 'Administration';
?>
<!DOCTYPE html>
<html lang="fr-CA" data-theme="clair">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= echapper($titrePage) ?> — Admin Maple Perroquets</title>
    <meta name="robots" content="noindex, nofollow">
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            if (t) document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <link rel="stylesheet" href="<?= echapper(URL_SITE) ?>/ressources/css/style.css">
    <link rel="stylesheet" href="<?= echapper(URL_SITE) ?>/ressources/css/admin.css">
</head>
<body>

<header class="entete-admin">
    <div class="entete-admin-interieur">
        <a href="<?= echapper(URL_SITE) ?>/admin/" class="logo">
            🦜 <span>Admin</span>
        </a>

        <nav class="nav-admin" aria-label="Navigation administration">
            <ul>
                <li><a href="<?= echapper(URL_SITE) ?>/admin/">Tableau de bord</a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/admin/especes/liste.php">Espèces</a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/admin/oiseaux/liste.php">Oiseaux</a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/admin/reservations/liste.php">Réservations</a></li>
            </ul>
        </nav>

        <div class="admin-actions">
            <button class="bouton-theme" id="bouton-theme" aria-label="Basculer le thème">
                <span class="icone-theme" aria-hidden="true">☀️</span>
            </button>
            <a href="<?= echapper(URL_SITE) ?>/admin/deconnexion.php"
               class="bouton bouton-contour bouton-sm">
                Déconnexion
            </a>
        </div>
    </div>
</header>

<main id="contenu-principal" class="admin-main">

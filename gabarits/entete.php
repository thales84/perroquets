<?php
// Variables attendues (définies dans chaque page) :
// $titrePage       (string)      — titre <title>
// $descriptionPage (string)      — meta description
// $urlCanonique    (string|null) — URL canonique absolue
// $ogImage         (string|null) — URL absolue de l'image Open Graph
// $jsonLd          (string|null) — bloc JSON-LD déjà encodé (sans balise <script>)
$titrePage       ??= 'Maple Perroquets';
$descriptionPage ??= t('meta_description_defaut');
$urlCanonique    ??= null;
$ogImage         ??= null;
$jsonLd          ??= null;
$langue = langueActive();
$urlCourante = URL_SITE . '/' . $langue . '/' . ltrim(obtenirParametreUrl('page', ''), '/');
?>
<!DOCTYPE html>
<html lang="<?= echapper($langue) ?>-CA" data-theme="clair">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= echapper($titrePage) ?> — Maple Perroquets</title>
    <meta name="description" content="<?= echapper($descriptionPage) ?>">

    <?php if ($urlCanonique) : ?>
    <?= genererCanonical($urlCanonique) ?>

    <?php endif; ?>
    <?= genererOpenGraph([
        'title'       => $titrePage . ' — Maple Perroquets',
        'description' => $descriptionPage,
        'url'         => $urlCanonique ?? $urlCourante,
        'image'       => $ogImage ?? '',
        'type'        => 'website',
    ]) ?>

    <?= isset($hreflangChemin) ? genererHreflang($hreflangChemin) : '' ?>

    <?php if ($jsonLd) : ?>
    <script type="application/ld+json"><?= $jsonLd ?></script>
    <?php endif; ?>

    <?php // Anti-flash : applique le thème avant rendu CSS pour éviter le scintillement ?>
    <script>
        (function () {
            var stocke = localStorage.getItem('theme');
            var prefereSombre = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = stocke || (prefereSombre ? 'sombre' : 'clair');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <link rel="stylesheet" href="<?= echapper(URL_SITE) ?>/ressources/css/style.css">
</head>
<body>

<header class="entete-site">
    <div class="entete-interieur">
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/" class="logo" aria-label="Maple Perroquets — <?= echapper(t('nav_accueil')) ?>">
            <span class="logo-icone" aria-hidden="true">🦜</span>
            <span class="logo-texte">Maple Perroquets</span>
        </a>

        <div class="entete-actions">
            <button class="bouton-theme" id="bouton-theme"
                    aria-label="Basculer le thème" title="Thème clair/sombre">
                <span class="icone-theme" aria-hidden="true">☀️</span>
            </button>

            <button class="bouton-hamburger" id="bouton-hamburger"
                    aria-controls="navigation-principale"
                    aria-expanded="false"
                    aria-label="Ouvrir le menu">
                <span class="barre"></span>
                <span class="barre"></span>
                <span class="barre"></span>
            </button>

            <!--
            Sélecteur de langue (à activer à l'étape bilingue) :
            <nav class="nav-langue" aria-label="Langue">
                <a href="/fr/" hreflang="fr" lang="fr">FR</a>
                <a href="/en/" hreflang="en" lang="en">EN</a>
            </nav>
            -->
        </div>

        <nav class="navigation-principale" id="navigation-principale" aria-label="<?= echapper(t('nav_accueil')) ?>">
            <ul>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/"><?= echapper(t('nav_accueil')) ?></a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"><?= echapper(t('nav_oiseaux')) ?></a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/espece/gris-du-gabon"><?= echapper(t('nav_gris_du_gabon')) ?></a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/espece/ara-ararauna"><?= echapper(t('nav_aras')) ?></a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/espece/cacatoes-a-huppe-jaune"><?= echapper(t('nav_cacatoes')) ?></a></li>
            </ul>
        </nav>
    </div>
</header>

<main id="contenu-principal">

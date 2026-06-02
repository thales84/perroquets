<?php
/* ----------------------------------------------------------------
   Gabarit entête — Maple Perroquets (nouvelle direction artistique)
   Variables attendues (définies dans chaque page) :
     $titrePage       — titre de l'onglet
     $descriptionPage — meta description
     $urlCanonique    — URL canonique absolue (optionnel)
     $ogImage         — URL absolue image OG (optionnel)
     $jsonLd          — JSON-LD déjà encodé (optionnel)
     $hreflangChemin  — chemin pour hreflang (optionnel)
   ---------------------------------------------------------------- */
$titrePage       ??= 'Maple Perroquets';
$descriptionPage ??= t('meta_description_defaut');
$urlCanonique    ??= null;
$ogImage         ??= null;
$jsonLd          ??= null;
$langue = langueActive();
$urlCourante = URL_SITE . '/' . $langue . '/' . ltrim(obtenirParametreUrl('page', ''), '/');

// État de connexion client (session déjà démarrée par index.php)
$clientConnecte = !empty($_SESSION['client_id']);
$prenomClient   = htmlspecialchars($_SESSION['client_prenom'] ?? '', ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="<?= echapper($langue) ?>-CA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= echapper($titrePage) ?> — Maple Perroquets</title>
    <meta name="description" content="<?= echapper($descriptionPage) ?>">

    <!-- Anti-flash thème : inline obligatoire pour éviter le scintillement -->
    <script>
    (function(){
        var t = localStorage.getItem('mp-theme');
        if (t) document.documentElement.setAttribute('data-theme', t);
    })();
    </script>

    <!-- Polices Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400;1,9..144,600;1,9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Feuille de style principale -->
    <link rel="stylesheet" href="<?= echapper(URL_SITE) ?>/assets/css/style.css">

    <?php if ($urlCanonique): ?>
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

    <?php if ($jsonLd): ?>
    <script type="application/ld+json"><?= $jsonLd ?></script>
    <?php endif; ?>
</head>
<body>

<!-- ============================================================
     Header sticky translucide
     ============================================================ -->
<header class="header">
    <div class="header-inner">
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/" class="logo"
           aria-label="Maple Perroquets — Accueil">
            🦜 Maple Perroquets
        </a>

        <nav class="nav-liens" aria-label="Navigation principale">
            <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Nos oiseaux</a>
            <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/">Comment ça marche</a>
        </nav>

        <div class="header-actions">
            <button class="btn-theme" id="btn-theme" aria-label="Basculer le thème">🌙</button>
            <?php if ($clientConnecte): ?>
                <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/mon-compte"
                   class="btn btn-ghost btn-sm header-cta-lg">
                    👤 <?= $prenomClient ?>
                </a>
            <?php else: ?>
                <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/connexion"
                   class="btn btn-ghost btn-sm header-cta-lg">Connexion</a>
                <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"
                   class="btn btn-primaire btn-sm header-cta-lg">Voir les oiseaux</a>
            <?php endif; ?>
            <button class="hamburger" id="hamburger" aria-expanded="false" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<!-- Fond semi-transparent derrière le panel -->
<div class="nav-overlay" id="nav-overlay" aria-hidden="true"></div>

<!-- Panel mobile : slide depuis la droite -->
<nav class="nav-mobile" id="nav-mobile"
     aria-label="Menu principal" aria-modal="true"
     role="dialog" hidden>

    <!-- En-tête du panel -->
    <div class="nav-panel-entete">
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/" class="logo">
            🦜 Maple Perroquets
        </a>
        <button class="nav-panel-fermer" id="nav-fermer" aria-label="Fermer le menu">✕</button>
    </div>

    <!-- Corps -->
    <div class="nav-panel-corps">

        <!-- Navigation principale -->
        <p class="nav-panel-section-titre">Navigation</p>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/"
           class="nav-panel-lien">
            <span class="nav-panel-icone">🏠</span> Accueil
            <span class="nav-panel-fleche">›</span>
        </a>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"
           class="nav-panel-lien">
            <span class="nav-panel-icone">🦜</span> Nos oiseaux
            <span class="nav-panel-fleche">›</span>
        </a>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/#comment"
           class="nav-panel-lien">
            <span class="nav-panel-icone">❓</span> Comment ça marche
            <span class="nav-panel-fleche">›</span>
        </a>

        <div class="nav-panel-sep"></div>

        <!-- Compte -->
        <p class="nav-panel-section-titre">Mon compte</p>

        <?php if ($clientConnecte): ?>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/mon-compte"
           class="nav-panel-lien nav-panel-lien--compte">
            <span class="nav-panel-icone">👤</span>
            <div>
                <span style="display:block;font-weight:700;"><?= $prenomClient ?></span>
                <span style="font-size:.75rem;color:var(--doux);">Tableau de bord</span>
            </div>
        </a>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/mon-compte?section=reservations"
           class="nav-panel-lien">
            <span class="nav-panel-icone">📋</span> Mes réservations
            <span class="nav-panel-fleche">›</span>
        </a>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/mon-compte?section=profil"
           class="nav-panel-lien">
            <span class="nav-panel-icone">✏️</span> Mon profil
            <span class="nav-panel-fleche">›</span>
        </a>
        <form method="post" action="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/deconnexion">
            <input type="hidden" name="csrf_token" value="<?= genererJetonCsrfClient() ?>">
            <button type="submit" class="nav-panel-lien nav-panel-btn-decon">
                <span class="nav-panel-icone">🔓</span> Déconnexion
            </button>
        </form>
        <?php else: ?>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/connexion"
           class="nav-panel-lien">
            <span class="nav-panel-icone">👤</span> Se connecter
            <span class="nav-panel-fleche">›</span>
        </a>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/inscription"
           class="nav-panel-lien nav-panel-lien--highlight">
            <span class="nav-panel-icone">✨</span> Créer un compte gratuit
        </a>
        <?php endif; ?>

    </div>

    <!-- Pied fixe -->
    <div class="nav-panel-pied">
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"
           class="btn btn-primaire btn-full btn-lg">
            Voir les oiseaux disponibles →
        </a>
    </div>
</nav>

<main id="contenu-principal">

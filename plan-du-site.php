<?php
require_once __DIR__ . '/configuration/config.php';
require_once __DIR__ . '/configuration/connexion.php';
require_once __DIR__ . '/modeles/oiseau-modele.php';

header('Content-Type: application/xml; charset=UTF-8');

$base     = rtrim(URL_SITE, '/');
$maintenant = date('Y-m-d');

// Oiseaux disponibles et espèces depuis la base
$oiseaux = recupererOiseauxDisponibles();
$especes = recupererListeEspeces();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    <!-- Pages fixes -->
    <url>
        <loc><?= htmlspecialchars($base . '/fr/', ENT_XML1) ?></loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
        <lastmod><?= $maintenant ?></lastmod>
    </url>
    <url>
        <loc><?= htmlspecialchars($base . '/fr/oiseaux', ENT_XML1) ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
        <lastmod><?= $maintenant ?></lastmod>
    </url>

    <!-- Pages d'espèces -->
    <?php foreach ($especes as $espece) : ?>
    <url>
        <loc><?= htmlspecialchars($base . '/fr/espece/' . $espece['slug_fr'], ENT_XML1) ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
        <lastmod><?= $maintenant ?></lastmod>
    </url>
    <?php endforeach; ?>

    <!-- Fiches oiseaux disponibles -->
    <?php foreach ($oiseaux as $oiseau) : ?>
    <url>
        <loc><?= htmlspecialchars($base . '/fr/oiseau/' . $oiseau['slug_fr'], ENT_XML1) ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        <lastmod><?= $maintenant ?></lastmod>
    </url>
    <?php endforeach; ?>

    <!--
    URLs anglaises (à décommenter à l'étape bilingue) :
    <url>
        <loc><?= htmlspecialchars($base, ENT_XML1) ?>/en/</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    -->

</urlset>

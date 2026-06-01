<?php
require_once __DIR__ . '/config.php';

/**
 * Génère les balises hreflang pour la langue active.
 * La version anglaise est commentée — à activer à l'étape bilingue.
 *
 * @param  string $cheminRelatif Chemin sans préfixe de langue, ex. "oiseaux" ou "oiseau/mon-slug"
 * @return string                HTML des balises <link rel="alternate">
 */
function genererHreflang(string $cheminRelatif): string
{
    $base = rtrim(URL_SITE, '/');
    $chemin = ltrim($cheminRelatif, '/');
    $html  = '<link rel="alternate" hreflang="fr-CA" href="' . htmlspecialchars($base . '/fr/' . $chemin, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    // <link rel="alternate" hreflang="en-CA" href="' . htmlspecialchars($base . '/en/' . $chemin, ENT_QUOTES, 'UTF-8') . '"> <!-- à activer -->
    $html .= '    <link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($base . '/fr/' . $chemin, ENT_QUOTES, 'UTF-8') . '">';
    return $html;
}

/**
 * Génère la balise canonique.
 *
 * @param  string $url URL absolue canonique
 * @return string      HTML de la balise <link rel="canonical">
 */
function genererCanonical(string $url): string
{
    return '<link rel="canonical" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Génère les balises Open Graph.
 *
 * @param  array $donnees Clés attendues : title, description, url, image (optionnel), type (optionnel)
 * @return string         HTML des balises <meta property="og:...">
 */
function genererOpenGraph(array $donnees): string
{
    $e = fn(string $v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

    $type        = $e($donnees['type']        ?? 'website');
    $title       = $e($donnees['title']       ?? '');
    $description = $e($donnees['description'] ?? '');
    $url         = $e($donnees['url']         ?? '');

    $html  = '<meta property="og:type"        content="' . $type . '">' . "\n";
    $html .= '    <meta property="og:site_name"   content="Maple Perroquets">' . "\n";
    $html .= '    <meta property="og:title"       content="' . $title . '">' . "\n";
    $html .= '    <meta property="og:description" content="' . $description . '">' . "\n";
    $html .= '    <meta property="og:url"         content="' . $url . '">';

    if (!empty($donnees['image'])) {
        $html .= "\n    " . '<meta property="og:image" content="' . $e($donnees['image']) . '">';
    }

    return $html;
}

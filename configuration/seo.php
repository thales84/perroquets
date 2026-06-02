<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../modeles/parametre-modele.php';

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

    $siteNom = $e(param('site_nom', 'Maple Perroquets'));
    $locale  = $e(param('og_locale', 'fr_CA'));

    $html  = '<meta property="og:type"        content="' . $type . '">' . "\n";
    $html .= '    <meta property="og:site_name"   content="' . $siteNom . '">' . "\n";
    $html .= '    <meta property="og:locale"      content="' . $locale . '">' . "\n";
    $html .= '    <meta property="og:title"       content="' . $title . '">' . "\n";
    $html .= '    <meta property="og:description" content="' . $description . '">' . "\n";
    $html .= '    <meta property="og:url"         content="' . $url . '">';

    if (!empty($donnees['image'])) {
        $html .= "\n    " . '<meta property="og:image" content="' . $e($donnees['image']) . '">';
    }

    return $html;
}

/**
 * Génère la Twitter Card (summary_large_image si une image est fournie).
 *
 * @param array $donnees Clés : title, description, image (optionnel)
 */
function genererTwitterCard(array $donnees): string
{
    $e = fn(string $v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

    $image  = $donnees['image'] ?? '';
    $compte = trim(param('twitter_compte'));

    $html  = '<meta name="twitter:card"        content="' . ($image !== '' ? 'summary_large_image' : 'summary') . '">' . "\n";
    $html .= '    <meta name="twitter:title"       content="' . $e($donnees['title'] ?? '') . '">' . "\n";
    $html .= '    <meta name="twitter:description" content="' . $e($donnees['description'] ?? '') . '">';

    if ($compte !== '') {
        $compte = '@' . ltrim($compte, '@');
        $html .= "\n    " . '<meta name="twitter:site" content="' . $e($compte) . '">';
    }
    if ($image !== '') {
        $html .= "\n    " . '<meta name="twitter:image" content="' . $e($image) . '">';
    }

    return $html;
}

/**
 * Balises d'indexation : robots (index/noindex global) + vérifications
 * Google Search Console / Bing Webmaster.
 */
function genererMetaIndexation(): string
{
    $e = fn(string $v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

    $autorise = param('index_autoriser', '1') === '1';
    $robots = $autorise ? 'index, follow' : 'noindex, nofollow';
    $html = '<meta name="robots" content="' . $robots . '">';

    if (($g = trim(param('verif_google'))) !== '') {
        $html .= "\n    " . '<meta name="google-site-verification" content="' . $e($g) . '">';
    }
    if (($b = trim(param('verif_bing'))) !== '') {
        $html .= "\n    " . '<meta name="msvalidate.01" content="' . $e($b) . '">';
    }

    return $html;
}

/**
 * JSON-LD Organization (global, sur toutes les pages publiques).
 * Sans adresse (NAP non configuré) : nom, URL, logo/image, sameAs.
 */
function genererOrganisationJsonLd(): string
{
    $base = rtrim(URL_SITE, '/');

    $sameAs = array_values(array_filter([
        trim(param('social_facebook')),
        trim(param('social_instagram')),
    ]));

    $org = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => param('site_nom', 'Maple Perroquets'),
        'url'      => $base . '/',
    ];
    if (($img = trim(param('partage_image'))) !== '') {
        $org['logo']  = $img;
        $org['image'] = $img;
    }
    if (!empty($sameAs)) {
        $org['sameAs'] = $sameAs;
    }

    return json_encode($org, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * JSON-LD BreadcrumbList à partir d'une liste d'éléments.
 *
 * @param array<int,array{nom:string,url:string}> $elements
 */
function genererBreadcrumbJsonLd(array $elements): string
{
    $items = [];
    foreach (array_values($elements) as $i => $el) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $el['nom'] ?? '',
            'item'     => $el['url'] ?? '',
        ];
    }
    return json_encode([
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Scripts de mesure d'audience à placer dans le <head> :
 * Google Analytics 4, Google Tag Manager, Meta (Facebook) Pixel.
 * N'émet que ce qui est configuré.
 */
function genererScriptsAnalytics(): string
{
    $e = fn(string $v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    $html = '';

    if (($ga = trim(param('ga4_id'))) !== '') {
        $gaJs = rawurlencode($ga);
        $html .= "\n" . '    <script async src="https://www.googletagmanager.com/gtag/js?id=' . $gaJs . '"></script>' . "\n";
        $html .= '    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","' . $e($ga) . '");</script>';
    }

    if (($gtm = trim(param('gtm_id'))) !== '') {
        $html .= "\n" . '    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start":new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";j.async=true;j.src="https://www.googletagmanager.com/gtm.js?id="+i+dl;f.parentNode.insertBefore(j,f);})(window,document,"script","dataLayer","' . $e($gtm) . '");</script>';
    }

    if (($px = trim(param('pixel_id'))) !== '') {
        $html .= "\n" . '    <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,"script","https://connect.facebook.net/en_US/fbevents.js");fbq("init","' . $e($px) . '");fbq("track","PageView");</script>';
    }

    return $html;
}

/**
 * Partie <body> du Google Tag Manager (balise noscript), si configuré.
 */
function genererGtmNoscript(): string
{
    $gtm = trim(param('gtm_id'));
    if ($gtm === '') {
        return '';
    }
    $e = htmlspecialchars($gtm, ENT_QUOTES, 'UTF-8');
    return '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . $e
        . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
}

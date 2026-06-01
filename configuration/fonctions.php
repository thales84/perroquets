<?php
require_once __DIR__ . '/config.php';

/**
 * Transforme un texte en slug URL.
 * Exemple : "Gris du Gabon élevé à la main" → "gris-du-gabon-eleve-a-la-main"
 *
 * @param  string $texte Texte source
 * @return string        Slug en minuscules, sans accents, sans caractères spéciaux
 */
function genererSlug(string $texte): string
{
    // Translittération des caractères accentués vers ASCII
    $de  = ['à','â','ä','á','ã','å','æ','ç','è','é','ê','ë','î','ï','ì','í',
            'ô','ö','ò','ó','õ','ø','œ','ù','ú','û','ü','ÿ','ñ',
            'À','Â','Ä','Á','Ã','Å','Æ','Ç','È','É','Ê','Ë','Î','Ï','Ì','Í',
            'Ô','Ö','Ò','Ó','Õ','Ø','Œ','Ù','Ú','Û','Ü','Ÿ','Ñ'];
    $vers = ['a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i',
             'o','o','o','o','o','o','oe','u','u','u','u','y','n',
             'a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i',
             'o','o','o','o','o','o','oe','u','u','u','u','y','n'];

    $texte = str_replace($de, $vers, $texte);
    $texte = strtolower($texte);
    // Remplacer tout caractère non alphanumérique par un tiret
    $texte = preg_replace('/[^a-z0-9]+/', '-', $texte);
    // Supprimer les tirets en début et fin
    return trim($texte, '-');
}

/**
 * Échappe une valeur pour affichage HTML sécurisé.
 * À utiliser sur toute donnée affichée dans un template.
 *
 * @param  mixed  $valeur Valeur à échapper (convertie en string)
 * @return string         Valeur échappée
 */
function echapper(mixed $valeur): string
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Formate un montant en prix canadien-français.
 * Exemple : 1250 → "1 250,00 $"
 *           null  → "Prix sur demande"
 *
 * @param  float|int|null $montant Montant en dollars canadiens
 * @return string                  Prix formaté
 */
function formaterPrixCad(float|int|null $montant): string
{
    if ($montant === null) {
        return function_exists('t') ? t('prix_sur_demande') : 'Prix sur demande';
    }

    // number_format : séparateur de milliers = espace, décimale = virgule
    return number_format((float) $montant, 2, ',', "\u{202F}") . ' $';
}

/**
 * Formate une date MySQL (YYYY-MM-DD) en français lisible.
 * Exemple : "2023-04-15" → "15 avril 2023"
 *
 * @param  string|null $date Date au format YYYY-MM-DD
 * @return string            Date formatée ou chaîne vide si null
 */
function formaterDate(?string $date): string
{
    if ($date === null || $date === '') {
        return '';
    }

    $mois = [
        1  => 'janvier', 2  => 'février',  3  => 'mars',
        4  => 'avril',   5  => 'mai',       6  => 'juin',
        7  => 'juillet', 8  => 'août',      9  => 'septembre',
        10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
    ];

    $ts = strtotime($date);
    if ($ts === false) {
        return '';
    }

    return (int) date('j', $ts) . ' ' . $mois[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Effectue une redirection HTTP et stoppe l'exécution.
 * Préfixe automatiquement URL_SITE pour les URLs relatives.
 *
 * @param string $url URL absolue ou relative (commence par /)
 */
function rediriger(string $url): never
{
    if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
        $url = URL_SITE . $url;
    }

    header('Location: ' . $url, true, 302);
    exit;
}

/**
 * Récupère un segment d'URL réécrite transmis via $_GET.
 * Nettoie la valeur (trim, strip_tags).
 *
 * @param  string $cle     Nom du paramètre GET
 * @param  string $defaut  Valeur par défaut si absent
 * @return string          Valeur nettoyée
 */
function obtenirParametreUrl(string $cle, string $defaut = ''): string
{
    if (!isset($_GET[$cle])) {
        return $defaut;
    }

    return trim(strip_tags($_GET[$cle]));
}

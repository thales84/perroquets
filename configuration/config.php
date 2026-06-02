<?php
/**
 * Configuration — VERSIONNÉE et déployée (ce fichier ne contient AUCUN secret).
 *
 * Les identifiants de base de données (secrets) sont chargés depuis un fichier
 * situé HORS de l'arborescence web et HORS du dépôt : il n'est donc jamais
 * déployé, écrasé ni supprimé par un déploiement.
 *   - En production : un niveau au-dessus de public_html → perroquets-secret.php
 *   - En local      : configuration/config-secret.php (optionnel)
 * Voir configuration/config-secret.exemple.php pour le modèle.
 *
 * Si aucun fichier secret n'est trouvé, on retombe sur les variables
 * d'environnement puis sur des valeurs par défaut (pratiques en local).
 */

// 1) Charger les secrets (define BD_*, URL_SITE, MODE_DEVELOPPEMENT) si présents.
$__racineWeb = $_SERVER['DOCUMENT_ROOT'] ?? '';
if ($__racineWeb === '') {
    $__racineWeb = dirname(__DIR__); // contexte CLI (lint, scripts)
}
$__candidatsSecret = [
    dirname($__racineWeb) . '/perroquets-secret.php', // prod : hors public_html
    __DIR__ . '/config-secret.php',                   // local (optionnel)
];
foreach ($__candidatsSecret as $__fichierSecret) {
    if (is_file($__fichierSecret)) {
        require $__fichierSecret;
        break;
    }
}

// 2) Repli : variable d'environnement si définie, sinon valeur par défaut.
function env_ou(string $cle, string $defaut): string
{
    $valeur = getenv($cle);
    return ($valeur !== false && $valeur !== '') ? $valeur : $defaut;
}

$__hote    = $_SERVER['HTTP_HOST'] ?? '';
$__estLocal = str_contains($__hote, 'localhost') || str_contains($__hote, '127.0.0.1');

// --- Base de données ---
if (!defined('BD_HOTE'))         define('BD_HOTE',         env_ou('BD_HOTE', 'localhost'));
if (!defined('BD_NOM'))          define('BD_NOM',          env_ou('BD_NOM', 'perroquets'));
if (!defined('BD_UTILISATEUR'))  define('BD_UTILISATEUR',  env_ou('BD_UTILISATEUR', 'root'));
if (!defined('BD_MOT_DE_PASSE')) define('BD_MOT_DE_PASSE', env_ou('BD_MOT_DE_PASSE', ''));

// --- URL publique du site (sans barre oblique finale) ---
if (!defined('URL_SITE')) {
    define('URL_SITE', env_ou('URL_SITE', $__estLocal ? 'http://localhost/perroquets' : 'https://mapleperroquets.com'));
}

// --- Chemins ---
if (!defined('CHEMIN_MEDIAS')) define('CHEMIN_MEDIAS', '/medias/oiseaux/');

// --- Langue par défaut ---
if (!defined('LANGUE_PAR_DEFAUT')) define('LANGUE_PAR_DEFAUT', 'fr');

// --- Mode développement : ON en local, OFF ailleurs (surchargeable) ---
if (!defined('MODE_DEVELOPPEMENT')) {
    define('MODE_DEVELOPPEMENT', env_ou('MODE_DEV', $__estLocal ? '1' : '0') === '1');
}

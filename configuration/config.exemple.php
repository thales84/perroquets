<?php
/**
 * Modèle de configuration — copier en config.php et remplir les vraies valeurs.
 *
 * Ce fichier (config.exemple.php) est versionné.
 * config.php n'est NI versionné (voir .gitignore) NI déployé : il est exclu
 * du déploiement FTP, donc le fichier présent sur le serveur de production
 * n'est jamais écrasé par un déploiement.
 *
 * Deux façons de renseigner les valeurs sensibles :
 *  1) les écrire en dur dans config.php (cas classique en mutualisé) ;
 *  2) les laisser lire depuis les variables d'environnement via getenv()
 *     si l'hébergeur en fournit (SetEnv .htaccess, panneau cPanel, etc.).
 * Les valeurs ci-dessous utilisent l'option 2 avec repli sur l'option 1.
 */

// Variable d'environnement si définie et non vide, sinon valeur par défaut.
function env_ou(string $cle, string $defaut): string
{
    $valeur = getenv($cle);
    return ($valeur !== false && $valeur !== '') ? $valeur : $defaut;
}

// --- Base de données ---
define('BD_HOTE',          env_ou('BD_HOTE', 'localhost'));
define('BD_NOM',           env_ou('BD_NOM', 'perroquets'));
define('BD_UTILISATEUR',   env_ou('BD_UTILISATEUR', 'root'));
define('BD_MOT_DE_PASSE',  env_ou('BD_MOT_DE_PASSE', ''));

// --- URL publique du site (sans barre oblique finale) ---
// En local : http://localhost/perroquets
define('URL_SITE', env_ou('URL_SITE', 'https://mapleperroquets.com'));

// --- Chemins ---
define('CHEMIN_MEDIAS', '/medias/oiseaux/');

// --- Langue par défaut ---
define('LANGUE_PAR_DEFAUT', 'fr');

// --- Mode développement ---
// Détecté automatiquement : activé sur localhost/127.0.0.1, désactivé ailleurs.
// Surchargeable explicitement via la variable d'environnement MODE_DEV (1 ou 0).
$hote = $_SERVER['HTTP_HOST'] ?? '';
$estLocal = str_contains($hote, 'localhost') || str_contains($hote, '127.0.0.1');
define('MODE_DEVELOPPEMENT', env_ou('MODE_DEV', $estLocal ? '1' : '0') === '1');

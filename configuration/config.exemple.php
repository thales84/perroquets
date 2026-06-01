<?php
/**
 * Modèle de configuration — copier en config.php et remplir les vraies valeurs.
 * Ce fichier est versionné. config.php ne l'est PAS (voir .gitignore).
 */

// --- Base de données ---
define('BD_HOTE',          'localhost');
define('BD_NOM',           'perroquets');
define('BD_UTILISATEUR',   'root');
define('BD_MOT_DE_PASSE',  '');

// --- URL publique du site (sans barre oblique finale) ---
// En local, remplacer par : http://localhost/perroquets
define('URL_SITE', 'https://mapleperroquets.com');

// --- Chemins ---
define('CHEMIN_MEDIAS', '/medias/oiseaux/');

// --- Langue par défaut ---
define('LANGUE_PAR_DEFAUT', 'fr');

// --- Mode développement ---
// true  → affiche les erreurs PHP (local uniquement)
// false → masque les erreurs au visiteur (production)
define('MODE_DEVELOPPEMENT', false);

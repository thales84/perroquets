<?php
/**
 * MODÈLE de fichier secret — à copier hors du dépôt et à remplir.
 *
 * Ce fichier ne contient que les valeurs sensibles. config.php le charge
 * automatiquement s'il existe, puis complète le reste.
 *
 * EMPLACEMENTS attendus (le premier trouvé est utilisé) :
 *   - PRODUCTION : un niveau AU-DESSUS de public_html, nommé exactement
 *       perroquets-secret.php
 *       ex. /home/u783045005/domains/mapleperroquets.com/perroquets-secret.php
 *       → hors web (non servi), hors déploiement (jamais écrasé/supprimé).
 *   - LOCAL (optionnel) : configuration/config-secret.php (gitignored).
 *
 * NE JAMAIS committer le fichier réel ni le placer dans public_html.
 */

// --- Identifiants base de données ---
define('BD_HOTE',          'localhost');
define('BD_NOM',           'u783045005_parrots');
define('BD_UTILISATEUR',   'u783045005_parrots');
define('BD_MOT_DE_PASSE',  'REMPLACER_PAR_LE_MOT_DE_PASSE_BD');

// --- URL publique (sans barre oblique finale) ---
define('URL_SITE', 'https://mapleperroquets.com');

// --- Mode développement : false en production ---
define('MODE_DEVELOPPEMENT', false);

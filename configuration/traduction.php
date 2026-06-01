<?php
require_once __DIR__ . '/config.php';

// Tableau de traductions chargé une seule fois
$_traductions     = [];
$_langueActive    = LANGUE_PAR_DEFAUT;
$_traductionsFr   = [];

/**
 * Initialise le système de traduction.
 * Détecte la langue depuis $_GET['langue'] (injecté par .htaccess),
 * charge le fichier correspondant, et prépare le repli vers le français.
 */
function initialiserTraduction(): void
{
    global $_traductions, $_langueActive, $_traductionsFr;

    $languesDisponibles = ['fr', 'en'];
    $langue = isset($_GET['langue']) ? trim($_GET['langue']) : LANGUE_PAR_DEFAUT;

    if (!in_array($langue, $languesDisponibles, true)) {
        $langue = LANGUE_PAR_DEFAUT;
    }

    $_langueActive = $langue;

    // Charger le français (toujours, sert de repli)
    $_traductionsFr = require __DIR__ . '/../langues/fr.php';

    if ($langue === 'fr') {
        $_traductions = $_traductionsFr;
    } else {
        $fichier = __DIR__ . '/../langues/' . $langue . '.php';
        if (file_exists($fichier)) {
            $_traductions = require $fichier;
        } else {
            $_traductions = $_traductionsFr;
        }
    }
}

/**
 * Retourne le libellé traduit pour une clé donnée.
 * Repli automatique vers le français si la valeur est absente ou vide.
 *
 * @param  string $cle Clé de traduction
 * @return string      Libellé traduit
 */
function t(string $cle): string
{
    global $_traductions, $_traductionsFr;

    $valeur = $_traductions[$cle] ?? '';

    // Repli vers français si vide (langue non encore traduite)
    if ($valeur === '' || $valeur === null) {
        $valeur = $_traductionsFr[$cle] ?? $cle;
    }

    return $valeur;
}

/**
 * Retourne le code de langue actif (ex. 'fr', 'en').
 */
function langueActive(): string
{
    global $_langueActive;
    return $_langueActive;
}

// Initialisation automatique à l'inclusion
initialiserTraduction();

<?php
/**
 * Garde d'accès — à inclure en première ligne de chaque page admin protégée.
 * Démarre la session et redirige vers la connexion si non authentifié.
 */

// Chemin absolu vers la racine du projet
define('RACINE', dirname(__DIR__, 2));

require_once RACINE . '/configuration/config.php';
require_once RACINE . '/configuration/connexion.php';
require_once RACINE . '/configuration/fonctions.php';

// Configuration des cookies de session (avant session_start)
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict',
    // 'secure' => true, // activer en production HTTPS
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie que l'administrateur est connecté.
 * Redirige vers la connexion si la session est absente ou expirée.
 */
function verifierAdminConnecte(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . URL_SITE . '/admin/connexion.php');
        exit;
    }
}

verifierAdminConnecte();

/**
 * Génère (ou récupère) un jeton CSRF stocké en session.
 */
function genererJetonCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie que le jeton CSRF soumis est valide.
 */
function verifierJetonCsrf(string $jeton): bool
{
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $jeton);
}

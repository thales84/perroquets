<?php
/**
 * Helpers de session pour les comptes clients publics.
 * À inclure en tête des pages nécessitant une authentification.
 */

function demarrerSessionClient(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => false, // passer à true en production (HTTPS)
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function clientConnecte(): bool
{
    demarrerSessionClient();
    return !empty($_SESSION['client_id']);
}

function idClientConnecte(): ?int
{
    return clientConnecte() ? (int) $_SESSION['client_id'] : null;
}

/**
 * Redirige vers /fr/connexion si le client n'est pas connecté.
 */
function exigerConnexionClient(string $langue = 'fr'): void
{
    if (!clientConnecte()) {
        header('Location: ' . URL_SITE . '/' . $langue . '/connexion');
        exit;
    }
}

function genererJetonCsrfClient(): string
{
    demarrerSessionClient();
    if (empty($_SESSION['csrf_client'])) {
        $_SESSION['csrf_client'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_client'];
}

function verifierJetonCsrfClient(string $jeton): bool
{
    demarrerSessionClient();
    return isset($_SESSION['csrf_client']) && hash_equals($_SESSION['csrf_client'], $jeton);
}

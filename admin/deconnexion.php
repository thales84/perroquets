<?php
define('RACINE', dirname(__DIR__));
require_once RACINE . '/configuration/config.php';
require_once RACINE . '/configuration/fonctions.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruction propre de la session
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

header('Location: ' . URL_SITE . '/admin/connexion.php');
exit;

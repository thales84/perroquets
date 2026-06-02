<?php
$langue = langueActive();

// Déconnexion uniquement via POST + CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifierJetonCsrfClient($_POST['csrf_token'] ?? '')) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

header('Location: ' . URL_SITE . '/' . $langue . '/');
exit;

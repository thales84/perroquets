<?php
define('RACINE', dirname(__DIR__));

require_once RACINE . '/configuration/config.php';
require_once RACINE . '/configuration/connexion.php';
require_once RACINE . '/configuration/fonctions.php';

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

// Déjà connecté → tableau de bord
if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . URL_SITE . '/admin/');
    exit;
}

$erreur   = '';
$MAX_TENTATIVES = 5;
$DELAI_BLOCAGE  = 300; // secondes

// --- Limitation des tentatives ---
$tentatives    = $_SESSION['connexion_tentatives']   ?? 0;
$dernierEchec  = $_SESSION['connexion_dernier_echec'] ?? 0;

$bloque = $tentatives >= $MAX_TENTATIVES
    && (time() - $dernierEchec) < $DELAI_BLOCAGE;

// --- Traitement POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$bloque) {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $motDePasse  = $_POST['mot_de_passe'] ?? '';

    if ($identifiant !== '' && $motDePasse !== '') {
        $pdo = obtenirConnexion();
        $req = $pdo->prepare("SELECT id_admin, mot_de_passe_hash FROM admin WHERE identifiant = :identifiant LIMIT 1");
        $req->execute([':identifiant' => $identifiant]);
        $admin = $req->fetch();

        if ($admin && password_verify($motDePasse, $admin['mot_de_passe_hash'])) {
            // Succès : réinitialiser compteur, ouvrir session
            unset($_SESSION['connexion_tentatives'], $_SESSION['connexion_dernier_echec']);
            session_regenerate_id(true);
            $_SESSION['admin_id']         = $admin['id_admin'];
            $_SESSION['admin_identifiant'] = htmlspecialchars($identifiant, ENT_QUOTES, 'UTF-8');

            header('Location: ' . URL_SITE . '/admin/');
            exit;
        } else {
            // Échec : incrémenter compteur
            $_SESSION['connexion_tentatives']   = $tentatives + 1;
            $_SESSION['connexion_dernier_echec'] = time();
            $erreur = 'Identifiant ou mot de passe incorrect.';
        }
    } else {
        $erreur = 'Veuillez remplir tous les champs.';
    }
}

if ($bloque) {
    $resteSecondes = $DELAI_BLOCAGE - (time() - $dernierEchec);
    $erreur = 'Trop de tentatives. Réessayez dans ' . ceil($resteSecondes / 60) . ' minute(s).';
}
?>
<!DOCTYPE html>
<html lang="fr-CA" data-theme="clair">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Admin Maple Perroquets</title>
    <meta name="robots" content="noindex, nofollow">
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            if (t) document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <link rel="stylesheet" href="<?= echapper(URL_SITE) ?>/ressources/css/style.css">
    <link rel="stylesheet" href="<?= echapper(URL_SITE) ?>/ressources/css/admin.css">
</head>
<body class="page-connexion">

<main id="contenu-principal">
    <div class="connexion-enveloppe">
        <h1>🦜 Administration</h1>
        <p class="connexion-sous-titre">Maple Perroquets</p>

        <?php if ($erreur) : ?>
            <div class="resa-erreur-global" role="alert"><?= echapper($erreur) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= echapper(URL_SITE) ?>/admin/connexion.php" class="resa-formulaire" novalidate>

            <div class="champ">
                <label for="identifiant">Identifiant</label>
                <input type="text" id="identifiant" name="identifiant"
                       autocomplete="username"
                       required
                       <?= $bloque ? 'disabled' : '' ?>>
            </div>

            <div class="champ">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe"
                       autocomplete="current-password"
                       required
                       <?= $bloque ? 'disabled' : '' ?>>
            </div>

            <button type="submit" class="bouton bouton-primaire" style="width:100%"
                    <?= $bloque ? 'disabled' : '' ?>>
                Se connecter
            </button>

        </form>
    </div>
</main>

<script src="<?= echapper(URL_SITE) ?>/ressources/js/theme.js"></script>
</body>
</html>

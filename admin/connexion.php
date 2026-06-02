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

<button class="bouton-theme connexion-theme" id="bouton-theme" type="button" aria-label="Basculer le thème">
    <span class="icone-theme" aria-hidden="true">☀️</span>
</button>

<main id="contenu-principal" class="connexion-page">
    <div class="connexion-carte">

        <aside class="connexion-marque">
            <div class="connexion-marque-fond"></div>
            <div class="connexion-marque-contenu">
                <div class="connexion-logo" aria-hidden="true">🦜</div>
                <h2>Maple Perroquets</h2>
                <p>Console d’administration</p>
                <ul class="connexion-atouts">
                    <li>🦜 Gestion des oiseaux &amp; espèces</li>
                    <li>📋 Suivi des réservations</li>
                    <li>🔒 Accès sécurisé et chiffré</li>
                </ul>
            </div>
        </aside>

        <section class="connexion-panneau">
            <header class="connexion-tete">
                <h1>Connexion</h1>
                <p class="connexion-sous-titre">Accédez à votre tableau de bord</p>
            </header>

            <?php if ($erreur) : ?>
                <div class="alerte alerte--erreur" role="alert"><?= echapper($erreur) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= echapper(URL_SITE) ?>/admin/connexion.php" class="connexion-formulaire" novalidate>

                <div class="champ-icone">
                    <label for="identifiant">Identifiant</label>
                    <div class="champ-icone-rangee">
                        <span class="champ-icone-symbole" aria-hidden="true">👤</span>
                        <input type="text" id="identifiant" name="identifiant"
                               autocomplete="username" required
                               <?= $bloque ? 'disabled' : '' ?>>
                    </div>
                </div>

                <div class="champ-icone">
                    <label for="mot_de_passe">Mot de passe</label>
                    <div class="champ-icone-rangee">
                        <span class="champ-icone-symbole" aria-hidden="true">🔒</span>
                        <input type="password" id="mot_de_passe" name="mot_de_passe"
                               autocomplete="current-password" required
                               <?= $bloque ? 'disabled' : '' ?>>
                        <button type="button" class="champ-oeil" id="bascule-mdp"
                                aria-label="Afficher le mot de passe"
                                <?= $bloque ? 'disabled' : '' ?>>👁️</button>
                    </div>
                </div>

                <button type="submit" class="bouton bouton-primaire connexion-soumettre"
                        <?= $bloque ? 'disabled' : '' ?>>
                    Se connecter
                </button>

            </form>

            <p class="connexion-pied">🔒 Réservé au personnel autorisé</p>
        </section>

    </div>
</main>

<script src="<?= echapper(URL_SITE) ?>/ressources/js/theme.js"></script>
<script>
(function () {
    var bouton = document.getElementById('bascule-mdp');
    var champ  = document.getElementById('mot_de_passe');
    if (!bouton || !champ) return;
    bouton.addEventListener('click', function () {
        var visible = champ.type === 'password';
        champ.type = visible ? 'text' : 'password';
        bouton.classList.toggle('actif', visible);
        bouton.setAttribute('aria-label', visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
    });
})();
</script>
</body>
</html>

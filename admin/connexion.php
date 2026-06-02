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
<html lang="fr-CA">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — Admin Maple Perroquets</title>
<meta name="robots" content="noindex, nofollow">
<style>
/* ============================================================
   Page de connexion admin — styles autonomes (aucune dépendance)
   ============================================================ */
:root {
    --lg-fond1:   #f0eade;
    --lg-fond2:   #e7ddcd;
    --lg-carte:   #ffffff;
    --lg-texte:   #1f2420;
    --lg-discret: #6b7268;
    --lg-bordure: #e0d8c9;
    --lg-champ:   #f7f4ee;
    --lg-vert:    #2d7a4f;
    --lg-vert-fonce: #1f5638;
    --lg-vert-clair: #3a945f;
    --lg-ambre:   #e07b39;
    --lg-erreur-fond: #fbe9e7;
    --lg-erreur-txt:  #b3261e;
    --lg-erreur-bord: #f3c5bf;
    --lg-ombre:   0 20px 50px -18px rgba(31, 86, 56, 0.45);
}
@media (prefers-color-scheme: dark) {
    :root:not([data-theme="clair"]) {
        --lg-fond1:   #141a16;
        --lg-fond2:   #0e1310;
        --lg-carte:   #1c241e;
        --lg-texte:   #e8ede9;
        --lg-discret: #9aaa9c;
        --lg-bordure: #2f3a31;
        --lg-champ:   #232d26;
        --lg-vert:    #5ab87f;
        --lg-vert-fonce: #3a945f;
        --lg-vert-clair: #7acf98;
        --lg-ambre:   #f09a5a;
        --lg-erreur-fond: #3a201d;
        --lg-erreur-txt:  #f3b0a8;
        --lg-erreur-bord: #5e2f2a;
        --lg-ombre:   0 20px 50px -18px rgba(0, 0, 0, 0.6);
    }
}
[data-theme="sombre"] {
    --lg-fond1:   #141a16;
    --lg-fond2:   #0e1310;
    --lg-carte:   #1c241e;
    --lg-texte:   #e8ede9;
    --lg-discret: #9aaa9c;
    --lg-bordure: #2f3a31;
    --lg-champ:   #232d26;
    --lg-vert:    #5ab87f;
    --lg-vert-fonce: #3a945f;
    --lg-vert-clair: #7acf98;
    --lg-ambre:   #f09a5a;
    --lg-erreur-fond: #3a201d;
    --lg-erreur-txt:  #f3b0a8;
    --lg-erreur-bord: #5e2f2a;
    --lg-ombre:   0 20px 50px -18px rgba(0, 0, 0, 0.6);
}

* { box-sizing: border-box; }
html, body { height: 100%; }
body.lg-body {
    margin: 0;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    color: var(--lg-texte);
    background:
        radial-gradient(900px 500px at 85% -10%, color-mix(in srgb, var(--lg-vert) 22%, transparent), transparent 60%),
        radial-gradient(700px 450px at 0% 110%, color-mix(in srgb, var(--lg-ambre) 18%, transparent), transparent 60%),
        linear-gradient(160deg, var(--lg-fond1), var(--lg-fond2));
    min-height: 100dvh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}

.lg-theme {
    position: fixed;
    top: 1rem;
    right: 1rem;
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 50%;
    border: 1px solid var(--lg-bordure);
    background: var(--lg-carte);
    color: var(--lg-texte);
    font-size: 1.1rem;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: transform 0.15s;
}
.lg-theme:hover { transform: rotate(20deg); }

.lg-carte {
    width: 100%;
    max-width: 410px;
    background: var(--lg-carte);
    border: 1px solid var(--lg-bordure);
    border-radius: 20px;
    box-shadow: var(--lg-ombre);
    padding: 2.5rem 2.25rem 2rem;
    animation: lg-apparition 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
}
@keyframes lg-apparition {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

.lg-logo {
    width: 60px;
    height: 60px;
    margin: 0 auto 1.1rem;
    display: grid;
    place-items: center;
    font-size: 1.9rem;
    border-radius: 16px;
    color: #fff;
    background: linear-gradient(140deg, var(--lg-vert-clair), var(--lg-vert-fonce));
    box-shadow: 0 8px 20px -6px color-mix(in srgb, var(--lg-vert) 60%, transparent);
}
.lg-titre {
    text-align: center;
    font-size: 1.45rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
}
.lg-sous-titre {
    text-align: center;
    color: var(--lg-discret);
    font-size: 0.92rem;
    margin: 0 0 1.6rem;
}

.lg-alerte {
    background: var(--lg-erreur-fond);
    color: var(--lg-erreur-txt);
    border: 1px solid var(--lg-erreur-bord);
    border-radius: 10px;
    padding: 0.7rem 0.9rem;
    font-size: 0.88rem;
    margin-bottom: 1.2rem;
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.lg-champ { margin-bottom: 1.1rem; }
.lg-champ label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
}
.lg-saisie {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--lg-champ);
    border: 1.5px solid var(--lg-bordure);
    border-radius: 11px;
    padding: 0 0.85rem;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.lg-saisie:focus-within {
    border-color: var(--lg-vert);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--lg-vert) 20%, transparent);
}
.lg-saisie .lg-icone { font-size: 1rem; opacity: 0.6; }
.lg-saisie input {
    flex: 1;
    border: 0;
    outline: 0;
    background: transparent;
    color: var(--lg-texte);
    font-size: 0.97rem;
    padding: 0.8rem 0;
    min-width: 0;
}
.lg-saisie input:disabled { opacity: 0.6; cursor: not-allowed; }
.lg-oeil {
    border: 0;
    background: transparent;
    cursor: pointer;
    font-size: 1rem;
    opacity: 0.5;
    padding: 0.2rem;
    transition: opacity 0.15s;
}
.lg-oeil:hover, .lg-oeil.actif { opacity: 1; }

.lg-bouton {
    width: 100%;
    margin-top: 0.4rem;
    padding: 0.85rem;
    border: 0;
    border-radius: 11px;
    font-size: 1rem;
    font-weight: 600;
    color: #fff;
    cursor: pointer;
    background: linear-gradient(135deg, var(--lg-vert-clair), var(--lg-vert-fonce));
    transition: filter 0.15s, transform 0.05s;
}
.lg-bouton:hover { filter: brightness(1.07); }
.lg-bouton:active { transform: translateY(1px); }
.lg-bouton:disabled { opacity: 0.55; cursor: not-allowed; filter: none; }

.lg-pied {
    margin: 1.5rem 0 0;
    text-align: center;
    font-size: 0.78rem;
    color: var(--lg-discret);
}
</style>
<script>
(function () {
    var t = localStorage.getItem('theme');
    if (t) document.documentElement.setAttribute('data-theme', t);
})();
</script>
</head>
<body class="lg-body">

<button class="lg-theme" id="lg-theme" type="button" aria-label="Basculer le thème clair/sombre">☀️</button>

<main class="lg-carte">
    <div class="lg-logo" aria-hidden="true">🦜</div>
    <h1 class="lg-titre">Administration</h1>
    <p class="lg-sous-titre">Maple Perroquets — accès réservé</p>

    <?php if ($erreur) : ?>
        <div class="lg-alerte" role="alert"><span aria-hidden="true">⚠️</span><span><?= echapper($erreur) ?></span></div>
    <?php endif; ?>

    <form method="post" action="<?= echapper(URL_SITE) ?>/admin/connexion.php" novalidate>

        <div class="lg-champ">
            <label for="identifiant">Identifiant</label>
            <div class="lg-saisie">
                <span class="lg-icone" aria-hidden="true">👤</span>
                <input type="text" id="identifiant" name="identifiant"
                       autocomplete="username" autofocus required
                       <?= $bloque ? 'disabled' : '' ?>>
            </div>
        </div>

        <div class="lg-champ">
            <label for="mot_de_passe">Mot de passe</label>
            <div class="lg-saisie">
                <span class="lg-icone" aria-hidden="true">🔒</span>
                <input type="password" id="mot_de_passe" name="mot_de_passe"
                       autocomplete="current-password" required
                       <?= $bloque ? 'disabled' : '' ?>>
                <button type="button" class="lg-oeil" id="lg-oeil"
                        aria-label="Afficher le mot de passe"
                        <?= $bloque ? 'disabled' : '' ?>>👁️</button>
            </div>
        </div>

        <button type="submit" class="lg-bouton" <?= $bloque ? 'disabled' : '' ?>>
            Se connecter
        </button>

    </form>

    <p class="lg-pied">🔒 Connexion sécurisée — personnel autorisé uniquement</p>
</main>

<script>
/* Bascule afficher / masquer le mot de passe */
(function () {
    var oeil  = document.getElementById('lg-oeil');
    var champ = document.getElementById('mot_de_passe');
    if (oeil && champ) {
        oeil.addEventListener('click', function () {
            var visible = champ.type === 'password';
            champ.type = visible ? 'text' : 'password';
            oeil.classList.toggle('actif', visible);
            oeil.setAttribute('aria-label', visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });
    }
})();

/* Bascule thème clair / sombre (mémorisé) */
(function () {
    var bouton = document.getElementById('lg-theme');
    if (!bouton) return;
    function majIcone() {
        var sombre = document.documentElement.getAttribute('data-theme') === 'sombre';
        bouton.textContent = sombre ? '🌙' : '☀️';
    }
    majIcone();
    bouton.addEventListener('click', function () {
        var actuel = document.documentElement.getAttribute('data-theme');
        var prochain = actuel === 'sombre' ? 'clair' : 'sombre';
        document.documentElement.setAttribute('data-theme', prochain);
        localStorage.setItem('theme', prochain);
        majIcone();
    });
})();
</script>
</body>
</html>

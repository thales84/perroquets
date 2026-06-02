<?php
require_once __DIR__ . '/../modeles/client-modele.php';

$langue = langueActive();

// Déjà connecté → tableau de bord
if (clientConnecte()) {
    header('Location: ' . URL_SITE . '/' . $langue . '/mon-compte');
    exit;
}

const MAX_TENTATIVES_CLIENT = 5;
const DELAI_BLOCAGE_CLIENT  = 300; // secondes

$erreur  = '';
$bloque  = false;
$email   = '';

// Vérification du blocage
if (!empty($_SESSION['client_tentatives_blocage']) && time() < $_SESSION['client_tentatives_blocage']) {
    $bloque = true;
    $resteSecondes = $_SESSION['client_tentatives_blocage'] - time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$bloque) {

    if (!verifierJetonCsrfClient($_POST['csrf_token'] ?? '')) {
        $erreur = 'Jeton de sécurité invalide. Rechargez la page.';
    } else {
        $email = trim($_POST['email']        ?? '');
        $mdp   = trim($_POST['mot_de_passe'] ?? '');

        $client = ($email !== '' && $mdp !== '') ? authentifierClient($email, $mdp) : null;

        if ($client) {
            // Succès — réinitialiser compteur et ouvrir session
            unset($_SESSION['client_tentatives'], $_SESSION['client_tentatives_blocage']);
            session_regenerate_id(true);
            $_SESSION['client_id']     = (int) $client['id_client'];
            $_SESSION['client_prenom'] = $client['prenom'];

            // Rediriger vers l'URL d'origine si demandée, sinon tableau de bord
            $retour = filter_var($_GET['retour'] ?? '', FILTER_SANITIZE_URL);
            $retour = (str_starts_with($retour, '/') && !str_starts_with($retour, '//')) ? $retour : '/' . $langue . '/mon-compte';
            header('Location: ' . URL_SITE . $retour);
            exit;
        } else {
            $_SESSION['client_tentatives'] = ($_SESSION['client_tentatives'] ?? 0) + 1;
            if ($_SESSION['client_tentatives'] >= MAX_TENTATIVES_CLIENT) {
                $_SESSION['client_tentatives_blocage'] = time() + DELAI_BLOCAGE_CLIENT;
                $bloque = true;
                $resteSecondes = DELAI_BLOCAGE_CLIENT;
                $erreur = '';
            } else {
                $restantes = MAX_TENTATIVES_CLIENT - $_SESSION['client_tentatives'];
                $erreur = 'Identifiant ou mot de passe incorrect. ' . $restantes . ' tentative' . ($restantes > 1 ? 's' : '') . ' restante' . ($restantes > 1 ? 's' : '') . '.';
            }
        }
    }
}

$csrf = genererJetonCsrfClient();

$titrePage       = 'Connexion';
$descriptionPage = 'Connectez-vous à votre compte Maple Perroquets pour suivre vos réservations.';

require_once __DIR__ . '/../gabarits/entete.php';
?>

<div class="page-auth">
    <div class="auth-carte">
        <h1 class="auth-titre">Se connecter</h1>
        <p class="auth-sous">Accédez à votre tableau de bord et suivez vos réservations.</p>

        <?php if ($bloque): ?>
        <div class="auth-erreur" role="alert">
            Trop de tentatives. Réessayez dans <?= ceil($resteSecondes / 60) ?> minute<?= $resteSecondes > 60 ? 's' : '' ?>.
        </div>
        <?php elseif ($erreur !== ''): ?>
        <div class="auth-erreur" role="alert"><?= echapper($erreur) ?></div>
        <?php endif; ?>

        <?php if (!empty($_GET['inscrit'])): ?>
        <div class="auth-succes">Compte créé avec succès. Connectez-vous.</div>
        <?php endif; ?>

        <?php if (!$bloque): ?>
        <form method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">

            <div class="champ">
                <label for="email">Adresse courriel</label>
                <input type="email" id="email" name="email"
                       value="<?= echapper($email) ?>"
                       autocomplete="email" required autofocus>
            </div>

            <div class="champ">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe"
                       autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn-primaire btn-full" style="margin-top:.5rem;">
                Me connecter
            </button>
        </form>
        <?php endif; ?>

        <p class="auth-lien">
            Pas encore de compte ? <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/inscription">Créer un compte</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

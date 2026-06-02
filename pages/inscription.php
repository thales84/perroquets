<?php
require_once __DIR__ . '/../modeles/client-modele.php';

$langue = langueActive();

// URL de retour après connexion (ex. : page réservation)
$retour = filter_var($_GET['retour'] ?? $_POST['retour'] ?? '', FILTER_SANITIZE_URL);
$retour = (str_starts_with($retour, '/') && !str_starts_with($retour, '//')) ? $retour : '';

// Déjà connecté → retour ou tableau de bord
if (clientConnecte()) {
    header('Location: ' . URL_SITE . ($retour ?: '/' . $langue . '/mon-compte'));
    exit;
}

$erreurs = [];
$valeurs = ['prenom' => '', 'nom' => '', 'email' => '', 'province' => '', 'telephone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifierJetonCsrfClient($_POST['csrf_token'] ?? '')) {
        $erreurs['_global'] = 'Jeton de sécurité invalide. Rechargez la page.';
    } else {
        $valeurs['prenom']    = trim($_POST['prenom']    ?? '');
        $valeurs['nom']       = trim($_POST['nom']       ?? '');
        $valeurs['email']     = trim($_POST['email']     ?? '');
        $valeurs['province']  = trim($_POST['province']  ?? '');
        $valeurs['telephone'] = trim($_POST['telephone'] ?? '');
        $mdp                  = $_POST['mot_de_passe']         ?? '';
        $mdpConfirm           = $_POST['mot_de_passe_confirm'] ?? '';

        if ($valeurs['prenom'] === '')                               $erreurs['prenom']    = 'Champ requis.';
        if ($valeurs['nom']    === '')                               $erreurs['nom']       = 'Champ requis.';
        if ($valeurs['email']  === '')                               $erreurs['email']     = 'Champ requis.';
        elseif (!filter_var($valeurs['email'], FILTER_VALIDATE_EMAIL)) $erreurs['email']   = 'Adresse courriel invalide.';
        if (strlen($mdp) < 8)                                        $erreurs['mdp']       = 'Minimum 8 caractères.';
        elseif ($mdp !== $mdpConfirm)                                $erreurs['mdp']       = 'Les mots de passe ne correspondent pas.';

        if (empty($erreurs)) {
            $idClient = inscrireClient([
                'prenom'       => $valeurs['prenom'],
                'nom'          => $valeurs['nom'],
                'email'        => $valeurs['email'],
                'mot_de_passe' => $mdp,
                'province'     => $valeurs['province'],
                'telephone'    => $valeurs['telephone'],
            ]);

            if ($idClient === false) {
                $erreurs['email'] = 'Cette adresse courriel est déjà utilisée.';
            } else {
                // Connexion automatique après inscription
                session_regenerate_id(true);
                $_SESSION['client_id']     = $idClient;
                $_SESSION['client_prenom'] = $valeurs['prenom'];
                // Rediriger vers la page d'origine si définie, sinon le tableau de bord
                $destination = $retour ?: '/' . $langue . '/mon-compte';
                header('Location: ' . URL_SITE . $destination);
                exit;
            }
        }
    }
}

$csrf = genererJetonCsrfClient();

$titrePage       = 'Créer un compte';
$descriptionPage = 'Créez votre compte Maple Perroquets pour suivre vos réservations.';

require_once __DIR__ . '/../gabarits/entete.php';

$provinces = ['AB','BC','MB','NB','NL','NS','NT','NU','ON','PE','QC','SK','YT'];
$nomsProvinces = [
    'AB'=>'Alberta','BC'=>'Colombie-Britannique','MB'=>'Manitoba','NB'=>'Nouveau-Brunswick',
    'NL'=>'Terre-Neuve-et-Labrador','NS'=>'Nouvelle-Écosse','NT'=>'Territoires du Nord-Ouest',
    'NU'=>'Nunavut','ON'=>'Ontario','PE'=>'Île-du-Prince-Édouard','QC'=>'Québec',
    'SK'=>'Saskatchewan','YT'=>'Yukon',
];
?>

<div class="page-auth">
    <div class="auth-carte">
        <h1 class="auth-titre">Créer un compte</h1>
        <p class="auth-sous">Suivez vos réservations et accélérez vos prochaines demandes.</p>

        <?php if (!empty($erreurs['_global'])): ?>
        <div class="auth-erreur" role="alert"><?= echapper($erreurs['_global']) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">
            <?php if ($retour): ?>
            <input type="hidden" name="retour" value="<?= echapper($retour) ?>">
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 1rem;">
                <div class="champ <?= isset($erreurs['prenom']) ? 'champ--erreur' : '' ?>">
                    <label for="prenom">Prénom *</label>
                    <input type="text" id="prenom" name="prenom"
                           value="<?= echapper($valeurs['prenom']) ?>"
                           autocomplete="given-name" required>
                    <?php if (isset($erreurs['prenom'])): ?>
                    <span class="champ__erreur" role="alert"><?= echapper($erreurs['prenom']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="champ <?= isset($erreurs['nom']) ? 'champ--erreur' : '' ?>">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom"
                           value="<?= echapper($valeurs['nom']) ?>"
                           autocomplete="family-name" required>
                    <?php if (isset($erreurs['nom'])): ?>
                    <span class="champ__erreur" role="alert"><?= echapper($erreurs['nom']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="champ <?= isset($erreurs['email']) ? 'champ--erreur' : '' ?>">
                <label for="email">Adresse courriel *</label>
                <input type="email" id="email" name="email"
                       value="<?= echapper($valeurs['email']) ?>"
                       autocomplete="email" required>
                <?php if (isset($erreurs['email'])): ?>
                <span class="champ__erreur" role="alert"><?= echapper($erreurs['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="champ <?= isset($erreurs['mdp']) ? 'champ--erreur' : '' ?>">
                <label for="mot_de_passe">Mot de passe * <span style="font-weight:400;color:var(--doux)">(min. 8 caractères)</span></label>
                <input type="password" id="mot_de_passe" name="mot_de_passe"
                       autocomplete="new-password" required minlength="8">
                <?php if (isset($erreurs['mdp'])): ?>
                <span class="champ__erreur" role="alert"><?= echapper($erreurs['mdp']) ?></span>
                <?php endif; ?>
            </div>

            <div class="champ">
                <label for="mot_de_passe_confirm">Confirmer le mot de passe *</label>
                <input type="password" id="mot_de_passe_confirm" name="mot_de_passe_confirm"
                       autocomplete="new-password" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 1rem;">
                <div class="champ">
                    <label for="province">Province</label>
                    <select id="province" name="province" autocomplete="address-level1">
                        <option value="">— Optionnel —</option>
                        <?php foreach ($nomsProvinces as $code => $nom): ?>
                        <option value="<?= echapper($code) ?>"
                            <?= $valeurs['province'] === $code ? 'selected' : '' ?>>
                            <?= echapper($nom) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="champ">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone"
                           value="<?= echapper($valeurs['telephone']) ?>"
                           autocomplete="tel" placeholder="514-555-0192">
                </div>
            </div>

            <button type="submit" class="btn btn-primaire btn-full" style="margin-top:.5rem;">
                Créer mon compte
            </button>
        </form>

        <p class="auth-lien">
            Déjà un compte ? <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/connexion">Se connecter</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

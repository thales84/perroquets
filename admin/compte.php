<?php
require_once __DIR__ . '/inc/securite.php';

$erreurs = [];
$succes  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierJetonCsrf($_POST['csrf_token'] ?? '')) {
        $erreurs['_global'] = 'Jeton de sécurité invalide. Réessayez.';
    } else {
        $actuel       = $_POST['mot_de_passe_actuel'] ?? '';
        $nouveau      = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirmation = $_POST['confirmation_mot_de_passe'] ?? '';

        $pdo = obtenirConnexion();
        $req = $pdo->prepare("SELECT mot_de_passe_hash FROM admin WHERE id_admin = :id LIMIT 1");
        $req->execute([':id' => $_SESSION['admin_id']]);
        $admin = $req->fetch();

        if (!$admin) {
            $erreurs['_global'] = 'Compte introuvable.';
        } elseif ($actuel === '' || $nouveau === '' || $confirmation === '') {
            $erreurs['_global'] = 'Veuillez remplir tous les champs.';
        } elseif (!password_verify($actuel, $admin['mot_de_passe_hash'])) {
            $erreurs['mot_de_passe_actuel'] = 'Mot de passe actuel incorrect.';
        } elseif (mb_strlen($nouveau) < 10) {
            $erreurs['nouveau_mot_de_passe'] = 'Le nouveau mot de passe doit contenir au moins 10 caractères.';
        } elseif ($nouveau !== $confirmation) {
            $erreurs['confirmation_mot_de_passe'] = 'La confirmation ne correspond pas.';
        } elseif (password_verify($nouveau, $admin['mot_de_passe_hash'])) {
            $erreurs['nouveau_mot_de_passe'] = 'Le nouveau mot de passe doit être différent de l\'actuel.';
        } else {
            $hash = password_hash($nouveau, PASSWORD_DEFAULT);
            $maj  = $pdo->prepare("UPDATE admin SET mot_de_passe_hash = :hash WHERE id_admin = :id");
            $maj->execute([':hash' => $hash, ':id' => $_SESSION['admin_id']]);
            // Nouvelle session après changement de mot de passe (anti-fixation)
            session_regenerate_id(true);
            $succes = true;
        }
    }
}

$titrePage = 'Mon compte';
require_once __DIR__ . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Mon compte</h1>
    </div>

    <?php if ($succes) : ?>
        <div class="alerte alerte--succes" role="status">Mot de passe modifié avec succès.</div>
    <?php endif; ?>

    <?php if (!empty($erreurs['_global'])) : ?>
        <div class="alerte alerte--erreur" role="alert"><?= echapper($erreurs['_global']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= echapper(URL_SITE) ?>/admin/compte.php" class="formulaire-admin" autocomplete="off" novalidate>
        <input type="hidden" name="csrf_token" value="<?= echapper(genererJetonCsrf()) ?>">

        <h2 style="margin-top:0">Changer le mot de passe</h2>

        <div class="champ <?= isset($erreurs['mot_de_passe_actuel']) ? 'champ--erreur' : '' ?>">
            <label for="mot_de_passe_actuel">Mot de passe actuel</label>
            <input type="password" id="mot_de_passe_actuel" name="mot_de_passe_actuel"
                   autocomplete="current-password" required>
            <?php if (!empty($erreurs['mot_de_passe_actuel'])) : ?>
                <span class="champ__erreur"><?= echapper($erreurs['mot_de_passe_actuel']) ?></span>
            <?php endif; ?>
        </div>

        <div class="champ <?= isset($erreurs['nouveau_mot_de_passe']) ? 'champ--erreur' : '' ?>">
            <label for="nouveau_mot_de_passe">Nouveau mot de passe</label>
            <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe"
                   autocomplete="new-password" minlength="10" required>
            <small class="texte-discret">Au moins 10 caractères.</small>
            <?php if (!empty($erreurs['nouveau_mot_de_passe'])) : ?>
                <span class="champ__erreur"><?= echapper($erreurs['nouveau_mot_de_passe']) ?></span>
            <?php endif; ?>
        </div>

        <div class="champ <?= isset($erreurs['confirmation_mot_de_passe']) ? 'champ--erreur' : '' ?>">
            <label for="confirmation_mot_de_passe">Confirmer le nouveau mot de passe</label>
            <input type="password" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe"
                   autocomplete="new-password" minlength="10" required>
            <?php if (!empty($erreurs['confirmation_mot_de_passe'])) : ?>
                <span class="champ__erreur"><?= echapper($erreurs['confirmation_mot_de_passe']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="bouton bouton-primaire">Mettre à jour le mot de passe</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/inc/pied-admin.php'; ?>

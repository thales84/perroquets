<?php
$titrePage       = 'Page introuvable';
$descriptionPage = 'La page que vous cherchez n\'existe pas ou a été déplacée.';

if (!headers_sent()) {
    http_response_code(404);
}

require_once __DIR__ . '/../gabarits/entete.php';
?>

<div class="conteneur texte-centre">
    <div class="erreur-404">
        <p class="erreur-404__icone" aria-hidden="true">🦜</p>
        <h1>404 — Page introuvable</h1>
        <p>La page que vous cherchez n'existe pas ou a été déplacée.</p>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper(langueActive()) ?>/"
           class="bouton bouton-primaire">
            <?= echapper(t('nav_accueil')) ?>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

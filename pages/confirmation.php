<?php
$titrePage       = t('resa_confirmation');
$descriptionPage = t('resa_confirmation');

require_once __DIR__ . '/../gabarits/entete.php';
$langue = langueActive();
?>

<div class="conteneur texte-centre">
    <div class="confirmation">
        <p class="confirmation__icone" aria-hidden="true">✅</p>
        <h1><?= echapper(t('resa_confirmation')) ?></h1>
        <p>Nous avons bien reçu votre demande et vous répondrons dans les 48 heures.</p>
        <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"
           class="bouton bouton-primaire marge-bas-1">
            <?= echapper(t('nav_oiseaux')) ?>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

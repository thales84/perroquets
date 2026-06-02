<?php
$titrePage       = 'Demande envoyée — Merci !';
$descriptionPage = t('resa_confirmation');
$langue          = langueActive();

require_once __DIR__ . '/../gabarits/entete.php';
?>

<div class="page-confirmation">
    <div class="confirmation-icone" aria-hidden="true">✅</div>
    <h1 class="confirmation-titre">Votre demande a été envoyée !</h1>
    <p class="confirmation-texte">
        Nous avons bien reçu votre demande de réservation. Nous vous répondrons
        sous 48&nbsp;h pour organiser la suite, sans aucun paiement précipité.
    </p>
    <a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"
       class="btn btn-jungle btn-lg">Voir les autres oiseaux</a>
</div>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

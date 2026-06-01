<?php
$titrePage       = t('nav_accueil');
$descriptionPage = t('meta_description_defaut');

require_once __DIR__ . '/../gabarits/entete.php';
?>

<section class="heros">
    <h1><?= echapper(t('accueil_heros_titre')) ?></h1>
    <p><?= echapper(t('accueil_heros_sous_titre')) ?></p>
    <a href="<?= echapper(URL_SITE) ?>/<?= echapper(langueActive()) ?>/oiseaux"
       class="bouton bouton-secondaire"><?= echapper(t('accueil_voir_oiseaux')) ?></a>
</section>

<div class="conteneur">
    <section class="marge-bas-2">
        <h2><?= echapper(t('accueil_pourquoi_titre')) ?></h2>
        <p><?= echapper(t('accueil_pourquoi_texte')) ?></p>
    </section>
</div>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>

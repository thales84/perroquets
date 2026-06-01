<?php
// Pied de page commun — inclus à la fin de chaque page publique.
$langue = langueActive();
?>
</main>

<footer class="pied-site">
    <div class="pied-interieur">
        <div class="pied-section">
            <p class="pied-logo">🦜 Maple Perroquets</p>
            <p><?= echapper(t('pied_slogan')) ?></p>
            <p><?= echapper(t('pied_slogan2')) ?></p>
        </div>

        <div class="pied-section">
            <h3><?= echapper(t('pied_contact')) ?></h3>
            <address>
                <p>Québec, Canada</p>
                <p><a href="mailto:info@mapleperroquets.com">info@mapleperroquets.com</a></p>
            </address>
        </div>

        <div class="pied-section">
            <h3><?= echapper(t('pied_navigation')) ?></h3>
            <ul>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/"><?= echapper(t('nav_accueil')) ?></a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux"><?= echapper(t('nav_oiseaux')) ?></a></li>
            </ul>
        </div>
    </div>

    <div class="pied-bas">
        <p>&copy; <?= date('Y') ?> Maple Perroquets. <?= echapper(t('pied_droits')) ?></p>
        <p><?= echapper(t('pied_prix_cad')) ?></p>
    </div>
</footer>

<script src="<?= echapper(URL_SITE) ?>/ressources/js/theme.js"></script>
<script src="<?= echapper(URL_SITE) ?>/ressources/js/menu-mobile.js"></script>
</body>
</html>

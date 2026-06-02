<?php $langue = langueActive(); ?>
</main>

<!-- ============================================================
     Footer
     ============================================================ -->
<footer class="footer">
    <div class="footer-inner">
        <div>
            <span class="footer-logo">🦜 Maple Perroquets</span>
            <p class="footer-pitch">
                Éleveur passionné au Québec. Nos perroquets sont élevés à la main
                avec amour et expertise depuis plus de 10&nbsp;ans.
            </p>
        </div>
        <div class="footer-col">
            <div class="footer-col-titre">Espèces</div>
            <ul>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Aras</a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Gris du Gabon</a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Cacatoès</a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Éclectus</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <div class="footer-col-titre">Aide</div>
            <ul>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/">Comment ça marche</a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/">Préparer l'accueil</a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Voir les oiseaux</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <div class="footer-col-titre">Contact</div>
            <ul>
                <li><a href="mailto:bonjour@mapleperroquets.com">bonjour@mapleperroquets.com</a></li>
                <li><a href="<?= echapper(URL_SITE) ?>/<?= echapper($langue) ?>/oiseaux">Québec, Canada 🍁</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bas">
        <span>&copy; <?= date('Y') ?> Maple Perroquets — Tous droits réservés.</span>
        <span>Élevage déclaré · Conforme à la réglementation canadienne sur la faune</span>
    </div>
</footer>

<?= genererBoutonWhatsapp() ?>

<script src="<?= echapper(URL_SITE) ?>/assets/js/main.js"></script>
</body>
</html>

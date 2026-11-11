<?php
$langue = langueActive();
// Coordonnées gérées dans l'admin (Paramètres > Contact & WhatsApp).
$coord  = coordonneesEntreprise();
$lieu   = implode(', ', array_filter([$coord['ville'], $coord['province_nom']]));
?>
</main>

<!-- ============================================================
     Footer
     ============================================================ -->
<footer class="footer">
    <div class="footer-inner">
        <div>
            <span class="footer-logo">🦜 <?= echapper($coord['nom']) ?></span>
            <p class="footer-pitch">
                <?= echapper($lieu !== '' ? str_replace('%lieu%', $lieu, t('pied_pitch_lieu')) : t('pied_slogan')) ?>
                <?= echapper($coord['annee'] !== '' ? str_replace('%annee%', $coord['annee'], t('pied_pitch_depuis')) : t('pied_slogan2')) ?>
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
            <div class="footer-col-titre"><?= echapper(t('pied_contact')) ?></div>
            <ul>
                <?php if ($coord['courriel'] !== '') : ?>
                    <li><a href="mailto:<?= echapper($coord['courriel']) ?>"><?= echapper($coord['courriel']) ?></a></li>
                <?php endif; ?>
                <?php if ($coord['telephone_lien'] !== '') : ?>
                    <li><a href="tel:<?= echapper($coord['telephone_lien']) ?>"><?= echapper($coord['telephone']) ?></a></li>
                <?php endif; ?>
                <li>
                    <address class="footer-adresse">
                        <?php if ($coord['rue'] !== '') : ?><?= echapper($coord['rue']) ?><br><?php endif; ?>
                        <?php if ($lieu !== '') : ?><?= echapper(trim($lieu . ' ' . $coord['code_postal'])) ?><br><?php endif; ?>
                        <?= echapper(t('pied_pays')) ?> 🍁
                    </address>
                </li>
            </ul>
        </div>
    </div>
    <div class="footer-bas">
        <span>&copy; <?= date('Y') ?> <?= echapper($coord['nom']) ?> — <?= echapper(t('pied_droits')) ?></span>
        <span>Élevage déclaré · Conforme à la réglementation canadienne sur la faune</span>
    </div>
</footer>

<?= genererBoutonWhatsapp() ?>

<script src="<?= echapper(URL_SITE) ?>/assets/js/main.js"></script>
</body>
</html>

<?php // Pied de page admin ?>
        </main>

        <footer class="pied-admin">
            <p>Maple Perroquets &mdash; Administration &mdash; <?= date('Y') ?></p>
        </footer>

    </div><!-- /.admin-zone -->
</div><!-- /.admin-shell -->

<script src="<?= echapper(URL_SITE) ?>/ressources/js/theme.js"></script>
<script>
/* Sidebar mobile — toggle */
(function () {
    var burger  = document.getElementById('admin-burger');
    var fermer  = document.getElementById('admin-sidebar-fermer');
    var sidebar = document.getElementById('admin-sidebar');
    var overlay = document.getElementById('admin-overlay');
    if (!burger || !sidebar) return;

    function ouvrir() {
        sidebar.classList.add('ouverte');
        if (overlay) overlay.classList.add('visible');
        document.body.style.overflow = 'hidden';
    }
    function ferme() {
        sidebar.classList.remove('ouverte');
        if (overlay) overlay.classList.remove('visible');
        document.body.style.overflow = '';
    }
    burger.addEventListener('click', ouvrir);
    if (fermer)  fermer.addEventListener('click', ferme);
    if (overlay) overlay.addEventListener('click', ferme);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') ferme();
    });
})();
</script>
</body>
</html>

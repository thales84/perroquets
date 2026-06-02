/* ================================================================
   Maple Perroquets — JS natif principal
   ================================================================ */

/* ----------------------------------------------------------------
   Thème clair / sombre
   ---------------------------------------------------------------- */
(function () {
    var btn  = document.getElementById('btn-theme');
    var html = document.documentElement;
    if (!btn) return;

    function themeActuel() {
        var attr = html.getAttribute('data-theme');
        if (attr === 'sombre' || attr === 'clair') return attr;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'sombre' : 'clair';
    }

    function appliquerIcone(theme) {
        btn.textContent = theme === 'sombre' ? '☀' : '🌙';
        btn.setAttribute('aria-label', theme === 'sombre' ? 'Passer en mode clair' : 'Passer en mode sombre');
    }

    appliquerIcone(themeActuel());

    btn.addEventListener('click', function () {
        var nouveau = themeActuel() === 'sombre' ? 'clair' : 'sombre';
        html.setAttribute('data-theme', nouveau);
        localStorage.setItem('mp-theme', nouveau);
        appliquerIcone(nouveau);
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
        if (!localStorage.getItem('mp-theme')) appliquerIcone(themeActuel());
    });
})();

/* ----------------------------------------------------------------
   IntersectionObserver — animation reveal
   ---------------------------------------------------------------- */
(function () {
    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) { e.target.classList.add('in'); obs.unobserve(e.target); }
        });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal').forEach(function (el) { obs.observe(el); });
})();

/* ----------------------------------------------------------------
   Menu mobile — panel slide-from-right
   ---------------------------------------------------------------- */
(function () {
    var btnOpen    = document.getElementById('hamburger');
    var btnClose   = document.getElementById('nav-fermer');
    var overlay    = document.getElementById('nav-overlay');
    var panel      = document.getElementById('nav-mobile');
    if (!btnOpen || !panel) return;

    function ouvrir() {
        panel.removeAttribute('hidden');
        /* micro delay pour que la transition CSS se déclenche */
        requestAnimationFrame(function () {
            panel.classList.add('ouverte');
            if (overlay) overlay.classList.add('visible');
        });
        btnOpen.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        /* Focus sur le bouton fermer */
        if (btnClose) setTimeout(function () { btnClose.focus(); }, 50);
    }

    function fermer() {
        panel.classList.remove('ouverte');
        if (overlay) overlay.classList.remove('visible');
        btnOpen.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        /* Remettre hidden après la transition */
        panel.addEventListener('transitionend', function handler() {
            panel.setAttribute('hidden', '');
            panel.removeEventListener('transitionend', handler);
        });
        btnOpen.focus();
    }

    /* Ouvrir */
    btnOpen.addEventListener('click', ouvrir);

    /* Fermer via bouton ✕ */
    if (btnClose) btnClose.addEventListener('click', fermer);

    /* Fermer via l'overlay */
    if (overlay) overlay.addEventListener('click', fermer);

    /* Fermer avec Echap */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panel.classList.contains('ouverte')) fermer();
    });

    /* Fermer quand on clique un lien du panel (navigation interne) */
    panel.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', fermer);
    });
})();

/* ----------------------------------------------------------------
   Boutons favoris — bascule ♡ / ♥
   ---------------------------------------------------------------- */
document.querySelectorAll('.btn-fav').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var actif = btn.dataset.actif === 'true';
        btn.dataset.actif = actif ? 'false' : 'true';
        btn.textContent   = actif ? '♡' : '♥';
    });
});

/* ----------------------------------------------------------------
   Galerie miniatures (fiche oiseau)
   ---------------------------------------------------------------- */
(function () {
    var principale = document.getElementById('photo-principale');
    if (!principale) return;
    document.querySelectorAll('.galerie-miniature').forEach(function (btn) {
        btn.addEventListener('click', function () {
            principale.style.opacity = '0';
            setTimeout(function () {
                principale.src = btn.dataset.src;
                principale.alt = btn.dataset.alt;
                principale.style.opacity = '1';
            }, 150);
            document.querySelectorAll('.galerie-miniature').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
        });
    });
})();

/* ----------------------------------------------------------------
   Filtres liste — soumission automatique au changement de select
   ---------------------------------------------------------------- */
(function () {
    document.querySelectorAll('.filtres-auto select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            sel.closest('form').submit();
        });
    });
})();

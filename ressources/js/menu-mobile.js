/**
 * Menu hamburger — mobile.
 * Ouvre/ferme la navigation principale, met à jour aria-expanded.
 */
(function () {
    var bouton = document.getElementById('bouton-hamburger');
    var nav    = document.getElementById('navigation-principale');
    if (!bouton || !nav) return;

    bouton.addEventListener('click', function () {
        var ouvert = bouton.getAttribute('aria-expanded') === 'true';
        bouton.setAttribute('aria-expanded', ouvert ? 'false' : 'true');
        bouton.setAttribute('aria-label', ouvert ? 'Ouvrir le menu' : 'Fermer le menu');
        nav.classList.toggle('ouverte', !ouvert);
    });

    // Fermer le menu si on clique en dehors
    document.addEventListener('click', function (e) {
        if (!bouton.contains(e.target) && !nav.contains(e.target)) {
            bouton.setAttribute('aria-expanded', 'false');
            bouton.setAttribute('aria-label', 'Ouvrir le menu');
            nav.classList.remove('ouverte');
        }
    });

    // Fermer le menu à la redimension vers bureau
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            bouton.setAttribute('aria-expanded', 'false');
            nav.classList.remove('ouverte');
        }
    });
})();

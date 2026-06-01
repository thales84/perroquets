/**
 * Bascule thème clair/sombre.
 * Lit localStorage en priorité, sinon prefers-color-scheme.
 * Sauvegarde le choix manuel dans localStorage.
 */
(function () {
    var bouton = document.getElementById('bouton-theme');
    if (!bouton) return;

    var icone = bouton.querySelector('.icone-theme');

    function obtenirThemeActuel() {
        return document.documentElement.getAttribute('data-theme') || 'clair';
    }

    function appliquerTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        if (icone) {
            icone.textContent = theme === 'sombre' ? '🌙' : '☀️';
        }
        bouton.setAttribute('aria-label', theme === 'sombre' ? 'Passer au thème clair' : 'Passer au thème sombre');
    }

    // Initialiser l'icône selon le thème déjà appliqué (par l'anti-flash de entete.php)
    appliquerTheme(obtenirThemeActuel());

    bouton.addEventListener('click', function () {
        var actuel   = obtenirThemeActuel();
        var nouveau  = actuel === 'sombre' ? 'clair' : 'sombre';
        appliquerTheme(nouveau);
        localStorage.setItem('theme', nouveau);
    });
})();

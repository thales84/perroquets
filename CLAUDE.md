# Projet : mapleperroquets.com — vitrine de vente de perroquets (Canada)

Site vitrine + réservation (pas de paiement en ligne). Chaque oiseau est un
individu unique : une fois réservé/vendu, il disparaît de la vitrine.
Domaine de production : mapleperroquets.com

Stack : PHP vanilla + PDO, MySQL, JS natif, CSS natif, Apache (.htaccess).
Spécifications complètes : docs/00-cahier-des-charges.md (à lire si besoin).

## Règles non négociables
- Fichiers nommés en français, code et commentaires en français.
- Requêtes préparées PDO partout. htmlspecialchars() sur tout affichage.
- SEO dès le début, responsive mobile-first, thème clair/sombre adaptatif.
- Cible Canada : devise CAD (prix_cad), lang="fr-CA", provinces canadiennes.
- Bilingue par conception, français à l'exécution : colonnes _fr/_en en base
  (_en vides au départ), URLs préfixées /fr/, libellés d'interface via t().
  Ne jamais coder de libellé d'interface en dur.

## Méthode de travail (économie de tokens)
- On avance étape par étape selon docs/NN-*.md.
- Tu implémentes UNE seule étape à la fois, celle que je nomme.
- Tu ne lis PAS les autres fichiers docs/ (sauf docs/00 si nécessaire).
- Tu travailles sur les fichiers que je nomme ; tu ne fouilles pas le projet.
- À la fin, tu vérifies les critères de validation listés dans le .md de l'étape.

## Domaine et URLs
- URL de base : https://mapleperroquets.com
- Sitemap : https://mapleperroquets.com/sitemap.xml
- Exemples de routes : /fr/oiseaux, /fr/oiseau/{slug}, /fr/reservation/{slug}

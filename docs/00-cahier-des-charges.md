# 00 — Cahier des charges

> **Fichier de référence.** Toutes les autres étapes (`01` à `12`) s'appuient sur ce document.
> Avant chaque session d'implémentation, relire les conventions ci-dessous et les appliquer sans exception.

---

## 1. Présentation du projet

Site **vitrine + réservation** pour la vente de perroquets élevés à la main, destiné au **marché canadien**.

Le site n'est **pas** une boutique avec paiement en ligne. Le visiteur consulte les oiseaux disponibles et envoie une **demande de réservation** sur un oiseau précis. Le propriétaire traite ensuite la demande hors ligne (contact, paiement, remise de l'animal).

Chaque oiseau est un **individu unique** (un exemplaire réel, avec son sexe, son âge et sa bague d'identification). Une fois réservé ou vendu, il ne s'affiche plus dans la vitrine publique.

---

## 2. Pile technique (stack)

Volontairement simple, sans framework ni dépendance lourde.

- **PHP vanilla** (PHP 8.x), accès base via **PDO**.
- **MySQL** comme système de gestion de base de données.
- **JavaScript natif** (vanilla, sans bibliothèque) pour les interactions côté client (thème, menu mobile, galerie).
- **CSS natif** (sans framework type Bootstrap/Tailwind), variables CSS pour le thème.
- **Apache** avec `mod_rewrite` (`.htaccess`) pour les URLs propres. Hébergement mutualisé visé.

Aucun gestionnaire de paquets (npm/composer) n'est requis pour faire fonctionner le site.

---

## 3. Conventions de code

### 3.1 Langue du code
- **Tous les fichiers sont nommés en français** (ex. `liste-oiseaux.php`, `connexion.php`).
- **Tous les commentaires sont rédigés en français**, pour faciliter la maintenance.
- Les noms de variables, fonctions et colonnes de base sont en français (ex. `$prixCad`, `recupererOiseauxDisponibles()`, `date_naissance`).

### 3.2 Style
- Indentation : 4 espaces.
- Noms de fonctions en `camelCase`, noms de tables et colonnes en `snake_case`.
- Chaque fichier débute par un commentaire d'en-tête : rôle du fichier, dépendances, date.
- Chaque fonction est précédée d'un commentaire expliquant son rôle, ses paramètres et sa valeur de retour.

### 3.3 Sécurité (à appliquer partout)
- Toutes les requêtes SQL passent par des **requêtes préparées PDO** (jamais de concaténation de variables dans le SQL).
- Toute donnée affichée venant de la base ou de l'utilisateur est **échappée** avec `htmlspecialchars()`.
- Les mots de passe admin sont hachés avec `password_hash()` et vérifiés avec `password_verify()`.
- L'espace admin est protégé par session ; chaque page admin vérifie l'authentification en tête de fichier.

---

## 4. Cible géographique : Canada

- Devise : **dollar canadien (CAD)**, champ `prix_cad`. Format d'affichage français canadien : `1 250,00 $`.
- Pas de calcul de taxes (vente hors paiement en ligne). Mention « taxes en sus » si nécessaire.
- Langue HTML : `lang="fr-CA"`.
- Formulaire de réservation : téléphone au format nord-américain `+1 (XXX) XXX-XXXX`, champ **province** canadienne.
- Données structurées Schema.org : `priceCurrency: "CAD"`.

---

## 5. Bilinguisme : « bilingue par conception, français à l'exécution »

Règle centrale, à respecter dans **toutes** les étapes :

- La **base de données** contient toutes les colonnes `_fr` **et** `_en`. Les colonnes `_en` restent **vides** au démarrage.
- Les **URLs** portent le préfixe `/fr/` dès maintenant (ex. `/fr/oiseau/gris-du-gabon-male-2024`). L'ajout futur de `/en/` ne doit casser aucune URL existante.
- Le code lit **toujours** les libellés d'interface via la **fonction de traduction** (voir `04-systeme-multilingue.md`), **jamais en dur**.
- `lang/fr.php` est rempli ; `lang/en.php` est créé en **squelette vide**, prêt à remplir.
- Le **back-office** affiche les champs français ; les champs anglais sont **prévus mais masqués/optionnels**.
- Le SEO déclare `hreflang="fr-CA"` ; le `hreflang="en-CA"` est présent mais **commenté**, prêt à activer.

**Objectif : activer l'anglais plus tard = remplir des fichiers de contenu, jamais réécrire du code ni migrer la base.**

---

## 6. SEO (dès le départ, non négociable)

- **URLs propres** via `.htaccess` (pas de `?id=`). Slugs uniques par espèce et par oiseau.
- **Balises méta dynamiques** (`<title>`, `<meta description>`) générées depuis les données, jamais codées en dur.
- **HTML sémantique** : un seul `<h1>` par page, structure `<header>/<nav>/<main>/<article>/<footer>`.
- **Attribut `alt`** obligatoire sur chaque image (champ `texte_alt_fr` en base).
- **Données structurées JSON-LD** Schema.org (`Product` / `Offer`) sur chaque fiche oiseau.
- **Open Graph** (partage Facebook) sur les fiches.
- **sitemap.xml** généré dynamiquement + **robots.txt**.
- **hreflang** déclaré (fr-CA actif, en-CA prêt).

---

## 7. Ergonomie, responsive et thème

### 7.1 Responsive — mobile-first
- Conception **mobile-first** : styles de base pour petit écran, élargissement par `min-width`.
- Grilles fluides (Flexbox / CSS Grid), images fluides.
- Menu **hamburger** sur mobile.
- Images optimisées et **lazy-loading** (`loading="lazy"`) — connexions mobiles variables.

### 7.2 Thème clair / sombre
- Deux jeux de **variables CSS** sous `:root` (clair) et `[data-theme="sombre"]`.
- Détection automatique de la préférence système : `@media (prefers-color-scheme: dark)`.
- **Bouton de bascule** manuel ; choix mémorisé dans `localStorage` (priorité sur la préférence système).
- **Anti-flash** : petit script inline dans le `<head>` qui applique le thème avant le rendu.

---

## 8. Modèle de données (rappel)

Cinq tables. Détail complet et SQL dans `01-base-de-donnees.md`.

```
ESPECE (id_espece, nom_commun_fr, nom_commun_en, nom_scientifique,
        famille_fr, famille_en, slug_fr, slug_en,
        description_fr, description_en)

OISEAU (id_oiseau, slug_fr, slug_en, sexe, date_naissance, num_bague,
        prix_cad, sevre_main, statut, description_fr, description_en,
        date_ajout, id_espece → ESPECE)

PHOTO (id_photo, chemin_fichier, texte_alt_fr, texte_alt_en,
       est_principale, ordre_affichage, id_oiseau → OISEAU)

RESERVATION (id_reservation, nom_client, email_client, telephone,
             province, message, langue_demande, statut_reservation,
             date_demande, id_oiseau → OISEAU)

ADMIN (id_admin, identifiant, mot_de_passe_hash)
```

Valeurs contrôlées :
- `OISEAU.statut` : `disponible | reserve | vendu` (seuls les `disponible` s'affichent en vitrine).
- `OISEAU.sexe` : `male | femelle | inconnu`.
- `OISEAU.sevre_main` : booléen (EAM — argument commercial, champ filtrable).
- `RESERVATION.statut_reservation` : `nouvelle | traitee | annulee`.
- `PHOTO.est_principale` : une seule photo principale par oiseau (règle applicative).

---

## 9. Arborescence du site

```
Espace public (/fr/)
 ├── Accueil               → mise en avant + espèces
 ├── Liste des oiseaux     → oiseaux "disponible", filtres : espèce / sevré main / sexe
 ├── Fiche oiseau          → galerie + détails + formulaire de réservation
 └── Confirmation demande  → accusé de réception

Espace admin (sécurisé par login)
 ├── Connexion
 ├── Tableau de bord       → compteurs (disponibles, réservés, demandes nouvelles)
 ├── Gestion espèces       → CRUD
 ├── Gestion oiseaux       → CRUD + upload photos + changement de statut
 └── Gestion réservations  → liste + changement de statut
```

---

## 10. Ordre d'implémentation des fichiers `.md`

Respecter l'ordre des dépendances :

| # | Fichier | Rôle |
|---|---|---|
| 00 | `00-cahier-des-charges.md` | Ce document (référence) |
| 01 | `01-base-de-donnees.md` | Schéma SQL + jeu de test |
| 02 | `02-configuration.md` | Connexion PDO, constantes, `.htaccess`, utilitaires |
| 03 | `03-gabarit-et-theme.md` | Gabarit HTML, variables CSS, thème, responsive |
| 04 | `04-systeme-multilingue.md` | Fonction de traduction, `lang/fr.php`, `lang/en.php` (vide) |
| 05 | `05-public-liste-oiseaux.md` | Page liste + filtres |
| 06 | `06-public-fiche-oiseau.md` | Fiche + JSON-LD + Open Graph |
| 07 | `07-reservation.md` | Formulaire + validation + confirmation |
| 08 | `08-seo-technique.md` | sitemap.xml, robots.txt, hreflang global |
| 09 | `09-admin-authentification.md` | Connexion sécurisée |
| 10 | `10-admin-especes.md` | CRUD espèces |
| 11 | `11-admin-oiseaux.md` | CRUD oiseaux + photos + statut |
| 12 | `12-admin-reservations.md` | Traitement des demandes |

---

## 11. Rappel pour chaque session Claude Code

À chaque démarrage de session :
1. Lire ce cahier des charges.
2. Lire le fichier `.md` de l'étape en cours.
3. Appliquer **toutes** les conventions : nommage français, commentaires français, sécurité PDO, SEO, responsive, thème, règle bilingue.
4. Produire un code testable et autonome pour l'étape, sans déborder sur les étapes suivantes.

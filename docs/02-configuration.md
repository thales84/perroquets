# 02 — Configuration

> Dépend de : `00`, `01`.
> Produit : connexion à la base, constantes globales, réécriture d'URL, fonctions utilitaires partagées.

---

## Objectif

Mettre en place le socle technique réutilisé par toutes les pages : connexion PDO, configuration centralisée, URLs propres, et les fonctions outils (slug, échappement, formatage du prix).

## Fichiers à produire

```
/configuration/config.php          → constantes globales
/configuration/config.exemple.php  → modèle versionné (sans identifiants réels)
/configuration/connexion.php       → connexion PDO
/configuration/fonctions.php       → fonctions utilitaires
/.htaccess                         → réécriture d'URL Apache
```

---

## `config.php`

Constantes globales (commentées en français) :

- Identifiants base de données :
  - `BD_HOTE` (ex. `localhost`)
  - `BD_NOM` (nom de la base)
  - `BD_UTILISATEUR`
  - `BD_MOT_DE_PASSE`
- **`URL_SITE = 'https://mapleperroquets.com'`** — racine du site, sans barre oblique finale. Utilisée pour les liens absolus, le sitemap, les balises canonical et Open Graph.
- `CHEMIN_MEDIAS = '/medias/oiseaux/'` — dossier des photos d'oiseaux.
- `LANGUE_PAR_DEFAUT = 'fr'`.
- `MODE_DEVELOPPEMENT` (booléen) : si `true`, affiche les erreurs PHP ; si `false` (production), les masque au visiteur.

> Note : en environnement local de développement, `URL_SITE` pourra être surchargée
> (ex. `http://localhost/perroquets`) dans le `config.php` local, qui n'est pas versionné.
> La valeur de production reste `https://mapleperroquets.com`.

**Important** : `config.php` contient les identifiants réels et **ne doit jamais être versionné**.
Produire un `config.exemple.php` identique mais avec des valeurs fictives, lui versionné, qui sert de modèle.
Le `.gitignore` exclut `config.php`.

## `connexion.php`

- Crée une instance **PDO** vers MySQL, encodage `utf8mb4`.
- Mode d'erreur `PDO::ERRMODE_EXCEPTION`.
- Récupération en tableau associatif par défaut (`PDO::FETCH_ASSOC`).
- Fonction `obtenirConnexion()` qui retourne une connexion unique (motif singleton simple : on ne rouvre pas une connexion à chaque appel).
- Gestion d'erreur propre : en production (`MODE_DEVELOPPEMENT = false`), ne jamais afficher les détails techniques au visiteur ; journaliser plutôt et afficher un message générique.

## `fonctions.php`

Fonctions utilitaires, chacune commentée (rôle, paramètres, retour) :

- `genererSlug($texte)` : transforme un texte en slug URL (minuscules, sans accents, tirets). Gère les accents français (é→e, à→a, ç→c, etc.).
- `echapper($valeur)` : raccourci sécurisé pour `htmlspecialchars()` (UTF-8, `ENT_QUOTES`). À utiliser sur **tout** affichage de donnée.
- `formaterPrixCad($montant)` : formate un nombre en `1 250,00 $` (format canadien français : espace comme séparateur de milliers, virgule décimale, symbole `$` après le montant). Retourne « Prix sur demande » si NULL.
- `formaterDate($date)` : format de date lisible en français.
- `rediriger($url)` : redirection HTTP propre (`header('Location: ...')`) + `exit`. Préfixe avec `URL_SITE` si l'URL est relative.
- `obtenirParametreUrl($cle)` : récupère proprement un segment d'URL réécrite.

## `.htaccess`

Réécriture **mod_rewrite** pour des URLs propres et bilingues-prêtes :

- Activer `RewriteEngine On`.
- Préfixe de langue `/fr/` dès maintenant (bloc pour `/en/` présent mais **commenté**).
- Exemples de routes à supporter :
  - `/fr/oiseaux` → page liste
  - `/fr/oiseau/{slug}` → fiche oiseau
  - `/fr/espece/{slug}` → liste filtrée par espèce
  - `/fr/reservation/{slug}` → formulaire de réservation
  - `/sitemap.xml` → `plan-du-site.php` (préparé pour l'étape 08)
- Redirection de la racine `/` vers `/fr/`.
- Forcer l'absence de `.php` visible dans les URLs publiques.
- Protéger les dossiers sensibles (`/configuration/`, `/base-de-donnees/`, `/modeles/`) contre l'accès direct (renvoyer 403).
- Forcer le HTTPS (redirection http → https), cohérent avec `URL_SITE` en https.

---

## Critères de validation

- Une page de test affiche « Connexion réussie » en se connectant à la base.
- `URL_SITE` vaut bien `https://mapleperroquets.com` et sert de base aux liens absolus.
- `genererSlug("Gris du Gabon élevé à la main")` retourne `gris-du-gabon-eleve-a-la-main`.
- `formaterPrixCad(1250)` retourne `1 250,00 $`.
- Une URL propre `/fr/oiseaux` est correctement routée par `.htaccess`.
- L'accès direct à `/configuration/config.php` est refusé (403).
- Commentaires français partout.
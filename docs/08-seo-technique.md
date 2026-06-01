# 08 — SEO technique

> Dépend de : `00`, `02`, `05`, `06`.
> Produit : sitemap dynamique, robots.txt, et finalisation des signaux SEO globaux.

---

## Objectif

Compléter le référencement technique : plan du site automatique, directives robots, et cohérence des balises sur tout le site. C'est ce qui rend le site « propre » aux yeux de Google dès le lancement.

## Fichiers à produire

```
/plan-du-site.php       → génère le sitemap.xml dynamiquement (route /sitemap.xml)
/robots.txt             → directives d'indexation
/configuration/seo.php  → fonctions SEO partagées
```

---

## `plan-du-site.php` (sitemap dynamique)

- En-tête `Content-Type: application/xml`.
- Génère un `<urlset>` conforme au protocole sitemap.
- Inclut automatiquement :
  - Pages fixes : accueil, liste des oiseaux.
  - **Toutes les fiches d'oiseaux disponibles** (URL `/fr/oiseau/{slug_fr}`), récupérées depuis la base.
  - Pages d'espèces.
- Pour chaque URL : `<loc>`, `<lastmod>`, `<changefreq>`, `<priority>`.
- Emplacement prévu (commenté) pour les URLs `/en/` futures.
- Le `.htaccess` (étape 02) doit router `/sitemap.xml` vers ce fichier.

## `robots.txt`

- Autoriser l'indexation des pages publiques.
- **Interdire** : `/admin/`, `/configuration/`, `/base-de-donnees/`, `/modeles/`.
- Indiquer l'URL du sitemap (`Sitemap: {URL_SITE}/sitemap.xml`).

## `seo.php` (fonctions partagées)

- `genererBalisesMeta($titre, $description)` : produit `<title>` + `<meta description>` proprement échappés.
- `genererHreflang($cheminRelatif)` : produit la balise `hreflang fr-CA` (et `en-CA` commentée).
- `genererCanonical($url)` : balise canonique.
- `genererOpenGraph($donnees)` : factorise les balises OG (réutilisable par la fiche oiseau).

> Refactoriser l'étape 03 (entête) et 06 (fiche) pour qu'elles utilisent ces fonctions, afin d'éviter la duplication.

## Vérifications transversales
- Chaque page publique a un `<title>` et une `<meta description>` uniques et pertinents.
- Un seul `<h1>` par page.
- Toutes les images ont un `alt`.
- URLs propres partout (aucune URL en `?id=` exposée).

---

## Critères de validation

- `/sitemap.xml` retourne un XML valide listant les fiches disponibles.
- `robots.txt` bloque bien les dossiers sensibles et pointe vers le sitemap.
- Les balises hreflang, canonical et Open Graph sont présentes et cohérentes.
- Aucune page sensible n'est indexable.

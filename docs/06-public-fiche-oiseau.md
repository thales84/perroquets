# 06 — Page publique : fiche oiseau

> Dépend de : `00`, `02`, `03`, `04`, `05`.
> Produit : la page de détail d'un oiseau, avec galerie photos, SEO avancé et accès à la réservation.

---

## Objectif

Afficher tous les détails d'un oiseau précis, sa galerie de photos, et un bouton de réservation. C'est la page la plus travaillée côté SEO (données structurées, Open Graph).

## Fichiers à produire

```
/oiseau.php                    → fiche détaillée (route /fr/oiseau/{slug})
```

(Réutilise `oiseau-modele.php` de l'étape 05.)

---

## Récupération des données

- Ajouter au modèle : `recupererOiseauParSlug($slug)` (jointure espèce + **toutes** les photos triées par `ordre_affichage`).
- Si le slug n'existe pas → page **404** propre (titre adapté, message via `t()`).
- Si l'oiseau n'est pas `disponible` → afficher une mention claire (ex. « Cet oiseau n'est plus disponible ») et masquer le bouton de réservation. Ne pas faire un 404 brutal : l'URL peut déjà être indexée.

## Contenu de la fiche

- `$titrePage` dynamique : ex. « Gris du Gabon mâle EAM — Perroquet à vendre (Canada) ».
- `$descriptionPage` dynamique : extrait de `description_fr`.
- Un seul `<h1>` : nom commun de l'espèce + caractéristique.
- **Galerie photos** : photo principale en grand + miniatures cliquables (JS simple, sans bibliothèque). `alt` = `texte_alt_fr`, `loading="lazy"`.
- **Tableau de caractéristiques** (libellés via `t()`) : espèce, nom scientifique, sexe, âge calculé depuis `date_naissance`, n° de bague, EAM oui/non, prix formaté CAD.
- `description_fr` affichée (échappée).
- **Bouton « Réserver »** → `/fr/reservation/{slug_fr}` (visible seulement si disponible).

## SEO avancé (spécifique à cette page)

- **JSON-LD Schema.org** `Product` + `Offer` :
  - `name`, `description`, `image`.
  - `offers.price` = prix, `offers.priceCurrency` = `"CAD"`.
  - `offers.availability` selon le statut (`InStock` si disponible).
- **Open Graph** (`og:title`, `og:description`, `og:image`, `og:type`, `og:url`) — pour le partage Facebook.
- Emplacement `hreflang` (fr-CA actif, en-CA commenté).
- URL canonique.

---

## Critères de validation

- `/fr/oiseau/{slug}` d'un oiseau test affiche toutes ses infos et photos.
- Un slug inexistant renvoie une 404 propre.
- Un oiseau `vendu` s'affiche avec mention, sans bouton réserver.
- Le JSON-LD est valide (testable sur l'outil de test des résultats enrichis de Google).
- L'âge est calculé correctement depuis la date de naissance.
- Open Graph présent ; partage Facebook montre titre + image.
